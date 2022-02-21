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

session_start();

if (!isset($_SESSION['userid'])) {
   header('Location: ' . 'index.php');
   exit();
 }
?>

<!doctype html>

<?php include 'copyright.php'; ?>

<html>
<head>
 <title> Iragu: Badminton Court Management Software </title>
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
  <button class="menu"> <a href="logout.php">Logout</a> </button>
</div>

</body>
</html>

