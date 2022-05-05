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

class PageRazorpayLanding extends IraguWebapp {
   public $razorpay;
   public $order_id;
   public $payment_id;
   public $signature;
   public $orderIdFromDB;
   public $tablePayment;
   public $tableBalance;
   public $tablePassbook;
   public $paymentObj;
   public $recharge_id;
   public $cashback;

   function __construct() {
       $this->razorpay = new IraguRazorpay();
       $this->tablePayment = new TableRazorpayPayment();
       $this->tableBalance = new TableBalance($this->mysqli);
       $this->tablePassbook = new TablePassbook($this->mysqli);
   }

   /** Do some basic checks before making DB connection.
   @return true on success, false on failure. */
   public function init() {
       if (empty($_POST['razorpay_order_id'])) {
           $this->error = "Razorpay Order Id is invalid";
           $this->errno = errno::INVALID_RAZORPAY_ORDER_ID;
           return false;
       }
       if (empty($_POST['razorpay_payment_id'])) {
           $this->error = "Razorpay Payment ID is invalid";
           $this->errno = errno::INVALID_RAZORPAY_PAYMENT_ID;
           return false;
       }
       if (empty($_POST['razorpay_signature'])) {
           $this->error = "Razorpay signature is missing";
           $this->errno = errno::MISSING_RAZORPAY_SIGNATURE;
           return false;
       }
       $this->order_id   = $_POST['razorpay_order_id'];
       $this->payment_id = $_POST['razorpay_payment_id'];
       $this->signature  = $_POST['razorpay_signature'];
       $this->errno = errno::PASS;
       return true;
   }

   public function savePaymentId() {
       $this->tablePayment->paymentId = $this->payment_id;
       $this->tablePayment->order_id = $this->order_id;
       $this->tablePayment->recharge_id = $this->recharge_id;
       $this->tablePayment->recharge_amount = $this->recharge_amount;
       if ($this->tablePayment->insert($this->mysqli) == false) {
           $this->errno = $this->tablePayment->errno;
           $this->error = $this->tablePayment->error;
           return false;
       }
       $this->errno = errno::PASS;
       return true;
   }

   public function restoreSession() {
       $razorpay_session = new TableRazorpaySession($this->mysqli);
       $razorpay_session->order_id = $_POST['razorpay_order_id'];
       $row_obj = $razorpay_session->fetch_object();
       if ($razorpay_session->errno != 0) {
           echo "<pre>" . "\n";
           print_r($_POST);
           echo "</pre>" . "\n";
           echo "<p> $razorpay_session->errno </p>";
           echo "<p> $razorpay_session->error </p>";
           $this->error = "Failed to fetch session from DB.";
           $this->errno = errno::FAILED_FETCH_OBJECT;
           return false;
       }
       $this->orderIdFromDB = $row_obj->order_id;
       session_id($row_obj->sid);
       session_start();
       if (strcmp($_SESSION['userid'], $row_obj->created_by) != 0) {
           $this->error = "User id mismatch. Retry...";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       $this->recharge_id = $_SESSION['recharge_id'];
       $this->recharge_amount = $_SESSION['recharge_amount'];
       $this->cashback = $_SESSION['cashback'];
       if ($this->tablePayment->setNickFromSession() == false) {
           $this->error = "Session has no nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       if ($this->tableBalance->setNickFromSession() == false) {
           $this->error = "Session has no nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       if ($this->tablePassbook->setNickFromSession() == false) {
           $this->error = "Session has no nick";
           $this->errno = errno::INVALID_NICK;
           return false;
       }
       $this->errno = errno::PASS;
       return true;
   }

   public function savePaymentObj() {
       if (empty($this->paymentObj) || is_null($this->paymentObj)) {
           $this->error = "Missing Razorpay Payment Object";
           $this->errno = errno::MISSING_RAZORPAY_PAYMENT_OBJ;
           return false;
       }
       $this->tablePayment->paymentId = $this->paymentObj->id;
       $this->tablePayment->status = $this->paymentObj->status;
       $this->tablePayment->update($this->mysqli);
       return true;
   }

   public function work() {
       if ($this->errno != 0) {
           return false;
       }
       $this->tableBalance->setDB($this->mysqli);
       $this->tablePassbook->setDB($this->mysqli);
       if (!$this->restoreSession()) {
           $this->error = "Failed to restore session";
           $this->errno = errno::FAILED_RESTORE_SESSION;
           return false;
       }
       if ($this->razorpay->verifySignature($this->orderIdFromDB,
                                            $this->payment_id,
                                            $this->signature) == false) {
           $this->error = "Razorpay signature verification failed";
           $this->errno = errno::FAILED_RAZORPAY_SIGNATURE;
           return false;
       }
       if ($this->savePaymentId() == false) {
           return false;
       }
       $this->paymentObj = $this->razorpay->fetchPayment($this->payment_id);
       if ($this->savePaymentObj() == false) {
           return false;
       }

       if (strcmp($this->paymentObj->status, "captured") != 0) {
           $this->error = "Payment status is " . $this->paymentObj->status;
           $this->errno = errno::FAILED_RAZORPAY;
           return false;
       }
       if ($this->increaseBalance() == false) {
           return false;
       }
       if ($this->giveCashback() == false) {
           return false;
       }
       return true;
   }

   public function increaseBalance() {
       /* Increase the balance. */
       if ($this->tableBalance->addBalance($this->paymentObj->amount) == false) {
           $this->error = $this->tableBalance->error;
           $this->errno = 1;
           return false;
       }
       /* Get the current balance */
       if (($balance = $this->tableBalance->getCurrentBalance()) == false) {
           $this->error = $this->tableBalance->error;
           $this->errno = 1;
           return false;
       }

       /* Add entry to passbook. */
       if ($this->tablePassbook->recharge($this->paymentObj->amount,
                                          $balance,
                                          $this->recharge_id) == false) {
           $this->error = $this->tablePassbook->error;
           $this->errno = $this->tablePassbook->errno;
           return false;
       }
       return true;
   }

   public function giveCashback() {
       /* Increase the balance. */
       if ($this->tableBalance->addBalance($this->cashback) == false) {
           $this->error = $this->tableBalance->error;
           $this->errno = 1;
           return false;
       }

       /* Get the current balance */
       $balance = $this->tableBalance->getCurrentBalance();

       /* Add entry to passbook. */
       if ($this->tablePassbook->cashback($this->cashback,
                                          $balance,
                                          $this->recharge_id) == false) {
           $this->error = $this->tablePassbook->error;
           $this->errno = $this->tablePassbook->errno;
           return false;
       }
       return true;
   }

   public function view() {
       ir_doctype();
       ir_copyright();
       ir_html_open();
       ir_head();
       ir_body_open();
       ir_page_top();

       if ($this->errno == errno::PASS) {
           echo "<p> RECHARGE SUCCESSFUL </p> ";
       } else {
           echo "<p> " . $this->error . " </p> ";
       }


       echo "<pre>" . "\n";
       print_r($_POST);
       echo "</pre>" . "\n";

       ir_body_close();
       ir_html_close();
   }

   public function cleanup() {
       unset($_SESSION['recharge_id']);
       unset($_SESSION['recharge_amount']);
       unset($_SESSION['cashback']);
   }
}
