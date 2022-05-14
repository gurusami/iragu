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

class TextLocal {
   public $apiKey;

   function __construct() {
       $this->apiKey = PrivateConfig::TEXTLOCAL_APIKEY;
   }

   public function verifyMobile($mobile_no, $otp) {
       $numbers = array($mobile_no);
       $sender = urlencode('TXTLCL');
       $message = rawurlencode("Verify Mobile OTP: $otp");
       $numbers = implode(',', $numbers);
     
       // Prepare data for POST request
       $data = array('apikey'  => $this->apiKey,
                     'numbers' => $numbers,
                     'sender'  => $sender,
                     'message' => $message);

       // Send the POST request with cURL
       $ch = curl_init('https://api.textlocal.in/send/');
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $response = curl_exec($ch);
       curl_close($ch);
       // Process your response here
       return $response;
   }

}

?>

