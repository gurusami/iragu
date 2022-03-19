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

class IraguMenu extends IraguWebapp {
}

$page = new IraguMenu();
$page->is_user_authenticated();
// $page->work();
?>

<!doctype html>

<?php include 'copyright.php'; ?>

<html>
<head>
 <title> <?php $page->displayTitle(); ?> </title>
</head>

<body>

<?php
if (isset($_POST['username'])) {
   $page->report_failure();
   $page->disconnect();
}
?>

<div style="width: 80%;">
  <button class="menu">
    <a href="make-register-offer.php">Make a registration offer</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="02-iragu-admin-offer-recharge.php">Make Recharge Offer</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="00iradre.php">Register a Customer</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="03-iragu-admin-recharge.php">Recharge For Customer</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="04-iragu-admin-add-campus.php">Add a Campus</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="05-iragu-admin-court-add.php">Add Court to Campus</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="06-iragu-admin-bookings-open.php">Open Bookings</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="07-iragu-check-availability.php">Check Availability</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu">
    <a href="08-iragu-admin-court-booking.php">Book Court (Admin)</a>
  </button>
</div>

<div style="width: 80%;">
  <button class="menu"> <a href="logout.php">Logout</a> </button>
</div>

</body>
</html>

