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

function echoTitle() {
   echo "Iragu: Badminton Court Management Software";
}

function getPostAsHiddenInput($name) {
  $result = "";
  if (isset($_POST[$name])) {
     $value = $_POST[$name];
$result = <<<EOF
<input type="hidden" name="$name" value="$value">
EOF;
  }
  return $result;
}

function getDurationString($slots) {
   $minutes = $slots * 15;
   $result = "$minutes min";
   if ($minutes >= 60) {
     $hours = intdiv($minutes, 60);
     $minutes = $minutes % 60;
     if ($minutes == 0) {
        $result = "$hours hr"; 
     } else {
        $result = "$hours hr $minutes min";
     }
   }
   return $result;
}

function showOneDuration($url, $slots) {
  $player_id = getPostAsHiddenInput('player_id');
  $court_id  = getPostAsHiddenInput('court_id');
  $play_date  = getPostAsHiddenInput('play_date');
  $duration = getDurationString($slots);
echo <<<EOF
<div>
  <form action=$url method="post">
  <button>
    <p> $duration </p>
  </button>
  <input type="hidden" name="play_duration" value="$slots">
  $player_id
  $court_id 
  $play_date
  </form>
</div>
EOF;
}

function convertMinutes($minutes) {
   $hours = intdiv($minutes, 60);
   $minutes = $minutes % 60;
   $hours_xx = "$hours";
   $minutes_xx = "$minutes";
   if ($hours < 10) {
     $hours_xx = "0$hours";
   }
   if ($minutes < 10) {
     $minutes_xx = "0$minutes";
   }
   return "$hours_xx:$minutes_xx";
}

function getStartTimeDisplay($slot) {
   $minutes = ($slot - 1) * 15;
   return convertMinutes($minutes);
}

function getEndTimeDisplay($slot) {
   $minutes = $slot * 15;
   return convertMinutes($minutes);
}

function getTimeDisplay($begin_slot, $end_slot) {
   $from = getStartTimeDisplay($begin_slot);
   $to = getEndTimeDisplay($end_slot);
   return "$from to $to";
}

function showOneBookableSlot($url, $begin_slot, $end_slot) {
  $player_id = getPostAsHiddenInput('player_id');
  $court_id  = getPostAsHiddenInput('court_id');
  $play_date  = getPostAsHiddenInput('play_date');
  $play_duration  = getPostAsHiddenInput('play_duration');
  $slot_in_time = getTimeDisplay($begin_slot, $end_slot);
echo <<<EOF
<div>
  <form action="$url" method="post">
  <button>
    <p> $slot_in_time </p>
  </button>
  <input type="hidden" name="end_slot" value="$end_slot">
  <input type="hidden" name="begin_slot" value="$begin_slot">
  $player_id
  $court_id
  $play_date
  $play_duration
  </form>
</div>
EOF;
}

function showAvailableSlots($url, $mysqli, $court_id, $play_date, $play_duration) {
  $query = "SELECT COUNT(*) FROM ir_booking_slots WHERE court_id = ? AND " .
           "play_date = ? AND play_slot >= ? AND play_slot <= ? " .
           " AND player_id IS NULL";
  $stmt = $mysqli->prepare($query);
  for ($begin_slot = 1; $begin_slot < 97; $begin_slot++) {
      $end_slot = $begin_slot + $play_duration - 1;
      $stmt->bind_param('ssii', $court_id, $play_date, $begin_slot, $end_slot);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($row = $result->fetch_array()) {
         if ($row[0] == $play_duration) {
             showOneBookableSlot($url, $begin_slot, $end_slot);
         } 
      }
  }
}

function showOneDate($url, $row) {
  $play_date = $row['play_date'];
  $player_id = getPostAsHiddenInput('player_id');
  $court_id  = getPostAsHiddenInput('court_id');
echo <<<EOF
<div>
  <form action=$url method="post">
  <button>
    <p> $play_date </p>
  </button>
  <input type="hidden" name="play_date" value="$play_date">
  $player_id
  $court_id 
  </form>
</div>
EOF;
}

function showOneCourt($url, $row) {
  $court_id = $row['court_id'];
  $campus_id = $row['campus_id'];
  $slot_price = $row['price_per_slot'];
  $player_id = getPostAsHiddenInput('player_id');
echo <<<EOF
<div>
  <form action=$url method="post">
  <button>
    <p> $court_id </p>
    <p> $campus_id </p>
    <p> $slot_price </p>
  </button>
  <input type="hidden" name="court_id" value="$court_id">
  $player_id
  </form>
</div>
EOF;
}

function showOneUser($url, $row) {
  $nick = $row['nick'];
  $full_name = $row['full_name'];
  $mobile_no = $row['mobile_no'];
echo <<<EOF
<div>
  <form action=$url method="post">
  <button>
    <p> $nick </p>
    <p> $full_name </p>
    <p> $mobile_no </p>
  </button>
  <input type="hidden" name="player_id" value="$nick">
  </form>
</div>
EOF;
}

class IraguWebapp {
  public $mysqli;
  public $success; /* FALSE if an error occurred. */
  public $errmsg;
  public $copyright_notice = <<<EOF
<!--
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
-->
EOF;

   public function isBookingOpen($court_id, $date_value) {
        $query = "SELECT count(*) FROM ir_bookings_open WHERE " .
                 "play_date = '$date_value'";
        if ($result = $this->mysqli->query($query)) {
           $row = $result->fetch_row();
           $result->close();
           $this->success = true;
        } else {
           $this->success = false;
        }
        return $row[0];
   }

  public function connect() {
    $this->mysqli = mysqli_init();

    if (!$this->mysqli) {
      die('mysqli_init failed');
    }

    /* The user l2admin can do SELECT, INSERT, UPDATE, DELETE operations on
    all tables in kdb database. */
    if (!$this->mysqli->real_connect('localhost', 'l2admin', '#TNExit2030#',
        'kdb')) {
       die('Connect Error (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
    }
    $this->success = true;

    $trace_insert = "INSERT INTO ir_trace (trace_log) VALUES (?)";
    $trace_stmt = $this->mysqli->prepare($trace_insert);
  }

  public function is_user_authenticated() {
    session_start();
    if (!isset($_SESSION['userid'])) {
      header('Location: ' . 'index.php');
      exit();
    }
    return TRUE;
  }

  public function displayStatus() {
     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($this->success) {
           echo '<p> SUCCESS ' . $this->errmsg . ' </p>';
        } else {
           echo '<p> FAILURE: ' . $this->errmsg . ' </p>';
        }
     }
  }

  public function disconnect() {
      $this->mysqli->close();
  }

  public function displayTitle() {
     echo 'Iragu: Badminton Court Management Software';
  }

  public function displayCopyright() {
     echo $this->copyright_notice;
  }

  public function startTrx() {
    $this->mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
  }

  public function commitTrx() {
    $this->mysqli->commit();
  }

  public function rollbackTrx() {
    $this->mysqli->rollback();
  }

  public function getSelfURL() {
     return htmlspecialchars($_SERVER["PHP_SELF"]);
  }

  public function displaySelfURL() {
     echo htmlspecialchars($_SERVER["PHP_SELF"]);
  }

  public function output_begin() {
    echo '<!doctype html>';
    echo $this->copyright_notice;
echo <<<EOF
<html>
<head>
 <title> Iragu: Badminton Court Management Software</title>
</head>
<body>
EOF;
  }

  /** Collect the necessary data from the database to display to the end user.
  This is done when a page is displayed with a form (form is not yet submitted).
  The default implementation in this base class is to do nothing. */
  public function beReady() {
  }

  /** Process the POST data when a form is submitted.  The default
  implementation in this base class is to do nothing. */
  public function work() {
  }

  /** Pick a user/player on behalf of whom an operation is being done. */
  public function pickUser() {
    if (isset($_POST['player_id'])) {
      /* If a player is already selected, display the player id. */
      echo "<p> Player ID: ", $_POST['player_id'], "</p>";
    } else {
      $url = $this->getSelfURL();
      echo "<p> Choose Player ID </p>";
      $query = "SELECT nick, full_name, mobile_no FROM ir_people LIMIT 50";
      $result = $this->mysqli->query($query);

      while ($row = $result->fetch_assoc()) {
         showOneUser($url, $row);
      }
    }
  }

  public function pickCourt() {
    if (isset($_POST['court_id'])) {
      /* If a court is already selected, display the court id. */
      echo "<p> Court ID: ", $_POST['court_id'], "</p>";
    } else {
      $url = $this->getSelfURL();
      echo "<p> Choose Court ID </p>";
      $query = "SELECT court_id, campus_id, price_per_slot FROM ir_court LIMIT 50";
      $result = $this->mysqli->query($query);

      while ($row = $result->fetch_assoc()) {
         showOneCourt($url, $row);
      }
    }
  }

  public function pickDate() {
    if (isset($_POST['play_date'])) {
      echo "<p> Play Date: ", $_POST['play_date'], "</p>";
    } else {
      $url = $this->getSelfURL();
      echo "<p> Choose Play Date </p>";
      $query = "SELECT play_date FROM ir_bookings_open WHERE " .
               "play_date >= CURRENT_DATE";
      $result = $this->mysqli->query($query);
      while ($row = $result->fetch_assoc()) {
         showOneDate($url, $row);
      }
    }
  } /* pickDate() */

  public function pickDuration() {
    if (isset($_POST['play_duration'])) {
      echo "<p> Play Duration: ", $_POST['play_duration'], "</p>";
    } else {
      $url = $this->getSelfURL();
      for ($slots = 1; $slots < 13; $slots++) {
         showOneDuration($url, $slots);
      }
    }
  }

  public function pickSlots() {
    if (isset($_POST['begin_slot'])) {
      echo "<p> Begin Slot: ", $_POST['begin_slot'], "</p>";
    } else {
      $url = $this->getSelfURL();
      showAvailableSlots($url, $this->mysqli, $_POST['court_id'],
                         $_POST['play_date'], $_POST['play_duration']);
    }
  }

  public function addComment($comment) {
echo <<<EOF
<!-- $comment -->
EOF;
  }

  public function trace($log) {
    $trace_stmt->bind_param('s', $log);
    $trace_stmt->execute();
  }
} /* class IraguWebapp */

?>
