<?
require_once (__DIR__."/../src/class.page.php");
$page = new page;
	
if(isset($_COOKIE['remember_usrid']) && isset($_COOKIE['remember_usrpass'])) {
	
	//login user from remembered cookie
	
	$pass = base64_decode($_COOKIE['remember_usrpass']);
	$q = "SELECT * FROM users WHERE usrid='".mysqli_real_escape_string($db['link'], $_COOKIE['remember_usrid'])."' AND password='".mysqli_real_escape_string($db['link'], $pass)."' LIMIT 1";
	$res = mysqli_query($db['link'], $q);
	if($userdat = mysqli_fetch_object($res)) {
		
		if(!$_SESSION['usrid'] = $userdat->usrid) $errors[] = "Couldn't set session variable 'usrid'.";
		
		if(!$errors) {
			//update activity
			$q2 = "UPDATE users SET last_login='".date("Y-m-d H:i:s")."', last_login_2='".$userdat->last_login."' WHERE usrid='".$_SESSION['usrid']."' LIMIT 1";
			mysqli_query($db['link'], $q2);
		}
		
	}

}

//login
if(isset($_POST['submit_login'])) {
	
	$email = $_POST['email'];
	$pass  = $_POST['password'];
	
	if(!$email || !$pass) {
		$page->title .= " / Login";
		$page->header();
		?>
		<h2>Error logging in</h2>
		Please input both an e-mail address and password.
		<?
		$page->footer();
		exit;
	}
	//validate email
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$page->title .= " / Login";
		$page->header();
		?>
		<h2>Error logging in</h2>
		The e-mail address <i><?=$email?></i> isn't valid.
		<?
		$page->footer();
		exit;
	}
	
	$q = sprintf("SELECT * FROM `users` WHERE `email` = '%s' AND `password` = '%s' LIMIT 1",
		mysqli_real_escape_string($db['link'], $email),
		mysqli_real_escape_string($db['link'], $pass));
	$res = mysqli_query($db['link'], $q);
	if(mysqli_num_rows($res)) {
		
		if(!$userdat = mysqli_fetch_object($res)) die("Error: Couldn't get user data");
		if(!$_SESSION['usrid'] = $userdat->usrid) die("Couldn't set session variable 'usrid'.");
		
		//remember?
		if($_POST['remember']) {
			// time()+60*60*24*100 = 100 days
			setcookie("remember_usrid", $_SESSION['usrid'], time()+60*60*24*100, "/");
			setcookie("remember_usrpass", base64_encode($pass), time()+60*60*24*100, "/");
		}
		
		//update activity
		$q2 = "UPDATE users SET last_login='".date("Y-m-d H:i:s")."', last_login_2='".$userdat->last_login."' WHERE usrid='".$_SESSION['usrid']."' LIMIT 1";
		mysqli_query($db['link'], $q2);
		
		$ref = $_SERVER['HTTP_REFERER'];
		if(strstr($ref, "login.php")) $ref = "/";
		header("Status: 303");
		header("Location: ".$ref);
		exit;
		
	} else {
		
		// failed login
		
		// email exists?
		$q = "SELECT * FROM `users` WHERE `email` = '".mysqli_real_escape_string($db['link'], $email)."' LIMIT 1";
		if(!mysqli_num_rows(mysqli_query($db['link'], $q))) {
			
			// REG FORM //
			
			$page->title.= " / Register";
			$page->header();
			?>
			<h2>Register</h2>
			The e-mail address <i><?=$email?></i> is not yet registered. Would you like to register now?<br/><br/>
			<form action="login.php" method="post">
				<input type="hidden" name="email" value="<?=$email?>"/>
				<input type="hidden" name="password" value="<?=$pass?>"/>
				<input type="hidden" name="remember" value="<?=$_POST['remember']?>"/>
				<input type="hidden" name="ref" value="<?=$_SERVER['HTTP_REFERER']?>"/>
				<input type="submit" name="submit_reg" value="Submit Registration" style="font-size:15px; font-weight:bold;"/>
			</form>
			<?
			$page->footer();
			exit;
			
		} else {
		
			setcookie(session_name(), '', time()-42000, '/');
			setcookie("remember_usrid", "", time()-60*60*24*100, "/");
			setcookie("remember_usrpass", "", time()-60*60*24*100, "/");
			unset($_SESSION['usrid']);
			session_destroy();
			
			$page->title.= " / Log in";
			$page->header();
			?>
			<h2>Wrong Password</h2>
			The password you entered is incorrect.
			<?
			$page->footer();
			exit;
		}
		
	}
}

if(isset($_POST['submit_reg'])) {
	
	//SUBMIT REG//
	
	$email = $_POST['email'];
	$pass  = $_POST['password'];
	
	if(!$email || !$pass) {
		$page->title .= " / Login";
		$page->header();
		?>
		<h2>Error</h2>
		Please input both an e-mail address and password.
		<?
		$page->footer();
		exit;
	}
	//validate email
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$page->title .= " / Login";
		$page->header();
		?>
		<h2>Error</h2>
		The e-mail address <i><?=$email?></i> isn't valid.
		<?
		$page->footer();
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
	
	$page->title .= " / Register";
	$page->header();
	?>
	<h2>Successful Registration</h2>
	You have been successfully registered and your first vocab lists have been created: <a href="/vocab.php?tag=General+Vocab">General Vocab</a> and <a href="/vocab.php?tag=Measure+Words">Measure Words</a>. To add to these lists, or to create a new list, search for something in the search field above.
	<p><a href="<?=$_POST['ref']?>">Back to where you came from</a></p>
	<?
	$page->footer();
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