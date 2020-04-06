<?php

require '../vendor/autoload.php';

use Pced\PrimezeroTools;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__."/../config/config_app.php");
require_once (__DIR__."/../src/PrimezeroTools.php");
$pz = new PrimezeroTools();

// Filter input
$query = filter_input(INPUT_GET, "query");
$query = trim($query);
$in_defs = filter_input(INPUT_GET, "in_defs");
$query_definition = str_replace("%", "", $query);

include __DIR__."/../templates/page_header.php";

?>
<h2>Search</h2>
<?

if (!$query) {

	echo "<p>Input a search term in the form field above.</p>";

} else {

	// Check query for alphanumeric characters as pinyin, or hanzi
	$pinyin = '';
	preg_match("/[a-z]/i", $query, $inp_pinyin);
	if($inp_pinyin) {
		//inp pinyin
		$pinyin = $pz->pzpinyin_tonedisplay_convert_to_number($query);
	} else {
		//inp hanzi
		$pinyin = $pz->pzhanzi_hanzi_to_pinyin($query);
		$hanzi = $pz->pzhanzi_traditional_to_simplified($query);
	}

	if($pinyin){
		$pinyin = preg_replace('/([1-5])/', '${1} ', $pinyin);
		$pinyin = preg_replace("/ +/", " ", $pinyin);
		$pinyin = trim($pinyin);
		
		//format pinyin
		$arr = array();
		$arr = explode(" ", $pinyin);
		for($i=0; $i < count($arr); $i++){
			if(preg_match('/[a-z]/', substr($arr[$i], -1))) $arr[$i].= '_';
		}
		$pinyin = implode(" ", $arr);
	}

	if ($_SESSION['logged_in']) {
		
		// search vocab list
		
		$sql = "";
		$statement = $pdo->prepare($sql);
		$statement->execute();

		// Iterate over search results
		$rows = [];
		$num_rows = 0;
		while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
			$rows[] = $row;
			$num_rows++;
		}
		
		if($num_rows == 0) {

			echo '<h3>No results from your vocab lists</h3>';

		} else {
			
			?>
			<h3 style="margin-bottom:0;">
				<?=($num_rows >= 100 ? "More than 100" : $num_rows)?> result<?=($num_rows != 1 ? 's' : '')?> in your vocab for '<span class="hz"><?=htmlspecialchars($query)?></span>'
			</h3>
			<a href="#dicres">Skip to Dictionary Results &gt;</a>
			<div class="vocablist">
				<?
				foreach($rows as $row) outputVocab($row);
				?>
			</div>
			<?
			
		}
	}

	// Build query to search dictionary
	$like = "";
	$execute = [];
	if ($in_defs) {
		$like.= "definitions LIKE CONCAT('%', :query_definition, '%') OR ";
		$execute['query_definition'] = $query_definition;
	}
	if ($hanzi) {
		$like.= "hanzi_jt LIKE :query ";
		$execute['query'] = $query;
	}
	elseif ($pinyin) {
		$like.= "pinyin LIKE CONCAT('%', :pinyin, '%') ";
		$execute['pinyin'] = $pinyin;
	}
	$sql = "SELECT * FROM zhongwen WHERE $like LIMIT 0, 100";
	$statement = $pdo->prepare($sql);
	$statement->execute($execute);

	// Iterate over search results
	$rows = [];
	$num_rows = 0;
	while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
		$rows[] = $row;
		$num_rows++;
	}

	?>
	<h3 id="dicres">
		<?=($num_rows >= 100 ? "More than 100" : $num_rows)?> Dictionary Result<?=($num_rows != 1 ? 's' : '')?> for '<span class="hz" lang="zh"><?=htmlspecialchars($query)?></span>'
	</h3>
	<div style="margin:3px 0 0 15px; padding-left:10px; background:url(/assets/img/arrow-down-right.png) no-repeat 0 5px;">
		<?=($pinyin != $query ? $pinyin.' &nbsp; ' : '')?>
		<span style="font-weight:normal !important; color:#888;">[<?=($in_defs ? '<a href="?query='.urlencode($query).'">Search only hanzi and pinyin</a>' : '<a href="?query='.urlencode($query).'&in_defs=1">Include definitition search</a>')?>]</span>
	</div>
	<div style="height:10px;">&nbsp;</div>
	<?

	if($num_rows == 0) {
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
				<th><big><dfn title="Simplified Chinese"><span lang="zh-Hans" class="hz">简体</span></dfn></big></th> 
				<th><big><dfn title="Traditional Chinese"><span lang="zh-Hant" class="hz">繁體</span></dfn></big></th>
				<th><big><dfn title="Pinyin"><span lang="zh-Hans" class="hz">拼音</span></dfn></big></th>
				<th><big><dfn title="Definitions"><span lang="zh-Hans" class="hz">定义</span></dfn></big></th>
				<th style="border-top-color:transparent;"><big style="font-size:200%;"><b>+</b></big></th>
			</tr>
			<?
			foreach ($rows as $row) {
				$row['definitions'] = preg_replace("@^/|/$@", "", $row['definitions']);
				$row['definitions'] = str_replace("/", '&nbsp;&nbsp;<span style="color:#AAA;">/</span>&nbsp;&nbsp;', $row['definitions']);
				?>
				<tr>
					<td>
						<big class="hz" lang="zh-Hans"><?=$row['hanzi_jt']?></big> 
						<a href="http://www.mdbg.net/chindict/chindict.php?wdqb=*<?=$row['hanzi_jt']?>*&wdrst=0" target="_blank" class="mdbglink">MDBG</a>
					</td>
					<td><?=($row['hanzi_jt'] != $row['hanzi_ft'] ? '<big class="hz" lang="zh-Hant">'.$row['hanzi_ft'].'</big>' : '&nbsp;')?></td>
					<td><?=$pz->pzpinyin_tonedisplay_convert_to_mark($row['pinyin'])?></td>
					<td><?=$row['definitions']?></td>
					<td nowrap="nowrap"><big><b><a href="vocab.php?add=<?=$row['zid']?>" title="Add to my vocab">+</a></b></big></td>
				</tr>
				<?
			}
			?>
		</table>
	<?
	}
}

include __DIR__."/../templates/page_footer.php";