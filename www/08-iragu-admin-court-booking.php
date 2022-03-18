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

   public function bookCourt($player_id, $court_id, $play_date,
                             $begin_slot, $end_slot) {
     $query = <<<EOF
SELECT * FROM ir_booking_slots WHERE court_id = ? AND play_date = ? AND
play_slot >= ? AND play_slot <= ? FOR UPDATE
EOF;
     $this->startTrx();
     $stmt = $this->mysqli->prepare($query);
     $stmt->bind_param('ssii', $court_id, $play_date, $begin_slot, $end_slot);
     $stmt->execute();
     $result = $stmt->get_result();
     while ($row = $result->fetch_assoc()) {
        if (!is_null($row['player_id'])) {
           $this->rollbackTrx();
           $this->success = false;
           $this->errmsg = $this->errmsg . ": Booking Court Failed";
           return false;
        }
     }

     $update = <<<EOF
UPDATE ir_booking_slots SET player_id = ? WHERE court_id = ?
AND play_date = ? AND play_slot >= ? AND play_slot <= ?
EOF;
     $upd_stmt = $this->mysqli->prepare($update);
     $upd_stmt->bind_param('sssii', $player_id,
                                    $court_id,
                                    $play_date,
                                    $begin_slot,
                                    $end_slot);
     $this->success = $upd_stmt->execute();
     if (!$this->success) {
        $this->errmsg = $this->errmsg . ":" . $stmt->error;
     }
     $this->commitTrx();
     return $this->success;
   }

   public function work() {
      if (isset($_POST['player_id']) &&
          isset($_POST['court_id']) &&
          isset($_POST['play_date']) &&
          isset($_POST['begin_slot']) &&
          isset($_POST['end_slot'])) {
      
         /* Now we have all the information necessary to book the court. */
         /* TODO:  1. Calculate total cost of booking. */
         /* TODO: 2. Check if balance is sufficient. */
          $this->success = $this->bookCourt($_POST['player_id'],
                                            $_POST['court_id'],
                                            $_POST['play_date'],
                                            $_POST['begin_slot'],
                                            $_POST['end_slot']);
          if ($this->success) {
              $this->errmsg = "BOOKING COURT PASSED";
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
   }
   $page->disconnect();
?>

</body>
</html>



