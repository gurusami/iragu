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
/* Iragu: User: Registration done by Customer themselves. */

include 'iragu-webapp.php';
include '01-iragu-global-utility.php';

class IraguRegister extends IraguWebapp {
   public $player_id;
   public $player_name;
   public $email;
   public $mobile_no;
   public $offer_id;
   public $offer_cashback;
   public $password_1;
   public $password_2;

   public function getOfferDetails() {
      $query = <<<EOF
SELECT offer_id, cash_back FROM ir_register_offers
WHERE CURRENT_DATE BETWEEN offer_from AND offer_to LIMIT 1;
EOF;
      $result = $this->mysqli->query($query) or die($this->mysqli->error);
      $row = $result->fetch_assoc();
      if ($row != null) {
         $this->offer_id = $row['offer_id'];
         $this->offer_cashback = $row['cash_back'];
      } else {
         $this->offer_id = null;
         $this->offer_cashback = 0;
      }
   }

   /** Check if all the necessary information is available. */
   public function canProceed() {
      if (!isset($_POST['player_id'])) {
        $this->errmsg = 'Player ID is missing';
        return FALSE;
      }
      if (!isset($_POST['player_name'])) {
        $this->errmsg = 'Player name is missing';
        return FALSE;
      }
      if (!isset($_POST['email'])) {
        $this->errmsg = 'Email Address is missing';
        return FALSE;
      }
      if (!isset($_POST['mobile_no'])) {
        $this->errmsg = 'Mobile phone number  is missing';
        return FALSE;
      }
      if (!isset($_POST['password_1'])) {
        $this->errmsg = 'Password is missing';
        return FALSE;
      }
      if (!isset($_POST['password_2'])) {
        $this->errmsg = 'Password confirmation is missing';
        return FALSE;
      }

      return TRUE;
   }

   public function work() {
      if (isset($_POST['player_id'])) {
         $this->player_id = $_POST['player_id'];
      }
      if (isset($_POST['password_1'])) {
         $this->password_1 = $_POST['password_1'];
      }
      if (isset($_POST['password_2'])) {
         $this->password_2 = $_POST['password_2'];
      }
      if (isset($_POST['player_name'])) {
         $this->player_name = $_POST['player_name'];
      }
      if (isset($_POST['email'])) {
         $this->email = $_POST['email'];
      }
      if (isset($_POST['mobile_no'])) {
         $this->mobile_no = $_POST['mobile_no'];
      }

      $this->getOfferDetails();

      if (isset($_POST['add_player'])) {
         if ($this->canProceed()) {
            $this->startTrx();
            if ($this->insertPeople() &&
                $this->tableLoginInsert() &&
                $this->tablePassbookRegister() &&
                $this->tableBalanceRegister($_POST['player_id'],
                                            $this->offer_cashback)) {
                $this->commitTrx();
            } else {
                $this->rollbackTrx();
            }
         } else {
            $this->errmsg .= 'Some info missing';
         }
      }
   }
}

$page = new IraguRegister();
$page->connect();
$page->work();
?>

<!doctype html>
<?php $page->displayCopyright(); ?>
<html>

<?php include '10-head.php'; ?>

<body>

<?php $page->displayStatus();
include '14-iragu-top.php'; ?>

<div id="div_signup"> 
 <form action="<?php $page->displaySelfURL(); ?>"
       method="post">
  <fieldset style="font-size: 1em;">
    <legend> Register A Player </legend>
      <p> <label for="player_id"> Player ID </label>
          <input type="text" id="player_id" name="player_id" maxlength="8"
                 size="8"
                 value="<?php echo $page->player_id; ?>"
                 required/>
      </p>
      <p> <label for="pass_1"> Choose Password </label>
          <input type="password" id="pass_1" name="password_1" maxlength="20"
                 size="20"
                 value="<?php echo $page->password_1; ?>"
                 required/>
      </p>
      <p> <label for="pass_2"> Confirm Password </label>
          <input type="password" id="pass_2" name="password_2" maxlength="20"
                 size="20"
                 value="<?php echo $page->password_2; ?>"
                 required/>
      </p>
      <p> <label for="player_name"> Player Name </label>
          <input type="text" id="player_name" name="player_name" maxlength="30"
                 size="30"
                 value="<?php echo $page->player_name; ?>" required />
      </p>
      <p> <label for="email"> Email </label>
          <input type="text" id="email" name="email" maxlength="256"
                 size="30"
                 value="<?php echo $page->email; ?>" required/>
      </p>
      <p> <label for="mobile"> Mobile </label>
          <input type="text" id="mobile" name="mobile_no" maxlength="15"
                 size="15"
                 value="<?php echo $page->mobile_no; ?>" required/>
      </p>
          <input type="text" id="offer_id" name="offer_id" maxlength="8"
                 size="8"
                 value="<?php echo $page->offer_id; ?>" readonly/>
      <p> <label for="cashback"> Offer Cash Back </label>
          <input type="text" id="cashback" name="offer_cashback" maxlength="8"
                 size="8"
                 value="<?php displayInRupees($page->offer_cashback); ?>"
                 readonly/>
      </p>

    <input type="submit" id="add_player" name="add_player" value="Register Me"/>
  </fieldset>
</form>
</div>

</body>
</html>

