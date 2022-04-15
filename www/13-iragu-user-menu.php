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
include 'iragu-webapp.php';

session_start();

if (!isset($_SESSION['userid'])) {
   header('Location: ' . 'index.php');
   exit();
}

class IraguMenu extends iragu\IraguWebapp {
}

$page = new IraguMenu();
?>

<!doctype html>

<?php include 'copyright.php'; ?>
<html>
<?php include '10-head.php'; ?>

<body>

<?php include '14-iragu-top.php'; ?>

<?php
if (isset($_POST['username'])) {
   $page->report_failure();
   $page->disconnect();
}
?>

<div class="grid-container">

<div class="grid-item">
  <a href="50-iragu-user-profile.php">Profile</a>
</div>

<div class="grid-item">
  <a href="20-iragu-user-recharge.php">Recharge</a>
</div>

<div class="grid-item">
  <a href="07-iragu-check-availability.php">Check Availability</a>
</div>

<div class="grid-item">
  <a href="30-iragu-user-court-booking.php">Book Court</a>
</div>

<div class="grid-item">
  <a href="logout.php">Logout</a>
</div>

</div> <!-- class="grid-container" -->

</body>
</html>

