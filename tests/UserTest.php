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

    public function testGetUser()
    {
        $user = User::getByEmail('test@test.com', $GLOBALS['pdo']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->data['email'], 'test@test.com');
        $this->assertEquals($user->data['user_id'], 1);

        return $user;
    }

    /**
    @depends testGetUser
     */
    public function testSaveUser($user)
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

    function testInsertUserDelete()
    {
        $sql = "DELETE FROM users WHERE email=:email";
        $statement = $GLOBALS['pdo']->prepare($sql);
        $statement->bindValue(':email', 'foo@bar.com');
        $this->assertTrue($statement->execute());
    }
}