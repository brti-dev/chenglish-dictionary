<?php

require_once (__DIR__."/../src/class.page.php");
$page = new page;
require_once (__DIR__."/../src/PrimezeroTools.php");
$cn = new PrimezeroTools();

$page->header(); 

?>
<h2><span class="hz" style="font-size:60px; opacity:.85;">欢迎光临!</span></h2>

<div style="line-height:1.5em">

<b>PCED is a simple Chinese-English Dictionary powered by <a href="http://cc-cedict.org/" target="_blank">CC-CEDICT</a> with the additional function of creating, saving, producing, and managing highly functional vocabulary lists for personal use and study.</b>
Although there are <a href="http://mdbg.net/chindict/chindict.php" title="MDBG Chinese-English Dictionary">already</a> <a href="http://dianhuadictionary.com/" title="Dianhua Chinese-English Dictionary">several</a> <a href="http://nciku.com" title="Nciku Chinese-English Dictionary">great</a> C-E dictionaries published on 
the internet, none of them give efficient and effective means to reproduce personal vocabulary lists on your computer or mobile device.

<p><div style="margin:0 -5px; padding:5px; background-color:#EEE;">
	<?
	if($_SERVER['HTTP_HOST'] == "cn-en-m.dreamhosters.com")
		echo 'You are currently using <b>PCED MOBILE</b>. <a href="http://cn-en.dreamhosters.com">Switch to the Full Version</a>.';
	else echo 'You are currently using <b>PCED FULL VERSION</b>, meant for use on your computer. <a href="http://cn-en-m.dreamhosters.com">Switch to the Mobile Version</a>';
	?>
</div></p>

<p>To begin, search for a term in the form field above.</p>

<p><b>TIP</b>: Use the * character as a wildcard.</p>

</div>

<h3 style="margin:20px 0 5px; padding: 20px 0 0; border-top:1px solid #CCC;">Random Dictionary Entry</h3>
<dl>
	<?
	$q = "SELECT * FROM zhongwen LIMIT ".rand(0, 91678).", 1";
	$row = mysqli_fetch_assoc(mysqli_query($db['link'], $q));
	$row['definitions'] = preg_replace("@^/|/$@", "", $row['definitions']);
	$row['definitions'] = str_replace("/", '&nbsp;&nbsp;<span style="color:#AAA;">/</span>&nbsp;&nbsp;', $row['definitions']);
	?>
	<dt><big class="hz"><a href="/search.php?query=<?=$row['hanzi_jt']?>"><?=$row['hanzi_jt']?></a><?=($row['hanzi_jt'] != $row['hanzi_ft'] ? '&nbsp;&nbsp;<span style="color:#CCC;">[</span> '.$row['hanzi_ft'].' <span style="color:#CCC;">]</span>' : '')?></big></dt>
	<dd style="margin:10px 0 0 10px;"><?=$cn->pzpinyin_tonedisplay_convert_to_mark($row['pinyin'])?></dd>
	<dd style="margin:10px 0 0 10px;"><?=$row['definitions']?></dd>
</dl>
<?

$page->footer();

?>