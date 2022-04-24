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
/* Iragu: User Recharge using Razorpay. */

/** 
   Documentation:

+  When this page is visited, the following variables are first set.
       $_POST['recharge_amount']
       $_POST['offer_id']
       $_POST['cashback']

+  <input type="submit" name="form_name" value="Recharge">

*/

require 'autoload.php';
include 'iragu-webapp.php';
include 'IraguRazorpay.php';
include 'iragu-private.php';

class IraguUserRechargeRazorpay extends IraguWebapp {
   public $recharge_amount; /* in paise */
   public $recharge_id;
   public $offer_id;
   public $cashback;
   public $nick;
   public $payApi;
   public $razorpay_order;
   public $order_id;

    function __construct() {
        $this->payApi = new IraguRazorpay();
    }

   public function work() {
       if (isset($_POST['recharge_amount'])) {
           $this->recharge_amount = $_POST['recharge_amount'];
       }
       if (isset($_POST['offer_id'])) {
           $this->offer_id = $_POST['offer_id'];
       } else {
           $this->errmsg .= "offer_id is not available";
           $this->success = FALSE;
           return FALSE;
       }
       if (isset($_POST['cashback'])) {
           $this->cashback = $_POST['cashback'];
       } else {
           ir_die("Cashback is not available");
       }
       if (isset($_SESSION['userid'])) {
           $this->nick = $_SESSION['userid'];
       }
       if (isset($_POST['form_name'])) {
           if ($this->tableRechargeInsert($this->nick, $this->offer_id) == FALSE) {
               echo "<p> FAILED: $this->errmsg </p>";
           } else {
               $this->recharge_id = $this->mysqli->insert_id;
               $this->razorpay_order = $this->payApi->createOrder(
                   $this->mysqli, $this->recharge_id, $this->recharge_amount,
                   $_SESSION['userid']);
               $this->order_id = $this->razorpay_order->id;
           }
       }
       $this->success = TRUE;
       return TRUE;
    }

   public function addRazorpayButton() {
       echo '<button type="button" class="razorpay_button" id="rzp-button1">Pay</button>' . "\n";
   }

   public function addRazorpayJS($name, $email, $mobile) {
       global $razorpay_api_key;

       if (is_null($this->order_id)) {
           ir_die("Razorpay Order ID is missing");
       }

       $local_order_id = $this->order_id;
       $amt = $this->recharge_amount;
       echo <<<EOF
<script src="https://checkout.razorpay.com/v1/checkout.js">
</script>
<script>
   var options = {
       "key": "$razorpay_api_key",
       "amount": "$amt",
       "currency": "INR",
       "name": "Goodminton Sports Services",
       "description": "Test Transaction",
       "image": "https://example.com/your_logo",
       "order_id": "$local_order_id",
       "callback_url": "https://goodminton.in/~agurusam/16-razorpay-payment-status.php",
       "prefill": {
           "name": "$name",
           "email": "$email", 
           "contact": "$mobile"
       },
       "notes": {"address": "Razorpay Corporate Office"},
       "theme": { "color": "#3399cc"}
   };

   var rzp1 = new Razorpay(options);
   document.getElementById('rzp-button1').onclick = function(e){
       rzp1.open();
       e.preventDefault();
   }
</script>
EOF;
   }

   public function getHiddenFormFields() {
       $html = <<<EOF
<input type="hidden" name="recharge_id" value="$this->recharge_id" readonly>
<input type="hidden" name="offer_id" value="$this->offer_id" readonly>
<input type="hidden" name="recharge_amount" value="$this->recharge_amount" readonly>
<input type="hidden" name="cashback" value="$this->cashback" readonly>
EOF;
       return $html;
   }

   public function viewRazorpayButton() {
       if (isset($_SESSION['userobj'])) {
           $userobj = $_SESSION['userobj'];
       } else {
           $userobj = $this->getUserDetails($_SESSION['userid']);
       }

       $rows = array();
       $rows['User Id'] =   $userobj->nick;
       $rows['Full Name'] = $userobj->full_name;
       $rows['E-mail'] = $userobj->email;
       $rows['Mobile'] = $userobj->mobile_no;
       $rows['Recharge Offer Id'] = $this->offer_id;
       $rows['Recharge Amount'] = paiseToRupees($this->recharge_amount);
       $rows['Razorpay Order Id'] = $this->order_id;
       $rows['Recharge Id'] = $this->recharge_id;
       $rows['Cashback'] = paiseToRupees($this->cashback);
       ir_table($rows);

       $rzr_session = new TableRazorpaySession($this->mysqli);
       $rzr_session->order_id = $this->order_id;
       $rzr_session->sid  = session_id();
       $rzr_session->userid  = $_SESSION['userid'];
       $rzr_session->insert();

       $this->addRazorpayButton();
       $this->addRazorpayJS($userobj->full_name,
                            $userobj->email,
                            $userobj->mobile_no);
   }

   public function viewRecharge() {
       if (is_null($this->offer_id) || is_null($this->recharge_amount)) {
           ir_die("Invalid");
       }

       $url = $this->getSelfURL();
       $rs_amount = paiseToRupees($this->recharge_amount);
       $hidden_fields = $this->getHiddenFormFields();

       echo <<<EOF
<div class="recharge-div">
   <form action="$url" method="post">
       <table align="center">
           <tr> 
               <td> Recharge Offer ID </td>
               <td> $this->offer_id </td>
           </tr>
           <tr> 
               <td> Recharge Amount </td>
               <td> $rs_amount </td>
           </tr>
       </table>
       $hidden_fields
       <input type="submit" name="form_name" value="Recharge">
   </form>
</div>
EOF;
   }

   public function view() {
       ir_doctype();
       ir_copyright();
       ir_html_open();
       ir_head();
       ir_body_open();
       ir_page_top();
       if (!isset($_POST['form_name'])) {
           $this->viewRecharge();
       } else {
           $this->viewRazorpayButton();
       }
       ir_body_close();
       ir_html_close();
   }
}

$page = new IraguUserRechargeRazorpay();
$page->is_user_authenticated();
$page->connect();
$page->work();
$page->view();
?>
