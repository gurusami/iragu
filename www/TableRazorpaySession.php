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

class TableRazorpaySession {
   public $mysqli;
   public $order_id;
   public $sid;
   public $error;
   public $errno;

   const ERRNO_INVALID_DBOBJ = 10;
   const ERRNO_ORDER_MISSING = 11;

   function __construct($mysqli) {
       $this->mysqli = $mysqli;
   }

   public function insert()
   {
       if (!isset($this->mysqli) || is_null($this->mysqli))
       {
           $this->error = "MySQL connection is not available";
           $this->errno = self::ERRNO_INVALID_DBOBJ;
           return FALSE;
       }

       if (!isset($this->order_id) || is_null($this->order_id))
       {
           $this->error = "Razorpay order_id is not available";
           $this->errno = self::ERRNO_ORDER_MISSING;
           return FALSE;
       }

       if (!isset($_SESSION['userid']))
       {
           $this->error = "User not authenticated";
           $this->errno = 3;
           return FALSE;
       }

       $this->sid = session_id();
       if (!isset($this->sid) || is_null($this->sid)) {
           $this->error = "PHP session id is missing";
           $this->errno = 4;
           return FALSE;
       }

       $query = "INSERT INTO ir_razorpay_session(order_id, sid, created_by) " .
           " VALUES(?, ?, ?)";

       if (($stmt = $this->mysqli->prepare($query)) == FALSE) {
           $this->error = "Could not fetch user details";
           $this->errno = 1;
           return FALSE;
       }
       if ($stmt->bind_param('sss', $this->order_id, $this->sid,
                                    $_SESSION['userid']) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = 5;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = 6;
           return FALSE;
       }

       return TRUE;
   }
};

?>

