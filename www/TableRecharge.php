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
/**
 * @file TableRecharge.php
 * @brief A class to access 'ir_recharge' table.
 *
 * A class to perform various operations on the database table
 * 'ir_recharge'. 
 */

require 'autoload.php';

/** A class to handle database operations on table ir_recharge. */
class TableRecharge {
   public $error;
   public $errno;
   public $recharge_id;

   public function insert($mysqli, $nick, $offer_id, $mode) {
       $query = "INSERT INTO ir_recharge (nick, offer_id, pay_mode, " .
               " recharge_by) VALUES (?, ?, ?, ?)";

       if (($stmt = $mysqli->prepare($query)) == false) {
           $this->errno = errno::FAILED_PREPARE;
           $this->error = $mysqli->error;
           return false;
       }

       if ($stmt->bind_param('ssss', $nick, $offer_id, $mode,
           $_SESSION['userid']) == false) {
           $this->errno = errno::FAILED_BINDPARAM;
           $this->error = $mysqli->error;
           return false;
       }

       if ($stmt->execute() == false) {
           $this->errno = errno::FAILED_EXECUTE;
           $this->error = $stmt->error;
           return false;
       }
       $this->recharge_id = $mysqli->insert_id;
       return true;
   }

}

?>


