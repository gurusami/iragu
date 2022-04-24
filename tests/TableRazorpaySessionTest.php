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

   public function testInsertOrderMissing(): void
   {
       $webapp = new IraguWebapp();
       $db = $webapp->connect();
       $obj = new TableRazorpaySession($db);
       $obj->insert();
       $this->assertEquals($obj->errno,
           TableRazorpaySession::ERRNO_ORDER_MISSING);
   }

   public function testInsertPass(): void
   {
       $webapp = new IraguWebapp();
       $db = $webapp->connect();
       $obj = new TableRazorpaySession($db);
       $obj->order_id = "order_JKgDOUgWIvsvyh";
       $obj->userid = "test000";
       $obj->sid = session_create_id();
       $result = $obj->insert();
       $this->assertEquals($result, TRUE);
       $this->assertEquals($obj->errno, 0);
   }
}

?>
