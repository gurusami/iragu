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
       if (empty($balance)) {
           $this->error = "TablePassbook::recharge():Invalid balance amount";
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
       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
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
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }
       $stmt->close();
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
       if (empty($balance)) {
           $this->error = "Invalid balance amount";
           $this->errno = errno::INVALID_AMOUNT;
           return false;
       }
       if (empty($cashback)) {
           $this->error = "Invalid cashback amount";
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
       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
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
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }

       return true;
   }

   public function registerCashback($mysqli, $cashback, $balance) {
       if (is_null($mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       if (empty($balance)) {
           $this->error = "Invalid balance amount";
           $this->errno = errno::INVALID_AMOUNT;
           return false;
       }
       if (empty($cashback)) {
           $this->error = "Invalid cashback amount";
           $this->errno = errno::INVALID_AMOUNT;
           return false;
       }
       if (empty($this->nick)) {
           $this->error = "Invalid nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       $trx_info = "Cashback for Registration: " . $this->nick;
       $query = "INSERT INTO ir_passbook (nick, trx_info, credit, " .
           " running_total) VALUES (?, ?, ?, ?);";

       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }

       if ($stmt->bind_param('ssii', $this->nick,
                                     $trx_info,
                                     $cashback,
                                     $balance) == FALSE) {
           $this->error = $stmt->error;
           $this->errno = errno::FAILED_BINDPARAM;
           $stmt->close();
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $this->error = $stmt->error;
           $this->errno = errno::FAILED_EXECUTE;
           $stmt->close();
           return FALSE;
       }

       return true;
   }
}

?>


