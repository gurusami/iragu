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

class PageLogin extends IraguWebapp {
   public $pass;

   public function work() {
       if (isset($_POST['username'])) {
           $_SESSION['login_username'] = $_POST['username'];
           $_SESSION['login_token']    = $_POST['token'];
       } else if (!empty($_POST['login_challenge'])) {
           $tableCaptcha = new TableCaptcha();
           if ($tableCaptcha->verify($_POST['login_challenge_id'],
                       $_POST['login_response'],
                       $_SESSION['login_challenge_obj'])) {
               if ($this->validate($_SESSION['login_username'],
                                   $_SESSION['login_token'])) {
                   header('Location: menu.php');
                   exit();
               } else {
                   unset($_SESSION['login_username']);
                   unset($_SESSION['login_token']);
                   $this->error = "Login failed";
                   $this->errno = errno::FAILED_LOGIN;
                   return false;
               }
           }
       }
       return true;
   }

   public function validate($user, $password) {
       if (!isset($this->mysqli) || is_null($this->mysqli)) {
           die("MySQL connection object is not initialized");
       }

       $query = "SELECT sha2(?, 256) = token, usertype FROM ir_login " .
           " WHERE nick = ?";

       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = FALSE;
           return FALSE;
       }

       $stmt->bind_param('ss', $password,  $user);
       $stmt->execute();
       $stmt->bind_result($valid, $usertype);
       $stmt->fetch();

       if ($valid == 1) {
           $this->pass = true;
           $_SESSION['userid'] = $user;
           $_SESSION['nick'] = $user;
           $_SESSION['usertype'] = $usertype;
           return true;
       } else {
           $this->pass = false;
           $this->errno = errno::FAILED_LOGIN;
           $this->error = "Login failed";
       }
       return false;
   }

   public function showChallenge() {
       $tableCaptcha = new TableCaptcha();
       $obj = $tableCaptcha->getRandomChallenge($this->mysqli);
       $_SESSION['login_challenge_obj'] = $obj;
       $challenge_id = $obj->id;
       $challenge = $obj->challenge;
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="elem_challenge"> Challenge </label>
           <input type="text" id="elem_challenge" name="login_challenge"
               maxlength="128" size="128" value="$challenge" readonly/>
       </p>
       <p>
           <label for="elem_response"> Response </label>
           <input type="text" id="elem_response" name="login_response"
               maxlength="64" size="64" value="" required/>
       </p>
       <input type="hidden" name="login_challenge_id"
               value="$challenge_id"/>
       <input type="submit" name="form_captcha" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function showFormUsername() {
       $url = htmlspecialchars($_SERVER["PHP_SELF"]);
       echo <<<EOF
<div id="login">
 <form class="ir-form-login" action="$url" method="post">
    <label class="form_label"> Username
       <input type="text" name="username" maxlength="8"/>
    </label>
    <label> Password
       <input type="password" name="token" maxlength="30"/> 
    </label>
    <input class="ir-sign-in" type="submit" name="login" value="Sign In">
 </form>
</div>
   <p style="font-size: small;" align="center">
      <a href="11-iragu-signup.php">Register</a>
   </p>
EOF;
   }

   public function viewPage() {
       if (empty($_SESSION['login_username']) ||
           empty($_SESSION['login_token'])) {
           $this->showFormUsername();
       } else if (empty($_SESSION['login_challenge']) ||
                  empty($_SESSION['login_response'])) {
           $this->showChallenge();
       }
   }
}

?>

