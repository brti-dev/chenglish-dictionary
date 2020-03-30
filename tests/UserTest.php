<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pced\User;

require __DIR__."/../config/config_db.php";

class UserTest extends TestCase
{
    public function testInvalidUser()
    {
        $this->expectException(Exception::class);
        User::getByEmail('invalid', $GLOBALS['pdo']);
    }

    function testUser()
    {
        $user = User::getByEmail('test@test.com', $GLOBALS['pdo']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->data['email'], 'test@test.com');
    }
}