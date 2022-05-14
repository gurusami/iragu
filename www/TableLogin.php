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

/** A class to handle database operations on table ir_login. */
class TableLogin {
   public $nick;
   public $token;
   public $error;
   public $errno;

   public function insert($mysqli) {
       if (empty($this->nick)) {
           $this->errno = errno::MISSING_NICK;
           $this->error = "Nick name is missing";
           return false;
       }
       if (empty($this->token)) {
           $this->errno = errno::MISSING_PASSWD;
           $this->error = "Password is missing";
           return false;
       }

       $query = "INSERT INTO ir_login (nick, token) VALUES (LOWER(TRIM(?)), " .
              " sha2(?, 256))";

       if (($stmt = $mysqli->prepare($query)) == false) {
           $this->errno = errno::FAILED_PREPARE;
           $this->error = $mysqli->error;
           return false;
       }

       if ($stmt->bind_param('ss', $this->nick, $this->token) == false) {
           $this->errno = errno::FAILED_BINDPARAM;
           $this->error = $mysqli->error;
           return false;
       }

       if ($stmt->execute() == false) {
           $this->error = $stmt->error;
           $this->errno = errno::FAILED_EXECUTE;
           return false;
       }
       $stmt->close();
       return true;
   }

}

?>


