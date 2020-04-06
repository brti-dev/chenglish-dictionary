<?php

require '../vendor/autoload.php';

use Pced\PrimezeroTools;
$cn = new PrimezeroTools();

require_once __DIR__."/../config/config_app.php";

include __DIR__."/../templates/page_header.php";

?>
<h2><span class="hz" style="font-size:60px; opacity:.85;">欢迎光临!</span></h2>

<div style="line-height:1.5em">

<p><b>PCED is a simple Chinese-English Dictionary powered by <a href="http://cc-cedict.org/" target="_blank">CC-CEDICT</a> with the additional function of creating, saving, producing, and managing highly functional vocabulary lists for personal use and study.</b>
Although there are <a href="http://mdbg.net/chindict/chindict.php" title="MDBG Chinese-English Dictionary">already</a> <a href="http://dianhuadictionary.com/" title="Dianhua Chinese-English Dictionary">several</a> <a href="http://nciku.com" title="Nciku Chinese-English Dictionary">great</a> C-E dictionaries published on the internet, none of them give efficient and effective means to reproduce personal vocabulary lists on your computer or mobile device.</p>

<p>To begin, search for a term in the form field above.</p>

<p><b>TIP</b>: Use the * character as a wildcard.</p>

</div>

<h3 style="margin:20px 0 5px; padding: 20px 0 0; border-top:1px solid #CCC;">Random Dictionary Entry</h3>
<dl>
	<?php
	$sql = "SELECT * FROM zhongwen LIMIT ".rand(0, 91678).", 1";
	$statement = $pdo->query($sql);
	$row = $statement->fetch();
	$row['definitions'] = preg_replace("@^/|/$@", "", $row['definitions']);
	$row['definitions'] = str_replace("/", '&nbsp;&nbsp;<span style="color:#AAA;">/</span>&nbsp;&nbsp;', $row['definitions']);
	?>
	<dt><big class="hz"><a href="/search.php?query=<?=$row['hanzi_jt']?>"><?=$row['hanzi_jt']?></a><?=($row['hanzi_jt'] != $row['hanzi_ft'] ? '&nbsp;&nbsp;<span style="color:#CCC;">[</span> '.$row['hanzi_ft'].' <span style="color:#CCC;">]</span>' : '')?></big></dt>
	<dd style="margin:10px 0 0 10px;"><?=$cn->pzpinyin_tonedisplay_convert_to_mark($row['pinyin'])?></dd>
	<dd style="margin:10px 0 0 10px;"><?=$row['definitions']?></dd>
</dl>
<?

include __DIR__."/../templates/page_footer.php";

?>