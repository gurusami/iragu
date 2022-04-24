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
/* Iragu: All Users: Recharge Account */

include 'iragu-webapp.php';

function getRechargeOffers($mysqli) {
    $query = "SELECT offer_id, offer_from, offer_to, recharge_amount, " .
        "cashback FROM ir_recharge_offers WHERE CURRENT_DATE BETWEEN " .
        "offer_from AND offer_to ORDER BY offer_from;";
    $offers = array();
    $result = $mysqli->query($query);
    while ($row = $result->fetch_object()) {
        array_push($offers, $row);
    }
    return $offers;
}

function displayCurrentOffers($offers, $url) {
     echo '<div class="grid-container">';
     if (count($offers) == 0) {
       echo '<p> Sorry, no recharge offers available. </p>';
     } else {
         foreach ($offers as $obj) {
            $offer_id = $obj->offer_id;
            $recharge_paisa = $obj->recharge_amount;
            $cashback = paiseToRupees($obj->cashback);
            $cashback_paisa = $obj->cashback;
            $recharge = paiseToRupees($obj->recharge_amount);
echo <<<EOF
<div class="grid-item">
  <form action="$url" method="post">
    <button class="button-grid-item">
      <p> Offer ID: $offer_id </p>
      <p> Recharge Amount: $recharge </p>
      <p> Cashback: $cashback </p>
     <input type="hidden" id="offer_id" name="offer_id" value="$offer_id">
     <input type="hidden" id="cashback" name="cashback" value="$cashback_paisa">
     <input type="hidden" id="recharge_amount" name="recharge_amount"
        value="$recharge_paisa">
    </button>
  </form>
</div>
EOF;
         } /* foreach */
     }
     echo '</div> <!-- grid-container -->';
}

class IraguUserRecharge extends IraguWebapp {
    public function displayRechargeOffers() {
        $url = "15-iragu-user-recharge-razorpay.php";
        $offers = getRechargeOffers($this->mysqli);
        displayCurrentOffers($offers, $url);
    }
}

$page = new IraguUserRecharge();
$page->is_user_authenticated();
$page->connect();
$page->work();
?>

<!doctype html>
<?php $page->displayCopyright(); ?>
<html>

<?php include '10-head.php'; ?>

<body>

<?php include '14-iragu-top.php'; ?>

<?php
  $page->displayStatus();
  $page->displayRechargeOffers();
?>

</body>
</html>

