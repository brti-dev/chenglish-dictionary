<?php

require '../vendor/autoload.php';

use Pced\PrimezeroTools;
use Pced\Vocab;

require_once (__DIR__."/../config/config_app.php");

$pz = new PrimezeroTools();

// Filter input
$query = filter_input(INPUT_GET, "query");
$query = trim($query);
$in_defs = filter_input(INPUT_GET, "in_defs");
$query_definition = str_replace("%", "", $query);

include __DIR__."/../templates/page_header.php";

?>
<h2>Search</h2>
<div id="searchres">
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
		
		// Search user's vocab list
		
		// Build SQL query
		$like = "";
		$execute = ["user_id" => $current_user->getId()];
		if ($in_defs) {
			$like.= "zhongwen.definitions LIKE CONCAT('%', :query_definition, '%') OR ";
			$execute['query_definition'] = $query_definition;
		}
		if ($hanzi) {
			$like.= "(zhongwen.hanzi_jt LIKE :query OR zhongwen.hanzi_ft LIKE :query) ";
			$execute['query'] = $query;
		}
		elseif ($pinyin) {
			$like.= "zhongwen.pinyin LIKE CONCAT('%', :pinyin, '%') ";
			$execute['pinyin'] = $pinyin;
		}
		$sql = "SELECT * FROM vocab LEFT JOIN zhongwen USING (zid) WHERE user_id=:user_id AND ($like) LIMIT 0, 100;";
		$statement = $pdo->prepare($sql);
		$statement->execute($execute);
		
		// Iterate over search results
		$rows = [];
		$num_rows = 0;
		while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
			$rows[] = new Vocab($row, $pdo, $logger);
			$num_rows += 1;
		}
		
		if($num_rows == 0) {
			echo '<h3>No results from your vocab lists</h3>';
		} else {
			?>
			<section id="vocabres">
				<header>
					<h3>
						<?=($num_rows >= 100 ? "More than 100" : $num_rows)?> result<?=($num_rows != 1 ? 's' : '')?> in your vocab for &lsquo;<span class="hz"><?=htmlspecialchars($query)?></span>&rsquo;
					</h3>
					<nav>
						<a href="#dictres">Skip to Dictionary Results &gt;</a>
					</nav>
				</header>
			
				<div class="vocablist">
					<?
					foreach($rows as $vocab) {
						$vocab->renderHTML();
					}
					?>
				</div>
			</section>
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
		$rows[] = new Vocab($row, $pdo, $logger);
		$num_rows++;
	}

	?>
	<section id="dictres">
		<header>
			<h3>
				<?=($num_rows >= 100 ? "More than 100" : $num_rows)?> Dictionary Result<?=($num_rows != 1 ? 's' : '')?> for '<span class="hz" lang="zh"><?=htmlspecialchars($query)?></span>'
			</h3>
			<nav>
				<span>
					<?=($pinyin != $query ? $pinyin.' &nbsp; ' : '')?>
					<?=($in_defs ? '<a href="?query='.urlencode($query).'#dictres">Search only hanzi and pinyin</a>' : '<a href="?query='.urlencode($query).'&in_defs=1#dictres">Include definitition search</a>')?>
				</span>
			</nav>
		</header>
		<?

		if($num_rows == 0) {
			echo "No exact matches found";
		} else {
			?>
			<div class="vocablist">
				<?
				foreach ($rows as $i => $vocab) {
					$vocab->renderHTML();
				}
				?>
			</div>
		<?
		}
	?>
	</section>
	<?
}
?>
</div><!-- #searchres -->
<?

include __DIR__."/../templates/page_footer.php";