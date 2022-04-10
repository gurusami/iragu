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
include '01-iragu-global-utility.php';

class IraguMakeRegisterOffer extends IraguWebapp {

   public $offer_id   = "";
   public $offer_from = "";
   public $offer_to   = "";
   public $cashback   = ""; /* in paise */
   public $reason     = "";
   public $offer_by   = "";

   public function list_upcoming_offers() {
     $query = <<<EOF
SELECT offer_id, offer_from, offer_to, cash_back, notes, offer_by
FROM ir_register_offers
WHERE CURRENT_DATE BETWEEN offer_from AND offer_to
OR CURRENT_DATE < offer_from;
EOF;
     $result = $this->mysqli->query($query) or die($this->mysqli->error);

     echo '<div>';
     echo '<h2> Current and Upcoming Offers </h2>';

     echo '<table>';
     echo '<tr> ';
     echo '<th> Offer ID </th> ';
     echo '<th> Offer Start Date </th> ';
     echo '<th> Offer End Date </th> ';
     echo '<th> Cash Back </th> ';
     echo '<th> Reason </th> ';
     echo '<th> Offer By </th> ';
     echo '</tr> ';

     while ($row = $result->fetch_assoc()) {
         echo '<tr> ';
         echo '<td> ' . $row['offer_id']   . ' </td> ';
         echo '<td> ' . $row['offer_from'] . ' </td> ';
         echo '<td> ' . $row['offer_to']   . ' </td> ';
         echo '<td> ' . paiseToRupees($row['cash_back'])  . ' </td> ';
         echo '<td> ' . $row['notes']      . ' </td> ';
         echo '<td> ' . $row['offer_by']   . ' </td> ';
         echo '</tr> ';
     }
     echo '</table>';
     echo '</div>';
   }

   public function check_offer_dates() {
     $query = 'SELECT COUNT(*) FROM ir_register_offers WHERE ' .
              ' ? BETWEEN offer_from AND offer_to OR ' .
              ' ? BETWEEN offer_from AND offer_to OR ' .
              ' (? < CURRENT_DATE AND ? < CURRENT_DATE)';
     $stmt = $this->mysqli->prepare($query);
     $this->success = $stmt->bind_param('ssss', 
                                        $this->offer_from,
                                        $this->offer_to,
                                        $this->offer_from,
                                        $this->offer_to);
     if (!$this->success) {
         $this->errmsg = $stmt->error;
         return $this->success;
     }
     $this->success = $stmt->execute();
     if (!$this->success) {
         $this->errmsg = $stmt->error;
         return $this->success;
     }
     $stmt->bind_result($count);
     $stmt->fetch();
     if ($count == 0) {
        $this->success = TRUE;
     } else {
        $this->success = FALSE;
        $this->errmsg = "Offer date overlaps with another offer";
     }
     return $this->success;
   }

   public function make_offer() {
     $query = 'INSERT INTO ir_register_offers (offer_id, offer_from, ' .
       'offer_to, cash_back, notes, offer_by) VALUES (?, ?, ?, ?, ?, ?)';
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('sssiss', $this->offer_id,
                                 $this->offer_from,
                                 $this->offer_to,
                                 $this->cash_back,
                                 $this->reason,
                                 $this->offer_by);
     $this->success = $stmt->execute();
     if (!$this->success) {
         $this->errmsg = $stmt->error;
     }
   }
}

$page = new IraguMakeRegisterOffer();
$page->connect();

if (isset($_POST['offer_id'])) {
   /* Form is being submitted. */

   $page->offer_id   = $_POST['offer_id'];
   $page->offer_from = $_POST['offer_from'];
   $page->offer_to   = $_POST['offer_to'];
   $page->cash_back  = $_POST['cash_back'] * 100; /* in paise */
   $page->reason     = $_POST['reason'];
   $page->offer_by   = $_SESSION['userid'];

   $page->check_offer_dates();
   if ($page->success) {
      $page->make_offer();
   }
}
?>

<!doctype html>

<?php $page->displayCopyright(); ?>

<html>
<?php include '10-head.php'; ?>
<body>

<?php

if (isset($_POST['offer_id'])) {
   $page->displayStatus();
}

include '14-iragu-top.php';
?>

<div>
 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
       method="post">
  <fieldset style="font-size: 1em;">
    <legend> Make a Register Offer </legend>
      <p> <label for="off_id"> Offer ID </label>
          <input type="text" id="off_id" name="offer_id" maxlength="8"
                 size="8"
                 value="<?php echo $page->offer_id; ?>"/>
      </p>
      <p> <label for="offer_from"> Offer Start Date </label>
          <input type="date" id="offer_from" name="offer_from"
                 value="<?php echo $page->offer_from; ?>" />
      </p>
      <p> <label for="offer_to"> Offer End Date </label>
          <input type="date" id="offer_to" name="offer_to"
                 value="<?php echo $page->offer_to; ?>" /> </p>

      <p> <label for="cash_back"> Cash Back </label>
          <input type="number" step="0.01" id="cash_back" name="cash_back"
                 value="<?php displayInRupees($page->cashback); ?>"
                 maxlength="8"/> </p>

      <p> <label for="notes"> Reason for Cash Back </label>
          <input type="text" id="notes" name="reason"
                 value="<?php echo $page->reason; ?>"
                 size="40" maxlength="100"/>
      </p>
    <input type="submit" id="do_offer" name="make_offer" value="Make An Offer"/>
  </fieldset>
</form>
</div>

<?php
   $page->list_upcoming_offers();
   $page->disconnect();
?>

</body>
</html>

