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
   public $paymentObj;

   function __construct() {
       $this->razorpay = new IraguRazorpay();
       $this->tablePayment = new TableRazorpayPayment();
       $this->tableBalance = new TableBalance($this->mysqli);
   }

   /** Do some basic checks before making DB connection. */
   public function init() {
       if (empty($_POST['razorpay_order_id'])) {
           die("Invalid");
       }
       $this->order_id = $_POST['razorpay_order_id'];

       if (empty($_POST['razorpay_payment_id'])) {
           die("Invalid");
       }
       $this->payment_id = $_POST['razorpay_payment_id'];
       if (empty($_POST['razorpay_signature'])) {
           die("Invalid");
       }
       $this->signature = $_POST['razorpay_signature'];
   }

   public function savePaymentId() {
       $tablePayment->payment_id = $this->payment_id;
       $tablePayment->order_id = $this->order_id;
       $tablePayment->recharge_id = $_SESSION['recharge_id'];
       $tablePayment->recharge_amount = $_SESSION['recharge_id'];
       if ($tablePayment->insert($this->mysqli) == false) {
           $this->errno = $tablePayment->errno;
           $this->error = $tablePayment->error;
           return false;
       }
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
           return false;
       }
       $this->orderIdFromDB = $row_obj->order_id;
       session_id($row_obj->sid);
       session_start();

       if (strcmp($_SESSION['userid'], $row_obj->created_by) != 0) {
           $this->error = "User id mismatch. Retry...";
           return false;
       }

       return true;
   }

   public function savePaymentObj() {
       if (empty($this->paymentObj) || is_null($this->paymentObj)) {
           die("Invalid Payment Object");
       }
       $this->tablePayment->paymentId = $this->paymentObj->id;
       $this->tablePayment->status = $this->paymentObj->status;
       $this->tablePayment->update($this->mysqli);
   }

   public function work() {
       $this->init();

       if (!$this->restoreSession()) {
           die($this->error);
       }
       if ($this->razorpay->verifySignature($this->orderIdFromDB,
                                            $this->payment_id,
                                            $this->signature) == false) {
           die("Invalid payment signature.");
       }
       if ($this->savePaymentId() == false) {
           die($this->error);
       }
       $this->paymentObj = $this->razorpay->fetchPayment($this->payment_id);
       $this->savePaymentObj();
       if (strcmp($paymentObj->status, "captured") == 0) {
           if ($this->tableBalance->addBalance($paymentObj->amount) == false) {
               die($this->tableBalance->error);
           }
           /* Add entry to passbook. */
           /* For cashback: Update the balance */
           /* Add another entry to passbook for cashback. */
       }
   }

   public function view() {
       ir_doctype();
       ir_copyright();
       ir_html_open();
       ir_head();
       ir_body_open();
       ir_page_top();

       echo "<pre>" . "\n";
       print_r($_POST);
       echo "</pre>" . "\n";

       ir_body_close();
       ir_html_close();
   }
}
