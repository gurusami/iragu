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
/* Iragu: Admin Interface: Recharge: Recharge For a Customer  */
session_start();

if (!isset($_SESSION['userid'])) {
   header('Location: ' . 'index.php');
   exit();
}

function isAuthorized() {
   return (isset($_SESSION['usertype']) &&
           strcmp($_SESSION['usertype'], "admin") == 0);
}

if (!isAuthorized()) {
  echo 'Not Authorized';
  exit();
}

include 'iragu-webapp.php';

class IraguAdminCustomerRecharge extends IraguWebapp {
   public $nick     = "";
   public $full_name     = "";
   public $email     = "";
   public $mobile_no     = "";
   public $recharge_amount  = "";
   public $offer_id    = "";
   public $cashback    = ""; /* in paise */
   public $notes       = "";
   public $recharge_by    = "";
   public $pay_mode    = "";
   public $balance;
   public $recharge_id;

   public function displayConfirmation() {
      if ($this->success) {
         echo '<p> Recharge Successful. </p>';
      } else {
         echo '<p> Recharge Failed. </p>';
      }
   }

   public function askForConfirmation() {
      $url = $this->getSelfURL();
      echo <<<EOF
<div>
 <form action="$url" method="post">
   <input type="text" id="pay_notes" name="pay_notes">
   <input type="hidden" id="nick" name="nick" value="$this->nick">
   <input type="hidden" id="offer_id" name="offer_id" value="$this->offer_id">
   <input type="hidden" id="pay_mode" name="pay_mode" value="$this->pay_mode">
   <input type="submit" id="recharge" name="recharge" value="Confirm Payment">
 </form>
</div>
EOF;
   }

   public function insert_into_recharge_table() {
     $query = "INSERT INTO ir_recharge (nick, offer_id, pay_mode, pay_notes, " .
              "recharge_by) VALUES (?, ?, ?, ?, ?)";
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('sssss', $_POST['nick'],
                                $_POST['offer_id'],
                                $_POST['pay_mode'],
                                $_POST['pay_notes'],
                                $_SESSION['userid']);
     $this->success = $stmt->execute();
     if ($this->success) {
        $this->recharge_id = $this->mysqli->insert_id;
     } else {
        $this->errmsg = $stmt->error;
     }
     return $this->success;
   }

   public function giveCashback() {
      $this->pay_notes = "Cashback for Recharge Id: " . $this->recharge_id;
      if ($this->updateBalanceCashback() && $this->getBalance() &&
          $this->cashbackPassbookEntry()) {
        return true;
      }
      return false;
   }

   public function cashbackPassbookEntry() {
     $query = "INSERT INTO ir_passbook (nick, trx_info, credit, running_total, " .
              " recharge_id) VALUES (?, ?, ?, ?, ?)";
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('ssiis', $_POST['nick'],
                               $this->pay_notes,
                               $this->cashback,
                               $this->balance,
                               $this->recharge_id);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg = $stmt->error;
     }
     return $this->success;
   }

   public function insert_into_passbook() {
     $this->pay_notes = "Recharge with Offer Id: " . $this->offer_id;
     $query = "INSERT INTO ir_passbook (nick, trx_info, credit, running_total, " .
              " recharge_id) VALUES (?, ?, ?, ?, ?)";
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('ssiis', $_POST['nick'],
                               $this->pay_notes,
                               $this->recharge_amount,
                               $this->balance,
                               $this->recharge_id);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg = $stmt->error;
     }
     return $this->success;
   }

   public function getBalance() {
     $query = <<<EOF
SELECT balance FROM ir_balance WHERE nick = ?;
EOF;
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('s', $_POST['nick']);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg = $this->errmsg . ": Failed to get balance.";
        return false;
     }
     $result = $stmt->get_result();
     if ($row = $result->fetch_array()) {
        $this->balance = $row[0];
        if (is_null($this->balance)) {
          $this->success = false;
          $this->errmsg = "Failed to get balance information";
          return false;
        }
     } else {
        $this->success = false;
        $this->errmsg .= $stmt->error . " MISSING DATA";
        return false;
     }
     $stmt->close();
     return true;
   }

   public function updateBalanceCashback() {
       $tableBalance = new TableBalance($this->mysqli);
       $this->success = $tableBalance->addBalance($this->cashback);
       return $this->success;
   }

   public function updateBalanceRechargeAmount() {
       $tableBalance = new TableBalance($this->mysqli);
       $this->success = $tableBalance->addBalance($this->recharge_amount);
       return $this->success;
   }

   public function updateDatabase() {
     /* Insert one record into the ir_recharge table */
     /* Insert two records in ir_passbook (recharge_amount and cashback) */
     /* Update the ir_balance table */
     $this->mysqli->begin_transaction();
     if ($this->insert_into_recharge_table()
         && $this->updateBalanceRechargeAmount()
         && $this->getBalance()
         && $this->insert_into_passbook()
         && $this->giveCashback()) {
        $this->mysqli->commit();
        $this->success = TRUE;
     } else {
        $this->mysqli->rollback();
        $this->success = FALSE;
     }
     return $this->success;
   }

   public function displayUserDetails() {
      echo <<<EOF
      <p> Nick Name: $this->nick </p>
      <p> Full Name: $this->full_name </p>
      <p> Email: $this->email </p>
      <p> Mobile: $this->mobile_no </p>
EOF;
   }

   public function displaySelectedOffer() {
      echo <<<EOF
      <p> Recharge Amount: $this->recharge_amount </p>
      <p> Cashback: $this->cashback </p>
EOF;
   }

   public function displayNickForm() {
      $url = $this->getSelfURL();
      $form = <<<EOD
<div>
 <form action="$url" method="post">
  <fieldset style="font-size: 1em;">
   <p> <label for="nick"> Nick </label>
       <input type="text" id="nick" name="nick" maxlength="8" size="8"/>
      </p>
   <input type="submit" id="choose_nick" name="choose_nick"
           value="Choose Nick"/>
  </fieldset>
</div>
EOD;
     echo $form;
}

   public function isUserKnown() {
     return isset($_POST['nick']);
   }

   public function isRechargeOfferChosen() {
     return isset($_POST['recharge_offer']);
   }

   public function getOfferDetails() {
      $query = "SELECT offer_id, recharge_amount, cashback FROM " .
               "ir_recharge_offers WHERE offer_id = ?";
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('s', $_POST['offer_id']);
      $stmt->execute();
      $stmt->bind_result($this->offer_id,
                         $this->recharge_amount,
                         $this->cashback);
      if ($stmt->fetch()) {
         $this->success = TRUE;
      } else {
         $this->success = FALSE;
         $this->errmsg = "Selected Offer Not Available: " . $query .
              ", offer_id=" . $_POST['offer_id'];
      }
      return $this->success;
   }

   public function getUserDetails() {
      $query = "SELECT nick, full_name, email, mobile_no FROM ir_people " .
               "WHERE nick = ?";
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('s', $_POST['nick']);
      $stmt->execute();
      $stmt->bind_result($this->nick,
                         $this->full_name,
                         $this->email,
                         $this->mobile_no);
      if ($stmt->fetch()) {
         $this->success = TRUE;
      } else {
         $this->success = FALSE;
         $this->errmsg = "User/Customer/Player Information Not Available: " .
                $query . ", NICK=" . $_POST['nick'];
      }
      return $this->success;
   }

   public function work() {
     if (isset($_POST['nick'])) {
       $this->getUserDetails();
     }
     if (isset($_POST['offer_id'])) {
       $this->getOfferDetails();
     }
     if (isset($_POST['pay_mode'])) {
       $this->pay_mode = $_POST['pay_mode'];
     }
     if (isset($_POST['pay_notes'])) {
       $this->pay_notes = $_POST['pay_notes'];
     }
     if (isset($_POST['nick']) && isset($_POST['offer_id']) &&
         isset($_POST['pay_mode']) && isset($_POST['pay_notes'])) {
       $this->updateDatabase();
     }
   }

   public function displaySelectedPaymentMethod() {
     echo <<<EOF
     <p> Payment Method: $this->pay_mode </p>
EOF;
   }

   public function displayPaymentOptions() {
     $query = <<<EOF
SELECT mode_id FROM ir_payment_mode;
EOF;
     $result = $this->mysqli->query($query) or die($this->mysqli->error);

     echo '<div>';
     echo '<h2> Payment Options </h2>';
     while ($row = $result->fetch_assoc()) {
         echo '<div>';
         echo ' <form action="' . $this->getSelfURL() . '" method="post">';
         echo ' <button>';
         echo '<p> Payment Mode: ' . $row['mode_id'] . ' </p>';
         echo ' </button>';
         echo ' <input type="hidden" id="nick" name="nick" value="' . $_POST['nick'] . '">';
         echo ' <input type="hidden" id="offer_id" name="offer_id" value="' . $_POST['offer_id'] . '">';
         echo ' <input type="hidden" id="pay_mode" name="pay_mode" value="' . $row['mode_id'] . '">';
         echo '</form>';
         echo ' </div>';
     }
     echo '</div>';
   }

   public function displayCurrentOffers() {
     $query = <<<EOF
SELECT offer_id, offer_from, offer_to, recharge_amount, cashback, notes, offer_made_by,
       offer_made_on
FROM ir_recharge_offers
WHERE CURRENT_DATE BETWEEN offer_from AND offer_to ORDER BY offer_from;
EOF;
     $result = $this->mysqli->query($query) or die($this->mysqli->error);

     echo '<div>';
     echo '<h2> Current Recharge Offers </h2>';
     while ($row = $result->fetch_assoc()) {
         $cashback = paiseToRupees($row['cashback']);
         $recharge = paiseToRupees($row['recharge_amount']);
         echo '<div>';
         echo ' <form action="' . $this->getSelfURL() . '" method="post">';
         echo ' <button>';
         echo '<p> Offer ID: ' . $row['offer_id'] . ' </p>';
         echo '<p> Recharge Amount: ' . $recharge . '</p>';
         echo '<p> Cashback: ' . $cashback . ' </p>';
         echo ' </button>';
         echo ' <input type="hidden" id="nick" name="nick" value="' . $_POST['nick'] . '">';
         $this->offer_id = $row['offer_id'];
echo <<<EOF
  <input type="hidden" id="offer_id" name="offer_id" value="$this->offer_id">
EOF;

         /* echo ' <input type="submit" id="choose_offer" name="choose_offer" value="' . $row['offer_id'] . '">'; */
         echo '</form>';
         echo ' </div>';
     }
     echo '</div>';
   }
}

$page = new IraguAdminCustomerRecharge();
$page->connect();
$page->beReady();
$page->work();

?>

<!doctype html>
<?php $page->displayCopyright(); ?>
<html>
<head>
 <title> <?php $page->displayTitle(); ?> </title>
</head>
<body>

<?php

$page->displayStatus(); 
include '14-iragu-top.php';

if (!isset($_POST['nick'])) {
    $page->displayNickForm();
} else if (isset($_POST['nick']) && !isset($_POST['offer_id'])) {
   $page->displayUserDetails();
   $page->displayCurrentOffers();
} else if (isset($_POST['nick']) && isset($_POST['offer_id']) &&
           !isset($_POST['pay_mode'])) {
   $page->displayUserDetails();
   $page->displaySelectedOffer();
   $page->displayPaymentOptions();
} else if (isset($_POST['nick']) && isset($_POST['offer_id']) &&
           isset($_POST['pay_mode']) && !isset($_POST['pay_notes'])) {
   $page->displayUserDetails();
   $page->displaySelectedOffer();
   $page->displaySelectedPaymentMethod();
   $page->askForConfirmation();
} else if (isset($_POST['nick']) && isset($_POST['offer_id']) &&
           isset($_POST['pay_mode']) && isset($_POST['pay_notes'])) {
   $page->displayUserDetails();
   $page->displaySelectedOffer();
   $page->displaySelectedPaymentMethod();
   $page->displayConfirmation();
}

?>

</body>
</html>
