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
    $stmt = $this->mysqli->prepare("SELECT sha2(?, 256) = token FROM " .
       " ir_login WHERE nick = ?");

    $stmt->bind_param('ss', $password,  $user);
    $stmt->execute();
    $stmt->bind_result($valid);
    $stmt->fetch();

    if ($valid == 1) {
      $this->pass = true;
      $_SESSION['userid'] = $user;
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

$page = new LoginPage();

if (isset($_POST['username'])) {
   $page->connect();
   $page->validate($_POST['username'], $_POST['token']);

   if ($page->pass) {
     /* Redirect browser */
     header('Location: ' . 'menu.php');
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
 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
       method="post">
  <label> Username </label>
  <input type="text" name="username" maxlength="8"/>
  <label> Password </label>
  <input type="password" name="token" maxlength="30"/> 
  <input type="submit" name="login" value="Sign In" />
 </form>
</div>
</body>
</html>

