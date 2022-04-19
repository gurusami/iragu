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

/*

razorpay_order_id
razorpay_payment_id
razorpay_signature 

*/

include 'iragu-webapp.php';
include '01-iragu-global-utility.php';
include 'IraguRazorpay.php';
include 'iragu-private.php';

class IraguRazorpayPaymentStatus extends IraguWebapp {

   /** Do some basic checks before making DB connection. */
   public function init() {
       if (!isset($_POST['razorpay_order_id'])) {
           die("Invalid");
       }
       if (!isset($_POST['razorpay_payment_id'])) {
           die("Invalid");
       }
       if (!isset($_POST['razorpay_signature'])) {
           die("Invalid");
       }
   }

   public function work() {
       /* Check if the order id is valid. */
       /* Check if the payment id is new. */
       /* Calculate your own signature. */
       /* Verify the signature. */
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

$page = new IraguRazorpayPaymentStatus();
/* This is the landing page.  So session is not available.
   $page->is_user_authenticated(); */
$page->init();
$page->connect();
$page->work();
$page->view();
?>
