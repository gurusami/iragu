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

/* The session variables:

   $_SESSION['signup_token']
   $_SESSION['signup_email']
   $_SESSION['signup_mobile']
   $_SESSION['signup_mobile_sent_otp']
   $_SESSION['signup_textlocal_response']
   $_SESSION['signup_mobile_otp']
   $_SESSION['signup_fullname']
   $_SESSION['signup_nick']
   $_SESSION['signup_table_invite_rowobj']
   $_SESSION['signup_offer']
   $_SESSION['signup_cashback']
   $_SESSION['signup_confirm']
   $_SESSION['signup_offer_obj']
*/

class PageSignup extends IraguWebapp {
   public $tableInvite;

   function __construct() {
       $this->tableInvite = new TableInvite();
   }

   public function verifyInviteToken($token) {
       $this->tableInvite->token = $token;
       if (strlen($token) != 40) {
           $this->error = "Invalid invitation token";
           $this->errno = errno::INVALID_INVITE_TOKEN;
           return false;
       }
       if (!ctype_xdigit($token)) {
           $this->error = "Invalid invitation token";
           $this->errno = errno::INVALID_INVITE_TOKEN;
           return false;
       }
       $rowObj = $this->tableInvite->verifyToken($this->mysqli);
       if ($rowObj == false) {
           $this->error = "Invalid invitation token";
           $this->errno = errno::INVALID_INVITE_TOKEN;
           return false;
       }
       $_SESSION['signup_table_invite_rowobj'] = $rowObj;
       return true;
   }

   public function emailMatchToken($given_email) {
       if (empty($_SESSION['signup_table_invite_rowobj'])) {
           return false;
       }
       $rowObj = $_SESSION['signup_table_invite_rowobj'];
       if (strcmp($given_email, $rowObj->email) == 0) {
           return true;
       }
       $this->errno = errno::INVALID_EMAIL;
       $this->error = "Mismatch b/w email and invitation token";
       return false;
   }

   public function collectEmail() {
       if (!empty($_SESSION['signup_email'])) {
           /* The email address is already part of session. */
           return true;
       }
       if (!empty($_POST['signup_email']) &&
           strcmp($_POST['form_email'], "Submit") == 0 ) {
           if ($this->emailMatchToken($_POST['signup_email'])) {
               $_SESSION['signup_email'] = $_POST['signup_email'];
               return true;
           }
       }
       return false;
   }

   /** Take the invitation token in the $_POST array and place it in the
   $_SESSION array after suitable validation. */
   public function collectInviteToken() {
       if (!empty($_SESSION['signup_token'])) {
           /* The invitation token is already part of session. */
           return true;
       }

       if (!empty($_POST['form_signup_token']) &&
           strcmp($_POST['form_signup_token'], "Submit") == 0 ) {
           /* Verify that the invite token is valid. */
           if ($this->verifyInviteToken($_POST['signup_token'])) {
               $_SESSION['signup_token'] = $_POST['signup_token'];
               return true;
           }
       }
       return false;
   }

   public function isNickAvailable($nick) {
       $tablePeople = new TablePeople();
       if ($tablePeople->get($this->mysqli, $nick) != false) {
           $this->errno = errno::DUPLICATE_NICK;
           $this->error = "Nick already taken";
           return false;
       }
       return true;

   }

   public function isNickValid($val) {
       if (strlen($val) > 8) {
           $this->errno = errno::INVALID_NICK;
           $this->error = "Nick is longer than 8 characters";
           return false;
       }
       if (preg_match('/^[a-z0-9]+$/', $val) == false) {
           $this->errno = errno::INVALID_NICK;
           $this->error = "Nick contains invalid characters";
           return false;
       }
       return true;
   }

   public function collectOffer() {
       if (isset($_SESSION['signup_offer'])) {
           /* The registration offer is already part of session. */
           return true;
       }
       if (!isset($_POST['signup_offer'])) {
           return false;
       }
       if (strcmp($_POST['form_offer'], "Submit") != 0) {
           return false;
       }
       $_SESSION['signup_offer'] = $_POST['signup_offer'];
       return true;
   }

   public function collectUserNick() {
       if (!empty($_SESSION['signup_nick'])) {
           /* The user nick name is already part of session. */
           return true;
       }

       if (!empty($_POST['signup_nick']) &&
           strcmp($_POST['form_nick'], "Submit") == 0 ) {
           if ($this->isNickValid($_POST['signup_nick']) &&
               $this->isNickAvailable($_POST['signup_nick'])) {
               $_SESSION['signup_nick'] = $_POST['signup_nick'];
               return true;
           }
       }
       return false;
   }

   public function makeOTP() {
       return random_int(0, 999999);
   }

   public function collectMobileOTP() {
       if (!empty($_SESSION['signup_mobile_otp'])) {
           return true;
       }
       if (empty($_SESSION['signup_mobile_sent_otp'])) {
           return false;
       }
       if (empty($_POST['signup_mobile_otp'])) {
           return false;
       }
       if (strcmp($_POST['form_mobile_otp'], "Submit") != 0) {
           return false;
       }
       if ($_POST['signup_mobile_otp'] == $_SESSION['signup_mobile_sent_otp']) {
           $_SESSION['signup_mobile_otp'] = $_POST['signup_mobile_otp'];
           return true;
       } else {
           $this->errno = errno::INVALID_OTP;
           $this->error = "OTP didn't match";
       }
       return false;
   }

   public function collectMobile() {
       if (!empty($_SESSION['signup_mobile'])) {
           return true;
       }
       if (!empty($_POST['signup_mobile']) &&
           strcmp($_POST['form_mobile'], "Submit") == 0 ) {
           $_SESSION['signup_mobile'] = $_POST['signup_mobile'];
           $otp = $this->makeOTP();
           // @todo Not able to send OTP to mobile. Till then, display OTP.
           // $textLocal = new TextLocal();
           // $msg = $textLocal->verifyMobile($_SESSION['signup_mobile'], $otp);
           $msg = "The OTP is: $otp";
           $_SESSION['signup_mobile_sent_otp'] = $otp;
           $_SESSION['signup_textlocal_response'] = $msg;
           return true;
       }
       return false;
   }

   public function confirm() {
       if (!isset($_SESSION['signup_confirm'])) {
           return false;
       }
       if (!isset($_POST['form_confirm'])) {
           return false;
       }
       if (strcmp($_POST['form_confirm'], "Confirm") != 0 ) {
           return false;
       }
       $tablePeople = new TablePeople();
       $tablePeople->nick      = $_SESSION['signup_nick'];
       $tablePeople->full_name = $_SESSION['signup_fullname'];
       $tablePeople->email     = $_SESSION['signup_email'];
       $tablePeople->mobile_no = $_SESSION['signup_mobile'];
       if (empty($_SESSION['signup_offer'])) {
           $tablePeople->offer_id  = null;
       } else {
           $tablePeople->offer_id  = $_SESSION['signup_offer'];
       }
       if ($tablePeople->insert($this->mysqli) == false) {
           $this->errno = $tablePeople->errno;
           $this->error = $tablePeople->error;
           $this->error .= "Insert failed (errno: $this->errno)";
           return false;
       }
       return true;
   }

   public function collectFullName() {
       if (!empty($_SESSION['signup_fullname'])) {
           return true;
       }

       if (!empty($_POST['signup_fullname']) &&
           strcmp($_POST['form_fullname'], "Submit") == 0 ) {
           $_SESSION['signup_fullname'] = $_POST['signup_fullname'];
           return true;
       }
       return false;
   }

   public function work() {
       $_SESSION['show_status'] = false;
       if ($this->collectInviteToken() == false) {
           return false;
       }
       if ($this->collectEmail() == false) {
           return false;
       }
       if ($this->collectMobile() == false) {
           return false;
       }
       if ($this->collectMobileOTP() == false) {
           return false;
       }
       if ($this->collectFullName() == false) {
           return false;
       }
       if ($this->collectUserNick() == false) {
           return false;
       }
       if ($this->collectOffer() == false) {
           return false;
       }
       if ($this->confirm() == false) {
           return false;
       }
       $_SESSION['show_status'] = true;
       return true;
   }

   /** Show a form to collect the invite token from the user. */
   public function getInviteToken() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_signup">
   <form action="$url" method="post">
      <p> <label for="signup_token"> Invitation Token </label>
          <input type="text" id="signup_token" name="signup_token" maxlength="40"
                 size="40"
                 value="" required/>
      </p>
       <input type="submit" id="token" name="form_signup_token" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getFullName() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form_fullname">
   <form action="$url" method="post">
       <p> <label for="fullname"> Full Name </label>
           <input type="text" id="fullname" name="signup_fullname"
               maxlength="100" size="100" value="" required/>
      </p>
       <input type="submit" name="form_fullname" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getMobile() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="elem_mobile"> Mobile Number </label>
           <input type="text" id="elem_mobile" name="signup_mobile"
               maxlength="10" size="10" value="" required/>
      </p>
       <input type="submit" name="form_mobile" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getMobileOTP() {
       $url = $this->getSelfURL();
       $msg = $_SESSION['signup_textlocal_response'];
       echo <<<EOF
<p> $msg </p>
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="elem_mobile_otp"> OTP sent to Mobile </label>
           <input type="text" id="elem_mobile_otp" name="signup_mobile_otp"
               maxlength="10" size="10" value="" required/>
      </p>
       <input type="submit" name="form_mobile_otp" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getEmail() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="elem_email"> Email </label>
           <input type="text" id="elem_email" name="signup_email"
               maxlength="100" size="100" value="" required/>
      </p>
       <input type="submit" name="form_email" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getOffer() {
       $tableOffer = new TableRegisterOffer();
       $offerObj = $tableOffer->getOfferDetails($this->mysqli);
       $url = $this->getSelfURL();
       $id = "";
       $title = "No Registration Offers Available";
       if ($offerObj != false) {
           $id = $offerObj->offer_id;
           $title = "Available Registration Offer";
           $_SESSION['signup_offer_obj'] = $offerObj;
           $_SESSION['signup_cashback'] = $offerObj->cashback;
       } else {
           $_SESSION['signup_cashback'] = 0;
       }
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="elem_offer"> $title </label>
           <input type="text" id="elem_offer" name="signup_offer"
               maxlength="8" size="8" value="$id" readonly/>
      </p>
       <input type="submit" name="form_offer" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getUserNick() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="usernick"> Choose Nick Name </label>
           <input type="text" name="signup_nick"
               maxlength="8" size="8" value="" required/>
      </p>
       <input type="submit" name="form_nick" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function getConfirmation() {
       $data = array();
       $data['Invite Token']  = $_SESSION['signup_token'];
       $data['Email Address'] = $_SESSION['signup_email'];
       $data['Mobile']        = $_SESSION['signup_mobile'];
       $data['Full Name']     = $_SESSION['signup_fullname'];
       $data['Nick Name']     = $_SESSION['signup_nick'];
       $data['Offer Name']    = $_SESSION['signup_offer'];
       $data['Cashback']      = $_SESSION['signup_cashback'];
       $_SESSION['signup_confirm'] = $data;
       ir_table($data, "table_invite");

       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <input type="submit" id="elem_confirm" name="form_confirm" value="Confirm"/>
   </form>
</div>
EOF;
   }

   public function finished() {
       ir_table($_SESSION['signup_confirm'], "table_invite");
   }

   public function viewPage() {
       if (empty($_SESSION['signup_token'])) {
           $this->getInviteToken();
       } else if (empty($_SESSION['signup_email'])) {
           $this->getEmail();
       } else if (empty($_SESSION['signup_mobile'])) {
           $this->getMobile();
       } else if (empty($_SESSION['signup_mobile_otp'])) {
           $this->getMobileOTP();
       } else if (empty($_SESSION['signup_fullname'])) {
           $this->getFullName();
       } else if (empty($_SESSION['signup_nick'])) {
           $this->getUserNick();
       } else if (!isset($_SESSION['signup_offer'])) {
           $this->getOffer();
       } else if (!isset($_SESSION['signup_confirm'])) {
           $this->getConfirmation();
       } else {
           $this->finished();
           unset($_SESSION['signup_token']);
           unset($_SESSION['signup_email']);
           unset($_SESSION['signup_mobile']);
           unset($_SESSION['signup_mobile_sent_otp']);
           unset($_SESSION['signup_mobile_otp']);
           unset($_SESSION['signup_fullname']);
           unset($_SESSION['signup_nick']);
           unset($_SESSION['signup_offer']);
           unset($_SESSION['signup_cashback']);
           unset($_SESSION['signup_offer_obj']);
           unset($_SESSION['signup_confirm']);
       }
   }

   public function cleanup() {
   }
}

?>
