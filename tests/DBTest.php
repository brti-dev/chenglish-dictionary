<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require __DIR__."/../config/config_db.php";

class DBTest extends TestCase
{
    public function testDBConnection()
    {
        $this->assertInstanceOf(PDO::class, $GLOBALS['pdo']);
    }
}