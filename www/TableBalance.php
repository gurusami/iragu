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
class TableBalance {
   public $nick;
   public $balance;
   public $error;
   public $errno;
   public $mysqli;

   function __construct($mysqli) {
       $this->mysqli = $mysqli;
       if (!empty($_SESSION['nick'])) {
           $this->nick = $_SESSION['nick'];
       }
   }

   public function setDB($mysqli) {
       $this->mysqli = $mysqli;
   }

   public function setNickFromSession() {
       if (empty($_SESSION['userid'])) {
           return false;
       }
       $this->nick = $_SESSION['userid'];
       return true;
   }

   public function getCurrentBalance() {
       if (is_null($this->mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       if (empty($this->nick)) {
           $this->error = "Invalid nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       $query = "SELECT * FROM ir_balance WHERE nick = ?";
       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       if ($stmt->bind_param('s', $this->nick) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }
       if ($stmt->execute() == FALSE) {
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
       if (($object = $result->fetch_object()) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_FETCH_OBJECT;
           return false;
       }

       if (is_null($object)) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::NULL_OBJECT;
           return false;
       }
       $stmt->close();
       $this->errno = errno::PASS;
       return $object->balance;
   }

   public function addBalance($recharge_amount) {
       if (is_null($this->mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       if (empty($recharge_amount)) {
           $this->error = "Invalid recharge amount";
           $this->errno = errno::INVALID_AMOUNT;
           return false;
       }
       if (empty($this->nick)) {
           $this->error = "Invalid nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }

       $query = "UPDATE ir_balance SET balance = balance + ? WHERE nick = ?";

       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }

       if ($stmt->bind_param('is', $recharge_amount,
                                   $this->nick) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }

       return true;
   }
}

?>


