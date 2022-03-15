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
/* Iragu: Admin Interface: Add Campus: Add a campus */
include 'iragu-webapp.php';
include '01irglut.php';

class IraguAdminAddCampus extends IraguWebapp {
   public $campus_id = "";
   public $campus_name  = "";
   public $address_1 = "";
   public $address_2 = "";
   public $landmark = "";
   public $city = "";
   public $pincode = "";
   public $state_code = "";
   public $country_code = "";

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
   </tr>
EOF;

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
  </tr>
EOF;
     } /* while loop end */
     echo '</table> </div>';
   } /* displayCampusList() */

   public function addCampus() {
      $query = <<<EOF
INSERT INTO ir_campus (campus_id, campus_name, address_1, address_2, landmark,
city, pincode, state_code, country_code, added_by) VALUES (?, ?, ?, ?, ?, ?, ?,
?, ?, ?);
EOF;
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param('ssssssssss',
                        $_POST['campus_id'],
                        $_POST['campus_name'],
                        $_POST['address_1'],
                        $_POST['address_2'],
                        $_POST['landmark'],
                        $_POST['city'],
                        $_POST['pincode'],
                        $_POST['state_code'],
                        $_POST['country_code'],
                        $_SESSION['userid']);
      $this->success = $stmt->execute();
      if (!$this->success) {
         $this->errmsg = $stmt->error . ' Add campus failed '; 
      }
      $stmt->close();
      return $this->success;
   }

   public function work() {
     if (isset($_POST['add_campus'])) {
         $this->addCampus();
     }
   }
}

$page = new IraguAdminAddCampus();
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

<div>
 <form action="<?php $page->displaySelfURL(); ?>" method="post">
  <fieldset style="font-size: 1em;">
    <legend> Add a Campus </legend>
      <p> <label for="campus_id"> Campus ID </label>
          <input type="text" id="campus_id" name="campus_id" maxlength="8"
                 size="8"
                 value="<?php echo $page->campus_id; ?>"/>
      </p>
      <p> <label for="campus_name"> Campus Name </label>
          <input type="text" id="campus_name" name="campus_name" maxlength="128"
                 size="128"
                 value="<?php echo $page->campus_name; ?>"/>
      </p>
      <p> <label for="address_1"> Address Line 1 </label>
          <input type="text" id="address_1" name="address_1" maxlength="128"
                 size="128"
                 value="<?php echo $page->address_1; ?>"/>
      </p>
      <p> <label for="address_2"> Address Line 2 </label>
          <input type="text" id="address_2" name="address_2" maxlength="128"
                 size="128"
                 value="<?php echo $page->address_2; ?>"/>
      </p>
      <p> <label for="landmark"> Nearby Landmark </label>
          <input type="text" id="landmark" name="landmark" maxlength="128"
                 size="128"
                 value="<?php echo $page->landmark; ?>"/>
      </p>
      <p> <label for="city"> City </label>
          <input type="text" id="city" name="city" maxlength="40"
                 size="40"
                 value="<?php echo $page->city; ?>"/>
      </p>
      <p> <label for="pincode"> Pincode </label>
          <input type="text" id="pincode" name="pincode" maxlength="6"
                 size="6"
                 value="<?php echo $page->pincode; ?>"/>
      </p>
      <p> <label for="state_code"> State Code </label>
          <select id="state_code" name="state_code">
             <option value="tn">TN</option>
          </select>
      </p>
      <p> <label for="country_code"> Country Code </label>
          <select id="country_code" name="country_code">
             <option value="in">IN</option>
          </select>
      </p>

    <input type="submit" id="add_campus" name="add_campus" value="Add Campus"/>
  </fieldset>
</form>
</div>

<?php $page->displayCampusList(); 
      $page->disconnect(); ?>

</body>
</html>

