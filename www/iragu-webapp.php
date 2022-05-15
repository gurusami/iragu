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

/* Details to connect to MySQL.  The user l2admin can do SELECT, INSERT,
UPDATE, DELETE operations on all tables in kdb database. */

require 'autoload.php';

include '01-iragu-global-utility.php';

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

<div class="grid-item">
  <form action="$url" method="post">
  <button>
    <p> $duration </p>
  </button>
  <input type="hidden" name="play_duration" value="$slots">
  $player_id
  $court_id 
  $play_date
  </form>
</div> <!-- grid-item -->

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

<div class="grid-item">
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
</div> <!-- grid-item -->

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

<div class="grid-item">
  <form action=$url method="post">
  <button>
    <p> $play_date </p>
  </button>
  <input type="hidden" name="play_date" value="$play_date">
  $player_id
  $court_id 
  </form>
</div> <!-- grid-item -->

EOF;
}

function showOneCourt($url, $row) {
  $court_id = $row['court_id'];
  $campus_id = $row['campus_id'];
  $slot_price = $row['price_per_slot'];
  $player_id = getPostAsHiddenInput('player_id');
echo <<<EOF

<div class="grid-item">
  <form action="$url" method="post">
  <button>
    <p> $court_id </p>
    <p> $campus_id </p>
    <p> $slot_price </p>
  </button>
  <input type="hidden" name="court_id" value="$court_id">
  $player_id
  </form>
</div> <!-- grid-item -->

EOF;
}

function showOneUser($url, $row) {
  $nick = $row['nick'];
  $full_name = $row['full_name'];
  $mobile_no = $row['mobile_no'];
echo <<<EOF

<div class="grid-item">
  <form action="$url" method="post">
   <button>
    <p> $nick </p>
    <p> $full_name </p>
    <p> $mobile_no </p>
   </button>
  <input type="hidden" name="player_id" value="$nick">
  </form>
</div> <!-- grid-item -->

EOF;
}

class IraguWebapp {
   public $mysqli;
   public $success; /* FALSE if an error occurred. */
   public $errmsg;
   public $error;
   public $errno;

   const ERRNO_HOSTNAME_INVALID = 11;
   const ERRNO_DBUSER_INVALID = 12;
   const ERRNO_DBNAME_INVALID = 13;
   const ERRNO_DBPASSWD_MISSING = 14;

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
       $dbhost = "localhost";
       $dbuser = 'l2admin';
       $dbname = 'kdb';
       $dbpasswd = PrivateConfig::DB_PASSWD;

       if (!isset($dbhost) || is_null($dbhost)) {
           $this->errno = self::ERRNO_HOSTNAME_INVALID;
           $this->error = "Database Hostname is invalid";
           return false;
       }

       if (!isset($dbuser) || is_null($dbuser)) {
           $this->errno = self::ERRNO_DBUSER_INVALID;
           $this->error = "Database username is invalid";
           return false;
       }

       if (!isset($dbname) || is_null($dbname)) {
           $this->errno = self::ERRNO_DBNAME_INVALID;
           $this->error = "Database name is invalid";
           return false;
       }

       if (!isset($dbpasswd) || is_null($dbpasswd)) {
           $this->errno = self::ERRNO_DBPASSWD_MISSING;
           $this->error = "Database password is missing";
           return false;
       }

       if (($this->mysqli = mysqli_init()) == FALSE) {
           return FALSE;
       }
       if (!$this->mysqli->real_connect($dbhost, $dbuser, $dbpasswd, $dbname)) {
           return FALSE;
       }
       return $this->mysqli;
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
     $show = false;
     if (!empty($_SESSION['show_status']) && $_SESSION['show_status']) {
       $show = true;
     }

     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($this->errno == 0) {
           if ($show) {
               echo '<p> SUCCESS ' . $this->error . ' </p>';
           }
        } else {
           echo '<div id="div_status"> <p id="p_error"> FAILURE: ' .
               $this->error .  ' </p> </div>';
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

   /** Do cleanup at the end of page display. */
   public function cleanup() {
       return true;
   }

/******************************************************************************/
/** TABLE: ir_people */

  /** Pick a user/player on behalf of whom an operation is being done. */
  public function pickUser() {
    if (isset($_POST['player_id'])) {
      /* If a player is already selected, display the player id. */
      echo "<p> Player ID: ", $_POST['player_id'], "</p>";
    } else {
      $url = $this->getSelfURL();
      $query = "SELECT nick, full_name, mobile_no FROM ir_people LIMIT 50";
      $result = $this->mysqli->query($query);

      while ($row = $result->fetch_assoc()) {
         showOneUser($url, $row);
      }
    }
  }

   public function getUserDetails($nick) {
       $query = "SELECT * FROM ir_people WHERE nick = ?";
       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
           $this->errmsg = "Could not fetch user details";
           $this->success = FALSE;
           return FALSE;
       }
       if ($stmt->bind_param('s', $nick) == FALSE) {
           $stmt->close();
           $this->errmsg = $this->mysqli->error;
           $this->success = FALSE;
           return FALSE;
       }
       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->errmsg .= $this->mysqli->error;
           $this->success = FALSE;
           return FALSE;
       }
       if (($result = $stmt->get_result()) == FALSE) {
           $this->errmsg .= $stmt->error;
           $stmt->close();
           $this->success = FALSE;
           return FALSE;
       }
       return $result->fetch_object();
   }

  /** Insert a record into the ir_people table. */
  public function insertPeople() {
    if (!isset($_POST['player_id'])) {
      $this->errmsg = "Player ID is not set";
      $this->success = FALSE;
      return FALSE;
    }
    if (!isset($_POST['player_name'])) {
      $this->errmsg = "Player name is not set";
      $this->success = FALSE;
      return FALSE;
    }
    if (!isset($_POST['email'])) {
      $this->errmsg = "Email address is not available.";
      $this->success = FALSE;
      return FALSE;
    }
    if (!isset($_POST['mobile_no'])) {
      $this->errmsg = "Mobile phone number is not available.";
      $this->success = FALSE;
      return FALSE;
    }
    if (!isset($_POST['offer_id'])) {
       /* It is not a problem if offer_id is missing.  It simply means, there
       were no offers available at the time of registration. */
    }

    $query = "INSERT INTO ir_people (nick, full_name, email, mobile_no, " .
             "offer_id, registered_by) VALUES (LOWER(TRIM(?)), ?, ?, ?, ?, ?)";

    if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
      $this->errmsg = $this->mysqli->error;
      $this->success = FALSE;
      return FALSE;
    }

    if ($stmt->bind_param('ssssss', $_POST['player_id'],
                                   $_POST['player_name'],
                                   $_POST['email'],
                                   $_POST['mobile_no'],
                                   $_POST['offer_id'],
                                   $_POST['player_id']) == FALSE) {
      $stmt->close();
      $this->errmsg = $this->mysqli->error;
      $this->success = FALSE;
      return FALSE;
    }

    if ($stmt->execute() == FALSE) {
      $stmt->close();
      $this->errmsg .= $this->mysqli->error;
      $this->errmsg .= "Iragu: insertPeople() FAILED";
      $this->success = FALSE;
      return FALSE;
    }

    return TRUE;
  }

/******************************************************************************/
/** TABLE: ir_login */

  public function tableLoginInsert() {
     if (!isset($_POST['player_id'])) {
        $this->errmsg .= "Player ID is not set";
        return FALSE;
     }
     if (!isset($_POST['password_1'])) {
        $this->errmsg .= "Password is not set";
        return FALSE;
     }
     if (!isset($_POST['password_2'])) {
        $this->errmsg .= "Confirmation password is not set";
        return FALSE;
     }
     if (strcmp($_POST['password_1'], $_POST['password_2']) != 0) {
        $this->errmsg .= "Passwords does not match";
        return FALSE;
     }

     $query = "INSERT INTO ir_login (nick, token) VALUES (LOWER(TRIM(?)), " .
              " sha2(?, 256))";

     if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
        $this->errmsg .= $this->mysqli->error;
        return FALSE;
     }

     if ($stmt->bind_param('ss', $_POST['player_id'], $_POST['password_1']) == FALSE) {
        $this->errmsg .= $this->mysqli->error;
        return FALSE;
     }
     if ($stmt->execute() == FALSE) {
        $this->errmsg .= $this->mysqli->error;
        $this->errmsg .= "Iragu: tableLoginInsert() FAILED";
        return FALSE;
     }
     $stmt->close();
     return TRUE;
  }

/******************************************************************************/
/** TABLE: ir_passbook */

   /** Add an entry to the table ir_passbook for a user while registering.
   @return TRUE on success, FALSE on failure. */
   public function tablePassbookRegister() {
     if (!isset($_POST['player_id'])) {
        $this->errmsg .= "Player ID is not set";
        $this->success = FALSE;
        return FALSE;
     }
     if (!isset($_POST['offer_cashback'])) {
        $this->errmsg .= "Offer cashback is not set";
        $this->success = FALSE;
        return FALSE;
     }

     $trx_info = 'Cashback for Registering';
     $query = "INSERT INTO ir_passbook (nick, trx_info, credit, running_total)"
              . " VALUES (?, ?, ?, ?)";
     if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
        $this->errmsg .= $this->mysqli->error;
        $this->success = FALSE;
        return FALSE;
     }

     if ($stmt->bind_param('ssii', $_POST['player_id'],
                               $trx_info,
                               $this->offer_cashback,
                               $this->offer_cashback) == FALSE) {
        $this->errmsg .= $this->mysqli->error;
        $this->success = FALSE;
        return FALSE;
     }
     $this->success = $stmt->execute();
     if (!$this->success) {
        $this->errmsg .= $stmt->error;
        $this->errmsg .= "Iragu: tablePassbookRegister() FAILED";
     }
     $stmt->close();
     return $this->success;
   }

/******************************************************************************/
/** TABLE: ir_balance */

   /** While registering insert a record into table ir_balance. */
   public function tableBalanceRegister($player_id, $cashback) {
      $query = "INSERT INTO ir_balance (nick, balance) VALUES (?, ?)";
      if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
         $this->errmsg .= $this->mysqli->error;
         $this->success = FALSE;
         return FALSE;
      }

      if ($stmt->bind_param('si', $player_id, $cashback) == FALSE) {
         $this->errmsg .= $this->mysqli->error;
         $this->success = FALSE;
         return FALSE;
      }

      if ($stmt->execute() == FALSE) {
         $this->errmsg .= $this->mysqli->error;
         $this->errmsg .= "Iragu: tableBalanceRegister() FAILED";
         $this->success = FALSE;
         return FALSE;
      }

      $stmt->close();
      return TRUE;
   }

/******************************************************************************/

  public function pickCourt() {
    if (isset($_POST['court_id'])) {
      /* If a court is already selected, display the court id. */
      echo "<p> Court ID: ", $_POST['court_id'], "</p>";
    } else {
      $url = $this->getSelfURL();
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

   public function tableRechargeInsert($nick, $offer_id) {
     $query = "INSERT INTO ir_recharge (nick, offer_id, pay_mode, " .
              "recharge_by) VALUES (?, ?, ?, ?)";
     $pay_mode = "razorpay";
     if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
        $this->errmsg = $this->mysqli->error;
        $this->success = FALSE;
        return FALSE;
     }
     $stmt->bind_param('ssss', $nick, $offer_id, $pay_mode, $nick);
     $this->success = $stmt->execute();
     if ($this->success) {
        $this->recharge_id = $this->mysqli->insert_id;
     } else {
        $this->errmsg = $stmt->error;
     }
     return $this->success;
   }

   /** This will be overloaded by the derived classes. */
   public function viewPage() {
       return true;
   }

   public function view() {
       ir_doctype();
       ir_copyright();
       ir_html_open();
       ir_head();
       ir_body_open();
       ir_page_top();
       $this->displayStatus();
       $this->viewPage();
       ir_body_close();
       ir_html_close();
   }

   /** This is the initialization routine. Override this in the derived
   class. */
   public function init() {
       return true;
   }

   /** This is the main top-level function. */
   public function process($checkAuth = true) {
       if ($checkAuth) {
           $this->is_user_authenticated();
       }
       if ($this->init() && $this->connect() && $this->work()) {
           $this->errno = 0;
       }
       $this->view();
       $this->cleanup();
   }

   function paiseToRupees($paise) {
       return number_format((float) $paise / 100, 2, '.', '');
   }

   public function isAdmin() {
       if (empty($_SESSION['usertype'])) {
           return false;
       }
       return (strcmp($_SESSION['usertype'],"admin") == 0);
   }

} /* class IraguWebapp */

?>
