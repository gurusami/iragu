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
/* Iragu: Admin: Bookings: Court Booking. */
include 'iragu-webapp.php';
include '01irglut.php';

class IraguAdminCourtBooking extends IraguWebapp {
   public $player_id;
   public $court_id;
   public $play_date;
   public $play_duration;
   public $begin_slot;
   public $end_slot;
   public $booking_cost;
   public $balance;

   public function isDataComplete() {
      if (isset($_POST['player_id']) &&
          isset($_POST['court_id']) &&
          isset($_POST['play_date']) &&
          isset($_POST['play_duration']) &&
          isset($_POST['begin_slot']) &&
          isset($_POST['end_slot'])) {
         $this->savePostData();
         return true;
      }
      return false;
   }

   public function show() {
      $balance = paiseToRupees($this->balance);
      $show_time = getTimeDisplay($this->begin_slot, $this->end_slot);
echo <<<EOF
<div>
  <table>
    <tr> <td> Player ID </td> <td> $this->player_id </td> </tr>
    <tr> <td> Court ID </td> <td> $this->court_id </td> </tr>
    <tr> <td> Play Date </td> <td> $this->play_date </td> </tr>
    <tr> <td> Time </td> <td> $show_time </td> </tr>
    <tr> <td> Balance </td> <td> $balance </td> </tr>
  </table>
</div>
EOF;
   }

   public function savePostData() {
      $this->player_id  = $_POST['player_id'];
      $this->court_id   = $_POST['court_id'];
      $this->play_date  = $_POST['play_date'];
      $this->play_duration  = $_POST['play_duration'];
      $this->begin_slot = $_POST['begin_slot'];
      $this->end_slot   = $_POST['end_slot'];
   }

   public function calcCost() {
     $query = <<<EOF
SELECT price_per_slot * ? FROM ir_court WHERE court_id = ?;
EOF;
     if (is_null($this->play_duration)) {
        $this->success = false;
        $this->errmsg .= ": PLAY DURATION IS NULL";
        return false;
     }

     if (is_null($this->court_id)) {
        $this->success = false;
        $this->errmsg .= ": COURT ID IS NULL";
        return false;
     }

     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('is', $this->play_duration, $this->court_id);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg .= ": Failed to calculate cost: " . $stmt->error;
        return false;
     }
     $result = $stmt->get_result();
     if ($row = $result->fetch_array()) {
        $this->booking_cost = $row[0];
     } else {
        $this->success = false;
        $this->errmsg .= ": Failed to fetch court details: $this->court_id";
        return false;
     }
     $stmt->close();
     return true;
   }

   public function deductMoney() {
     $query = <<<EOF
UPDATE ir_balance
SET last_updated = CURRENT_TIMESTAMP, balance = balance - ?
WHERE nick = ?;
EOF;
     if (is_null($this->booking_cost) ) {
        $this->success = false;
        $this->errmsg .= ": BOOKING COST IS NULL";
        return false;
     }

     if ($stmt = $this->mysqli->prepare($query)) {
        $stmt->bind_param('is', $this->booking_cost, $this->player_id);
        $this->success = $stmt->execute();
        if ($this->success) {
           $this->balance = $this->balance - $this->booking_cost;
        } else {
           $this->errmsg .= $stmt->error;
        }
        $stmt->close();
     } else {
        $this->success = false;
        $this->errmsg .= $stmt->error;
     }
     return $this->success;
   }

   public function checkAndReserveMoney() {
     $query = <<<EOF
SELECT balance FROM ir_balance WHERE nick = ? FOR UPDATE;
EOF;
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('s', $this->player_id);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg = $this->errmsg . ": Failed to reserve money.";
        return false;
     }
     $result = $stmt->get_result();
     if ($row = $result->fetch_array()) {
        $this->balance = $row[0];
        if (is_null($this->balance) || $this->balance < $this->booking_cost) {
          $this->success = false;
          $this->errmsg = "NOT SUFFICIENT BALANCE";
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

   public function checkAndReserveSlots() {
     $query = <<<EOF
SELECT * FROM ir_booking_slots WHERE court_id = ? AND play_date = ? AND
play_slot >= ? AND play_slot <= ? FOR UPDATE
EOF;
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('ssii', $this->court_id,
                               $this->play_date,
                               $this->begin_slot,
                               $this->end_slot);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg = $this->errmsg . ": QUERY FAILED: $query";
        $stmt->close();
        return false;
     }
     $result = $stmt->get_result();
     while ($row = $result->fetch_assoc()) {
        if (!is_null($row['player_id'])) {
           $stmt->close();
           $this->success = false;
           $this->errmsg = $this->errmsg . ": Booking Court Failed";
           return false;
        }
     }
     return true;
   }

   public function bookSlots() {
     $update = <<<EOF
UPDATE ir_booking_slots SET player_id = ? WHERE court_id = ?
AND play_date = ? AND play_slot >= ? AND play_slot <= ?
EOF;
     $stmt = $this->mysqli->prepare($update);
     $stmt->bind_param('sssii', $this->player_id,
                                    $this->court_id,
                                    $this->play_date,
                                    $this->begin_slot,
                                    $this->end_slot);
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg = $this->errmsg . ": QUERY FAILED: $query";
        $stmt->close();
        return false;
     }
     $stmt->close();
     return true;
   }

   public function bookCourt() {
     $this->startTrx();
     $this->success = $this->checkAndReserveSlots();
     if (!$this->success) {
        $this->errmsg .= ": Failed to reserve slots.";
        $this->rollbackTrx();
        return false;
     }
     $this->success = $this->calcCost();
     if (!$this->success) {
        $this->errmsg .= ": Cost calculation failed";
        $this->rollbackTrx();
        return false;
     }
     $this->success = $this->checkAndReserveMoney();
     if (!$this->success) {
        $this->errmsg .= ": Reserve Money Failed";
        $this->rollbackTrx();
        return false;
     }
     $this->success = $this->bookSlots();
     if (!$this->success) {
        $this->errmsg .= ": Booking Slots Failed";
        $this->rollbackTrx();
        return false;
     }
     $this->success = $this->deductMoney();
     if (!$this->success) {
        $this->errmsg .= ": Deduct Money Failed";
        $this->rollbackTrx();
        return false;
     }
     $this->commitTrx();
     return $this->success;
   }

   public function work() {
      if ($this->isDataComplete()) {
         /* Now we have all the information necessary to book the court. */
         /* TODO:  1. Calculate total cost of booking. */
         /* TODO: 2. Check if balance is sufficient. */
          $this->success = $this->bookCourt();
          if ($this->success) {
              $this->errmsg .= ": BOOKING COURT PASSED";
          } else {
              $this->errmsg .= ": BOOKING COURT FAILED";
          }
      }
   }
}

$page = new IraguAdminCourtBooking();
$page->is_user_authenticated();
$page->connect();
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

<?php 
   /* Pick the user/player for whom the court is being booked. */

   if (!isset($_POST['player_id'])) {
      $page->pickUser();
   } else if (!isset($_POST['court_id'])) {
      $page->pickCourt();
   } else if (!isset($_POST['play_date'])) {
      $page->pickDate();
   } else if (!isset($_POST['play_duration'])) {
      $page->pickDuration();
   } else if (!isset($_POST['begin_slot'])) {
      $page->pickSlots();
   } else {
      /* Now we have all the information necessary to book the court. */
      if ($page->success) {
         $page->show();
      }
   }
   $page->disconnect();
?>

</body>
</html>



