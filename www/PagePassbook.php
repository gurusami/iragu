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

class PagePassbook extends IraguWebapp {
   public $tablePassbook;

   function __construct() {
       $this->tablePassbook = new TablePassbook($this->mysqli);
   }

   public function viewPage() {
       if (is_null($this->tablePassbook)) {
           $file = basename(__FILE__, '.php');
           die("tablePassbook is NULL: " . $file . ":" . __LINE__ );
       }
       $this->tablePassbook->mysqli = $this->mysqli;
       $this->tablePassbook->nick = $_SESSION['nick'];
       if (($entryArray = $this->tablePassbook->get()) == false) {
           $this->error = $this->tablePassbook->error;
           $this->errno = $this->tablePassbook->errno;
           $this->error .= " (errno: $this->errno)";
           die($this->error);
           return false;
       }
       $this->showTable($entryArray);
       return true;
   }

   public function showTable($entryArray) {
       echo '<h1 align="center"> View Passbook </h1>';
       echo '<table align="center">';
       echo "<caption> Latest Passbook Entries </caption>";
       echo '<tr>' .
               '<th> Date </th>' .
               '<th> Details </th>' .
               '<th> Credit </th>' .
               '<th> Debit </th>' .
               '<th> Total </th>' .
            '</tr>';

       if (is_null($entryArray)) {
           die("null");
       }

       foreach ($entryArray as $entry) {
           $this->showOneRow($entry);
       }
       echo '</table>';

   }

   public function showOneRow($obj) {
       echo '<tr>' .
               '<td>' . $obj->trx_date . '</td>' .
               '<td>' . $obj->trx_info . '</td>' .
               '<td align="right">' . IraguWebapp::paiseToRupees($obj->credit) . '</td>' .
               '<td align="right">' . IraguWebapp::paiseToRupees($obj->debit) . '</td>' .
               '<td align="right">' . IraguWebapp::paiseToRupees($obj->running_total) . '</td>' .
            '</tr>';
   }
}

?>


