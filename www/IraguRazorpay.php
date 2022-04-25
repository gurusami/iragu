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
require __DIR__ . "/../vendor/autoload.php";
include 'iragu-private.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/*

{  "id": "order_DaZlswtdcn9UNV", 
   "entity":"order",
   "amount":50000,
   "amount_paid":0,
   "amount_due":50000,
   "currency":"INR",
   "receipt":"Receipt #20",
   "status":"created",
   "attempts":0,
   "notes": {  "key1": "value1",    "key2": "value2" },
   "created_at":1572502745
}

*/

class IraguRazorpay {
   public $api_key;
   public $api_secret;
   public $razorpayAPI;

   function __construct() {
       global $razorpay_api_key, $razorpay_api_secret;

       $this->api_key = $razorpay_api_key;
       $this->api_secret = $razorpay_api_secret;

       if (is_null($this->api_key) || is_null($this->api_secret)) {
           die("Razorpay API Keys are not available");
       }

       $this->razorpayAPI = new Api($this->api_key, $this->api_secret);
   }

   public function createOrder($mysqli, $recharge_id, $recharge_amount, $nick) {
       $order = $this->razorpayAPI->order->create(
           array('receipt'  => $recharge_id,
                 'amount'   => $recharge_amount,
                 'currency' => 'INR',
                 'notes'    => array('nick' => $nick)));

       if ($this->tableRazorpayOrderInsert($mysqli, $order->id,
           $order->amount, $recharge_id, $order->attempts, $order->status,
           $order->created_at, $nick) == FALSE) {
           die($mysqli->error);
       }

       return $order;
    }

   public function tableRazorpayOrderInsert($mysqli, $order_id, $amount,
       $recharge_id, $attempts, $status, $created_at, $created_by) {

       /* There are totally 7 placeholders. */
       $query = "INSERT INTO ir_razorpay_order (order_id, amount, " .
           " recharge_id, attempts, status, created_at, created_by) VALUES " .
           " (?, ?, ?, ?, ?, ?, ?)";

       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           return FALSE;
       }
       $stmt->bind_param('siiisis', $order_id, $amount, $recharge_id, $attempts,
           $status, $created_at, $created_by);
       return $stmt->execute();
   }

   public function verifySignature($order_id, $payment_id, $signature) {
       if (empty($order_id)   || is_null($order_id) ||
           empty($payment_id) || is_null($payment_id) ||
           empty($signature)  || is_null($signature)) {
           return false;
       }

       try { 
           $attributes = array(
               'razorpay_order_id' => $order_id,
               'razorpay_payment_id' => $payment_id,
               'razorpay_signature' => $signature);

           $this->razorpayAPI->utility->verifyPaymentSignature($attributes);
       }
       catch(SignatureVerificationError $e) {
           return false;
       }

       return true;
   }

   public function fetchPayment($paymentId) {
       return $this->razorpayAPI->payment->fetch($paymentId);
   }

}

?>

