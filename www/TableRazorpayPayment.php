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

/** A class to handle database operations on table ir_razorpay_payment. */
class TableRazorpayPayment {
   public $paymentId;
   public $order_id;
   public $recharge_id;
   public $recharge_amount;
   public $status;
   public $nick;
   public $errno;
   public $error;

   const ERRNO_INVALID_DBOBJ = 1;
   const ERRNO_INVALID_PAYMENT_ID = 2;
   const ERRNO_INVALID_ORDER_ID = 3;
   const ERRNO_INVALID_RECHARGE_ID = 4;
   const ERRNO_INVALID_RECHARGE_AMOUNT = 5;
   const ERRNO_INVALID_NICK = 6;
   const ERRNO_PREPARE_FAILED = 7;
   const ERRNO_BINDPARAM_FAILED = 8;
   const ERRNO_EXECUTE_FAILED = 9;

   function __construct() {
       $this->nick = $_SESSION['userid'];
   }

   public function insert($mysqli) {
       if (!isset($mysqli) || is_null($mysqli)) {
           $this->error = "MySQL connection object is not initialized";
           $this->errno = self::ERRNO_INVALID_DBOBJ;
           return false;
       }
       if (empty($this->paymentId)) {
           $this->error = "Razorpay Payment ID is not available.";
           $this->errno = self::ERRNO_INVALID_PAYMENT_ID;
           return false;
       }
       if (empty($this->order_id)) {
           $this->error = "Razorpay Order ID is invalid";
           $this->errno = self::ERRNO_INVALID_ORDER_ID;
           return false;
       }
       if (empty($this->recharge_id)) {
           $this->error = "Recharge ID number is invalid";
           $this->errno = self::ERRNO_INVALID_RECHARGE_ID;
           return false;
       }
       if (empty($this->recharge_amount)) {
           $this->error = "Recharge amount is invalid";
           $this->errno = self::ERRNO_INVALID_RECHARGE_AMOUNT;
           return false;
       }
       if (empty($this->nick)) {
           $this->error = "Invalid username";
           $this->errno = self::ERRNO_INVALID_NICK;
           return false;
       }

       $query = "INSERT INTO ir_razorpay_payment(payment_id, order_id, " .
           " recharge_id, recharge_amount, created_by) VALUES (?, ?, ?, ?, ?)";

       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_PREPARE_FAILED;
           return FALSE;
       }

       if ($stmt->bind_param('sssis', $this->paymentId,
                                      $this->order_id,
                                      $this->recharge_id,
                                      $this->recharge_amount,
                                      $this->nick) == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_BINDPARAM_FAILED;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_EXECUTE_FAILED;
           return FALSE;
       }

       return TRUE;
   }

   public function update($mysqli) {
       $query = "UPDATE ir_razorpay_payment SET status = ? WHERE " .
           " payment_id = ?";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_PREPARE_FAILED;
           return FALSE;
       }
       if ($stmt->bind_param('ss', $this->status,
                                   $this->paymentId) == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_BINDPARAM_FAILED;
           return FALSE;
       }
       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_EXECUTE_FAILED;
           return FALSE;
       }
   }
}

?>

