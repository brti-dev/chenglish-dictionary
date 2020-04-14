<?php

use Monolog\Logger;

$logger_tests = new Logger('tests');
// Register a handler -- file loc and minimum error level to record
$logger_tests->pushHandler(new Monolog\Handler\StreamHandler(__DIR__."/../var/logs/tests.log", Logger::DEBUG));
// Inject details of error source
$logger_tests->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::DEBUG));