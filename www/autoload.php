<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'challenge' => '/Challenge.php',
                'errno' => '/errno.php',
                'iraguadminaddcampus' => '/04-iragu-admin-add-campus.php',
                'iraguadminaddcourt' => '/05-iragu-admin-court-add.php',
                'iraguadmincustomerrecharge' => '/03-iragu-admin-recharge.php',
                'iraguadminopenbookings' => '/06-iragu-admin-bookings-open.php',
                'iraguadminrechargeoffer' => '/02-iragu-admin-offer-recharge.php',
                'iragucheckavailability' => '/07-iragu-check-availability.php',
                'iragumakeregisteroffer' => '/make-register-offer.php',
                'iragupagetemplate' => '/09-iragu-template.php',
                'iragurazorpay' => '/IraguRazorpay.php',
                'iraguuserrecharge' => '/20-iragu-user-recharge.php',
                'iraguuserrechargerazorpay' => '/15-iragu-user-recharge-razorpay.php',
                'iraguwebapp' => '/iragu-webapp.php',
                'pagebooking' => '/PageBooking.php',
                'pagecaptcha' => '/PageCaptcha.php',
                'pageinvite' => '/PageInvite.php',
                'pagelogin' => '/PageLogin.php',
                'pagemenu' => '/PageMenu.php',
                'pagepassbook' => '/PagePassbook.php',
                'pagerazorpaylanding' => '/PageRazorpayLanding.php',
                'pagerecharge' => '/PageRecharge.php',
                'pageregister' => '/PageRegister.php',
                'pagesignup' => '/PageSignup.php',
                'privateconfig' => '/PrivateConfig.php',
                'privateconfigsample' => '/PrivateConfigSample.php',
                'tablebalance' => '/TableBalance.php',
                'tablebooking' => '/TableBooking.php',
                'tablecaptcha' => '/TableCaptcha.php',
                'tablecourt' => '/TableCourt.php',
                'tableinvite' => '/TableInvite.php',
                'tablelogin' => '/TableLogin.php',
                'tablepassbook' => '/TablePassbook.php',
                'tablepeople' => '/TablePeople.php',
                'tablerazorpaypayment' => '/TableRazorpayPayment.php',
                'tablerazorpaysession' => '/TableRazorpaySession.php',
                'tablerecharge' => '/TableRecharge.php',
                'tablerechargeoffers' => '/TableRechargeOffers.php',
                'tableregisteroffer' => '/TableRegisterOffer.php',
                'tableslots' => '/TableSlots.php',
                'textlocal' => '/TextLocal.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd
