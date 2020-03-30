<?php

require '../vendor/autoload.php';

use Pced\PrimezeroTools;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__."/../src/configure.php");
require_once (__DIR__."/../src/PrimezeroTools.php");
$pz = new PrimezeroTools();

$_GET['query'] = trim($_GET['query']);
$q = str_replace("*", "%", $_GET['query']);

include __DIR__."/../templates/page_header.php";

?>
<h2>Search</h2>
<?

if(!$q) {
	echo "Input a search term in the form field above.";
	include __DIR__."/../templates/page_footer.php";
	exit;
}

preg_match("/[a-z]/i", $q, $inp_py);
if($inp_py) {
	//inp pinyin
	$py = $pz->pzpinyin_tonedisplay_convert_to_number($q);
} else {
	//inp hanzi
	$py = $pz->pzhanzi_hanzi_to_pinyin($q);
	$hz = $pz->pzhanzi_traditional_to_simplified($q);
}

if($py){
	$py = preg_replace('/([1-5])/', '${1} ', $py);
	$py = preg_replace("/ +/", " ", $py);
	$py = trim($py);
	
	//format pinyin
	$arr = array();
	$arr = explode(" ", $py);
	for($i=0; $i < count($arr); $i++){
		if(preg_match('/[a-z]/', substr($arr[$i], -1))) $arr[$i].= '_';
	}
	$py = implode(" ", $arr);
}

if($_SESSION['usrid']) {
	
	// search vocab list
	
	$query = "SELECT * FROM vocab WHERE usrid='".(int)$_SESSION['usrid']."' AND ";
	if($_GET['in_defs']) $query.= "definitions LIKE '%".str_replace("%", "", mysqli_real_escape_string($db['link'], $q))."%' OR ";
	if($hz) $query.= "(hanzi_jt LIKE '".mysqli_real_escape_string($db['link'], $q)."' OR hanzi_ft LIKE '".mysqli_real_escape_string($db['link'], $q)."') ";
	else $query.= "pinyin_raw LIKE '$py%' ";
	$query.= "LIMIT 0, 100";
	$vres = mysqli_query($db['link'], $query);
	if($vnumrows = mysqli_num_rows($vres)) {
		
		?>
		<h3 style="margin-bottom:0;">
			<?=($vnumrows >= 100 ? "More than 100" : $vnumrows)?> result<?=($vnumrows != 0 ? 's' : '')?> in your vocab for '<span lass="hz"><?=$_GET['query']?></span>'
		</h3>
		<a href="#dicres">Skip to Dictionary Results &gt;</a>
		<div class="vocablist">
			<?
			while($row = mysqli_fetch_assoc($vres)) outputVocab($row);
			?>
		</div>
		<?
		
	} else {
		echo '<h3>No results from your vocab lists</h3>';
	}
}

$query = "SELECT * FROM zhongwen WHERE ";
if($_GET['in_defs']) $query.= "definitions LIKE '%".str_replace("%", "", mysqli_real_escape_string($db['link'], $q))."%' OR ";
if($hz) $query.= "hanzi_jt LIKE '".mysqli_real_escape_string($db['link'], $q)."' ";
else $query.= "pinyin LIKE '$py' ";
$query.= "LIMIT 0, 100";
$res = mysqli_query($db['link'], $query);
$numrows = mysqli_num_rows($res);

?>
<h3 id="dicres" style="margin-bottom:0;">
	<?=($numrows >= 100 ? "More than 100" : $numrows)?> Dictionary Result<?=($numrows != 0 ? 's' : '')?> for '<span lass="hz"><?=$_GET['query']?></span>'
</h3>
<div style="margin:3px 0 0 15px; padding-left:10px; background:url(/assets/img/arrow-down-right.png) no-repeat 0 5px;">
	<?=($py != $q ? $py.' &nbsp; ' : '')?>
	<span style="font-weight:normal !important; color:#888;">[<?=($_GET['in_defs'] ? '<a href="?query='.$_GET['query'].'">Disclude definitition search</a>' : '<a href="?query='.$_GET['query'].'&in_defs=1">Include definitition search</a>')?>]</span>
</div>
<div style="height:10px;">&nbsp;</div>
<?

if(!$numrows) {
	echo "No exact matches found";
} else {
	?>
	<table border="1" cellpadding="5" cellspacing="0" class="results">
		<tr>
			<th colspan="5" style="text-align:right;">
				To add a term to your vocab lists, click <b>+</b>
			</th>
		</tr>
		<tr>
			<th><big class="hz">简体</big></th> 
			<th><big class="hz">繁體</big></th>
			<th><big class="hz">拼音</big></th>
			<th><big class="hz">定义</big></th>
			<th style="border-top-color:transparent;"><big style="font-size:200%;"><b>+</b></big></th>
		</tr>
		<?
		while($row = mysqli_fetch_assoc($res)) {
			$row['definitions'] = preg_replace("@^/|/$@", "", $row['definitions']);
			$row['definitions'] = str_replace("/", '&nbsp;&nbsp;<span style="color:#AAA;">/</span>&nbsp;&nbsp;', $row['definitions']);
			?>
			<tr>
				<td><big class="hz"><a href="http://www.mdbg.net/chindict/chindict.php?wdqb=*<?=$row['hanzi_jt']?>*&wdrst=0" target="_blank"><?=$row['hanzi_jt']?></a></big></td>
				<td><?=($row['hanzi_jt'] != $row['hanzi_ft'] ? '<big class="hz"><a href="http://www.mdbg.net/chindict/chindict.php?wdqb=*'.$row['hanzi_ft'].'*&wdrst=1" target="_blank">'.$row['hanzi_ft'].'</a></big>' : '&nbsp;')?></td>
				<td><?=$pz->pzpinyin_tonedisplay_convert_to_mark($row['pinyin'])?></td>
				<td><?=$row['definitions']?></td>
				<td nowrap="nowrap"><big style="font-size:200%;"><b><a <?=(isset($_SESSION['usrid']) ? 'href="vocab.php?add='.$row['zid'].'"' : 'href="#login" onclick="alert(\'Register and/or log in to add and modify personal vocab lists\');"')?> title="Add to my vocab" style="text-decoration:none;">+</a></b></big></td>
			</tr>
			<?
		}
		?>
	</table>
<?
}

include __DIR__."/../templates/page_footer.php";