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
require 'autoload.php';

class PageMenu extends IraguWebapp {

   public function viewGridItem($href, $name) {
       echo <<<EOF
<div class="grid-item">
<form action="$href" action="post">
<button class="button-grid-item" type="submit">
   $name
</button>
</form>
</div>
EOF;
   }

   public function viewAdminMenu() {
       $this->viewGridItem("make-register-offer.php", "Make a registration offer");
       $this->viewGridItem("02-iragu-admin-offer-recharge.php", "Make Recharge Offer");
       $this->viewGridItem("00-iragu-admin-register-customer.php", "Register a Customer");
       $this->viewGridItem("03-iragu-admin-recharge.php", "Recharge For Customer");
       $this->viewGridItem("04-iragu-admin-add-campus.php", "Add a Campus");
       $this->viewGridItem("05-iragu-admin-court-add.php", "Add Court to Campus");
       $this->viewGridItem("06-iragu-admin-bookings-open.php", "Open Bookings");
       $this->viewGridItem("07-iragu-check-availability.php", "Check Availability");
       $this->viewGridItem("08-iragu-admin-court-booking.php", "Book Court (Admin)");
       $this->viewGridItem("captcha.php", "Add Captcha Challenge (Admin)");
   }

   public function viewUserMenu() {
       $this->viewGridItem("50-iragu-user-profile.php", "Profile");
       $this->viewGridItem("20-iragu-user-recharge.php", "Recharge");
       $this->viewGridItem("07-iragu-check-availability.php", "Check Availability");
       $this->viewGridItem("30-iragu-user-court-booking.php", "Book Court");
       $this->viewGridItem("invite.php", "Invite Friends");
       $this->viewGridItem("logout.php", "Logout");
   }

   public function viewPage() {
       echo '<div class="grid-container">';
       $this->viewUserMenu();

       if ($this->isAdmin()) {
           $this->viewAdminMenu();
       }

       echo '</div> <!-- class="grid-container" -->';
   }
}

?>

