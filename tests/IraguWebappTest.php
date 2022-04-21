<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class IraguWebappTest extends TestCase
{
   public function testDBConnect(): void
   {
       $webapp = new IraguWebapp();
       $db = $webapp->connect();
       $this->assertEquals($webapp->errno, 0);
   }
}

?>

