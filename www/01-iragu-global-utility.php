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
/* Iragu: Global: Utils: Common utility functions not specific to a page. */

function paiseToRupees($paise) {
   return number_format((float) $paise / 100, 2, '.', '');
}

function displayInRupees($paise) {
   echo number_format((float) $paise / 100, 2, '.', '');
}

function ir_head() {
   echo <<<EOF
<head>
 <title> Iragu: Badminton Court Management Software </title>
 <link rel="stylesheet" type="text/css" href="css/00-iragu-green-on-black.css">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
EOF;
}

function ir_doctype() {
   echo <<<EOF
<!doctype html>
EOF;
}

function ir_copyright() {
   echo <<<EOF
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
}

function ir_html_open() {
   echo <<<EOF
<html>
EOF;
}

function ir_html_close() {
   echo <<<EOF
</html>
EOF;
}

function ir_body_open() {
   echo <<<EOF
<body>
EOF;
}

function ir_body_close() {
   echo <<<EOF
</body>
EOF;
}

function ir_page_top() {
   $home_url = ir_home_link();
   if (!empty($_SESSION['userid'])) {
       $markAdmin = '';
       if (strcmp($_SESSION['usertype'], "admin") == 0) {
           $markAdmin = "<sup><small>[admin]</small></sup>";
       }
       $userid = $_SESSION['userid'];
       $bal = IraguWebapp::paiseToRupees($_SESSION['balance']);
       echo <<<EOF
<!-- BEGIN: ir_page_top() -->
<div style="width: 100%; border: 1px solid var(--text-color);">
  <ul class="ul-top-menu">
    <li class="li-top-menu"> $home_url </li>
    <li class="li-top-menu"> User: $userid $markAdmin</li>
    <li class="li-top-menu"> [Money: $bal] </li>
    <li class="li-top-menu"> <a href="logout.php">Logout</a> </li>
  </ul>
</div>
<!-- END: ir_page_top() -->
EOF;
   } else {
       echo  <<<EOF
<!-- BEGIN: ir_page_top() -->
<div style="width: 100%; border: 1px solid var(--text-color);">
  <ul class="ul-top-menu">
    <li class="li-top-menu"> $home_url </li>
  </ul>
</div>
<!-- END: ir_page_top() -->
EOF;
   }
}

function ir_home_link() {
   $menu = '<a href="menu.php">Home</a>';
   return $menu;
}

function ir_die($msg) {
   $url = ir_home_link();
   if (is_null($url)) {
       die("Something went wrong!");
   } else {
       die("$msg Go back to $url");
   }
}

function ir_table($data, $id) {
   echo "\n";
   echo "<!-- Table generated by function ir_table() -->\n";
   echo '<table align="center" id="' . $id . '">' . "\n";
   foreach ($data as $key => $value) {
       echo "  <tr> <td> $key </td> <td> $value </td> </tr>\n";
   }
   echo "</table>\n";
   echo "\n";
}

/** Check if the given value is a mobile number. Currently, the rule is very
simple:
   1. The string must be 10 characters long.
   2. All the 10 characters must be digits.
@return true if mobile number, false otherwise. */
function isMobileNumber($no) {
   return ((strlen($no) == 10) && ctype_digit($no));
}

?>
