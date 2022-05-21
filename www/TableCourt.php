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

/** A class to handle database operations on table ir_balance. */
class TableCourt {
   public $error;
   public $errno;
   public $mysqli;
   public $court_id;
   public $play_duration;

   public function getAll() {
       $query = "SELECT * FROM ir_court LIMIT 50";
       if (is_null($this->mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       if ($stmt->execute() == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }
       if (($result = $stmt->get_result()) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::NOT_FOUND_RECORD;
           return false;
       }

       $courtArray = array();
       while ($rowObj = $result->fetch_object()) {
           array_push($courtArray, $rowObj);
       }
       $stmt->close();
       return $courtArray;
   }

   public function calcCost() {
       $query = "SELECT price_per_slot * ? FROM ir_court WHERE court_id = ?";

       if (is_null($this->mysqli)) {
           $this->errno = errno::MISSING_PLAY_DURATION;
           $this->error = "PLAY DURATION IS NULL";
           return false;
       }

       if (is_null($this->play_duration)) {
           $this->errno = errno::MISSING_PLAY_DURATION;
           $this->error = "PLAY DURATION IS NULL";
           return false;
       }

       if (is_null($this->court_id)) {
           $this->errno = errno::MISSING_COURT_ID;
           $this->error .= ": COURT ID IS NULL";
           return false;
       }

       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->errno = errno::FAILED_PREPARE;
           $this->error = "Failed to prepare: $query";
           return false;
       }

       if ($stmt->bind_param('is', $this->play_duration,
                                   $this->court_id) == false) {
           $this->errno = errno::FAILED_BINDPARAM;
           $this->error = "Failed to bind params: $query";
           return false;

       }

       if ($stmt->execute() == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }

       if (($result = $stmt->get_result()) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::NOT_FOUND_RECORD;
           return false;
       }

       if (($row = $result->fetch_array()) == false) {
           $this->errno = 1;
           $this->error = "Failed to fetch cost";
           return false;
       }

       if (is_null($row)) {
           $this->errno = 1;
           $this->error = "Failed to fetch cost";
           return false;
       }
       $stmt->close();
       return $row[0];
   }
}

?>



