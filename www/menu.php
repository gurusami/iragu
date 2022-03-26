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
<?php include '10-head.php'; ?>

<body>

<?php
if (isset($_POST['username'])) {
   $page->report_failure();
   $page->disconnect();
}
?>

<div class="grid-container">
<div class="grid-item">
  <a href="make-register-offer.php">Make a registration offer</a>
</div>

<div class="grid-item">
  <a href="02-iragu-admin-offer-recharge.php">Make Recharge Offer</a>
</div>

<div class="grid-item">
  <a href="00-iragu-admin-register-customer.php">Register a Customer</a>
</div>

<div class="grid-item">
  <a href="03-iragu-admin-recharge.php">Recharge For Customer</a>
</div>

<div class="grid-item">
  <a href="04-iragu-admin-add-campus.php">Add a Campus</a>
</div>

<div class="grid-item">
  <a href="05-iragu-admin-court-add.php">Add Court to Campus</a>
</div>

<div class="grid-item">
  <a href="06-iragu-admin-bookings-open.php">Open Bookings</a>
</div>

<div class="grid-item">
  <a href="07-iragu-check-availability.php">Check Availability</a>
</div>

<div class="grid-item">
  <a href="08-iragu-admin-court-booking.php">Book Court (Admin)</a>
</div>

<div class="grid-item">
  <a href="logout.php">Logout</a>
</div>

</div> <!-- class="grid-container" -->

</body>
</html>

