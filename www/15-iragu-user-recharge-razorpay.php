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
/* Iragu: User Recharge using Razorpay. */

include 'iragu-webapp.php';
include '01-iragu-global-utility.php';
include 'IraguRazorpay.php';

class IraguUserRechargeRazorpay extends IraguWebapp {
    public $recharge_amount; /* in paise */
    public $recharge_id;
    public $offer_id;
    public $nick;
    public $payApi;

    function __construct() {
        $this->payApi = new IraguRazorpay();
    }

    public function work() {
        if (isset($_POST['recharge_amount'])) {
            $this->recharge_amount = $_POST['recharge_amount'];
        }
        if (isset($_POST['offer_id'])) {
            $this->offer_id = $_POST['offer_id'];
        } else {
            $this->errmsg .= "offer_id is not available";
            $this->success = FALSE;
            return FALSE;
        }
        if (isset($_SESSION['userid'])) {
            $this->nick = $_SESSION['userid'];
        }
        if (isset($_POST['form_name'])) {
            if ($this->tableRechargeInsert($this->nick, $this->offer_id) == FALSE) {
                echo "<p> FAILED: $this->errmsg </p>";
            } else {
                $this->recharge_id = $this->mysqli->insert_id;
                $this->payApi->createOrder($this->mysqli, $this->recharge_id,
                                           $this->recharge_amount,
                                           $_SESSION['userid']);
            }
        }
        $this->success = TRUE;
        return TRUE;
    }

    public function addHiddenFormFields() {
        echo <<<EOF
<input type="hidden" name="recharge_id" value="$this->recharge_id" readonly>
<input type="hidden" name="offer_id" value="$this->offer_id" readonly>
<input type="hidden" name="recharge_amount" value="$this->recharge_amount" readonly>
EOF;
   }
}

$page = new IraguUserRechargeRazorpay();
$page->is_user_authenticated();
$page->connect();
$page->work();
?>

<!doctype html>
<?php $page->displayCopyright(); ?>
<html>

<?php include '10-head.php'; ?>

<body>

<?php include '14-iragu-top.php'; ?>

<div class="recharge-div">
   <form action="<?php echo $page->getSelfURL(); ?>" method="post">
      <table align="center">
        <tr> 
          <td> Recharge Offer ID </td>
          <td> <?php echo $_POST['offer_id']; ?> </td>
        </tr>
        <tr> 
          <td> Recharge Amount </td>
          <td> <?php echo paiseToRupees($page->recharge_amount); ?> </td>
        </tr>
      </table>
      <?php $page->addHiddenFormFields(); ?>
      <input type="submit" name="form_name" value="Recharge">
   </form>
</div>

</body>
</html>

