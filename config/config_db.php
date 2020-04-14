<?php

$db_options = [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_STRINGIFY_FETCHES => false,
];
$db_config_file = __DIR__.'/../config_db.ini';

try {
    $db_config = parse_ini_file($db_config_file);

    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;port=%d;charset=utf8",
        $db_config['host'],
        $db_config['dbname_main'],
        $db_config['port']
    );

    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $db_options);
} catch (PDOException $e) {
    // Database connection failed
    echo "Database connection failed";
    exit;
}