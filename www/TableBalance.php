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
   public $bookingCost;
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
       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }
       if ($stmt->bind_param('s', $this->nick) == false) {
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

       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('is', $recharge_amount,
                                   $this->nick) == false) {
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

       return true;
   }

   public function insert($mysqli) {
      $query = "INSERT INTO ir_balance (nick, balance) VALUES (?, ?)";
      if (($stmt = $mysqli->prepare($query)) == false) {
         $this->error = $mysqli->error;
         $this->errno = errno::FAILED_PREPARE;
         return false;
      }

      if ($stmt->bind_param('si', $this->nick, $this->balance) == false) {
         $this->error = $stmt->error;
         $this->errno = errno::FAILED_BINDPARAM;
         return false;
      }

      if ($stmt->execute() == false) {
         $this->error = $stmt->error;
         $this->errno = errno::FAILED_EXECUTE;
         return false;
      }

      $stmt->close();
      return true;
   }

   public function checkAndReserveMoney() {
       if (empty($this->nick)) {
           die("Invalid nick");
       }

       if (empty($this->bookingCost)) {
           die("Invalid booking cost");
       }

       $query = 'SELECT balance FROM ir_balance WHERE nick = ? FOR UPDATE';

       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('s', $this->nick) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return false;
       }

     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->error = $this->error . ": Failed to reserve money.";
        return false;
     }
     $result = $stmt->get_result();
     if ($row = $result->fetch_array()) {
        $this->balance = $row[0];
        if (is_null($this->balance) || $this->balance < $this->bookingCost) {
          $this->errno = 1;
          $this->error = "NOT SUFFICIENT BALANCE";
          return false;
        }
     } else {
        $this->errno = 1;
        $this->error .= $stmt->error . " MISSING DATA";
        return false;
     }
     $stmt->close();
     return true;
   }

   public function deductMoney() {
       $query = "UPDATE ir_balance SET last_updated = CURRENT_TIMESTAMP, " .
                " balance = balance - ? WHERE nick = ?";

       if (is_null($this->bookingCost) ) {
           $this->errno = errno::INVALID_COST;
           $this->error .= ": BOOKING COST IS NULL";
           return false;
       }

       if (($stmt = $this->mysqli->prepare($query)) == false) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('is', $this->bookingCost, $this->nick) == false) {
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

       $this->balance = $this->balance - $this->bookingCost;
       $stmt->close();
       return true;
   }
}

?>


