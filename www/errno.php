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

class errno {
   const PASS = 0;
   const FAIL = 1;

   /* The range 10 to 49 is reserved for unclassified errors. */
   const FAILED_RESTORE_SESSION = 10;
   const MISMATCH_PASSWD        = 11;
   const UNAUTHORIZED           = 12;
   const INSUFFICIENT_BALANCE   = 13;

   /* The range 50 to 99 is reserved for DB failures. */
   const FAILED_BINDPARAM    = 50;
   const FAILED_EXECUTE      = 51;
   const FAILED_FETCH_OBJECT = 52;
   const FAILED_PREPARE      = 53;
   const FAILED_LOGIN        = 54;
   const FAILED_COST_CALC    = 55;

   /* The range from 100 to 149 is reserved for INVALID data. */
   const INVALID_AMOUNT       = 100;
   const INVALID_DBOBJ        = 101;
   const INVALID_NICK         = 102;
   const INVALID_INVITE_TOKEN = 103;
   const INVALID_EMAIL        = 104;
   const INVALID_STATE        = 105;
   const INVALID_OTP          = 106;
   const INVALID_COST         = 107;
   const INVALID_BOOKING_ID   = 108;
   const INVALID_MOBILE_NO    = 109;

   /* The range from 150 to 199 is reserved for MISSING data. */
   const NOT_FOUND_RECORD      = 150;
   const NULL_OBJECT           = 151;
   const MISSING_NICK          = 152;
   const MISSING_FULLNAME      = 153;
   const MISSING_EMAIL         = 154;
   const MISSING_MOBILE        = 155;
   const MISSING_PASSWD        = 156;
   const MISSING_COURT_ID      = 157;
   const MISSING_PLAY_DURATION = 158;

   /* The range 200 to 249 is reserved for Razorpay errors */
   const FAILED_RAZORPAY             = 200;
   const FAILED_RAZORPAY_SIGNATURE   = 201;
   const INVALID_RAZORPAY_ORDER_ID   = 202;
   const INVALID_RAZORPAY_PAYMENT_ID = 203;
   const MISSING_RAZORPAY_SIGNATURE  = 204;
   const MISSING_RAZORPAY_PAYMENT_OBJ  = 205;

   /* The range 250 to 299 is reserved for duplicate errors. */
   const DUPLICATE_NICK    = 250;
};

?>
