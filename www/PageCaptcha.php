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

class PageCaptcha extends IraguWebapp {
   public $tableCaptcha;

   function __construct() {
       $this->tableCaptcha = new TableCaptcha();
   }


   public function work() {
       if (empty($_POST['captcha_challenge'])) {
           return false;
       }
       if (empty($_POST['captcha_response'])) {
           return false;
       }

       $this->tableCaptcha->challenge = $_POST['captcha_challenge'];
       $this->tableCaptcha->response  = $_POST['captcha_response'];

       if ($this->tableCaptcha->insert($this->mysqli) == false) {
           $this->error = $this->tableCaptcha->error;
           $this->errno = $this->tableCaptcha->errno;
           return false;
       }

       return true;
   }

   /** Show a form to collect the invite token from the user. */
   public function viewFormAddChallenge() {
       $url = $this->getSelfURL();
       echo <<<EOF
<div id="div_form">
   <form action="$url" method="post">
       <p> <label for="elem_challenge"> Challenge </label>
           <input type="text" id="elem_challenge" name="captcha_challenge"
               maxlength="128" size="128" value="" required/>
       </p>
       <p>
           <label for="elem_response"> Response </label>
           <input type="text" id="elem_response" name="captcha_response"
               maxlength="64" size="64" value="" required/>
       </p>
       <input type="submit" name="form_captcha" value="Submit"/>
   </form>
</div>
EOF;
   }

   public function displayCount() {
       $n_rows = $this->tableCaptcha->count($this->mysqli);
       echo <<<EOF
<p align="center"> Total number of challenges available: $n_rows </p>
EOF;
   }

   public function viewPage() {
       $this->displayCount();
       $this->viewFormAddChallenge();
   }
}

?>
