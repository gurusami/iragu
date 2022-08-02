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
/**
 * @file PageRecharge.php
 * @brief An admin recharges a user/player account.
 *
 * An admin recharges a user/player account. The steps are elaborated here for
 * better understanding.
 *
 * Step 1: The admin enters a player id or a mobile number or an email address.
 * All of these are supposed to be unique for one person.  If the entered
 * information is valid, then proceed to step 2.  If the entered information is
 * not correct (data not available in database), then report error and stay in
 * step 1.
 */

require 'autoload.php';

class PageRecharge extends IraguWebapp {
   /** Nick name of the user for whom the recharge is being done. */
   public $recharge_nick;

   /** Process the POST data when a form is submitted. Identify which form is
   being submitted, collect the necessary POST data and save it in the SESSION.
   Ensure that enough vaidation happens. */
   public function work() {
       if (isset($_POST['choose_nick'])) {
           $_SESSION['recharge_nick'] = $_POST['nick'];
       }
       
   }

   public function displayNickForm() {
      $url = $this->getSelfURL();
      $form = <<<EOD
<div>
 <form action="$url" method="post">
  <fieldset style="font-size: 1em;">
   <p> <label for="nick"> Nick </label>
       <input type="text" id="nick" name="nick" maxlength="8" size="8"/>
      </p>
   <input type="submit" id="choose_nick" name="choose_nick"
           value="Choose Nick"/>
  </fieldset>
</div>
EOD;
     echo $form;
}

   public function viewPage() {
       if (!isset($_SESSION['recharge_nick'])) {
           $this->displayNickForm();
       }
   }

}

?>

