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
 * @file TableRechargeOffers.php
 * @brief A class to access 'ir_recharge_offers' table.
 *
 * A class to perform various operations on the database table
 * 'ir_recharge_offers'. 
 */

require 'autoload.php';

/** A class to handle database operations on table ir_people. */
class TableRechargeOffers {
   public $error;
   public $errno;
   public $mysqli;

   public function __construct($mysqli) {
       $this->mysqli = $mysqli;
   }

   public function displayCurrentOffers($url) {
     $query = <<<EOF
SELECT * FROM ir_recharge_offers
WHERE CURRENT_DATE BETWEEN offer_from AND offer_to ORDER BY offer_from;
EOF;
     $result = $this->mysqli->query($query) or die($this->mysqli->error);

     echo '<h2> Current Recharge Offers </h2>';
     echo '<div class="grid-container">';
     while ($row = $result->fetch_assoc()) {
         $cashback = paiseToRupees($row['cashback']);
         $recharge = paiseToRupees($row['recharge_amount']);
         echo '<div class="grid-item">';
         echo ' <form action="' . $url . '" method="post">';
         echo ' <button class="button-grid-item">';
         echo '<p> Offer ID: ' . $row['offer_id'] . ' </p>';
         echo '<p> Recharge Amount: ' . $recharge . '</p>';
         echo '<p> Cashback: ' . $cashback . ' </p>';
         echo ' </button>';
         echo ' <input type="hidden" id="nick" name="nick" value="' . $_POST['nick'] . '">';
         $this->offer_id = $row['offer_id'];
echo <<<EOF
  <input type="hidden" id="offer_id" name="offer_id" value="$this->offer_id">
EOF;

         echo '</form>';
         echo ' </div>';
     }
     echo '</div>';
   }
}

?>


