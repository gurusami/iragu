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
/* Iragu: Template Page. */

include 'iragu-webapp.php';
include '01-iragu-global-utility.php';

class IraguPageTemplate extends IraguWebapp {
}

$page = new IraguPageTemplate();
$page->is_user_authenticated();
$page->connect();
$page->work();
?>

<!doctype html>
<?php $page->displayCopyright(); ?>
<html>

<?php include '10-heap.php'; ?>

<body>

<?php $page->displayStatus(); ?>

</body>
</html>

