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

/** A class to handle database operations on table ir_people. */
class TablePeople {
   public $nick;
   public $full_name;
   public $email;
   public $mobile_no;
   public $offer_id;
   public $balance;
   public $error;
   public $errno;

   public function get($mysqli, $nick) {
       $query = 'SELECT * FROM ir_people WHERE nick = ?';
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       $this->nick = $nick;
       if ($stmt->bind_param('s', $this->nick) == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }
       if ($stmt->execute() == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_EXECUTE;
           return FALSE;
       }
       if (($result = $stmt->get_result()) == false) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::NOT_FOUND_RECORD;
           return false;
       }
       if (($object = $result->fetch_object()) == false) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_FETCH_OBJECT;
           return false;
       }
       return $object;
   }

   public function insert($mysqli) {
       if (empty($this->nick)) {
           $this->errno = errno::MISSING_NICK;
           $this->error = "Missing User Nick";
           return false;
       }
       if (empty($this->full_name)) {
           $this->errno = errno::MISSING_FULLNAME;
           $this->error = "Missing User Fullname";
           return false;
       }
       if (empty($this->email)) {
           $this->errno = errno::MISSING_EMAIL;
           $this->error = "Missing User Email";
           return false;
       }
       if (empty($this->mobile_no)) {
           $this->errno = errno::MISSING_MOBILE;
           $this->error = "Missing User Mobile Number";
           return false;
       }

       $query = "INSERT INTO ir_people (nick, full_name, email, mobile_no, " .
           "offer_id, registered_by) VALUES (LOWER(TRIM(?)), ?, ?, ?, ?, ?)";

       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->errno = errno::FAILED_PREPARE;
           $this->error = $mysqli->error;
           return FALSE;
       }

       if ($stmt->bind_param('ssssss', $this->nick,
                                       $this->full_name,
                                       $this->email,
                                       $this->mobile_no,
                                       $this->offer_id,
                                       $this->nick) == FALSE) {
           $stmt->close();
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_BINDPARAM;
           return FALSE;
       }

       if ($stmt->execute() == FALSE) {
           $this->error = $stmt->error;
           $this->errno = errno::FAILED_EXECUTE;
           $stmt->close();
           return FALSE;
       }

       return TRUE;
   }
}

?>


