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
           echo '<p> SUCCESS </p>';
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
}

?>
