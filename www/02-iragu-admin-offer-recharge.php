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
/* Iragu: Admin Interface: Recharge Offer: Make a Recharge Offer. */
include 'iragu-webapp.php';
include '01-iragu-global-utility.php';

class IraguAdminRechargeOffer extends IraguWebapp {
   public $offer_id   = "";
   public $offer_from = "";
   public $offer_to   = "";
   public $recharge_amount  = ""; /* in paise */
   public $cashback   = ""; /* in paise */
   public $notes      = "";
   public $offer_by   = "";

   public function isOfferPeriodValid() {
     $query = 'SELECT COUNT(*) FROM ir_recharge_offers WHERE ' .
              ' ((? BETWEEN offer_from AND offer_to OR
                  ? BETWEEN offer_from AND offer_to)
                 AND recharge_amount = ?) ' .
              ' OR (? < CURRENT_DATE AND ? < CURRENT_DATE)';
     $stmt = $this->mysqli->prepare($query);
     $this->success = $stmt->bind_param('ssiss', $this->offer_from,
                                                $this->offer_to,
                                                $this->recharge_amount,
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
        $this->errmsg = "Offer conflicts with another offer or ";
        $this->errmsg = $this->errmsg . "Offer date is in the past";
     }
     return $this->success;
   }

   public function work() {
     if (!isset($_POST['offer_id'])) {
        return;
     }
     $this->offer_id   = $_POST['offer_id'];
     $this->offer_from = $_POST['offer_from'];
     $this->offer_to   = $_POST['offer_to'];
     $this->cashback   = $_POST['cash_back'] * 100; /* Convert to paise. */
     $this->notes      = $_POST['reason'];
     $this->offer_by   = $_SESSION['userid'];
     $this->recharge_amount = $_POST['recharge_amount'] * 100; /* Paise */

     $this->isOfferPeriodValid();
     if ($this->success) {
        $this->make_offer();
     }
   }

   public function make_offer() {
     $query = 'INSERT INTO ir_recharge_offers (offer_id, offer_from, ' .
       'offer_to, recharge_amount, cashback, notes, offer_made_by) ' .
       'VALUES (?, ?, ?, ?, ?, ?, ?)';
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('sssiiss', $this->offer_id,
                                  $this->offer_from,
                                  $this->offer_to,
                                  $this->recharge_amount,
                                  $this->cashback,
                                  $this->reason,
                                  $this->offer_by);
     $this->success = $stmt->execute();
     if (!$this->success) {
         $this->errmsg = $stmt->error;
     }
   }

   public function displayUpcomingOffers() {
     $query = <<<EOF
SELECT offer_id, offer_from, offer_to, recharge_amount, cashback, notes,
       offer_made_by, offer_made_on
FROM ir_recharge_offers
WHERE CURRENT_DATE BETWEEN offer_from AND offer_to
OR CURRENT_DATE < offer_from ORDER BY offer_from;
EOF;
     $result = $this->mysqli->query($query) or die($this->mysqli->error);

     echo '<div>';
     echo '<h2> Current and Upcoming Offers </h2>';

     echo '<table>';
     echo '<tr> ';
     echo '<th> Offer ID </th> ';
     echo '<th> Offer Start Date </th> ';
     echo '<th> Offer End Date </th> ';
     echo '<th> Recharge Amount </th> ';
     echo '<th> Cash Back </th> ';
     echo '<th> Reason </th> ';
     echo '<th> Offer By </th> ';
     echo '<th> Offer Made On </th> ';
     echo '</tr> ';

     while ($row = $result->fetch_assoc()) {
         echo '<tr> ';
         echo '<td> ' . $row['offer_id']        . ' </td> ';
         echo '<td> ' . $row['offer_from']      . ' </td> ';
         echo '<td> ' . $row['offer_to']        . ' </td> ';
         echo '<td> ' . paiseToRupees($row['recharge_amount']) . ' </td> ';
         echo '<td> ' . paiseToRupees($row['cashback'])        . ' </td> ';
         echo '<td> ' . $row['notes']           . ' </td> ';
         echo '<td> ' . $row['offer_made_by']   . ' </td> ';
         echo '<td> ' . $row['offer_made_on']   . ' </td> ';
         echo '</tr> ';
     }
     echo '</table>';
     echo '</div>';
   }
}

$page = new IraguAdminRechargeOffer();
$page->is_user_authenticated();
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

<?php $page->displayStatus(); ?>

<div style="width: 80%;">
  <button class="menu">
    <a href="menu.php">Menu</a>
  </button>
</div>

<div>
 <form action="<?php $page->displaySelfURL(); ?>" method="post">
  <fieldset style="font-size: 1em;">
    <legend> Create Recharge Offer </legend>
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

      <p> <label for="recharge_amount"> Recharge Amount</label>
          <input type="number" step="0.01" id="recharge_amount"
                 name="recharge_amount"
                 value="<?php displayInRupees($page->recharge_amount); ?>"/>
      </p>

      <p> <label for="cash_back"> Cash Back </label>
          <input type="number" step="0.01" id="cash_back" name="cash_back"
                 value="<?php displayInRupees($page->cashback); ?>"
                 maxlength="8"/> </p>

      <p> <label for="notes"> Reason for Cash Back </label>
          <input type="text" id="notes" name="reason"
                 value="<?php echo $page->notes; ?>"
                 size="40" maxlength="100"/>
      </p>
    <input type="submit" id="recharge_offer" name="recharge_offer"
           value="Recharge Offer"/>
  </fieldset>
</form>
</div>

<?php
   $page->displayUpcomingOffers();
   $page->disconnect();
?>

</body>
</html>
