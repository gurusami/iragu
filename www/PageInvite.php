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

class PageInvite extends IraguWebapp {
   public $tableInvite;

   function __construct() {
       $this->tableInvite = new TableInvite();
   }

   public function sendMail() {
       $to = $this->tableInvite->email;
       $subject = 'Goodminton: Invite Token';
       $message = "Invite Token: " . $this->tableInvite->token;
       $headers = 'From: agurusam@goodminton.in' . "\r\n" .
           'Reply-To: agurusam@goodminton.in' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

       return mail($to, $subject, $message, $headers);
   }


   public function work() {
       if (!empty($_POST['invite_email'])) {
           $this->tableInvite->createToken();
           $this->tableInvite->email = $_POST['invite_email'];
           $this->tableInvite->invite_by = $_SESSION['nick'];
           $this->tableInvite->insert($this->mysqli);
           if ($this->sendMail() == false) {
               $this->errno = 1;
               $this->error = "Failed to send e-mail";
               return false;
           }
       }
       return true;
   }

   /** Show a form to collect the invite token from the user. */
   public function viewEmailForm() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_invite">
   <form action="$url" method="post">
      <p> <label for="invite_email"> Provide Email to Invite Your Friend </label>
          <input type="text" id="invite_email" name="invite_email" maxlength="100"
                 size="100"
                 value="" required/>
      </p>
       <input type="submit" id="email" name="form_invite_email" value="Send"/>
   </form>
</div>
EOF;
   }

   public function viewPage() {
       $this->viewEmailForm();
   }
}

?>
