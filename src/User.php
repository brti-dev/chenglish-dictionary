<?php

namespace Pced;

use Pced\Exception;

/**
 * New class
 */

class User {

    public const GUEST = 0;
    public const RESTRICTED = 1;
    public const MEMBER = 2;
    
    protected static $ranks = [
        self::GUEST      => 'GUEST',
        self::RESTRICTED => 'RESTRICTED',
        self::MEMBER     => 'MEMBER',
    ];

    private $logger;

    /**
     * Data that corresponds to the Database columns
     * @var array
     */
    public $data = array();

    function __construct(array $params, $logger = [])
    {
        if(isset($logger)) $this->logger = $logger;

        foreach ($params as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    /**
     * Get a User object
     * 
     * @param  int    $id Usrid
     * @return User   The User object
     */
    
    public static function getByEmail($email, $pdo, $logger = []): User
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->execute();
        if(!$row = $statement->fetch()) {
            // Throw an exception with code 439 to later help determine if the user should register
            throw new Exception(sprintf("The email `%s` couldn't be found", $email), 439);
        }

        return new self($row, $logger);
    }

    public static function getCurrentUser()
    {
        return new User(array(
            "usrid" => 1,
            "username" => "Matt",
            "email" => "matt@bar.com",
            "rank" => 9,
        )); // return now so we don't have to revalidate
    }

    public static function getCurrentUsername(): ?str
    {
        return "Matt";

        return null;
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
            throw new InvalidArgumentException('Rank "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$ranks)));

        return static::$ranks[$rank];
    }

    public function isLoggedIn(): bool
    {
        return $this->logged_in;
    }

    public function save(): bool
    {
        if(isset($this->logger)) $this->logger->info("Save User data {username}", $this->data);

        return true;
    }
}
