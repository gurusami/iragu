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

class LoginPage {
  public $mysqli;
  public $pass;

  public function connect() {
    $this->mysqli = mysqli_init();

    if (!$this->mysqli) {
      die('mysqli_init failed');
    }

    /* The user l2admin can do SELECT, INSERT, UPDATE, DELETE operations on
    all tables in kdb database. */
    if (!$this->mysqli->real_connect('localhost', 'l2admin', '#TNExit2030#',
        'kdb')) {
       die('Connect Error (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
    }
  }

  public function validate($user, $password) {
    $stmt = $this->mysqli->prepare("SELECT sha2(?, 256) = token FROM " .
       " ir_login WHERE username = ?");

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

  public function cleanup() {
      $this->mysqli->close();
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
<head>
 <title> Iragu: Badminton Court Management Software </title>
</head>

<body>

<?php
if (isset($_POST['username'])) {
   $page->report_failure();
   $page->cleanup();
}
?>

<div>
 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
       method="post">
  <table>
    <tr> <td> Username: </td> <td> <input type="text" name="username"
         maxlength="8"/> </td> </tr>
    <tr> <td> Password: </td> <td> <input type="password" name="token"
         maxlength="30"/> </td> </tr>
    <tr> <td colspan="2"> <input type="submit" name="login" value="Sign In" />
         </td> </tr>
  </table>
 </form>
</div>
</body>
</html>

