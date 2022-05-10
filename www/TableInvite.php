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

/** A class to handle database operations on table ir_invite. */
class TableInvite {
   public $error;
   public $errno;
   public $invite_by;
   public $email;
   public $token;

   function __construct() {
       $this->errno = errno::PASS;
   }

   public function createToken() {
       $this->token = bin2hex(random_bytes(20));
       return $this->token;
   }

   public function verifyToken($mysqli) {
       if (empty($this->token)) {
           $this->errno = errno::INVALID_INVITE_TOKEN;
           $this->error = "Invalid invitation token";
           return false;
       }
       $query = "SELECT * FROM ir_invite WHERE token = ? AND CURRENT_DATE " .
                " BETWEEN valid_from AND valid_to";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       if ($stmt->bind_param('s', $this->token) == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }
       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }
       if (($result = $stmt->get_result()) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::NOT_FOUND_RECORD;
           return false;
       }
       if (($object = $result->fetch_object()) == false) {
           $stmt->close();
           $this->error = $this->mysqli->error;
           $this->errno = errno::FAILED_FETCH_OBJECT;
           return false;
       }
       return true;
   }

   public function insert($mysqli) {
       if ($this->errno != errno::PASS) {
           return false;
       }

       if (empty($this->token)) {
           $this->errno = errno::INVALID_INVITE_TOKEN;
           $this->error = "Invalid invitation token";
           return false;
       }

       if (empty($this->email)) {
           $this->errno = errno::INVALID_EMAIL;
           $this->error = "Invalid email address";
           return false;
       }

       if (empty($this->invite_by)) {
           $this->errno = errno::INVALID_NICK;
           $this->error = "Invalid userid or nick name";
           return false;
       }

       $query = "INSERT INTO ir_invite (token, email, invite_by) " . 
           " VALUES (?, ?, ?)";

       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('sss', $this->token,
                                    $this->email,
                                    $this->invite_by) == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return false;
       }

       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return false;
       }
       $stmt->close();
       return true;
   }
};

?>
