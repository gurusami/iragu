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

/** A class to handle database operations on table ir_booking. */
class TableBooking {
   public $bookingId;
   public $bookingCost;
   public $bookingTime;
   public $mysqli;
   public $error;
   public $errno;

   public function getBookingTime() {
       if (is_null($this->mysqli)) {
           $this->errno = errno::INVALID_DBOBJ;
           $this->error = "Invalid db handle";
           return false;
       }

       if (empty($this->bookingId)) {
           $this->errno = errno::INVALID_BOOKING_ID;
           $this->error = ": BOOKING ID IS NULL";
           return false;
       }

       $query = "SELECT booking_time FROM ir_booking WHERE booking_id = ?";

       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('i', $this->bookingId) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return false;
       }

       if ($stmt->execute() == false) {
           $stmt->close();
           $this->error = $thi->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return false;
       }

       $result = $stmt->get_result();
       $row = $result->fetch_array();
       if (is_null($row)) {
           return false;
       }

       $this->bookingTime = $row[0];
       $stmt->close();
       return true;
   }

   public function insertBookingID() {
       if (is_null($this->mysqli)) {
           die("Invalid db handle");
       }

       if (empty($this->bookingCost)) {
           die("Invalid booking cost");
       }

       $query = "INSERT INTO ir_booking (court_id, nick, play_date, " .
           " from_slot, to_slot, price) VALUES (?, ?, ?, ?, ?, ?)";


       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('sssiii', $_SESSION['court_id'],
                                       $_SESSION['nick'],
                                       $_SESSION['play_date'],
                                       $_SESSION['begin_slot'],
                                       $_SESSION['end_slot'],
                                       $this->bookingCost) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return false;
       }

       if ($stmt->execute() == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return false;
       }

       $this->bookingId = $this->mysqli->insert_id;
       $stmt->close();
       return true;
   }
}

?>


