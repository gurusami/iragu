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

class PageSignup extends IraguWebapp {

   public function verifyInviteToken($token) {
       if (strlen($token) != 20) {
           $this->error = "Invalid invitation token";
           $this->errno = errno::INVALID_INVITE_TOKEN;
           return false;
       }
       return true;
   }

   /** Take the invitation token in the $_POST array and place it in the
   $_SESSION array after suitable validation. */
   public function collectInviteToken() {
       if (!empty($_SESSION['invite_token'])) {
           /* The invitation token is already part of session. */
           return true;
       }

       if (!empty($_POST['form_invite_token']) &&
           strcmp($_POST['form_invite_token'], "Submit") == 0 ) {
           /* Verify that the invite token is valid. */
           if ($this->verifyInviteToken($_POST['invite_token'])) {
               $_SESSION['invite_token'] = $_POST['invite_token'];
               return true;
           }
       }
       return false;
   }

   public function work() {
       if ($this->collectInviteToken() == false) {
           return false;
       }
       return true;
   }

   /** Show a form to collect the invite token from the user. */
   public function getInviteToken() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_signup">
   <form action="$url" method="post">
      <p> <label for="invite_token"> Invitation Token </label>
          <input type="text" id="invite_token" name="invite_token" maxlength="20"
                 size="20"
                 value="" required/>
      </p>
       <input type="submit" id="token" name="form_invite_token" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function confirm() {
       $data = array();
       $data['Invite Token'] = $_SESSION['invite_token'];
       ir_table($data, "table_invite");
   }

   public function viewPage() {
       if (empty($_SESSION['invite_token'])) {
           $this->getInviteToken();
       } else {
           $this->confirm();
       }
   }

   public function cleanup() {
       unset($_SESSION['invite_token']);
   }
}

?>
