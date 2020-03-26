<?php

$db = array();

$config = parse_ini_file(__DIR__.'/../config-db.ini');

$db['link'] = mysqli_connect("localhost.squarehaven.com", $config['username'], $config['password'], $config['dbname_main']);
if (mysqli_connect_errno()) {
    die("Failed to connect to database: " . mysqli_connect_error());
}

mysqli_query($db['link'], "SET character_set_client=utf8");
mysqli_query($db['link'], "SET character_set_connection=utf8");
mysqli_query($db['link'], "SET character_set_results=utf8");
?>