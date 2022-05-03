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

/** A class to handle database operations on table ir_passbook. */
class TablePassbook {
   public $nick;
   public $balance;
   public $credit;
   public $debit;
   public $trx_info;
   public $error;
   public $errno;
   public $mysqli;

   function __construct($mysqli) {
       $this->mysqli = $mysqli;
       if (!empty($_SESSION['userid'])) {
           $this->nick = $_SESSION['nick'];
       }
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
   }

   public function recharge($recharge_amount, $balance, $recharge_id) {
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
       $trx_info = "Recharge: " . $recharge_id;
       $query = "INSERT INTO ir_passbook (nick, trx_info, credit, " .
           " running_total, recharge_id) VALUES (?, ?, ?, ?, ?);";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       if ($stmt->bind_param('ssiii', $this->nick,
                                      $trx_info,
                                      $recharge_amount,
                                      $balance,
                                      $recharge_id) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }

       return true;
   }

   public function cashback($cashback, $balance, $recharge_id) {
       if (is_null($this->mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       if (empty($recharge_id)) {
           $this->error = "Invalid recharge amount";
           $this->errno = errno::INVALID_AMOUNT;
           return false;
       }
       if (empty($this->nick)) {
           $this->error = "Invalid nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       $trx_info = "Cashback for Recharge: " . $recharge_id;
       $query = "INSERT INTO ir_passbook (nick, trx_info, credit, " .
           " running_total, recharge_id) VALUES (?, ?, ?, ?, ?);";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       if ($stmt->bind_param('ssiii', $this->nick,
                                      $trx_info,
                                      $cashback,
                                      $balance,
                                      $recharge_id) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }

       return true;
   }
}

?>


