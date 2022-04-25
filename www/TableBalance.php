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

/** A class to handle database operations on table ir_balance. */
class TableBalance {
   public $nick;
   public $balance;
   public $error;
   public $errno;
   public $errno;
   public $mysqli;

   const ERRNO_PREPARE_FAILED = 1;
   const ERRNO_BINDPARAM_FAILED = 2;
   const ERRNO_EXECUTE_FAILED = 3;
   const ERRNO_INVALID_DBOBJ = 4;
   const ERRNO_INVALID_AMOUNT = 5;
   const ERRNO_INVALID_NICK = 6;

   function __construct($mysqli) {
       $this->mysqli = $mysqli;
       $this->nick = $_SESSION['nick'];
   }

   public function addBalance($recharge_amount) {

       if (is_null($this->mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = self::ERRNO_INVALID_DBOBJ;
           return false;
       }

       if (empty($recharge_amount)) {
           $this->error = "Invalid recharge amount";
           $this->errno = self::ERRNO_INVALID_AMOUNT;
           return false;
       }

       if (empty($this->nick)) {
           $this->error = "Invalid nick";
           $this->errno = self::ERRNO_INVALID_NICK;
           return false;
       }

       $query = "UPDATE ir_balance SET balance = balance + ? WHERE nick = ?";

       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = self::ERRNO_PREPARE_FAILED;
           return FALSE;
       }

       if ($stmt->bind_param('is', $recharge_amount,
                                   $this->nick) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = self::ERRNO_BINDPARAM_FAILED;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = self::ERRNO_EXECUTE_FAILED;
           return FALSE;
       }

       return true;
   }
}

?>


