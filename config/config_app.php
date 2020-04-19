<?php

ini_set("error_reporting", 6135);
ini_set("session.save_path", __DIR__."/../var/sessions");

use Pced\User;
use Monolog\Logger;

define("TEMPLATE_PATH", "templates");
define("APP_NAME", "Chenglish Dict");
define("APP_DOMAIN", "chenglishdict.com");
define("ENVIRONMENT", "development");
define("DEFAULT_EMAIL", "mat.berti@gmail.com");

date_default_timezone_set("America/New_York");

require "config_db.php";

session_start();

$logger = new Logger('app');
// Register a handler -- file loc and minimum error level to record
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__."/../var/logs/app.log", (ENVIRONMENT == "development" ? Logger::DEBUG : Logger::INFO)));
// Inject details of error source
$logger->pushProcessor(new Monolog\Processor\IntrospectionProcessor(Logger::ERROR));

// Catch uncaught exceptions
set_exception_handler(function (\Throwable $e) {
    $GLOBALS['logger']->warning($e);
    if (ENVIRONMENT == "development") echo $e;
    else echo $e->getMessage();
});

//login from cookies
if (isset($_SESSION['logged_in'])) {
    if (isset($_SESSION['user_id']) === false) {
        unset($_SESSION['user_id']);
        unset($_SESSION['logged_in']);
        throw new Exception("A session was found, but user_id not set. Please refresh your browser and try logging in again.");
    }

    $logger->pushProcessor(function ($record) {
        $record['extra']['session user_id'] = $_SESSION['user_id'];
        $record['extra']['IP'] = $_SERVER['REMOTE_ADDR'];
        return $record;
    });
    $logger->debug("User session found");

    $current_user = User::getById($_SESSION['user_id'], $pdo, $logger);

    // If the user is a registred guest, flash the option to save their session
    if ($current_user->getRank() == User::GUEST) {
        $flash[] = "As a guest, your vocabulary lists won't be saved after you exit and the cookies expire. <a href=\"/login.php?register_guest=true\">Register and save your progress permanently</a>";
    }
}

function htmlSC($x) {
    $x = str_replace('"', '&quot;', $x);
    $x = str_replace("'", "&#039;", $x);
    $x = str_replace("<", "&lt;", $x);
    $x = str_replace(">", "&gt;", $x);
    return $x;
}

function mysqlNextAutoIncrement($table, $dontdie='') {
    $q = "SHOW TABLE STATUS LIKE '$table'";
    $r  = mysqli_query($db['link'], $q) or die ( "Query failed: " . mysqli_error() );
    $row = mysqli_fetch_assoc($r);
    if($row['Auto_increment']) return $row['Auto_increment'];
    elseif(!$dontdie) die("Couldn't get incremental ID for `$table`");
}

function str_split_utf8($str) {
    // php4 ?
    // place each character of the string into and array
    $split=1;
    $array = array();
    for ( $i=0; $i < strlen( $str ); ){
        $value = ord($str[$i]);
        if($value > 127){
            if($value >= 192 && $value <= 223)
                $split=2;
            elseif($value >= 224 && $value <= 239)
                $split=3;
            elseif($value >= 240 && $value <= 247)
                $split=4;
        }else{
            $split=1;
        }
            $key = NULL;
        for ( $j = 0; $j < $split; $j++, $i++ ) {
            $key .= $str[$i];
        }
        array_push( $array, $key );
    }
    return $array;
}

?>