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

/** A class to handle database operations on table ir_register_offers. */
class TableRegisterOffer {
   public $error;
   public $errno;

   public function getOfferDetails($mysqli) {
       $query = "SELECT * FROM ir_register_offers WHERE CURRENT_DATE " .
                "BETWEEN offer_from AND offer_to LIMIT 1;";
       if (($result = $mysqli->query($query)) == false) {
           return false;
       }
       if (is_null($result)) {
           return false;
       }
       if ($result->num_rows == 0) {
           return false;
       }
       if (($rowObj = $result->fetch_obj()) == false) {
           return false;
       }
       if (is_null($rowObj)) {
           return false;
       }
       return $rowObj;
   }

}

?>



