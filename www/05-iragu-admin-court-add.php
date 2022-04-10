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
/* Iragu: Admin Interface: Add Court: Add a court to a campus */

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

class IraguAdminAddCourt extends IraguWebapp {
   public $campus_id = "";
   public $court_id = "";
   public $price_per_slot = "";
   public $court_info = "";
   public $campus_name = "";
   public $city = "";
   public $pincode = "";
   public $state_code = "";
   public $country_code;
   public $added_by;

   public function displayCourtList() {
      $query = <<<EOF
SELECT court_id, campus_id, price_per_slot, court_info, added_by
FROM ir_court WHERE court_id = ? ;
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('s', $_POST['court_id']);
      $stmt->execute();
      $stmt->bind_result($this->court_id,
                         $this->campus_id,
                         $this->price_per_slot,
                         $this->court_info,
                         $this->added_by);

      while ($stmt->fetch()) {
echo <<<EOF
<div>
 <table>
   <tr> <td> Court ID </td> <td> $this->court_id </td> <tr>
   <tr> <td> Campus ID </td> <td> $this->campus_id </td> </tr>
   <tr> <td> Price Per Slot </td> <td> $this->price_per_slot </td> </tr>
   <tr> <td> Court Info </td> <td> $this->court_info </td> </tr>
   <tr> <td> Court Added By </td> <td> $this->added_by </td> </tr>
  </table>
</div>
EOF;
      }
   } /* displayCourtList() */

   public function displayCampusDetails() {
echo <<<EOF
<div>
 <table>
   <tr> <td> Campus ID </td> <td> $this->campus_id </td> <tr>
   <tr> <td> Campus Name </td> <td> $this->campus_name </td> </tr>
   <tr> <td> City </td> <td> $this->city </td> </tr>
   <tr> <td> Pincode </td> <td> $this->pincode </td> </tr>
   <tr> <td> State Code </td> <td> $this->state_code </td> </tr>
   <tr> <td> Country Code </td> <td> $this->country_code </td> </tr>
  </table>
</div>
EOF;
   } /* displayCampusDetails() */

   public function getCampusDetails() {
      $query = <<<EOF
SELECT campus_id, campus_name, city, pincode, state_code, country_code
FROM ir_campus WHERE campus_id = ?;
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('s', $_POST['campus_id']);
      $stmt->execute();
      $stmt->bind_result($this->campus_id,
                         $this->campus_name,
                         $this->city,
                         $this->pincode,
                         $this->state_code,
                         $this->country_code);
      if ($stmt->fetch()) {
         $this->success = TRUE;
      } else {
         $this->success = FALSE;
         $this->errmsg = $stmt->error;
      }
      return $this->success;
   } /* getCampusDetails() */

   public function displayCampusList() {
      $query = <<<EOF
SELECT campus_id, campus_name, city, pincode, state_code, country_code
FROM ir_campus;
EOF;
     $result = $this->mysqli->query($query) or die($this->mysqli->error);

echo <<<EOF
<div>
  <h2> Campus List </h2>
  <table>
   <tr>
    <th> Campus ID </th>
    <th> Campus Name </th>
    <th> City </th>
    <th> Pincode </th>
    <th> State Code </th>
    <th> Country Code </th>
    <th> Add Court </th>
   </tr>
EOF;

     $url = $this->getSelfURL();
     while ($row = $result->fetch_assoc()) {
       $campus_id = $row['campus_id'];
       $campus_name = $row['campus_name'];
       $city = $row['city'];
       $pincode = $row['pincode'];
       $state_code = $row['state_code'];
       $country_code = $row['country_code'];
echo <<<EOF
  <tr>
    <td> $campus_id </td>
    <td> $campus_name </td>
    <td> $city </td>
    <td> $pincode </td>
    <td> $state_code </td>
    <td> $country_code </td>
    <td>
      <form action="$url" method="post">
      <input type="hidden" name="campus_id" value="$campus_id">
      <input type="submit" name="for_campus" value="Add Court">
      </form>
    </td>
  </tr>
EOF;
     } /* while loop end */
     echo '</table> </div>';
   } /* displayCampusList() */

   public function displayFormAddCourt() {
      $url = $this->getSelfURL();
      $campus_id = $_POST['campus_id'];
echo <<<EOF
<form action="$url" method="post">
<fieldset style="font-size: 1em;">
<legend> Add Court to Campus </legend>
<input type="hidden" id="campus_id" name="campus_id" value="$campus_id" />
<p> <label for="court_id"> Court ID </label>
    <input type="text" id="court_id" name="court_id" maxlength="8" size="8">
</p>
<p> <label for="price_per_slot"> Price Per Slot </label>
    <input type="number" step="0.01" id="price_per_slot" name="price_per_slot"
           maxlength="8" size="8">
</p>
<p> <label for="court_info"> Court Info </label>
    <input type="text" id="court_info" name="court_info" maxlength="100"
    size="100">
</p>
<input type="submit" name="addCourt" value="Add Court">
</form>
EOF;
   } /* displayFormAddCourt() */

   public function addCourt() {
      $query = <<<EOF
INSERT INTO ir_court (court_id, campus_id, price_per_slot, court_info, added_by)
VALUES (?, ?, ?, ?, ?);
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('ssiss',
                        $_POST['court_id'],
                        $_POST['campus_id'],
                        $_POST['price_per_slot'],
                        $_POST['court_info'],
                        $_SESSION['userid']);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' Add court failed '; 
      }
      $stmt->close();
      return $this->success;
   }

   public function work() {
     if (isset($_POST['campus_id'])) {
        $this->getCampusDetails();
     }
     if (isset($_POST['addCourt'])) {
        $this->addCourt();
     }
   }
}

$page = new IraguAdminAddCourt();
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

if (!isset($_POST['campus_id'])) {
   $page->displayCampusList(); 
} else if (isset($_POST['campus_id']) && !isset($_POST['court_id'])) {
   $page->displayCampusDetails();
   $page->displayFormAddCourt(); 
   $page->displayCourtList();
} else {
   $page->displayCampusDetails();
   $page->displayCourtList();
}

   $page->disconnect();
?>

</body>
</html>

