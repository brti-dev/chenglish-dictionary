<?php

ini_set("error_reporting", 6135);

$extime = microtime(true);

require("db.php");

session_set_cookie_params(6000);
session_start();

$default_email = "mat.berti@gmail.com";

$html_tag = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
$root = $_SERVER['DOCUMENT_ROOT']."/";

$ver = ($_SERVER['HTTP_HOST'] == "cn-en-m.dreamhosters.com" ? "mobile" : "full");

//login from cookies
if(isset($_COOKIE['remember_usrid']) && isset($_COOKIE['remember_usrpass'])) {
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

class page {
	var $title = "PCE Dictionary";
	var $javascript;
	var $style = array();
	var $nocont; //blank page
	
function header() {
	
	////////////
	// HEADER //
	////////////
	
global $db, $login, $usrname, $usrid, $usrrank, $usrlastlogin, $results, $errors, $warnings, $html_tag, $ver;

//given stylesheets
$print_style = "";
if(is_array($this->style)) {
	foreach($this->style as $st) $print_style.= '<link rel="stylesheet" href="'.$st.'" type="text/css" media="screen"/>'."\n";
} elseif (!is_array($this->style) && isset($this->style)) {
	$print_style = '<link rel="stylesheet" href="'.$this->style.'" type="text/css" media="screen"/>'."\n";
}
if(isset($this->freestyle)) $print_style.= '<style type="text/css">'.$this->freestyle.'</style>';

?>
<?=$html_tag?>
<head>
	<title><?=($this->title ? strip_tags($this->title) : 'Chinese')?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="keywords" content="<?=$this->meta_keywords?>"/>
	<meta name="description" content="<?=$this->meta_description?>"/>
	<meta name="DC.title" content="<?=$this->title?>"/>
	
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	
	<link rel="shortcut icon" href="/favicon.ico"/>
	<link rel="stylesheet" type="text/css" href="/assets/css/screen.css" media="all"/>
	<?=($ver == "mobile" ? '<link rel="stylesheet" type="text/css" href="/assets/css/mobile.css" media="all"/>' : '')?>
	<?=$print_style?>
	<script type="text/javascript" src="/assets/script/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="/assets/script/global.js"></script>
	<script type="text/javascript" src="/assets/script/jquery.tooltip.js"></script>
	<?=$this->javascript?>
</head>
<body style="background-image:url(/assets/img/bg<?=rand(0,6)?>.png);">
<?

//outp errors, warnings & results
if($errors || $results || $warnings) {
	?>
	<div id="notify">
		<a href="javascript:void(0)" class="x" style="float:right" onclick="toggle('','notify')">X</a>
		<dl>
			<?
			
			if($errors) {
				?><dt style="background-color:#D23C3C">Errors</dt><?
				foreach($errors as $err) {
					echo '<dd>'.$err."</dd>\n";
				}
			}
			
			if($warnings) {
				?><dt style="background-color:#F4A90B">Warnings</dt><?
				foreach($warnings as $w) {
					echo '<dd>'.$w."</dd>\n";
				}
			}
			
			if($results) {
				?><dt style="background-color:#06C">Results</dt><?
				foreach($results as $res) {
					echo '<dd>'.$res."</dd>\n";
				}
			}
		
			?>
		</dl>
	</div>
	<?
}

if($this->nocont) return; // End the header here if $nocont is true

$rand = rand(1,5); //generate random # for header img
?>
<div id="htmlbody">
<div id="header">
	
	<h1 id="top"><a href="/"><span>Personal Chinese-English Dictionary</span></a></h1>
	
	<div id="nav">
		
		<div id="search">
			<form action="search.php" method="get">
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
			<?
			if($_SESSION['usrid']){
				?>
				<select onchange="document.location='/vocab.php?tag='+this.options[this.selectedIndex].value;">
					<option value="">My Vocab</option>
					<option value="">&ndash; All</option>
					<option value="_recent-3">&ndash; Recently Added</option>
					<option value="_singlechars">&ndash; Single Characters</option>
					<?
					$query2 = "SELECT DISTINCT(`tag`) FROM tags WHERE usrid='".$_SESSION['usrid']."' ORDER BY `tag`";
					$res2   = mysqli_query($db['link'], $query2);
					while($row = mysqli_fetch_assoc($res2)) {
						echo '<option value="'.$row['tag'].'" class="hz">'.$row['tag'].'</option>';
					}
					?>
				</select>
				<?
			}
			?>
		</div>
		
	</div>
	
	<div id="login">
		<div class="container">
			<form action="/login.php" method="post">
				<table border="0" cellpadding="0" cellspacing="5">
					<?
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
							<?
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
						<?
					}
					?>
				</table>
			</form>
		</div>
	</div>
		
</div>

<br style="clear:both;"/>

<div id="page">
<?

}

function footer() {
	
	////////////
	// FOOTER //
	////////////
	
global $db, $extime, $ver;

$extime = microtime(true) - $extime;
$extime = round($extime, 2);

?>
</div><!-- #page -->

<div id="footer">
	<span style="float:right; color:#AAA;"><?=$extime?> seconds</span>
	Personal Chinese-English Dictionary &nbsp; 
	<?
	if($ver == "mobile")
		echo '<a href="http://cn-en.dreamhosters.com">Full Version</a> <span style="color:#888;">|</span> <b>Mobile</b>';
	else echo '<b>Full Version</b> <span style="color:#888;">|</span> <a href="http://cn-en-m.dreamhosters.com">Mobile</a>';
	?>
</div>

</div><!-- #htmlbody -->

<input type="hidden" id="ver" value="<?=$ver?>"/>

</body>
</html>
<?

//close db connection
mysqli_close($db['link']);

}

} // end class Page

function outputVocab($row) {
	
	global $vcount, $rownum, $ver, $db;
	
	$vcount++;
	
	/*if($row['zid'] && !$row['hanzi_jt']) {
		//it's not custom -- get the data from zhongwen table
		$q = "SELECT * FROM zhongwen WHERE zid='".$row['zid']."' LIMIT 1";
		$zw = mysqli_fetch_assoc(mysqli_query($db['link'], $q));
		$row = array_merge($row, $zw);
	}*/
	
	$row['definitions'] = preg_replace("@^/|/$@", "", $row['definitions']);
	$row['definitions'] = str_replace("/", ' &nbsp;<span style="color:#AAA;">/</span>&nbsp; ', $row['definitions']);
	
	$query2 = "SELECT tag FROM tags WHERE vocabid='".$row['vocabid']."'";
	$res2   = mysqli_query($db['link'], $query2);
	$row['tags'] = array();
	while($row2 = mysqli_fetch_assoc($res2)){
		$row['tags'][] = '<a href="/vocab.php?tag='.urlencode($row2['tag']).'" title="view all entries tagged \''.htmlSC($row2['tag']).'\'">'.$row2['tag'].'</a>';
	}
	
	//if($vcount == 2) $posclass = " fcnav-curr";
	
	$row['pinyin'] = trim($row['pinyin']);
	$py = '<table border="0" cellpadding="0" cellspacing="0"><tr><td>'.str_replace(" ", '</td><td>', $row['pinyin']).'</td></tr></table>';
	?>
	<dl id="vocab-<?=$row['vocabid']?>" class="vocab<?=$posclass?>">
		<dt>
			<div class="num"><?=$vcount?> of <?=$rownum?></div>
			<big class="hz hz-jt"><?=$row['hanzi_jt']?></big>
			<big class="hz hz-ft"><?=$row['hanzi_ft']?></big>
		</dt>
		<dd class="pinyin"><?=$py?></dd>
		<dd class="definitions"><?=$row['definitions']?></dd>
		<?
		//if($row['get_compounds']) {
			//compounds
			$query3 = "SELECT hanzi_jt, pinyin, definitions FROM vocab WHERE hanzi_jt LIKE '%".$row['hanzi_jt']."%' AND hanzi_jt != '".$row['hanzi_jt']."'";
			$res3   = mysqli_query($db['link'], $query3);
			if(mysqli_num_rows($res3)){
				echo '<dd class="compounds hz">';
				while($row3 = mysqli_fetch_assoc($res3)) {
					$def = substr($row3['definitions'], 1, -1);
					$def = htmlSC($def);
					echo '<a href="/search.php?query=*'.$row3['hanzi_jt'].'*" title="'.$row3['pinyin'].'&lt;br/&gt;'.$def.'" class="tooltip">'.$row3['hanzi_jt'].'</a> &nbsp;&nbsp; ';
				}
				echo '</dd>';
			}
		//}
		?>
		<dd class="extras">
			
			<?=(count($row['tags']) ? '<ul class="tags"><li>'.implode("</li><li>", $row['tags']).'</li></ul>' : '')?>
			
			<ul class="controls">
				<li class="mark known" rel="check"><a href="#check" title="mark this entry as known and show it less frequently"><img src="/assets/img/mark_check.png" alt="check" border="0"/></a></li>
				<li class="mark unknown" rel="question"><a href="#question" title="mark this entry as unknown and show it more frequently"><img src="/assets/img/mark_question.png" alt="?" border="0"/></a></li>
				<li><a href="#edit" title="edit this entry" class="editvocab" rel="<?=$row['vocabid']?>">edit</a></li>
				<li class="exlink mdbg"><a href="http://www.mdbg.net/chindict/chindict.php?wdqb=*<?=$row['hanzi_jt']?>*&wdrst=0" target="_blank" title="search for this on MDGB Chinese-English Dictionary">MDBG</a></li>
				<li class="exlink nciku"><a href="http://www.nciku.com/search/all/<?=$row['hanzi_jt']?>" target="_blank" title="search for this on Nciku Dictionary">Nciku</a></li>
			</ul>
			
			<!--		<td><a href="vocab.php?edit=<?=$row['vocabid']?>" title="edit this entry and associated lists" style="background:url(/assets/img/edit.gif) no-repeat center center; text-decoration:none;">&nbsp;&nbsp;&nbsp;&nbsp;</a></td>
					<td><a href="#" class="rmv-<?=$row['vocabid']?> preventdefault" title="Remove this entry" onclick="removeVocab('<?=$row['vocabid']?>');" style="background:url(/assets/img/x.png) no-repeat center center; text-decoration:none;">&nbsp;&nbsp;&nbsp;</a></td>
					<td nowrap="nowrap"><label><input type="checkbox" name="memorized" value="<?=$row['vocabid']?>"<?=($row['memorized'] ? ' checked="checked"' : '')?> class="setmem-<?=$row['vocabid']?>" style="margin:-2px 3px 0 0; vertical-align:middle;"/>memorized</label></td>
					<td>Frequency: 
						<select class="chfreq-<?=$row['vocabid']?>" onchange="updateFrequency('<?=$row['vocabid']?>', this.options[this.selectedIndex].value);" style="padding:0; font-size:12px;">
							<?
							$sel[$row['frequency']] = ' selected="selected"';
							?>
							<option value="0"<?=$sel[0]?>>don't show</option>
							<option value="1"<?=$sel[1]?>>low</option>
							<option value="2"<?=$sel[2]?>>medium</option>
							<option value="3"<?=$sel[3]?>>high</option>
							<option value="4"<?=$sel[4]?>>very high</option>
						</select>
					</td>
				</tr>
			</table>-->
		</dd>
	</dl>
	<?
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
	$r 	= mysqli_query($db['link'], $q) or die ( "Query failed: " . mysqli_error() );
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