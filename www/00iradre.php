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
/* Iragu: Admin Interface: Register: Register a customer and/or player */
include 'iragu-webapp.php';
include '01-iragu-global-utility.php';

/** The class that is used to register a customer by an admin. */
class IraguAdminRegister extends IraguWebapp {
   public $player_id = "";
   public $player_name = "";
   public $gender = "";
   public $email = "";
   public $mobile = "";
   public $aadhar = "";
   public $dob = "";
   public $offer_id = "";
   public $offer_cashback = "";
   public $registered_by = "";

   public function displayDisabled() {
     if ($this->is_form_submitted()) {
        echo " disabled ";
     }
   }

   public function is_form_submitted() {
      if (isset($_POST['player_id'])) {
         return TRUE;
      } else {
         return FALSE;
      }
   }

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

   public function updateBalance() {
      $query = <<<EOF
INSERT INTO ir_balance (nick, balance) VALUES (?, ?);
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('si', $this->player_id, $this->offer_cashback);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' Add balance failed ';
      }
      $stmt->close();
      return $this->success;
   }

   public function addPassbookEntry() {
      $query = <<<EOF
INSERT INTO ir_passbook (nick, trx_amount, trx_info) VALUES (?, ?, ?);
EOF;
      $entry = "Registration incentive";
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('sis', $this->player_id, $this->offer_cashback, $entry);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' Add passbook failed Player=' .
                         $this->player_id . ', Cashback=' .
                         $this->offer_cashback . ', ' . $entry;
      }
      $stmt->close();
      return $this->success;
   }

   public function addPeople() {
      $query = <<<EOF
INSERT INTO ir_people (nick, full_name, gender, email, mobile_no, aadhar, dob,
offer_id, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('sssssssss', $this->player_id, $this->player_name, 
         $this->gender, $this->email, $this->mobile, $this->aadhar,
         $this->dob, $this->offer_id, $this->registered_by);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' Add people failed ';
      }
      $stmt->close();
      return $this->success;
   }

   public function store() {
      $this->mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

      if (!$this->addPeople()) {
         $this->mysqli->rollback();
         return FALSE;
      }
      if ($this->offer_cashback > 0) {
         if (!$this->addPassbookEntry()) {
            $this->mysqli->rollback();
            return FALSE;
         }
      }
      if (!$this->updateBalance()) {
         $this->mysqli->rollback();
         return FALSE;
      }
      return $this->mysqli->commit();
   }

   public function work() {
      if (!$this->is_form_submitted()) {
         return TRUE;
      }
      /* Form is being submitted. */
      $this->player_id     = trim($_POST['player_id']);
      $this->player_name   = $_POST['player_name'];
      $this->gender        = $_POST['gender'];
      $this->email         = $_POST['email'];
      $this->mobile        = $_POST['mobile'];
      $this->aadhar        = $_POST['aadhar'];
      $this->dob           = $_POST['dob'];
      $this->registered_by = $_SESSION['userid'];

      $this->connect();
      $this->getOfferDetails();
      $this->store();
      $this->disconnect();
   }
}

$page = new IraguAdminRegister();
$page->is_user_authenticated();
$page->work();
?>

<!doctype html>
<?php $page->displayCopyright(); ?>
<html>
<head>
 <title> <?php $page->displayTitle(); ?> </title>
</head>
<body>

<?php $page->displayStatus(); ?>

<div style="width: 80%;">
  <button class="menu">
    <a href="menu.php">Menu</a>
  </button>
</div>

<div>
 <form action="<?php $page->displaySelfURL(); ?>" method="post">
  <fieldset style="font-size: 1em;">
    <legend> Register A Player </legend>
      <p> <label for="player_id"> Player ID </label>
          <input type="text" id="player_id" name="player_id" maxlength="8"
                 size="8"
                 value="<?php echo $page->player_id; ?>"/>
      </p>
      <p> <label for="player_name"> Player Name </label>
          <input type="text" id="player_name" name="player_name" maxlength="30"
                 size="30"
                 value="<?php echo $page->player_name; ?>"/>
      </p>
      <p> <label for="gender"> Gender </label>
          <input type="text" id="gender" name="gender" maxlength="1"
                 size="1"
                 value="<?php echo $page->gender; ?>"/>
      </p>
      <p> <label for="email"> Email </label>
          <input type="text" id="email" name="email" maxlength="256"
                 size="30"
                 value="<?php echo $page->email; ?>"/>
      </p>
      <p> <label for="mobile"> Mobile </label>
          <input type="text" id="mobile" name="mobile" maxlength="15"
                 size="15"
                 value="<?php echo $page->mobile; ?>"/>
      </p>
      <p> <label for="aadhar"> Aadhar </label>
          <input type="text" id="aadhar" name="aadhar" maxlength="12"
                 size="12"
                 value="<?php echo $page->aadhar; ?>"/>
      </p>
      <p> <label for="dob"> Date of Birth </label>
          <input type="date" id="dob" name="dob"
                 value="<?php echo $page->dob; ?>" /> </p>

      <p> <label for="offer_id"> Offer ID </label>
          <input type="text" id="offer_id" name="offer_id" maxlength="8"
                 size="8"
                 value="<?php echo $page->offer_id; ?>" disabled/>
      </p>

      <p> <label for="cashback"> Offer Cash Back </label>
          <input type="text" id="cashback" name="cashback" maxlength="8"
                 size="8"
                 value="<?php displayInRupees($page->offer_cashback); ?>"
                 disabled/>
      </p>

    <input type="submit" id="add_player" name="add_player" value="Add Player"/>
  </fieldset>
</form>
</div>

</body>
</html>
