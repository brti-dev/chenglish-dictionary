<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pced\User;

require __DIR__."/../config/config_db.php";

class UserTest extends TestCase
{
    public function testRanks()
    {
        $this->assertEquals(User::GUEST, 0);
        $this->assertEquals(User::getRanks()['RESTRICTED'], 1);
        $this->assertEquals(User::getRankName(2), "MEMBER");
    }

    public function testRanksException()
    {
        $this->expectException(InvalidArgumentException::class);
        User::getRankName(99);
    }

    public function testInvalidUser()
    {
        $this->expectException(Exception::class);
        User::getByEmail('invalid', $GLOBALS['pdo']);
    }

    public function testGetUser()
    {
        $user = User::getByEmail('test@test.com', $GLOBALS['pdo']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->data['email'], 'test@test.com');
        $this->assertEquals($user->data['user_id'], 1);

        $user_by_id = User::getById(1, $GLOBALS['pdo']);
        $this->assertEquals($user, $user_by_id);

        return $user;
    }

    /**
    @depends testGetUser
     */
    public function testSaveUser(User $user)
    {
        $this->assertEquals($user->data['email'], 'test@test.com');
        $user->data['email'] = 'foo@bar.com';
        $this->assertTrue($user->save());

        $user = User::getByEmail('foo@bar.com', $GLOBALS['pdo']);
        $this->assertEquals($user->data['email'], 'foo@bar.com');
        $user->data['email'] = 'test@test.com';
        $this->assertTrue($user->save());
    }

    public function testInsertUserExists()
    {
        $this->expectException(Exception::class);
        $user_params = array(
            "email" => "test@test.com",
            "password" => "password",
        );
        $user = new User($user_params, $GLOBALS['pdo']);
        $user->insert();
    }

    public function testInsertUser()
    {
        $user_params = array(
            "email" => "foo@bar.com",
            "password" => "password",
        );
        $user = new User($user_params, $GLOBALS['pdo']);
        $this->assertTrue($user->insert());
        $this->assertIsNumeric($user->getId());

        return $user;
    }

    /**
    @depends testInsertUser
     */
    function testInsertUserDelete(User $user)
    {
        $this->assertTrue($user->delete());
    }
}