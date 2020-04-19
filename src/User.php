<?php

namespace Pced;

use Pced\Vocab;

class User {

    public const GUEST = 0;
    public const RESTRICTED = 1;
    public const MEMBER = 2;
    
    private static $ranks = [
        self::GUEST      => 'GUEST',
        self::RESTRICTED => 'RESTRICTED',
        self::MEMBER     => 'MEMBER',
    ];

    private $pdo;
    private $logger;
    private $user_id;
    private $rank = 0;

    /**
     * Data that corresponds to the Database columns
     * @var array
     */
    public $data = array();

    /**
     * User construction
     * May be passed by static functions like self::getByEmail
     * @param array    $params  Corresponds to DB Users table
     * @param PDO      $pdo     Database Injection
     * @param Monolog  $logger  Logger Injection
     */
    public function __construct(array $params, $pdo, $logger=[])
    {
        $this->pdo = $pdo;
        if (!empty($logger)) {
            $this->logger = $logger;
            $this->logger->debug("User object construction", $params);
        }

        if (isset($params['user_id'])) $this->user_id = $params['user_id'];
        if (isset($params['rank'])) $this->rank = $params['rank'];

        foreach ($params as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    public static function getByEmail($email, $pdo, $logger=[]): User
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->execute();
        if(!$row = $statement->fetch()) {
            // Throw an exception with code 439 to later help determine if the user should register
            $message = sprintf("The email `%s` couldn't be found", $email);
            if ($logger) $logger->notice($message);
            throw new Exception($message, 439);
        }

        return new self($row, $pdo, $logger);
    }

    public static function getById(int $user_id, $pdo, $logger=[]): User
    {
        $sql = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();
        if(!$row = $statement->fetch()) {
            // Throw an exception with code 439 to later help determine if the user should register
            $message = sprintf("The user_id `%d` couldn't be found", $user_id);
            if ($logger) $logger->notice($message);
            throw new Exception($message, 439);
        }

        return new self($row, $pdo, $logger);
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * Gets all supported ranks.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getRanks(): array
    {
        return array_flip(static::$ranks);
    }

    /**
     * Gets the name of the logging level.
     *
     * @throws \Psr\Log\InvalidArgumentException If rank is not defined
     */
    public static function getRankName(int $rank): string
    {
        if (!isset(static::$ranks[$rank]))
            throw new \InvalidArgumentException('Rank "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));

        return static::$ranks[$rank];
    }

    public function isLoggedIn(): bool
    {
        return $this->logged_in;
    }

    /**
     * Update the user in the database using $this->data parameters
     * @return Boolean    
     */
    public function save(): bool
    {
        if (!$this->user_id)
            throw new Exception("Couldn't save User: The user id hasn't been set.");

        $sql = "UPDATE `users` SET `email`=:email,`password`=:password,`rank`=:rank,`last_login`=:last_login,`last_login_2`=:last_login_2 WHERE `user_id`=:user_id";
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':email', $this->data['email']);
        $statement->bindValue(':password', $this->data['password']);
        $statement->bindValue(':rank', $this->data['rank']);
        $statement->bindValue(':last_login', $this->data['last_login']);
        $statement->bindValue(':last_login_2', $this->data['last_login_2']);
        $statement->bindValue(':user_id', $this->user_id);
        if (!$statement->execute()) {
            throw new Exception("Error saving User data");
            if ($this->logger) $this->logger->error("Error saving User data at User::save()", $this->data);
        }

        if ($this->logger) $this->logger->info("Save User data ", $this->data);

        return true;
    }

    public function insert(): bool
    {
        $datetime = date("Y-m-d H:i:s");
        $sql = "INSERT INTO users (email, password, registered, last_login, last_login_2) VALUES (:email, :password, '$datetime', '$datetime', '$datetime');";
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':email', $this->data['email']);
        $statement->bindValue(':password', $this->data['password']);
        $statement->execute();

        $this->user_id = $this->pdo->lastInsertId();

        $_SESSION['logged_in'] = 'true';
        $_SESSION['user_id'] = $this->user_id;

        if ($this->logger) $this->logger->info("Insert into Users user_id:".$this->user_id, $this->data);
         
        return true;
    }

    public function delete(): bool
    {
        if (!$this->user_id)
            throw new Exception("Couldn't delete User: user_id hasn't been set.");

        $sql = sprintf("DELETE FROM users WHERE user_id = %d LIMIT 1", (int) $this->user_id);
        $statement = $this->pdo->query($sql);
        $statement->execute();

        if ($this->logger) $this->logger->info("DELETE user user_id:".$this->user_id, $this->data);

        return true;
    }

    public function registerGuest($pdo, $logger=[]): User
    {
        $user_params = [
            "email" => "temp-".uniqid()."@".APP_DOMAIN,
            "password" => "password",
        ];
        $user = new self($user_params, $pdo, $logger);
        $user->insert();

        if (!empty($logger)) $logger->info("Guest registration", $user->data);

        return $user;
    }
}
