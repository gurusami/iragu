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
/* Iragu: Admin Interface: Bookings: Open bookings for a day. */
include 'iragu-webapp.php';
include '01irglut.php';

class IraguAdminOpenBookings extends IraguWebapp {

   public function insertSlots($offset) {
      $query_court = "SELECT court_id from ir_court;";
      $query = "INSERT INTO ir_booking_slots (court_id, play_date, play_slot) VALUES (?, CURRENT_DATE + ?, ?);";
      $stmt = $this->mysqli->prepare($query);
      $court_id = '';
      $slot_no = 1;
      $stmt->bind_param('sii', $court_id, $offset, $slot_no);
      $result = $this->mysqli->query($query_court);
      while ($row = $result->fetch_row()) {
         $court_id = $row[0];
         $slot_no = 1;
         while ($slot_no < 97) {
             $this->success = $stmt->execute();
             if (!$this->success) {
                 $this->errmsg = $this->errmsg . $stmt->error .  ' SLOT INSERTION FAILED';
                 break;
             }
             $slot_no++;
         }
         if (!$this->success) {
             break;
         }
      }
      return $this->success;
   }

   public function doOpen($offset) {
      $this->startTrx();
      $query = <<<EOF
INSERT INTO ir_bookings_open (play_date, opened_by) VALUES (CURRENT_DATE + ?, ?);
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('is', $offset, $_SESSION['userid']);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' Open Booking Failed'; 
      }
      $stmt->close();
      if ($this->success) {
         $this->insertSlots($offset);
      }
      if ($this->success) {
         $this->commitTrx();
      } else {
         $this->rollbackTrx();
      }
      return $this->success;
   }

   public function getTheDate($offset) {
      $query = 'SELECT CURRENT_DATE + INTERVAL ? DAY';
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('i', $offset);
      $stmt->execute();
      $stmt->bind_result($the_date);
      $stmt->fetch();
      $stmt->close();
      return $the_date;
   }

   public function isBookingOpen($offset) {
      $query = <<<EOF
SELECT COUNT(*) FROM ir_bookings_open WHERE play_date = CURRENT_DATE + ?;
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('i', $offset);
      $stmt->execute();
      $stmt->bind_result($booking_open);
      $stmt->fetch();
      $stmt->close();
      return $booking_open;
   }

   public function displayFormForDay($offset) {
     $the_date = $this->getTheDate($offset);
     $is_open = $this->isBookingOpen($offset);
     $url = $this->getSelfURL();
    
     if ($is_open) {
       echo "<div> <button> <p> $the_date </p> <p> OPENED </p> </button> </div>";
     } else {
       echo <<<EOF
<div>
<form action="$url" method="post">
<button>
<p> $the_date </p>
</button>
<input type="hidden" name="date_offset" value="$offset">
</form>
</div>
EOF;
     }
   } /* displayFormForDay() */

   public function work() {
     if (isset($_POST['date_offset'])) {
        $this->doOpen($_POST['date_offset']);
     }
   }
}

$page = new IraguAdminOpenBookings();
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
   for ($i = 0; $i < 10; $i++) {
      $page->displayFormForDay($i);
   }
   $page->disconnect();
?>

</body>
</html>


