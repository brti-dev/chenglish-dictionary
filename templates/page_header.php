<?php

$page_title = $page_title ? strip_tags($page_title) : "PCE Dictionary";

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?=$page_title?></title>
		<link rel="shortcut icon" href="/favicon.ico">
		<link rel="stylesheet" type="text/css" href="/assets/css/screen.css">
		<script type="text/javascript" src="/assets/script/jquery-1.4.2.js"></script>
		<script type="text/javascript" src="/assets/script/global.js"></script>
		<script type="text/javascript" src="/assets/script/jquery.tooltip.js"></script>
	</head>
	<body style="background-image:url('/assets/img/bg<?=rand(0,6)?>.png');">
<?php

//outp errors, warnings & results
if($errors || $results || $warnings) {
	?>
	<div id="notify">
		<a href="javascript:void(0)" class="x" style="float:right" onclick="toggle('','notify')">X</a>
		<dl>
			<?php
			
			if($errors) {
				?><dt style="background-color:#D23C3C">Errors</dt><?php
				foreach($errors as $err) {
					echo '<dd>'.$err."</dd>\n";
				}
			}
			
			if($warnings) {
				?><dt style="background-color:#F4A90B">Warnings</dt><?php
				foreach($warnings as $w) {
					echo '<dd>'.$w."</dd>\n";
				}
			}
			
			if($results) {
				?><dt style="background-color:#06C">Results</dt><?php
				foreach($results as $res) {
					echo '<dd>'.$res."</dd>\n";
				}
			}
		
			?>
		</dl>
	</div>
	<?php
}

if($nocont) return; // End the header here if $nocont is true

$rand = rand(1,5); //generate random # for header img
?>
<div id="htmlbody">
<header id="header">
	
	<h1 id="top"><a href="/"><span>Personal Chinese-English Dictionary</span></a></h1>
	
	<nav id="nav">
		
		<div id="search">
			<form action="search.php" method="get">
				<input type="hidden" name="in_defs" value="1">
				<table border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td><input type="text" name="query" value="<?=$_GET['query']?>" id="search-input" onfocus="$('#search-info').fadeIn();" onblur="$('#search-info').animate({opacity:1}, 200, function(){ $(this).fadeOut(); });"/></td>
						<td><input type="submit" value="Find"/></td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="search-info" style="display:none; position:absolute; z-index:98; padding:3px 5px; border:1px solid #444; background-color:white;">
								Example queries: <span style="font-family:monospace; font-size:15px;">hello, ni3 hao3, ni hao, <span class="hz">你好</span>, *<span class="hz">茶</span>, <span class="hz">绿</span>_</span><br/>
								For a more advanced search, <a href="http://www.mdbg.net/chindict/chindict.php" target="_blank">MDBG</a> is recommended.
							</div>
						</td>
					</tr>
				</table>
			</form>
		</div>
		
		<div id="vocablist">
			<?php
			if($_SESSION['usrid']){
				?>
				<select onchange="document.location='/vocab.php?tag='+this.options[this.selectedIndex].value;">
					<option value="">My Vocab</option>
					<option value="">&ndash; All</option>
					<option value="_recent-3">&ndash; Recently Added</option>
					<option value="_singlechars">&ndash; Single Characters</option>
					<?php
					$query2 = "SELECT DISTINCT(`tag`) FROM tags WHERE usrid='".$_SESSION['usrid']."' ORDER BY `tag`";
					$res2   = mysqli_query($db['link'], $query2);
					while($row = mysqli_fetch_assoc($res2)) {
						echo '<option value="'.$row['tag'].'" class="hz">'.$row['tag'].'</option>';
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
			<form action="/login.php" method="post">
				<table border="0" cellpadding="0" cellspacing="5">
					<?php
					if(isset($_SESSION['usrid'])) {
						$usrdat = mysqli_fetch_object(mysqli_query($db['link'], "SELECT * FROM users WHERE usrid='".$_SESSION['usrid']."' LIMIT 1"));
						if(!$usrdat) echo "<tr><th>ERROR!</th></tr><tr><td>Couldn't get userdata for usrid '".$_SESSION['usrid']."'</td></tr>";
						else {
							$x = explode("@", $usrdat->email);
							?>
							<tr>
								<th>Welcome, <?=$x[0]?></th>
							</tr>
							<tr>
								<td><a href="/login.php?do=logout">Log out</a></td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr>
							<th>Register / Log In</th>
							<td colspan="2"><label style="font-size:14px; font-weight:normal; color:#888;"><input type="checkbox" name="remember" value="1" style="margin-left:0;"/> Remember Me</label></td>
						</tr>
						<tr>
							<td><input type="text" name="email" value="e-mail address" class="resetonfocus" style="width:155px;"/></td>
							<td>
								<input type="text" name="" value="password" class="resetonfocus" style="width:80px;" onfocus="$(this).hide().next().show().focus();"/>
								<input type="password" name="password" value="" style="display:none; width:80px;"/>
							</td>
							<td><input type="submit" name="submit_login" value="Submit" onclick="resetUnfocused();"/></td>
						</tr>
						<?php
					}
					?>
				</table>
			</form>
		</div>
	</div>
		
</header>

<br style="clear:both;"/>

<main id="page">