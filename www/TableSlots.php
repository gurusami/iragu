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

/** A class to handle database operations on table ir_booking_slots. */
class TableSlots {
   public $errno;
   public $error;
   public $mysqli;
   public $nick;
   public $court_id;
   public $play_date;
   public $begin_slot;
   public $end_slot;

   function __construct($mysqli) {
       $this->mysqli = $mysqli;
   }

   public function checkAndReserveSlots() {
       if (is_null($this->mysqli)) {
           die("Invalid db handle");
       }

       $query = "SELECT * FROM ir_booking_slots WHERE court_id = ? AND " .
                "play_date = ? AND play_slot >= ? AND play_slot <= ? " .
                "FOR UPDATE";
       $stmt = $this->mysqli->prepare($query);
       $stmt->bind_param('ssii', $_SESSION['court_id'],
                                 $_SESSION['play_date'],
                                 $_SESSION['begin_slot'],
                                 $_SESSION['end_slot']);
       $stmt->execute();
       $result = $stmt->get_result();
       while ($row = $result->fetch_assoc()) {
           if (!is_null($row['player_id'])) {
               $stmt->close();
               $this->errno = 1;
               $this->error = $this->error . ": Booking Court Failed";
               return false;
           }
       }
       return true;
   }

   public function bookSlots() {
       if (empty($this->nick)) {
           die("Invalid nick");
       }

       $update = "UPDATE ir_booking_slots SET player_id = ? WHERE " .
           " court_id = ? AND play_date = ? AND play_slot >= ? AND " .
           " play_slot <= ?";
       $stmt = $this->mysqli->prepare($update);
       $stmt->bind_param('sssii', $this->nick,
                                  $this->court_id,
                                  $this->play_date,
                                  $this->begin_slot,
                                  $this->end_slot);
       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }
       $stmt->close();
       return true;
   }
}

?>


