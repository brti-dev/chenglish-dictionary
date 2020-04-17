<?php

require '../vendor/autoload.php';

use Pced\PrimezeroTools;
use Pced\Vocab;
use Pced\Zhongwen;

require_once (__DIR__."/../config/config_app.php");

$q = filter_input(INPUT_GET, "query");
$q = trim($q);
$q = str_replace("*", "%", $q);

$page_title = APP_NAME .  " / My Vocabulary";

if($_POST['_action'] == "delete") {
	
	// DELETE VOCAB ITEM //
	
	try {
		if (!$vocab_id = filter_input(INPUT_POST, "_vocab_id", FILTER_SANITIZE_NUMBER_INT)) {
			throw new Exception("Error filtering vocabulary data");
		}
		
		$pdo->beginTransaction();
		try {
			$sql = "DELETE FROM tags WHERE vocab_id=?";
			$statement = $pdo->prepare($sql);
			$statement->execute([$vocab_id]);

			$sql = "DELETE FROM vocab WHERE vocab_id=?";
			$statement = $pdo->prepare($sql);
			$statement->execute([$vocab_id]);

			$pdo->commit();
		} catch (PDOException $e) {
			$logger->error($e);
			$pdo->rollBack();
			echo "There was an error removing this vocabulary.";
		}
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
	exit;

}

if($_POST['_action'] == "mark") {
	
	try {
		if (!$vocab_id = filter_input(INPUT_POST, "_vocab_id", FILTER_SANITIZE_NUMBER_INT)) {
			throw new Exception("Error filtering vocabulary data");
		}
		
		$get_params = ['vocab_id'=>$vocab_id, 'user_id'=>$current_user->getId()];
		$vocab = Vocab::get($get_params, $pdo, $logger)[0];
		if (is_null($vocab)) {
			throw new Exception("Error finding that vocab entry");
		}
		
		if ($_POST['_act'] == "check") {
			$vocab->frequency += 1;
		} else {
			$vocab->frequency -= 1;
		}
		$vocab->save();
	} catch (Exception $e) {
		Echo "Couldn't update memorization mark: " . $e->getMessage();
	}
	
	exit;
	
}

/*if($_POST['_action'] == "change_frequency") {
	if(!$vid = $_POST['_vocabid']) die("No vocabid given");
	$q = "SELECT * FROM vocab WHERE vocabid='$vid' and user_id='$_SESSION[user_id]' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($db['link'], $q))) {
		die("Coudn't find that vocab entry");
	}
	$q = "UPDATE vocab SET frequency='".mysqli_real_escape_string($db['link'], $_POST['_frequency'])."' WHERE vocabid='".mysqli_real_escape_string($db['link'], $vid)."' LIMIT 1";
	if(!mysqli_query($db['link'], $q)) die("Couldn't update frequency value");
	exit;
}*/

if ($_POST['action'] == "edit_tag") {
	
	// EDIT VOCAB LIST //
	
	$tag = filter_input(INPUT_POST, "tag");
	$listname = filter_input(INPUT_POST, "listname");
	$listname = trim($listname);
	
	if($_POST['removelist']){
		$sql = "DELETE FROM tags WHERE tag=? AND user_id=?";
		$statement = $pdo->prepare($sql);
		$statement->execute([$tag, $current_user->getId()]);
		header("Location: /vocab.php");
		exit;
	}
	
	if($tag != $listname && $listname != ""){
		$sql = "UPDATE tags SET tag=:listname WHERE tag=:tag AND user_id=:user_id";
		$statement = $pdo->prepare($sql);
		$statement->execute([
			":listname" => $listname,
			":tag" => $tag,
			":user_id" => $current_user->getId(),
		]);
		header("Location: /vocab.php?tag=".urlencode($listname));
		exit;
	}
	
}

if (!isset($_SESSION['logged_in'])) {
	include __DIR__."/../templates/page_header.php";
	echo "<h2>My Vocabulary</h2>\n<p>Please register and/or log in to view and modify personal lists.</p>";
	include __DIR__."/../templates/page_footer.php";
	exit;
}

// SUBMIT ADD //

if (isset($_POST['submit_add'])) {

	try {
		$in = $_POST['in'];
		
		$insert = array("user_id" => $current_user->getId());

		if ($in['singlechars'][0]) {
			foreach ($in['singlechars'] as $zid) {
				$insert['zid'] = $zid;
				$vocab = new Vocab($insert, $pdo, $logger);
				$vocab->insert();
			}
		}
		
		$insert['tags'] = $in['tags'];
		$insert['zid'] = filter_var($in['zid'], FILTER_SANITIZE_NUMBER_INT);
		$vocab = new Vocab($insert, $pdo, $logger);
		$vocab->insert();
	} catch (Exception $e) {
		include __DIR__."/../templates/page_header.php";
		Vocab::renderError($e->getMessage());
		include __DIR__."/../templates/page_footer.php";
		exit;
	}
	
	header("Location: /vocab.php?sort=added_desc");
	exit;
	
}

if ($zid = filter_input(INPUT_GET, "add")) {
	
	// Print a form to add vocab to list
	
	$pz = new PrimezeroTools();
	
	$vocab = Vocab::get(["zid"=>$zid], $pdo, $logger)[0];
	
	include __DIR__."/../templates/page_header.php";
	
	?>
	<h2>Add Vocab</h2>

	<section class="add-vocab">
		<?=$vocab->renderHTML()?>

		<form action="vocab.php" method="post" class="vocab">
			<input type="hidden" name="in[zid]" value="<?=$zid?>"/>
			<table border="0" cellpadding="0" cellspacing="0" class="vocab-aded">
				<?
				// Character decomposition
				if (mb_strlen($vocab->hanzi_jt) > 1) {
					?>
					<tr>
						<th>Character Decomposition</th>
						<td>
							Also add the following characters to your vocab list:
							<ul>
								<?
								// mb_str_split() only supported in (PHP 7 >= 7.4.0)
								// foreach (mb_str_split($vocab->hanzi_jt) as $chr) {
								foreach (str_split_utf8($vocab->hanzi_jt) as $chr) {
									$zhongwen = Zhongwen::getByHanzi($chr, $pdo)[0];
									// Check if already in user's vocab
									if (Vocab::get(["zid"=>$zhongwen->zid, "user_id"=>$current_user->getId()], $pdo))
										continue;
									echo '<li><label style="white-space:nowrap"><input type="checkbox" name="in[singlechars][]" value="'.$zhongwen->zid.'"/> <span class="hz" lang="zh">'.$zhongwen->hanzi_jt.'</span>'.($zhongwen->hanzi_jt != $zhongwen->hanzi_ft ? ' <span style="color:#AAA;">(<span class="hz" style="color:black">'.$zhongwen->hanzi_ft.'</span>)</span>' : '').'</label> '.str_replace("/", " / ", $zhongwen->definitions).'</li>';
								}
								?>
							</ul>
						</td>
					</tr>
					<?
				}
				?>
				<tr>
					<th valign="top">Lists</th>
					<td>
						<ul>
							<?
							$sql = "SELECT DISTINCT(tag) AS tag FROM tags WHERE user_id=?";
							$statement = $pdo->prepare($sql);
							$statement->execute([$current_user->getId()]);
							while($tag = $statement->fetchColumn()) {
								echo '<li><label><input type="checkbox" name="in[tags][]" value="'.$tag.'"/> '.$tag.'</label></li>';
							}
							?>
							<li>
								<input type="text" name="in[tags][]" maxlength="60" title="Manually input a new or existing list name" class="tooltip"/> &nbsp; 
								<a href="#append" onclick="$(this).closest('ul').append('&#60;li style=&#34;margin:5px 0;&#34;&#62;&#60;input type=&#34;text&#34; name=&#34;in[tags][]&#34; maxlength=&#34;60&#34; /&#62;&#60;/li&#62;');">Add another</a>
							</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" name="submit_add" value="Add Vocab"/></td>
				</tr>
			</table>
		</form>
	</section>
	<?
	
	include __DIR__."/../templates/page_footer.php";
	exit;
	
}

if($vocab_id = filter_input(INPUT_POST, "edit")) {
	
	// EDIT VOCAB FORM //

	$vocab = Vocab::get(["vocab_id" => $vocab_id], $pdo, $logger);

	if (is_null($vocab)) {
		die("There was an error getting the vocab item for editing");
	}

	$vocab = $vocab[0];
	
	if ($vocab->getUserId() != $current_user->getId()) {
		die("There was an error reconciling the vocab item with your account.");
	}
	
	$tags = $vocab->getTags();	
	
	?>
	<h2>Edit Vocab</h2>
	<form onsubmit="return false;">
		<input type="hidden" name="vocab_id" value="<?=$vocab_id?>"/>
		<input type="hidden" name="ref" value="<?=$_SERVER['HTTP_REFERER']?>"/>
		<table border="0" cellpadding="0" cellspacing="0" class="vocab-aded">
			<tbody>
				<tr>
					<th valign="top">Lists</th>
					<td>
						<?
						if ($tags){
							foreach($tags as $tag){
								echo '<div style="margin:0 0 2px;"><input type="text" name="tags[]" value="'.$tag.'" maxlength="60" style="width:200px;"/> <a href="#rmtag" title="remove this item from this list" style="color:red; font-size:16px;" onclick="$(this).parent().fadeOut(function(){ $(this).remove() })">&times;</a></div>';
							}
						}
						?>
						<div id="appfield">
							<div style="margin:2px 0;">
								<input type="text" name="tags[]" maxlength="60" style="width:200px;"/> &nbsp; 
							</div>
						</div>
						<a href="#append" onclick="$(this).before( $('#appfield').html() );">Add another</a>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<a href="#rm" title="remove this entry" onclick="removeVocab('<?=$vocab_id?>');" style="float:right; display:block; padding:3px 8px; border:1px solid #CF1B1B; color:#CF1B1B; text-decoration:none; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;">Delete from vocab</a>
						<input type="button" name="" value="Submit" onclick="submitEditVocab(<?=$vocab_id?>, $(this));" style="font-size:15px; font-weight:bold;"/>
						<span class="loading" style="display:none; padding:0 0 0 30px; background:url(/assets/img/loading-green-arrows.gif) no-repeat 10px center;">Saving</span>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<?
	exit;
	
}

// SUBMIT EDIT //

if (isset($_POST['submit_edit'])) {
	
	$inp = $_POST['_input'];
	parse_str($inp);
	$vocab_id = filter_var($vocab_id, FILTER_SANITIZE_NUMBER_INT);
	$ret = array();

	try {
		$pdo->beginTransaction();

		// Delete all tags before adding given tags
		$sql = "DELETE FROM tags WHERE vocab_id=?";
		$delete_statement = $pdo->prepare($sql);
		$delete_statement->execute([$vocab_id]);

		if (count($tags)) {
			$tags = array_unique($tags);
			$tag_queries = [];
			foreach ($tags as $i => &$tag) {
				$tag = trim($tag);
				$tag = filter_var($tag, FILTER_SANITIZE_SPECIAL_CHARS);
				if (empty($tag)) {
					unset($tags[$i]);
				} else {
					$tag_queries[] = sprintf("(%d, ?, %d)", $current_user->getId(), $vocab_id);
				}
			}
			if (!empty($tag_queries)) {
				$sql = sprintf("INSERT INTO tags (user_id, tag, vocab_id) VALUES %s;", implode(",", $tag_queries));
				$insert_statement = $pdo->prepare($sql);
				$insert_statement->execute($tags);
			}
		}

		$pdo->commit();
	} catch (Exception $e) {
	    $pdo->rollBack();
	    $logger->error($e);
	    Vocab::renderError("There was an error updating tag information");
	    exit;
	}
	
	$vocab = Vocab::get(["vocab_id" => $vocab_id], $pdo, $logger)[0];
	$vocab->renderHTML();
	
	exit;
	
}

//////////////////
// INDEX / LIST //
//////////////////

$tag = filter_input(INPUT_GET, "tag");
$tag = urldecode($tag);
$tag = trim($tag);

$sort = filter_input(INPUT_GET, "sort");
$sort = trim($sort);

$view = filter_input(INPUT_GET, "view");
$view = trim($view);

$page_heading = $tag;

if ($tag == "_singlechars") {
	$page_heading = "Single Characters";
	$slist = "singlechars";
} elseif (substr($tag, 0, 7) == "_recent") {
	$since = (int) substr($tag, 8);
	if (!is_int($since)) $since = 1;
	$where.= " AND DATE_SUB(CURDATE(),INTERVAL $since DAY) <= vocab.`added`";
	$page_heading = "Recent Additions: $since day".($since != 1 ? 's' : '');
	$slist = "recent";
}

if ($sort == "added_desc") $orderby = "`added` DESC";
elseif ($sort == "added_asc") $orderby = "`added` ASC";
elseif ($sort == "random") $orderby = "RAND()";
else $orderby = "`frequency` ASC, RAND()";

if ($tag && !$slist) {
	$sql = "SELECT * FROM tags INNER JOIN vocab USING (vocab_id) INNER JOIN zhongwen USING (zid) WHERE tags.tag=:tag AND tags.user_id=:user_id $where ORDER BY $orderby";
	$statement = $pdo->prepare($sql);
	$statement->bindValue(":tag", $tag);
	$statement->bindValue(":user_id", $current_user->getId());
} else {
	if (!$page_heading) $page_heading = "My Vocab";
	$sql = "SELECT * FROM vocab INNER JOIN zhongwen USING (zid) WHERE user_id=:user_id $where ORDER BY $orderby";
	$statement = $pdo->prepare($sql);
	$statement->bindValue(":user_id", $current_user->getId());
}

$statement->execute();
$rows = array();
$num_vocab_items = 0;
while ($row = $statement->fetch()) {
	if ($slist == "singlechars") {
		//filter out non-single chars
		if (strlen($row['hanzi_jt']) == 3) {
			if ($num_vocab_items++ == 50) break;
			$rows[] = new Vocab($row, $pdo, $logger);
		}
	} else {
		if ($num_vocab_items++ == 50) break;
		$rows[] = new Vocab($row, $pdo, $logger);
	}
}

if ($tag) $page_title.= ' / '.$page_heading;
include __DIR__."/../templates/page_header.php";

?>
<h2><?=$page_heading?></h2>

<div class="vocab-container">

<div id="vocablistsel">
	<?
	if($tag && !$slist) {
		?>
		<div class="listdetails">
			<form action="vocab.php" method="post">
				<input type="hidden" name="action" value="edit_tag"/>
				<input type="hidden" name="tag" value="<?=htmlSC($tag)?>"/>
				<details>
					<summary>List Details</summary>
					<p>List name: <input type="text" name="listname" value="<?=htmlSC($tag)?>" size="25" maxlength="60"/></p>
					<p><label><input type="checkbox" name="removelist" value="1"/> Delete this list (but keep all associated vocab)</label></p>
					<input type="submit" name="edit_tag" value="Submit Changes"/>
				</details>
			</form>
		</div>
		<?
	}
	?>
	<select onchange="document.location='/vocab.php?tag='+this.options[this.selectedIndex].value;">
		<option value="">Special lists...</option>
		<option value="">All Vocab</option>
		<option value="_singlechars" <?=($tag == "_singlechars" ? 'selected="selected"' : '')?>>Single Characters</option>
		<option value="_recent-1" <?=($tag == "_recent-1" ? 'selected="selected"' : '')?>>Recently Added: 1 day</option>
		<option value="_recent-3" <?=($tag == "_recent-3" ? 'selected="selected"' : '')?>>Recently Added: 3 days</option>
		<option value="_recent-7" <?=($tag == "_recent-7" ? 'selected="selected"' : '')?>>Recently Added: 7 days</option>
		<option value="_recent-14" <?=($tag == "_recent-14" ? 'selected="selected"' : '')?>>Recently Added: 14 days</option>
		<option value="_recent-30" <?=($tag == "_recent-30" ? 'selected="selected"' : '')?>>Recently Added: 30 days</option>
	</select>
</div>
	
<fieldset id="listcontrols">
	<?=($tag ? '<b style="color:black;">'.$num_vocab_items.'</b> vocab item'.($num_vocab_items != 1 ? 's' : '').' in this list.' : '<b style="color:black;">'.$num_vocab_items.'</b> vocab items total.')?> &nbsp; 
	<!--<label title="toggle vocab you marked as memorized"><input type="checkbox" name="toggleMemorized" value=""<?=$ch_mem?> onchange="document.location='/vocab.php?tag=<?=urlencode($_GET['tag']).($view == "flashcards" ? '&view=flashcards' : '')?>&showmemorized='+( $(this).is(':checked') ? 'true' : 'false' );"/>Show Memorized</label> <span style="color:black;">&middot;</span> -->
	<?
	if ($view == "flashcards") {
		?>
		<a href="/vocab.php?tag=<?=urlencode($tag)?>&sort=<?=$sort?>"><img src="/assets/img/mode_list.png" alt="list" title="list" border="0" style="vertical-align:middle; opacity:.3;"/></a> &nbsp; 
		<img src="/assets/img/mode_cards.png" alt="flash cards" title="flash cards" border="0" style="vertical-align:middle;"/>
		<?
	} else {
		?>
		<img src="/assets/img/mode_list.png" alt="list" title="list" style="vertical-align:middle;"/> &nbsp; 
		<a href="/vocab.php?tag=<?=urlencode($tag)?>&view=flashcards&sort=<?=$sort?>"><img src="/assets/img/mode_cards.png" alt="flash cards" title="flash cards" border="0" style="vertical-align:middle; opacity:.3;"/></a>
		<?
	}
	?>
	<p></p>
	Sort by 
	<select onchange="document.location='/vocab.php?tag=<?=$tag?>&view=<?=$view?>&sort='+this.options[this.selectedIndex].value;">
		<option value="" <?=(!$sort ? 'selected="selected"' : '')?>>difficulty</option>
		<option value="added_desc" <?=($sort == "added_desc" ? 'selected="selected"' : '')?>>date added (recent first)</option>
		<option value="added_asc" <?=($sort == "added_asc" ? 'selected="selected"' : '')?>>date added (oldest first)</option>
		<option value="random" <?=($sort == "random" ? 'selected="selected"' : '')?>>random</option>
	</select>
</fieldset>

<fieldset id="toggcontr">
	<legend>Toggle</legend>
	<a href="#" title="toggle chinese characters" accesskey="h" class="preventdefault" onclick="$('.vocablist dt').toggleClass('toggle-vis');"><img src="/assets/img/key_h.png" alt="H" border="0" style="vertical-align:top;"/></a> <span class="sw sw-hz sw-on">汉字</span> &nbsp;&nbsp; 
	<a href="#" title="toggle between traditional and simplified characters" accesskey="f" class="preventdefault" onclick="togglefj();"><img src="/assets/img/key_f.png" alt="F" border="0" style="vertical-align:top;"/></a> <span class="fjsw sw sw-fj" title="traditional characters">繁</span> &middot; <span class="fjsw sw sw-fj sw-on" title="simplified characters">简</span> &nbsp;&nbsp; 
	<a href="#" title="toggle phonetics (pinyin)" accesskey="p" class="preventdefault" onclick="$('.vocablist dd.pinyin').toggleClass('toggle-vis');"><img src="/assets/img/key_p.png" alt="P" border="0" style="vertical-align:top;"/></a> <span class="sw sw-py sw-on">拼音</span> &nbsp;&nbsp; 
	<a href="#" title="toggle definitions" accesskey="d" class="preventdefault" onclick="$('.vocablist dd.definitions').toggleClass('toggle-vis');"><img src="/assets/img/key_d.png" alt="D" border="0" style="vertical-align:top;"/></a> <span class="sw sw-df sw-on">Definitions</span>
</fieldset>

<?
if ($num_vocab_items) {
	?>
	<div class="vocablist">
		<?
		if($view == "flashcards") {
			?>
			<div class="fcards">
				<a href="#prev" onclick="fcnav(-1);return false;" class="fcnav fcnav-prev"></a>
				<a href="#next" onclick="fcnav(1);return false;" class="fcnav fcnav-next"></a>
				<div id="fcards-container" class="fcards-container">
					<div class="fcard">
						<dl class="vocab">
							<dd>
								<p><b><?=($num_vocab_items)?></b> flash cards in this set.</p>
								<div style="width:30px; margin:0 auto; background:green url(/assets/img/mark_check.png) no-repeat center center;">&nbsp;</div>
								I know this one<br/>(decrease frequency of this card)<p></p>
								<div style="width:30px; margin:0 auto; background:#e10909 url(/assets/img/mark_question.png) no-repeat center center;">&nbsp;</div>
								I'm not too sure about this one<br/>(increase frequency)
								<p><a href="#init" class="preventdefault" onclick="fcnav(1)">Begin session</a></p>
							</dd>
						</dl>
					</div>
					<?
					foreach($rows as $i => $vocab) {
						echo '<div class="fcard">';
						$vocab->renderHTML($i + 1, $num_vocab_items);
						echo '</div>';
					}
					?>
					<div class="fcard">
						<dl class="vocab">
							<dd>
								<p>You've reached the end of the flashcard set.</p>
								<p><a href="#init" class="preventdefault" onclick="fcnav(0)">Go back to the beginning</a></p>
							</dd>
						</dl>
					</div>
				</div>
			</div>
			<?
		} else {
			//list
			foreach($rows as $vocab) {
				$vocab->renderHTML();
			}
			if($num_vocab_items == 50) echo '<div style="padding:3px 10px; font-size:18px; border:1px solid #DDD; background-color:#EEE;">Showing only the first 50 vocab entries. <b><a href="#loadvocab">Load all vocab</a></b></div>';
		}
		?>
	</div>
	<?
}

?>
</div><!-- .vocab-container -->
<?

include __DIR__."/../templates/page_footer.php";