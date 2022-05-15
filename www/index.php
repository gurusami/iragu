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

session_start();

include 'iragu-webapp.php';

class LoginPage extends IraguWebapp {
   public $pass;

   public function validate($user, $password) {
       if (!isset($this->mysqli) || is_null($this->mysqli)) {
           die("MySQL connection object is not initialized");
       }

    $query = "SELECT sha2(?, 256) = token, usertype FROM ir_login WHERE nick = ?";
    if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
       $this->errmsg .= $this->mysqli->error;
       $this->success = FALSE;
       $this->pass = false;
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
    } else {
      $this->pass = false;
    }
  }

  public function report_failure() {
    if (!$this->pass) {
        echo '<p> Login Failed. </p>';
    }
  }

}

if (isset($_SESSION['nick']) && isset($_SESSION['usertype'])) {
   /* Redirect browser */
   header('Location: menu.php');
   exit();
}

$page = new LoginPage();

if (isset($_POST['username'])) {
   $page->connect();
   $page->validate($_POST['username'], $_POST['token']);

   if ($page->pass) {
     /* Redirect browser */
       header('Location: menu.php');
       exit();
   }
}

?>

<!doctype html>

<?php include 'copyright.php'; ?>

<html>
<?php include '10-head.php'; ?>

<body>

<?php
if (isset($_POST['username'])) {
   $page->report_failure();
   $page->disconnect();
}
?>

<div id="login">
 <form class="ir-form-login" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
       method="post">
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
</body>
</html>

