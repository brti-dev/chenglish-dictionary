<?php

require '../vendor/autoload.php';

use Pced\User;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__."/../src/configure.php");

/**
 * Filter POST vars & hash password
 * @return array $email, $password, $password_hash
 */
function validateUserInput() {

	$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
	if (!$email) {
		throw new Exception('The e-mail address <i>'.$email.'</i> couldn\'t be validated. Please try again!');
	}
	
	$password = filter_input(INPUT_POST, "password");
	if (!$password) {
		throw new Exception('Password is required.');
	}

	$password_hash = password_hash($password, PASSWORD_DEFAULT);
	if ($password_hash === false) {
		throw new Exception('Password hash failure!');
	}

	return array($email, $password, $password_hash);

}

//login
if (isset($_POST['submit_login'])) {

	try {
		[$email, $password, $password_hash] = validateUserInput();

		$user = User::getByEmail($email, $GLOBALS['pdo']);

		if (password_verify($password, $user->data['password']) === false) {
			throw new Exception('Invalid password');
		}

		// Re-hash password if necessary
		$currentHashAlgorithm = PASSWORD_DEFAULT;
		$passwordNeedsRehash = password_needs_rehash(
			$user->data['password'],
			$currentHashAlgorithm
		);
		if ($passwordNeedsRehash === true) {
			// Save new password hash
			$user->data['password'] = password_hash(
				$password,
				$currentHashAlgorithm
			);
			$user->save();
		}

		$_SESSION['logged_in'] = 'true';
		$_SESSION['email'] = $email;

		if($_POST['remember']) {}

		//update activity
		$user->data['last_login_2'] = $user->data['last_login'];
		$user->data['last_login'] = date("Y-m-d H:i:s");
		$user->save();
		
		$ref = $_SERVER['HTTP_REFERER'];
		if(strstr($ref, "login.php")) $ref = "/";

		header("HTTP/1.1 302 Redirect");
		header("Location: ".$ref);

	} catch (Exception $e) {
		$page_title = APP_NAME .  " / Login";
		include __DIR__."/../templates/page_header.php";

		if ($e->getCode() == 439) {
			// Email couldn't be found
			// Offer option to register it!
			?>
			<h2>Register</h2>
			<p>The e-mail address <i><?=$email?></i> is not yet registered. Would you like to register now?</p>
			<form action="login.php" method="post">
				<input type="hidden" name="email" value="<?=$email?>"/>
				<input type="submit" name="submit_registration" value="Submit Registration" style="font-size:15px; font-weight:bold;"/>
			</form>
			<?
			include __DIR__."/../templates/page_footer.php";
			exit;
		} else {
			?>
			<h2>Error logging in</h2>
			<p><?=$e->getMessage()?></p>
			<?
			include __DIR__."/../templates/page_footer.php";
		}
		exit;
	}
}

// User wanna register

if(isset($_POST['submit_registration'])) {
	
	try {

		[$email, $password, $password_hash] = validateUserInput();
		
		

	} catch (Exception $e) {
		$page_title = APP_NAME .  " / Login Error";
		include __DIR__."/../templates/page_header.php";
		?>
		<h2>Error logging in</h2>
		<p><?=$e->getMessage()?></p>
		<?
		include __DIR__."/../templates/page_footer.php";
		exit;
	}
	
	if(mysqli_num_rows(mysqli_query($db['link'], "SELECT * FROM users WHERE email='".mysqli_real_escape_string($db['link'], $email)."' LIMIT 1"))) die("The e-mail address '$email' is already registered");
	
	$dt = date("Y-m-d H:i:s");
	$q = "INSERT INTO users (email, password, registered, last_login, last_login_2) VALUES 
		('".mysqli_real_escape_string($db['link'], $email)."', '".mysqli_real_escape_string($db['link'], $pass)."', '$dt', '$dt', '$dt');";
	if(!mysqli_query($db['link'], $q)) {
		mail($default_email, "[CN-EN DICT] Error registering", mysqli_error());
		die("Database Eror: ".mysqli_error());
	}
	
	$usrdat = mysqli_fetch_object(mysqli_query($db['link'], "SELECT * FROM users WHERE email='".mysqli_real_escape_string($db['link'], $email)."' LIMIT 1"));
	if(!$usrdat) die("Successfully registered, but an unknown error caused data to be lost. Try logging in with your email and password.");
	
	if(!$_SESSION['usrid'] = $usrdat->usrid) die("Couldn't set session variable 'usrid'. Do you have cookies enabled?");
	
	//remember?
	if($_POST['remember']) {
		// time()+60*60*24*100 = 100 days
		setcookie("remember_usrid", $_SESSION['usrid'], time()+60*60*24*100, "/");
		setcookie("remember_usrpass", base64_encode($pass), time()+60*60*24*100, "/");
	}
	
	//create first vocab & tags
	$q = "INSERT INTO vocab (usrid, zid) VALUES ('".$usrdat->usrid."', '5401'),  ('".$usrdat->usrid."', '6246');";
	mysqli_query($db['link'], $q);
	$q = "SELECT * FROM vocab WHERE usrid='".$usrdat->usrid."' LIMIT 2";
	$r = mysqli_query($db['link'], $q);
	while($row = mysqli_fetch_assoc($r)){
		$vids[$row['zid']] = $row['vocabid'];
	}
	$q = "INSERT INTO tags (usrid, tag, vocabid) VALUES ('".$usrdat->usrid."', 'General Vocab', '".$vids[5401]."'),  ('".$usrdat->usrid."', 'Measure Words', '".$vids[6246]."');";
	mysqli_query($db['link'], $q);
	
	if(strstr($_POST['ref'], "login.php")) $_POST['ref'] = "/";
	
	$page_title = APP_NAME .  " / Register";
	include __DIR__."/../templates/page_header.php";
	?>
	<h2>Successful Registration</h2>
	You have been successfully registered and your first vocab lists have been created: <a href="/vocab.php?tag=General+Vocab">General Vocab</a> and <a href="/vocab.php?tag=Measure+Words">Measure Words</a>. To add to these lists, or to create a new list, search for something in the search field above.
	<p><a href="<?=$_POST['ref']?>">Back to where you came from</a></p>
	<?
	include __DIR__."/../templates/page_footer.php";
	exit;
	
}

//logout
if(isset($_GET['do']) && $_GET['do'] == "logout") {
	setcookie(session_name(), '', time()-42000, '/');
	setcookie("remember_usrid", "", time()-60*60*24*100, "/");
	setcookie("remember_usrpass", "", time()-60*60*24*100, "/");
	unset($_SESSION['usrid']);
	session_destroy();
	$ref = $_SERVER['HTTP_REFERER'];
	if(strstr($ref, "login.php")) $ref = "/";
	header("Status: 303");
	header("Location: ".$ref);
	exit();
}