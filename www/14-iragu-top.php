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

?>

<div style="width: 100%; border: 1px solid var(--text-color);">
  <ul class="ul-top-menu">
    <li class="li-top-menu"> <a href="menu.php">Home</a> </li>
    <li class="li-top-menu"> User: <?php echo $_SESSION['userid']; ?> </li>
    <li class="li-top-menu"> <a href="logout.php">Logout</a> </li>
  </ul>
</div>

