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
/**
 * @file PageRecharge.php
 * @brief An admin recharges a user/player account.
 *
 * An admin recharges a user/player account. The steps are elaborated here for
 * better understanding.
 *
 * Step 1: The admin enters a player id or a mobile number or an email address.
 * All of these are supposed to be unique for one person.  If the entered
 * information is valid, then proceed to step 2.  If the entered information is
 * not correct (data not available in database), then report error and stay in
 * step 1. (@todo: Currently, I am not supporting email address; Can be added
 * later on.)
 */

require 'autoload.php';

class PageRecharge extends IraguWebapp {
   /** Nick name of the user for whom the recharge is being done. */
   public $recharge_nick;
   public $tableBalance;
   public $tableRechargeOffers;
   public $tableRecharge;

   function __construct() {
       $this->tableBalance = new TableBalance($this->mysqli);
       $this->tablePassbook = new TablePassbook($this->mysqli);
       $this->tableRechargeOffers = new TableRechargeOffers($this->mysqli);
       $this->tableRecharge = new TableRecharge();
   }

   private function processNickForm() {
       $tablePeople = new TablePeople();
       /* Validate the provided nick. */
       if (isMobileNumber($_POST['nick'])) {
           $peopleObj = $tablePeople->getUserDetails($this->mysqli, $_POST['nick']);
       } else {
           /* Assume that it is a nick name. */
           $peopleObj = $tablePeople->get($this->mysqli, $_POST['nick']);
       }
       if ($peopleObj == FALSE) {
           $this->errno = errno::INVALID_MOBILE_NO;
           $this->errno = "Mobile number not found: " . $_POST['nick'];
       } else {
           $this->errno = errno::PASS;
           $_SESSION['recharge_nick'] = $peopleObj;
       }
   }

   public function displayUserDetails() {
       $url = $this->getSelfURL();
       $obj = $_SESSION['recharge_nick'];
       echo <<<EOF
<p> Nick Name: $obj->nick </p>
<p> Full Name: $obj->full_name </p>
<p> Email: $obj->email </p>
<p> Mobile: $obj->mobile_no </p>
<div>
   <form action="$url" method="post">
  <fieldset style="font-size: 1em;">
   <input type="submit" id="confirm_nick" name="confirm_nick"
           value="Confirm"/>
  </fieldset>
   </form>
</div>
EOF;
   }

   public function giveCashback() {
       $rechargeObj = $_SESSION['recharge_offer_obj'];
       $amount = $rechargeObj->cashback;

       /* Increase the balance. */
       if ($this->tableBalance->addBalance($amount) == false) {
           $this->error = $this->tableBalance->error;
           $this->errno = 1;
           return false;
       }

       /* Get the current balance */
       $balance = $this->tableBalance->getCurrentBalance();

       /* Add entry to passbook. */
       $rid = $this->tableRecharge->recharge_id;
       if ($this->tablePassbook->cashback($amount, $balance, $rid) == false) {
           $this->error = $this->tablePassbook->error;
           $this->errno = $this->tablePassbook->errno;
           return false;
       }

       $pplObj = $_SESSION['recharge_nick'];
       if (strcmp($_SESSION['nick'], $pplObj->nick) == 0) {
           $_SESSION['balance'] = $balance;
       }

       return true;
   }

   public function increaseBalance() {
       $rechargeObj = $_SESSION['recharge_offer_obj'];
       $amount = $rechargeObj->recharge_amount;
       $this->tableBalance->setDB($this->mysqli);
       $this->tablePassbook->setDB($this->mysqli);
       /* Increase the balance. */
       if ($this->tableBalance->addBalance($amount) == false) {
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
       $rid = $this->tableRecharge->recharge_id;
       if ($this->tablePassbook->recharge($amount, $balance, $rid) == false) {
           $this->error = $this->tablePassbook->error;
           $this->errno = $this->tablePassbook->errno;
           return false;
       }
       return true;
   }

   public function insertTableRecharge() {
       $peopleObj = $_SESSION['recharge_nick'];
       $offerId = $_SESSION['recharge_offer_id'];
       if ($this->tableRecharge->insert($this->mysqli, $peopleObj->nick,
           $offerId, "cash") == false) {
           $this->error = $this->tableRecharge->error;
           $this->errno = $this->tableRecharge->errno;
           return false;
       }
       return true;
   }

   public function takeAction() {
       $peopleObj = $_SESSION['recharge_nick'];
       $this->tableBalance->nick = $peopleObj->nick;
       $this->tablePassbook->nick = $peopleObj->nick;

       $this->startTrx();
       if ($this->insertTableRecharge() && $this->increaseBalance() &&
           $this->giveCashback()) {
           $this->commitTrx();
       } else {
           $this->rollbackTrx();
           return false;
       }
       return true;
   }

   /** Process the POST data when a form is submitted. Identify which form is
   being submitted, collect the necessary POST data and save it in the SESSION.
   Ensure that enough vaidation happens. */
   public function work() {
       if (isset($_POST['choose_nick'])) {
           $this->processNickForm();
       } else if (isset($_POST['confirm_nick'])) {
           $_SESSION['recharge_nick_confirm'] = true;
       } else if (isset($_POST['offer_id'])) {

           $_SESSION['recharge_offer_id'] = $_POST['offer_id'];
           $_SESSION['recharge_offer_obj'] =
               $this->tableRechargeOffers->getRechargeOffer(
                   $this->mysqli,
                   $_POST['offer_id']);

       } else if (isset($_POST['confirm_recharge'])) {
           /* Update database. */
           if ($this->takeAction()) {
               /* Pass */
               $this->error = "SUCCESSFULLY RECHARGED: ". $this->error;
               $this->errno = 0;
           } else {
               /* Fail */
               $this->error = "FAILED TO RECHARGE: " . $this->error;
               $this->errno = 1;
           }
           $_SESSION['recharge_confirm'] = true;
           $_SESSION['show_status'] = true;
       }
   }

   public function displayNickForm() {
      $url = $this->getSelfURL();
      $form = <<<EOD
<div>
 <form action="$url" method="post">
  <fieldset style="font-size: 1em;">
   <p> <label for="nick"> Nick/Mobile </label>
       <input type="text" id="nick" name="nick" maxlength="10" size="10"/>
      </p>
   <input type="submit" id="choose_nick" name="choose_nick"
           value="Choose Nick"/>
  </fieldset>
</div>
EOD;
     echo $form;
}

   public function showOffers() {
       $obj = $_SESSION['recharge_nick'];
       echo "<h3> Recharging for player: $obj->nick </h3>";
       echo <<<EOF
<table>
<tr> <td> Nick Name </td> <td> $obj->nick </td> </tr>
<tr> <td> Full Name: </td> <td> $obj->full_name </td> </tr>
<tr> <td> Email: </td> <td> $obj->email </td> </tr>
<tr> <td> Mobile: </td> <td> $obj->mobile_no </td> </tr>
</table>
EOF;
       $url = $this->getSelfURL();
       $this->tableRechargeOffers->setDB($this->mysqli);
       $this->tableRechargeOffers->displayCurrentOffers($url);
   }

   public function showConfirm() {
       $obj = $_SESSION['recharge_nick'];
       echo "<h3> Recharging for player: $obj->nick </h3>";
       echo <<<EOF
<table>
<tr> <td> Nick Name </td> <td> $obj->nick </td> </tr>
<tr> <td> Full Name: </td> <td> $obj->full_name </td> </tr>
<tr> <td> Email: </td> <td> $obj->email </td> </tr>
<tr> <td> Mobile: </td> <td> $obj->mobile_no </td> </tr>
</table>
EOF;
       $url = $this->getSelfURL();
       echo "<h3> Recharge Offer Selected </h3>";
       $this->tableRechargeOffers->setDB($this->mysqli);
       $this->tableRechargeOffers->displayRechargeOffer($this->mysqli, 
           $_SESSION['recharge_offer_id']);

       echo <<<EOF

       <form action="$url" method="post">
  <fieldset style="font-size: 1em;">
   <input type="submit" id="confirm_recharge" name="confirm_recharge"
           value="Confirm Recharge"/>
  </fieldset>
   </form>
EOF;
   }

   public function displayResult() {
       $obj = $_SESSION['recharge_nick'];
       echo "<h3> Recharging for player: $obj->nick </h3>";
       echo <<<EOF
<table>
<tr> <td> Nick Name </td> <td> $obj->nick </td> </tr>
<tr> <td> Full Name: </td> <td> $obj->full_name </td> </tr>
<tr> <td> Email: </td> <td> $obj->email </td> </tr>
<tr> <td> Mobile: </td> <td> $obj->mobile_no </td> </tr>
</table>
EOF;
       $url = $this->getSelfURL();
       echo "<h3> Recharge Offer Selected </h3>";
       $this->tableRechargeOffers->displayRechargeOffer($this->mysqli,
           $_SESSION['recharge_offer_id']);
   }

   public function viewPage() {
       if (!isset($_SESSION['recharge_nick'])) {
           $this->displayNickForm();
       } else if (!isset($_SESSION['recharge_nick_confirm'])) {
           $this->displayUserDetails();
       } else if (!isset($_SESSION['recharge_offer_id'])) {
           $this->showOffers();
       } else if (!isset($_SESSION['recharge_confirm'])) {
           $this->showConfirm();
       } else {
           $this->displayResult();
           unset($_SESSION['recharge_nick']);
           unset($_SESSION['recharge_nick_confirm']);
           unset($_SESSION['recharge_offer_id']);
           unset($_SESSION['recharge_offer_obj']);
           unset($_SESSION['recharge_confirm']);
           unset($_SESSION['show_status']);
       }
   }
}

?>

