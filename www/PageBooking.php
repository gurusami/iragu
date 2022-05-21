<?php
/*******************************************************************************
Iragu: Badminton Court Management Software

Copyright (C) 2022, Annamalai Gurusami <annamalai.gurusami@gmail.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

*******************************************************************************/
require 'autoload.php';

class PageBooking extends IraguWebapp {
   public $tableCourt;
   public $tableBalance;
   public $tableBooking;
   public $tableSlots;
   public $bookingCost;
   public $tablePassbook;

   function __construct() {
       $this->tableCourt   = new TableCourt();
       $this->tableBalance = new TableBalance($this->mysqli);
       $this->tableSlots = new TableSlots($this->mysqli);
       $this->tableBooking = new TableBooking($this->mysqli);
       $this->tablePassbook = new TablePassbook(null);
   }

   public function clear() {
       unset($_SESSION['court_id']);
       unset($_SESSION['play_date']);
       unset($_SESSION['play_duration']);
       unset($_SESSION['begin_slot']);
       unset($_SESSION['end_slot']);
       unset($_SESSION['show_status']);
   }

   public function isDataComplete() {
       if (!empty($_SESSION['court_id']) &&
           !empty($_SESSION['play_date']) &&
           !empty($_SESSION['play_duration']) &&
           !empty($_SESSION['begin_slot'])) {
               return true;
      }
      return false;
   }

   public function collectCourtId() {
       if (isset($_SESSION['court_id'])) {
           return true;
       }
       if (!isset($_POST['court_id'])) {
           return false;
       }
       $_SESSION['court_id'] = $_POST['court_id'];
       $_SESSION['price_per_slot'] = $_POST['price_per_slot'];
       return true;
   }

   public function collectPlayDate() {
       if (isset($_SESSION['play_date'])) {
           return true;
       }
       if (!isset($_POST['play_date'])) {
           return false;
       }
       $_SESSION['play_date'] = $_POST['play_date'];
       return true;
   }

   public function collectPlayDuration() {
       if (isset($_SESSION['play_duration'])) {
           return true;
       }
       if (!isset($_POST['play_duration'])) {
           return false;
       }
       $_SESSION['play_duration'] = $_POST['play_duration'];
       return true;
   }

   public function collectBeginSlot() {
       if (isset($_SESSION['begin_slot'])) {
           return true;
       }
       if (!isset($_POST['begin_slot'])) {
           return false;
       }
       $_SESSION['begin_slot'] = $_POST['begin_slot'];
       $_SESSION['end_slot']   = $_SESSION['begin_slot'] +
                                 $_SESSION['play_duration'] - 1;
       return true;
   }

   public function work() {
       $this->collectCourtId();
       $this->collectPlayDate();
       $this->collectPlayDuration();
       $this->collectBeginSlot();
       if ($this->isDataComplete()) {
           if ($this->bookCourt()) {
               $this->errno = errno::PASS;
               $this->error = "Court Booking Successful";
           }
           $_SESSION['show_status'] = true;
       }
   }

   public function bookCourt() {
       $this->startTrx();
       $this->tableSlots->mysqli = $this->mysqli;
       $this->tableSlots->nick = $_SESSION['nick'];
       $this->tableSlots->court_id = $_SESSION['court_id'];
       $this->tableSlots->play_date = $_SESSION['play_date'];
       $this->tableSlots->begin_slot = $_SESSION['begin_slot'];
       $this->tableSlots->end_slot = $_SESSION['end_slot'];

       if ($this->tableSlots->checkAndReserveSlots() == false) {
           $this->error .= ": Failed to reserve slots.";
           $this->rollbackTrx();
           return false;
       }

       $this->tableCourt->mysqli = $this->mysqli;
       $this->tableCourt->play_duration = $_SESSION['play_duration'];
       $this->tableCourt->court_id = $_SESSION['court_id'];

       if (($this->bookingCost = $this->tableCourt->calcCost()) == false) {
           $this->errno = errno::FAILED_COST_CALC;
           $this->error = "Cost calculation failed";
           $this->rollbackTrx();
           return false;
       }

       $this->tableBalance->mysqli = $this->mysqli;
       $this->tableBalance->nick = $_SESSION['nick'];
       $this->tableBalance->bookingCost = $this->bookingCost;

       if ($this->tableBalance->checkAndReserveMoney() == false) {
           $this->errno = errno::INSUFFICIENT_BALANCE;
           $this->error = ": Reserve Money Failed ($this->errno)";
           $this->rollbackTrx();
           return false;
       }

       if ($this->tableSlots->bookSlots() == false) {
           $this->error .= ": Booking Slots Failed";
           $this->rollbackTrx();
           return false;
       }

       if ($this->tableBalance->deductMoney() == false) {
           $this->error .= ": Deduct Money Failed";
           $this->rollbackTrx();
           return false;
       }

       $this->tableBooking->mysqli = $this->mysqli;
       $this->tableBooking->bookingCost = $this->bookingCost;

       if ($this->tableBooking->insertBookingID() == false) {
           $this->errno = $this->tableBooking->errno;
           $this->error = $this->tableBooking->error;
           $this->rollbackTrx();
           return false;
       }

       if ( $this->tableBooking->getBookingTime() == false) {
           $this->error .= ": Getting Booking Time Failed";
           $this->rollbackTrx();
           return false;
       }

       $this->tablePassbook->mysqli = $this->mysqli;
       $this->tablePassbook->nick  = $_SESSION['nick'];
       $this->tablePassbook->balance = $this->tableBalance->balance;
       $this->tablePassbook->bookingId = $this->tableBooking->bookingId;
       $this->tablePassbook->debit = $this->bookingCost;

       if ($this->tablePassbook->booking() == false) {
           $this->error .= ": Adding passbook entries failed";
           $this->rollbackTrx();
           return false;
       }
       $this->commitTrx();
       return true;
   }

   public function getDuration() {
       $slot_count = $_SESSION['play_duration'];
       $minutes = $slot_count * 15;
       if ($minutes > 59) {
           $hours = intdiv($minutes, 60);
       }
       $minutes = $minutes % 60;
       if ($hours > 0) {
           return "$hours hr(s) $minutes min(s)";
       } else {
           return "$minutes min(s)";
       }
   }

   public function confirmBooking() {
       $show_time = getTimeDisplay($_SESSION['begin_slot'],
                                   $_SESSION['end_slot']);
       $details = array();
       $details['Booking Id'] = $this->tableBooking->bookingId;
       $details['Booking Time'] = $this->tableBooking->bookingTime;
       $details['Court ID'] = $_SESSION['court_id'];
       $details['Play Date'] = $_SESSION['play_date'];
       $details['Play Duration'] = $this->getDuration();
       $details['Time'] = $show_time;
       ir_table($details, 'booking-table');
   }

   public function viewPage() {
       if (empty($_SESSION['court_id'])) {
           $this->pickCourt();
       } else if (empty($_SESSION['play_date'])) {
           $this->pickDate();
       } else if (empty($_SESSION['play_duration'])) {
           $this->pickDuration();
       } else if (empty($_SESSION['begin_slot'])) {
           $this->pickSlots($_SESSION['court_id'],
                            $_SESSION['play_date'],
                            $_SESSION['play_duration']);
       } else {
           if ($this->errno == 0) {
               $this->confirmBooking();
           }
           $this->clear();
       }
   }
}

?>

