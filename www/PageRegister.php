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

class PageRegister extends IraguWebapp {
   public $player_id;
   public $player_name;
   public $email;
   public $mobile;
   public $offer_id;
   public $cashback;  /* In paise */
   public $offerObj;

   public function work() {
       if (!empty($_POST['add_player'])) {
           /* Form is being submitted. */
           $this->player_id      = strtolower(trim($_POST['player_id']));
           $this->player_name    = $_POST['player_name'];
           $this->email          = $_POST['email'];
           $this->mobile         = $_POST['mobile'];
           if (!empty($_POST['offer_id'])) {
               $this->offer_id = $_POST['offer_id'];
               $this->cashback = $_POST['cashback'] * 100;
           }
           if (!$this->store()) {
               return false;
           }
           $_SESSION['show_status'] = true;
           $this->error = "Successfully added new user (Nick: $this->player_id)";
       } else {
           $tableRegisterOffer = new TableRegisterOffer($this->mysqli);
           $this->offerObj = $tableRegisterOffer->getOfferDetails($this->mysqli);
           if ($this->offerObj != false) {
               $this->offer_id = $this->offerObj->offer_id;
               $this->cashback = $this->offerObj->cash_back;
           }
       }
       return true;
   }

   public function addPeople() {
       $tablePeople = new TablePeople();
       $tablePeople->nick = $this->player_id;
       $tablePeople->full_name = $this->player_name;
       $tablePeople->email = $this->email;
       $tablePeople->mobile_no = $this->mobile;
       $tablePeople->offer_id = $this->offer_id;
       if ($tablePeople->insert($this->mysqli) == false) {
           $this->errno = $tablePeople->errno;
           $this->error .= $tablePeople->error;
           return false;
       }
       return true;
   }

   public function addPassbookEntry() {
       $tablePassbook = new TablePassbook(null);
       $tablePassbook->nick = $this->player_id;
       if ($tablePassbook->registerCashback($this->mysqli, $this->cashback, $this->cashback) == false) {
           $this->errno = $tablePassbook->errno;
           $this->error .= $tablePassbook->error;
           return false;
       }
       return true;
   }

   public function insertBalance() {
       $tableBalance = new TableBalance();
       $tableBalance->nick = $this->player_id;
       $tableBalance->balance = $this->cashback;
       if ($tableBalance->insert($this->mysqli) == false) {
           $this->errno = $tableBalance->errno;
           $this->error .= $tableBalance->error;
           return false;
       }
       return true;
   }

   public function store() {
      $this->mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

      if (!$this->addPeople()) {
         $this->mysqli->rollback();
         return FALSE;
      }
      if (!is_null($this->offer_id)) {
         if (!$this->addPassbookEntry()) {
            $this->mysqli->rollback();
            return FALSE;
         }
      }
      if (!$this->insertBalance()) {
         $this->mysqli->rollback();
         return FALSE;
      }
      return $this->mysqli->commit();
   }

   public function displayOffer() {
       $cb = $this->paiseToRupees($this->cashback);
       if (!empty($this->offer_id)) {

echo <<<EOF
   <p> <label for="offer_id"> Offer ID </label>
       <input type="text" id="offer_id" name="offer_id" maxlength="8"
                 size="8" value="$this->offer_id" readonly/>
   </p>

   <p> <label for="cashback"> Offer Cash Back (In Rupees) </label>
       <input type="text" id="cashback" name="cashback" maxlength="8"
                 size="8" value="$cb" readonly/>
   </p>
EOF;
       }
   }

   public function viewPage() {
       $url = $this->getSelfURL();

       echo <<<EOF
<div class="div_form">
   <form action="$url" method="post">
       <fieldset style="font-size: 1em;">
       <legend> Register A Player </legend>
       <p>
           <label style="color: mediumspringgreen;" for="player_id">
               Player ID </label>
           <input type="text" id="player_id" name="player_id" maxlength="8"
                 size="8" value="$this->player_id"/>
       </p>
       <p> <label for="player_name"> Player Name </label>
           <input type="text" id="player_name" name="player_name" maxlength="30"
                 size="30"
                 value="$this->player_name"/>
       </p>
      <p> <label for="email"> Email </label>
          <input type="text" id="email" name="email" maxlength="256"
                 size="30"
                 value="$this->email"/>
      </p>
      <p> <label for="mobile"> Mobile </label>
          <input type="text" id="mobile" name="mobile" maxlength="15"
                 size="15"
                 value="$this->mobile"/>
      </p>
EOF;

   $this->displayOffer();


echo <<<EOF
    <input type="submit" id="add_player" name="add_player" value="Add Player"/>
  </fieldset>
</form>
</div>
EOF;
   }
}

?>


