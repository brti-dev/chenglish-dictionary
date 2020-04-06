<?php

use PHPUnit\Framework\TestCase;

require __DIR__."/../config/config_db.php";

class DBTest extends TestCase
{
    public function testDBConnection()
    {
        $this->assertInstanceOf(PDO::class, $GLOBALS['pdo']);
    }
    
    public function testDBFetch()
    {
        $stmt = $GLOBALS['pdo']->query("SELECT zid, definitions FROM zhongwen WHERE pinyin='ni3 hao3'");
        $this->assertEquals($stmt->fetchColumn(), "5401");
        $this->assertStringContainsString($stmt->fetchColumn(1), "hello");
        $this->assertFalse(strpos($stmt->fetchColumn(1), "baz"));
        $this->assertFalse(false);

        $data = $GLOBALS['pdo']->query("SELECT * FROM users")->fetchAll(PDO::FETCH_UNIQUE);
        $this->assertEquals($data[1]['email'], 'test@test.com');

        $stmt = $GLOBALS['pdo']->query("SELECT 1 FROM users WHERE email='foo123@marypoppins69burt.com'");
        $user_exists = $stmt->fetchColumn();
        $this->assertFalse($user_exists);
    }
}
