<?php

$page_title = $page_title ? strip_tags($page_title) : APP_NAME;

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
		<title><?=$page_title?></title>
		<link rel="shortcut icon" href="/favicon.ico">
		<link rel="stylesheet" type="text/css" href="/assets/css/screen.css">
		<script type="text/javascript" src="/assets/script/jquery-3.5.0.min.js"></script>
		<script type="text/javascript" src="/assets/script/global.js"></script>
		<script type="text/javascript" src="/assets/script/jquery.tooltip.js"></script>
		<?=$page_javascript?>
	</head>
	<body style="background-image:url('/assets/img/bg<?=rand(0,6)?>.png');">
		<?

		$rand = rand(1,5); //generate random # for header img
		?>
		<header id="header">
			
			<h1 id="top"><a href="/"><span>Personal Chinese-English Dictionary</span></a></h1>
			
			<nav id="nav">
				
				<div id="search">
					<form action="search.php" method="get">
						<input type="hidden" name="in_defs" value="1">
						<input type="text" name="query" value="<?=$_GET['query']?>" id="search-input">
						<input type="submit" value="Find"/>
					</form>
					<div id="search-info">
						Example queries: <samp>hello</samp>, <samp>ni3 hao3</samp>, <samp>ni hao</samp>, <samp lang="zh" class="hz">你好</samp>, <samp>*<span lang="zh" class="hz">茶</span></samp>, <samp><span lang="zh" class="hz">绿</span>_</samp><br/>
						For a more advanced search, try <a href="http://www.mdbg.net/chindict/chindict.php" target="_blank">MDBG</a>.
					</div>
				</div>
				
				<div id="vocablist">
					<?
					if (isset($_SESSION['logged_in'])) {
						?>
						<select onchange="document.location='/vocab.php?tag='+this.options[this.selectedIndex].value;">
							<option value="">My Vocab</option>
							<option value="">&ndash; All</option>
							<option value="_recent-3">&ndash; Recently Added</option>
							<option value="_singlechars">&ndash; Single Characters</option>
							<?
							$sql = "SELECT DISTINCT(`tag`) FROM tags WHERE user_id=:user_id ORDER BY `tag`";
							$statement = $pdo->prepare($sql);
							$statement->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
							$statement->execute();
							while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
								echo '<option value="'.$row['tag'].'">'.$row['tag'].'</option>';
							}
							?>
						</select>
						<?php
					}
					?>
				</div>
				
			</nav>
			
			<div id="login">
				<div class="container">
					<?
					if (isset($_SESSION['logged_in'])) {
						if (!$current_user->getRank()) {
							$user_name = "Guest";
							$register_link = "<a href=\"/login.php?register_guest=true\">Register Account</a> | ";
						} else {
							$user_name = strstr($current_user->data['email'], "@", true);
						}

						echo '<b>Welcome, '.$user_name.'</b> '.$register_link.'<a href="/login.php?do=logout">Log out</a>';
					} else {
						?>
						<b>Register / Log In</b>
						<form action="/login.php" method="post">
							<input type="email" name="email" placeholder="E-mail" required>
						    <input type="password" name="password" placeholder="Password" required>
							<input type="submit" name="submit_login" value="Submit"/>
						</form>
						<?
					}
					?>
				</div>
			</div>
			
		</header>

		<main id="page">

			<?
			//outp errors, warnings & results
			if (!empty($flash)) {
				?>
				<section id="notify">
					<ul>
						<?
						foreach ($flash as $message) {
							echo '<li>' . $message . '</li>';
						}
						?>
					</ul>
				</section>
				<?
			}