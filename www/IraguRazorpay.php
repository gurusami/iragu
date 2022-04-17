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

use Razorpay\Api\Api;

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
       $this->api_key = "rzp_test_0SPT29grHC9zI4";
       $this->api_secret = "UrHcQIV8cc23miZ6tMKGKyeb";
       $this->razorpayAPI = new Api($this->api_key, $this->api_secret);
   }

   public function createOrder($mysqli, $recharge_id, $recharge_amount, $nick) {
       $order = $this->razorpayAPI->order->create(
           array('receipt'  => $recharge_id,
                 'amount'   => $recharge_amount,
                 'currency' => 'INR',
                 'notes'    => array('nick' => $nick)));

       /* $order = json_decode($result, false); */

       return $this->tableRazorpayOrderInsert($mysqli, $order->id,
           $order->amount, $recharge_id, $order->attempts, $order->status,
           $order->created_at, $nick);
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
}

?>

