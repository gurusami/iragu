<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class TableRazorpaySessionTest extends TestCase
{
   public function testInvalidDBConnection(): void
   {
       $invalid = null;
       $obj = new TableRazorpaySession($invalid);
       $obj->insert();
       $this->assertEquals($obj->errno,
           TableRazorpaySession::ERRNO_INVALID_DBOBJ);
   }
}

?>
