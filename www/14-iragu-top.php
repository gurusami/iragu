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
/* Iragu: Top */

$menu = "";

function showAdminMenu() {
    $menu = <<<EOF
<a href="12-iragu-admin-menu.php">Menu</a>
EOF;
EOF;
}

function showUserMenu() {
   $menu = <<<EOF
    <a href="13-iragu-user-menu.php">Menu</a>
EOF;
}

if (isset($_SESSION['userid'])) {
   if (strcmp($_SESSION['usertype'],"admin") == 0) {
      showAdminMenu();
   } else {
      showUserMenu();
   }
} else {
    $menu = '<a href="index.php">Login</a>';
}

?>

<div style="width: 80%;">
  <button class="menu">
    <?php echo $menu; ?>
  </button>
</div>

