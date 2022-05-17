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

/** A class to handle database operations on table ir_captcha. */
class TableCaptcha {
   public $id;
   public $challenge;
   public $response;
   public $nick;
   public $error;
   public $errno;

   public function getChallenge($mysqli, $id) {
       $query = "SELECT * FROM ir_captcha WHERE id = ?";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return FALSE;
       }
       if ($stmt->bind_param('i', $id) == FALSE) {
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

   /** Get type 1 of captcha. */
   public function getType1() {
       $c = "If arranged in ascending order, which will be first: ";
       $size = random_int(3, 5);
       $smallest = 2000;
       $numbers = array();
       for ($i = 0; $i < $size; $i++) {
           $numbers[$i] = random_int(1, 1000);
           if ($numbers[$i] < $smallest) {
               $smallest = $numbers[$i];
           }
       }
       $c .= implode(", ", $numbers);
       $r = $smallest;
       return new Challenge($c, $r);
   }

   /** Get type 2 of captcha. */
   public function getType2() {
       $c = "If arranged in ascending order, which will be last: ";
       $size = random_int(3, 5);
       $largest = 0;
       $numbers = array();
       for ($i = 0; $i < $size; $i++) {
           $numbers[$i] = random_int(1, 1000);
           if ($numbers[$i] > $largest) {
               $largest = $numbers[$i];
           }
       }
       $c .= implode(", ", $numbers);
       $r = $largest;
       return new Challenge($c, $r);
   }

   public function getFromDB($mysqli) {
       $maxId = $this->maxValue($mysqli);
       if ($maxId == false) {
           return $this->getType1();
       }
       $id = random_int(1, $maxId);
       $object = $this->getChallenge($mysqli, $id);
       return new Challenge($object->challenge, $object->response);
   }

   public function getRandomChallenge($mysqli) {
       $choice = random_int(1, 3);
       $obj = null;
       switch ($choice) {
           case 1:
               $obj = $this->getType1();
               break;
           case 2:
               $obj = $this->getType2();
               break;
           case 3:
           default:
               $obj = $this->getFromDB($mysqli);
               break;
       }
       return $obj;
   }

   public function maxValue($mysqli) {
       if (is_null($mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       $query = "SELECT MAX(id) FROM ir_captcha";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
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
       $row = $result->fetch_array();
       $stmt->close();
       $this->errno = errno::PASS;
       return $row[0];
   }

   public function count($mysqli) {
       if (is_null($mysqli)) {
           $this->error = "DB Connection object missing";
           $this->errno = errno::INVALID_DBOBJ;
           return false;
       }
       $query = "SELECT COUNT(*) FROM ir_captcha";
       if (($stmt = $mysqli->prepare($query)) == FALSE) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
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
       $row = $result->fetch_array();
       $stmt->close();
       $this->errno = errno::PASS;
       return $row[0];
   }

   public function insert($mysqli) {
       $query = "INSERT INTO ir_captcha (challenge, response, created_by) " .
           " VALUES (?, ?, ?)";

       if (($stmt = $mysqli->prepare($query)) == false) {
           $this->error = $mysqli->error;
           $this->errno = errno::FAILED_PREPARE;
           return false;
       }

       if ($stmt->bind_param('sss', $this->challenge, $this->response,
           $this->nick) == false) {
           $this->error = $stmt->error;
           $this->errno = errno::FAILED_BINDPARAM;
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


