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

/*

razorpay_order_id
razorpay_payment_id
razorpay_signature 

*/

require 'autoload.php';
include 'iragu-webapp.php';
include 'iragu-private.php';

$page = new PageRazorpayLanding();
/* This is the landing page.  So session is not available.
   $page->is_user_authenticated(); */
$page->init();
$page->connect();
$page->work();
$page->view();
?>
