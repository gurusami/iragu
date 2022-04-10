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
/* Iragu: Bookings: Check Availability. */
include 'iragu-webapp.php';
include '01-iragu-global-utility.php';

function showFormToPickDate($url, $date_value, $court_id, $campus_id,
                            $date_offset) {
echo <<<EOF
<div>
   <form action="$url" method="post">
      <button>
         <p> $date_value </p>
         <p> $court_id </p>
         <p> $campus_id </p>
      </button>
      <input type="hidden" name="court_id" value="$court_id">
      <input type="hidden" name="campus_id" value="$campus_id">
      <input type="hidden" name="date_offset" value="$date_offset">
   </form>
</div>
EOF;
} /* showFormToPickDate() */

function showFormToSelectCourt($url, $court_id, $campus_id) {
echo <<<EOF
<div>
   <form action="$url" method="post">
      <button>
         <p> $court_id </p>
         <p> $campus_id </p>
      </button>
      <input type="hidden" name="court_id" value="$court_id">
      <input type="hidden" name="campus_id" value="$campus_id">
   </form>
</div>
EOF;
}

function showOneSlot($row) {
  $slot_no = $row['play_slot'];
  $status = "BOOKED";
  if (is_null($row['player_id'])) {
    $status = "OPEN";
  }
echo <<<EOF
<div>
    <button>
        <p> $slot_no </p>
        <p> $status </p>
    </button>
</div>
EOF;
}

class IraguCheckAvailability extends IraguWebapp {

   public function showAvailability($date_offset, $court_id) {
      $query = "SELECT court_id, play_date, play_slot, player_id " .
               "FROM ir_booking_slots WHERE court_id = ? and " .
               "play_date = CURRENT_DATE + INTERVAL ? DAY";
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('ii', $court_id, $date_offset);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' SHOW AVAILABILITY FAILED'; 
      }
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
         showOneSlot($row);
      }
   }

   public function displayAvailability() {
      if (isset($_POST['date_offset']) &&
          isset($_POST['court_id'])) {
          $this->showAvailability($_POST['date_offset'], $_POST['court_id']);
      }
   }

   public function givenOffsetGetDate($date_offset) {
        $date_query = "SELECT CURRENT_DATE + INTERVAL $date_offset DAY";
        $result = $this->mysqli->query($date_query);
        $row = $result->fetch_row();
        $result->close();
        return $row[0];
   }

   public function displayDateSelect() {
      $url = $this->getSelfURL();
      if (isset($_POST['date_offset'])) { 
         $date_offset = $_POST['date_offset'];
         $date_value = $this->givenOffsetGetDate($date_offset);
         echo "<p> SELECTED Date: $date_value </p>";
      } else if (isset($_POST['court_id'])) {
         $court_id = $_POST['court_id'];
         $campus_id = $_POST['campus_id'];
         for ($offset = 0; $offset < 10; $offset++) {
            $date_value = $this->givenOffsetGetDate($offset);
            if ($this->isBookingOpen($court_id, $date_value) > 0) {
               showFormToPickDate($url, $date_value, $court_id, $campus_id,
                                  $offset);
            } else {
               break;
            }
         }
      }
   } /* displayDateSelect() */

   public function displayCourtSelect() {
     $url = $this->getSelfURL();
     if (isset($_POST['court_id'])) { 
        echo "<p> SELECTED COURT: ", $_POST['court_id'], "</p>";
     } else {
         $court_query = "SELECT court_id, campus_id from ir_court;";
         if ($result = $this->mysqli->query($court_query)) {
            $this->success = true;
            while ($row = $result->fetch_row()) {
               $court_id = $row[0];
               $campus_id = $row[1];
               /* Show one form per court. */
               showFormToSelectCourt($url, $court_id, $campus_id);
            }
         } else {
            $this->success = false;
         }
     }
   } /* displayCourtSelect() */

   public function work() {
   }
}

$page = new IraguCheckAvailability();
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

<?php $page->displayStatus();

include '14-iragu-top.php'; ?>

<?php 
   $page->displayCourtSelect();
   $page->displayDateSelect();
   $page->displayAvailability();
   $page->disconnect();
?>

</body>
</html>



