<?php

require '../vendor/autoload.php';

use Pced\User;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__."/../config/config_app.php");

/**
 * Filter POST vars & hash password
 * @return array $email, $password, $password_hash
 */
function validateUserInput() {
	$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
	if (!$email) {
		throw new Exception("The e-mail address <i>".$email."</i> couldn't be validated. Please try again!");
	}
	
	$password = filter_input(INPUT_POST, "password");
	if (!$password) {
		throw new Exception("Password is required.");
	}

	$password_hash = password_hash($password, PASSWORD_DEFAULT);
	if ($password_hash === false) {
		throw new Exception("Password couldn't be secured because of an error.");
	}

	return array($email, $password, $password_hash);
}

//login
if (isset($_POST['submit_login'])) {
	try {
		[$email, $password, $password_hash] = validateUserInput();

		$user = User::getByEmail($email, $GLOBALS['pdo'], $logger);

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
		$_SESSION['user_id'] = $user->user_id;

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
				<input type="hidden" name="password" value="loremipsum"/>
				<input type="password" name="password_hash" value="<?=$password_hash?>" style="display:none"/>
				<input type="submit" name="submit_registration" value="Submit Registration"/>
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

		// Password already hashed and sent via post
		$password_hash = filter_input(INPUT_POST, "password_hash");
		if (!$password_hash) {
			throw new Exception("Password couldn't be secured because of an error.");
		}

		$user_params = [
			"email" => $email,
			"password" => $password_hash,
		];
		
		$user = new User($user_params, $GLOBALS['pdo'], $logger);
		$user->insert();
	} catch (Exception $e) {
		$page_title = APP_NAME .  " / Register Error";
		include __DIR__."/../templates/page_header.php";
		?>
		<h2>Registration Error</h2>
		<p><?=$e->getMessage()?></p>
		<?
		include __DIR__."/../templates/page_footer.php";
		exit;
	}
	
	$page_title = APP_NAME .  " / Register";
	include __DIR__."/../templates/page_header.php";
	?>
	<h2>Successful Registration</h2>
	<p>太好了！ You have been successfully registered and your first vocab lists have been created: <a href="/vocab.php?tag=General+Vocab">General Vocab</a> and <a href="/vocab.php?tag=Measure+Words">Measure Words</a>. To add to these lists, or to create a new list, search for something in the search field above.</p>
	<?
	include __DIR__."/../templates/page_footer.php";
	
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