<?php

require '../vendor/autoload.php';

use Pced\PrimezeroTools;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__."/../src/configure.php");

$_GET['query'] = trim($_GET['query']);
$q = str_replace("*", "%", $_GET['query']);

include __DIR__."/../templates/page_header.php";

if($_POST['_action'] == "delete") {
	
	// DELETE VOCAB ITEM //
	
	if(!$vid = $_POST['_vocabid']) die("No vocabid given");
	
	$q = "SELECT * FROM vocab WHERE vocabid='".mysqli_real_escape_string($db['link'], $vid)."' and usrid='$_SESSION[usrid]' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($db['link'], $q))) die("Coudn't find that vocab entry");
	
	$q = "DELETE FROM tags WHERE vocabid = '".mysqli_real_escape_string($db['link'], $vid)."'";
	if(!mysqli_query($db['link'], $q)) die("Couldn't delete tags");
	
	$q = "DELETE FROM vocab WHERE vocabid = '".mysqli_real_escape_string($db['link'], $vid)."' LIMIT 1";
	if(!mysqli_query($db['link'], $q)) die("Couldn't delete vocab entry");
	
	exit;

}

if($_POST['_action'] == "mark") {
	
	if(!$vid = $_POST['_vocabid']) die("No vocabid given");
	$q = "SELECT * FROM vocab WHERE vocabid='$vid' and usrid='$_SESSION[usrid]' LIMIT 1";
	if(!$row = mysqli_fetch_assoc(mysqli_query($db['link'], $q))) {
		die("Coudn't find that vocab entry");
	}
	
	$freq = ($_POST['_act'] == "check" ? ++$row['frequency'] : --$row['frequency']);
	
	$q = "UPDATE vocab SET frequency='".mysqli_real_escape_string($db['link'], $freq)."' WHERE vocabid='".mysqli_real_escape_string($db['link'], $vid)."' LIMIT 1";
	if(!mysqli_query($db['link'], $q)) die("Couldn't update memorization mark");
	
	exit;
	
}

/*if($_POST['_action'] == "change_frequency") {
	if(!$vid = $_POST['_vocabid']) die("No vocabid given");
	$q = "SELECT * FROM vocab WHERE vocabid='$vid' and usrid='$_SESSION[usrid]' LIMIT 1";
	if(!mysqli_num_rows(mysqli_query($db['link'], $q))) {
		die("Coudn't find that vocab entry");
	}
	$q = "UPDATE vocab SET frequency='".mysqli_real_escape_string($db['link'], $_POST['_frequency'])."' WHERE vocabid='".mysqli_real_escape_string($db['link'], $vid)."' LIMIT 1";
	if(!mysqli_query($db['link'], $q)) die("Couldn't update frequency value");
	exit;
}*/

if($_POST['action'] == "edit_tag"){
	
	// EDIT VOCAB LIST //
	
	$tag = $_POST['tag'];
	$listname = trim($_POST['listname']);
	
	if($_POST['removelist']){
		$q = "DELETE FROM tags WHERE `tag` = '".mysqli_real_escape_string($db['link'], $tag)."' AND usrid = '$_SESSION[usrid]'";
		if(!mysqli_query($db['link'], $q)) die("Database error removing tag");
		header("Location: /vocab.php");
		exit;
	}
	
	if($tag != $listname && $listname != ''){
		$q = "UPDATE tags SET `tag` = '".mysqli_real_escape_string($db['link'], $listname)."' WHERE `tag` = '".mysqli_real_escape_string($db['link'], $tag)."' AND usrid = '$_SESSION[usrid]'";
		if(!mysqli_query($db['link'], $q)) die("Database error updating tag");
		header("Location: /vocab.php?tag=".urlencode($listname));
		exit;
	}
	
}

$page->title.= " / My Vocabulary";
$page->javascript.= '<script type="text/javascript" src="/assets/script/vocab.js"></script>';

if(!isset($_SESSION['usrid'])) {
	$page->header();
	echo "<h2>My Vocabulary</h2>\nPlease register and/or log in to view and modify personal lists.";
	$page->footer();
	exit;
}

// SUBMIT ADD //

if(isset($_POST['submit_add'])) {
	
	$pz = new PrimezeroTools();
	
	$in = $_POST['in'];
	$in['definitions'] = "/".$in['definitions']."/";
	
	if($in['singlechars'][0]) {
		foreach($in['singlechars'] as $zid) {
			$q = "SELECT * FROM zhongwen WHERE zid = '$zid' LIMIT 1";
			if($row = mysqli_fetch_assoc(mysqli_query($db['link'], $q))){
				$py_raw = $row['pinyin'];
				$py     = $pz->pzpinyin_tonedisplay_convert_to_mark($py_raw);
				$q = "INSERT INTO vocab (usrid, hanzi_ft, hanzi_jt, pinyin, pinyin_raw, definitions) VALUES ('".$_SESSION['usrid']."', '".mysqli_real_escape_string($db['link'], $row['hanzi_ft'])."', '".mysqli_real_escape_string($db['link'], $row['hanzi_jt'])."', '".mysqli_real_escape_string($db['link'], $py)."', '".mysqli_real_escape_string($db['link'], $py_raw)."', '".mysqli_real_escape_string($db['link'], $row['definitions'])."');";
				mysqli_query($db['link'], $q);
			}
		}
	}
	
	/*if($in['zid']) {
		//compare input with the original entry
		//if there's any difference, the new vocab item is custom
		$q = "SELECT hanzi_ft, hanzi_jt, definitions FROM zhongwen WHERE zid='".mysqli_real_escape_string($db['link'], $in['zid'])."' LIMIT 1";
		$zw = mysqli_fetch_assoc(mysqli_query($db['link'], $q));
		if($zw['hanzi_ft'] != $in['hanzi_ft'] || $zw['hanzi_jt'] != $in['hanzi_jt'] || $zw['definitions'] != $in['definitions']) {
			unset($in['zid']);
			$zw = $in;
		}
	}*/
	
	$py_raw = trim($in['pinyin_raw']);
	$py     = $pz->pzpinyin_tonedisplay_convert_to_mark($py_raw);
	
	$q = sprintf(
		"INSERT INTO vocab (usrid, hanzi_ft, hanzi_jt, pinyin, pinyin_raw, definitions) VALUES ('".$_SESSION['usrid']."', '%s', '%s', '%s', '%s', '%s');",
		mysqli_real_escape_string($db['link'], $in['hanzi_ft']),
		mysqli_real_escape_string($db['link'], $in['hanzi_jt']),
		mysqli_real_escape_string($db['link'], $py),
		mysqli_real_escape_string($db['link'], $py_raw),
		mysqli_real_escape_string($db['link'], $in['definitions'])
	);
	
	$vocabid = mysqlNextAutoIncrement("vocab");
	if(!mysqli_query($db['link'], $q)) die("ERROR #INSVOCAB: Couldn't add vocab to db");
	
	if(count($in['tags'])) {
		$tag_query = "";
		foreach($in['tags'] as $tag) {
			$tag = trim($tag);
			$tag = htmlSC($tag);
			if($tag) $tag_query.= "('".$_SESSION['usrid']."', '".mysqli_real_escape_string($db['link'], $tag)."', '$vocabid'),";
		}
		if($tag_query) {
			$tag_query = "INSERT INTO tags (usrid, tag, vocabid) VALUES ".substr($tag_query, 0, -1).";";
			if(!mysqli_query($db['link'], $tag_query)) die("ERROR #INSTAGS: Couldn't tag the vocab");
		}
	}
	
	header("Location: /vocab.php?sort=added_desc");
	exit;
	
}

if($zid = $_GET['add']) {
	
	// ADD VOCAB FORM //
	
	$pz = new PrimezeroTools();
	
	if($zid = $_GET['add']) {
		$q = "SELECT * FROM zhongwen WHERE zid='$zid' LIMIT 1";
		$zw = mysqli_fetch_object(mysqli_query($db['link'], $q));
		$zw->definitions = preg_replace("@^/|/$@", "", $zw->definitions);
		
		$zw->pinyin = str_replace("5", "", $zw->pinyin);
	}
	
	$page->header();
	
	?>
	<h2>Add Vocab</h2>
	<form action="vocab.php" method="post">
		<input type="hidden" name="in[zid]" value="<?=$zid?>"/>
		<table border="0" cellpadding="0" cellspacing="0" class="vocab-aded">
			<tr>
				<th valign="top">Simplified Chinese</th>
				<td><textarea name="in[hanzi_jt]" class="hz" style="width:400px; height:67px; font-size:150%;"><?=$zw->hanzi_jt?></textarea></td>
			</tr>
			<tr>
				<th valign="top">Traditional Chinese</th>
				<td><textarea name="in[hanzi_ft]" class="hz" style="width:400px; height:67px; font-size:150%;"><?=$zw->hanzi_ft?></textarea></td>
			</tr>
			<tr>
				<th valign="top">Pinyin</th>
				<td><input type="text" name="in[pinyin_raw]" value="<?=$zw->pinyin?>" style="width:400px;"/></td>
			</tr>
			<?
			//chr decomposition
			$chrs = array();
			$chrs = str_split_utf8($zw->hanzi_jt);
			$addlchrs = array();
			if(count($chrs) > 1){
				foreach($chrs as $c){
					$q = "SELECT * FROM vocab WHERE usrid='$_SESSION[usrid]' AND (hanzi_jt='$c' OR hanzi_ft='$c') LIMIT 1";
					if(!mysqli_num_rows(mysqli_query($db['link'], $q))){
						//the character isn't in the user's vocab yet
						//get character details and give option to add char to vocab
						$query = "SELECT * FROM zhongwen WHERE (hanzi_jt='$c' OR hanzi_ft='$c')";
						$res   = mysqli_query($db['link'], $query);
						while($row = mysqli_fetch_assoc($res)){
							$addlchrs[] = $row;
						}
					} else {
						$addlchrs[] = array("hanzi" => $c);
					}
				}
			}
			if(count($addlchrs)) {
				?>
				<tr>
					<th>Character Decomposition</th>
					<td>
						<div style="margin-bottom:5px">Also add the following characters to your vocab list:</div>
						<ul style="list-style:none; margin:0; padding:0;">
							<?
							foreach($addlchrs as $c){
								if($c['hanzi']){
									//already in vocab
									echo '<li style="color:#CCC;"><input type="checkbox" disabled="disabled"/> '.$c['hanzi'].' (already in your vocab)</li>';
								} else
									echo '<li><label style="white-space:nowrap"><input type="checkbox" name="in[singlechars][]" value="'.$c['zid'].'"/> <span class="hz">'.$c['hanzi_jt'].'</span>'.($c['hanzi_jt'] != $c['hanzi_ft'] ? ' <span style="color:#AAA;">(<span class="hz" style="color:black">'.$c['hanzi_ft'].'</span>)</span>' : '').'</label> '.$c['definitions'].'</li>';
							}
							?>
						</ul>
					</td>
				</tr>
				<?
			}
			?>
			<tr>
				<th valign="top">Definition(s)</th>
				<td>
					<div style="margin-bottom:3px; color:#888;">Separate defitions with a forward slash (/)</div>
					<textarea name="in[definitions]" rows="5" style="width:400px"><?=$zw->definitions?></textarea>
				</td>
			</tr>
			<tr>
				<th valign="top" style="padding-top:40px;">Lists</th>
				<td>
					<ul style="margin:0; padding:0; list-style:none;">
						<li style="margin:5px 0;">
							<input type="text" name="in[tags][]" maxlength="60" title="Manually input a new or existing list name" class="tooltip" style="width:200px;"/> &nbsp; 
							<a href="#append" onclick="$(this).closest('ul').append('&#60;li style=&#34;margin:5px 0;&#34;&#62;New List: &#60;input type=&#34;text&#34; name=&#34;in[tags][]&#34; maxlength=&#34;60&#34; style=&#34;width:200px;&#34;/&#62;&#60;/li&#62;');">Add another</a>
						</li>
						<?
						$query = "SELECT DISTINCT(tag) AS tag FROM tags WHERE usrid='".$_SESSION['usrid']."'";
						$res   = mysqli_query($db['link'], $query);
						while($row = mysqli_fetch_assoc($res)) {
							echo '<li style="margin:5px 0 0;"><label><input type="checkbox" name="in[tags][]" value="'.$row['tag'].'"/>'.$row['tag'].'</label></li>';
						}
						?>
					</ul>
				</td>
			</tr>
			<tr>
				<th valign="top">Flash Card frequency</th>
				<td>
					When reviewing vocab lists and flash cards, the sort order of the entries is based on how many times you click the <b>Check Button</b> or <b>Question Mark Button</b> on each vocab item. Clicking Check will move the item further toward the back of the list the next time you load it, while clicking Question Mark will move it further toward the front.
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><input type="submit" name="submit_add" value="Submit" style="font-size:15px; font-weight:bold;"/></td>
			</tr>
		</table>
	</form>
	<?
	
	$page->footer();
	exit;
	
}

if($vid = $_POST['edit']) {
	
	// EDIT VOCAB FORM //
	
	$q = "SELECT * FROM vocab WHERE vocabid='$vid' LIMIT 1";
	$zw = mysqli_fetch_object(mysqli_query($db['link'], $q));
	$zw->definitions = preg_replace("@^/|/$@", "", $zw->definitions);
	
	/*if($zid = $zw->zid) {
		$q = "SELECT * FROM zhongwen WHERE zid='$zid' LIMIT 1";
		$zw2 = mysqli_fetch_object(mysqli_query($db['link'], $q));
		$zw2->definitions = preg_replace("@^/|/$@", "", $zw2->definitions);
		
		$zw->hanzi_jt = $zw2->hanzi_jt;
		$zw->hanzi_ft = $zw2->hanzi_ft;
		$zw->definitions = $zw2->definitions;
	}*/
	
	if($zw->usrid != $_SESSION['usrid']) {
		echo "<h2>Error</h2>This vocab item doesn't seem to belong to you.";
		exit;
	}
	
	$tags = array();
	//get current tags
	$query = "SELECT * FROM tags WHERE vocabid='$vid'";
	$res   = mysqli_query($db['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		$tags[] = $row['tag'];
	}
	
	?>
	<h2>Edit Vocab</h2>
	<form onsubmit="return false;">
		<input type="hidden" name="in[vocabid]" value="<?=$vid?>"/>
		<input type="hidden" name="in[zid]" value="<?=$zid?>"/>
		<input type="hidden" name="in[ref]" value="<?=$_SERVER['HTTP_REFERER']?>"/>
		<table border="0" cellpadding="0" cellspacing="0" class="vocab-aded">
			<tbody>
				<tr>
					<th valign="top">Lists</th>
					<td>
						<?
						foreach($tags as $tag){
							echo '<div style="margin:0 0 2px;"><input type="text" name="in[tags][]" value="'.$tag.'" maxlength="60" style="width:200px;"/> <a href="#rmtag" title="remove this item from this list" style="color:red; font-size:16px;" onclick="$(this).parent().fadeOut(function(){ $(this).remove() })">&times;</a></div>';
						}
						?>
						<div id="appfield">
							<div style="margin:2px 0;">
								<input type="text" name="in[tags][]" maxlength="60" style="width:200px;"/> &nbsp; 
							</div>
						</div>
						<a href="#append" onclick="$(this).before( $('#appfield').html() );">Add another</a>
					</td>
				</tr>
				<tr>
					<th valign="top">
						<div class="hz" style="font-size:120%; font-weight:normal; margin-top:5px;" title="Simplified/Traditional Chinese">简体/繁體</div>
					</th>
					<td>
						<div style="float:right"><input type="text" name="in[hanzi_ft]" value="<?=$zw->hanzi_ft?>" class="hz" style="width:130px; font-size:120%;"></div>
						<input type="text" name="in[hanzi_jt]" value="<?=$zw->hanzi_jt?>" class="hz" style="width:130px; font-size:120%;">
					</td>
				</tr>
				<tr>
					<th valign="top">Pinyin</th>
					<td><input type="text" name="in[pinyin_raw]" value="<?=$zw->pinyin_raw?>" style="width:99%;"></td>
				</tr>
				<tr>
					<th valign="top">Definition(s)</th>
					<td>
						<div style="margin-bottom:3px; color:#888;">Separate defitions with / (a forward slash)</div>
						<textarea name="in[definitions]" rows="2" style="width:280px"><?=$zw->definitions?></textarea>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<a href="#rm" title="remove this entry" onclick="removeVocab('<?=$vid?>');" style="float:right; display:block; padding:3px 8px; border:1px solid #CF1B1B; color:#CF1B1B; text-decoration:none; border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;">Delete</a>
						<input type="button" name="" value="Submit" onclick="submitEditVocab(<?=$vid?>, $(this));" style="font-size:15px; font-weight:bold;"/>
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

if(isset($_POST['submit_edit'])) {
	
	$pz = new PrimezeroTools();
	
	$inp = $_POST['_input'];
	parse_str($inp);
	
	$ret = array();
	
	$in['definitions'] = "/".$in['definitions']."/";
	
	$py_raw = trim($in['pinyin_raw']);
	$py     = $pz->pzpinyin_tonedisplay_convert_to_mark($py_raw);
	$py     = trim($py);
	
	/*if($in['zid']) {
		//compare input with the original entry
		//if there's any difference, the new vocab item is custom
		$q = "SELECT hanzi_ft, hanzi_jt, definitions FROM zhongwen WHERE zid='".mysqli_real_escape_string($db['link'], $in['zid'])."' LIMIT 1";
		$zw = mysqli_fetch_assoc(mysqli_query($db['link'], $q));
		if($zw['hanzi_ft'] != $in['hanzi_ft'] || $zw['hanzi_jt'] != $in['hanzi_jt'] || $zw['definitions'] != $in['definitions']) {
			unset($in['zid']);
			$in = array_merge($in, $zw);
		}
	}*/
	
	if(!$in['zid']) {
		$q = sprintf(
			"UPDATE vocab SET zid = '', hanzi_ft = '%s', hanzi_jt = '%s', pinyin='%s', pinyin_raw='%s', definitions = '%s' WHERE vocabid = '%s' LIMIT 1",
			mysqli_real_escape_string($db['link'], $in['hanzi_ft']),
			mysqli_real_escape_string($db['link'], $in['hanzi_jt']),
			mysqli_real_escape_string($db['link'], $py),
			mysqli_real_escape_string($db['link'], $py_raw),
			mysqli_real_escape_string($db['link'], $in['definitions']),
			mysqli_real_escape_string($db['link'], $in['vocabid'])
		);
		if(!mysqli_query($db['link'], $q)) die("ERROR #UPDVOCAB: Couldn't update vocab to db");
	}
	
	mysqli_query($db['link'], "DELETE FROM tags WHERE vocabid='".mysqli_real_escape_string($db['link'], $in['vocabid'])."'");
	
	if(count($in['tags'])) {
		$tag_query = "";
		foreach($in['tags'] as $tag) {
			$tag = trim($tag);
			$tag = htmlSC($tag);
			if($tag) $tag_query.= "('".$_SESSION['usrid']."', '".mysqli_real_escape_string($db['link'], $tag)."', '".mysqli_real_escape_string($db['link'], $in['vocabid'])."'),";
		}
		if($tag_query) {
			$tag_query = "INSERT INTO tags (usrid, tag, vocabid) VALUES ".substr($tag_query, 0, -1).";";
			if(!mysqli_query($db['link'], $tag_query)) die("ERROR #INSTAGS: Couldn't tag the vocab");
		}
	}
	
	$query = "SELECT * FROM vocab WHERE vocabid='".mysqli_real_escape_string($db['link'], $in['vocabid'])."' LIMIT 1";
	$res   = mysqli_query($db['link'], $query);
	while($row = mysqli_fetch_assoc($res)) {
		if($row['zid']) {
			//get the data from zhongwen and merge it into $row
			$q = "SELECT * FROM zhongwen WHERE zid='".$row['zid']."' LIMIT 1";
			$zw = mysqli_fetch_assoc(mysqli_query($db['link'], $q));
			$row = array_merge($row, $zw);
		}
		outputVocab($row);
	}
	
	exit;
	
}

//////////////////
// INDEX / LIST //
//////////////////

$tag = urldecode($_GET['tag']);
$tag = trim($tag);
$pgtitle = $tag;

$sort = trim($_GET['sort']);
$view = trim($_GET['view']);

if($tag == "_singlechars") {
	$pgtitle = "Single Characters";
	$slist = "singlechars";
} elseif(substr($tag, 0, 7) == "_recent") {
	$since = (int)substr($tag, 8);
	if(!is_int($since)) $since = 1;
	$where.= " AND DATE_SUB(CURDATE(),INTERVAL $since DAY) <= vocab.`added`";
	$pgtitle = "Recent Additions: $since day".($since != 1 ? 's' : '');
	$slist = "recent";
}

if($sort == "added_desc") $orderby = "`added` DESC";
elseif($sort == "added_asc") $orderby = "`added` ASC";
elseif($sort == "random") $orderby = "RAND()";
else $orderby = "`frequency` ASC, RAND()";

if($tag && !$slist) {
	$query = "SELECT * FROM tags LEFT JOIN vocab USING (vocabid) WHERE tag='".mysqli_real_escape_string($db['link'], $tag)."' AND tags.usrid='".$_SESSION['usrid']."' $where ORDER BY $orderby";
} else {
	if(!$pgtitle) $pgtitle = "My Vocab";
	$query = "SELECT * FROM vocab WHERE usrid='".$_SESSION['usrid']."' $where ORDER BY $orderby";
}
$res   = mysqli_query($db['link'], $query);
$num   = 0;
$rows  = array();
while($row = mysqli_fetch_assoc($res)) {
	/*if($row['zid']) {
		//get the data from zhongwen and merge it into $row
		$q = "SELECT * FROM zhongwen WHERE zid='".$row['zid']."' LIMIT 1";
		$zw = mysqli_fetch_assoc(mysqli_query($db['link'], $q));
		$row = array_merge($row, $zw);
	}*/
	
	if($slist == "singlechars") {
		//filter out non-single chars
		if(strlen($row['hanzi_jt']) == 3) {
			if($num++ < 50) $rows[] = $row;
		}
	} else {
		if($num++ < 50) $rows[] = $row;
	}
}

$rownum = count($rows);

if($tag) $page->title.= ' / '.$pgtitle;
$page->header();

?>
<h2 style="margin-bottom:0;"><?=$pgtitle?></h2>

<div id="vocablistsel" style="margin-bottom:20px;">
	<table border="0" cellpadding="10" cellspacing="0" width="100%" style="background-color:white; border-width:0 1px 1px; border-style:solid; border-color:#DDD;">
		<tr>
			<?
			if($tag && !$slist) {
				?>
				<td nowrap="nowrap" style="border-right:1px solid #DDD;">
					<a href="#" class="arrow-toggle preventdefault" onclick="$(this).toggleClass('arrow-toggle-on').next().toggle();" style="font:normal 15px Arial;">List Details</a>
					<div style="display:none; font-size:13px; font-family:arial; line-height:40px;">
						<form action="vocab.php" method="post">
							<input type="hidden" name="action" value="edit_tag"/>
							<input type="hidden" name="tag" value="<?=htmlSC($tag)?>"/>
							List name: <input type="text" name="listname" value="<?=htmlSC($tag)?>" size="25" maxlength="60"/><br/>
							<label><input type="checkbox" name="removelist" value="1"/> Delete this list (but keep all associated vocab)</label><br/>
							<input type="submit" name="edit_tag" value="Submit Changes"/>
						</form>
					</div>
				</td>
				<?
			}
			
			/*?>
			<td valign="top" style="border-right:1px solid #DDD;">
				<select onchange="document.location='/vocab.php?tag='+this.options[this.selectedIndex].value;">
					<option value="">My lists...</option>
					<?
					$query2 = "SELECT tag, COUNT(tag) AS num FROM tags WHERE usrid='".$_SESSION['usrid']."' GROUP BY tag ORDER BY tag";
					$res2   = mysqli_query($db['link'], $query2);
					while($row = mysqli_fetch_assoc($res2)) {
						echo '<option value="'.$row['tag'].'" class="hz">'.$row['tag'].' ('.$row['num'].')</option>';
					}
					?>
				</select>
			</td><? */
			?><td valign="top" width="100%">
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
			</td>
		</tr>
	</table>
</div>
	
<fieldset id="listcontrols">
	<?=($tag ? '<b style="color:black;">'.$num.'</b> vocab item'.($num != 1 ? 's' : '').' in this list.' : '<b style="color:black;">'.$num.'</b> vocab items total.')?> &nbsp; 
	<!--<label title="toggle vocab you marked as memorized"><input type="checkbox" name="toggleMemorized" value=""<?=$ch_mem?> onchange="document.location='/vocab.php?tag=<?=urlencode($_GET['tag']).($view == "flashcards" ? '&view=flashcards' : '')?>&showmemorized='+( $(this).is(':checked') ? 'true' : 'false' );"/>Show Memorized</label> <span style="color:black;">&middot;</span> -->
	<?
	if($view == "flashcards") {
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
	Sort: 
	<select onchange="document.location='/vocab.php?tag=<?=$tag?>&view=<?=$view?>&sort='+this.options[this.selectedIndex].value;">
		<option value="" <?=(!$sort ? 'selected="selected"' : '')?>>Difficulty</option>
		<option value="added_desc" <?=($sort == "added_desc" ? 'selected="selected"' : '')?>>Date added (recent first)</option>
		<option value="added_asc" <?=($sort == "added_asc" ? 'selected="selected"' : '')?>>Date added (oldest first)</option>
		<option value="random" <?=($sort == "random" ? 'selected="selected"' : '')?>>Random</option>
	</select>
</fieldset>

<p></p>

<fieldset id="toggcontr">
	<legend>Toggle</legend>
	<a href="#" title="toggle chinese characters" class="preventdefault" onclick="$('.vocablist dt').toggleClass('toggle-vis');"><img src="/assets/img/key_h.png" alt="H" border="0" style="vertical-align:top;"/></a> <span class="sw sw-hz sw-on">汉字</span> &nbsp;&nbsp; 
	<a href="#" title="toggle between traditional and simplified characters" class="preventdefault" onclick="togglefj();"><img src="/assets/img/key_f.png" alt="F" border="0" style="vertical-align:top;"/></a> <span class="fjsw sw sw-fj" title="traditional characters">繁</span> &middot; <span class="fjsw sw sw-fj sw-on" title="simplified characters">简</span> &nbsp;&nbsp; 
	<a href="#" title="toggle phonetics (pinyin)" class="preventdefault" onclick="$('.vocablist dd.pinyin').toggleClass('toggle-vis');"><img src="/assets/img/key_p.png" alt="P" border="0" style="vertical-align:top;"/></a> <span class="sw sw-py sw-on">拼音</span> &nbsp;&nbsp; 
	<a href="#" title="toggle definitions" class="preventdefault" onclick="$('.vocablist dd.definitions').toggleClass('toggle-vis');"><img src="/assets/img/key_d.png" alt="D" border="0" style="vertical-align:top;"/></a> <span class="sw sw-df sw-on">Definitions</span>
</fieldset>

<p></p>

<?
if($num) {
	?>
	<div class="vocablist">
		<?
		if($view == "flashcards") {
			//flashcards
			$rownum = count($rows);
			?>
			<div class="fcards">
				<a href="#prev" onclick="fcnav('prev');" class="fcnav fcnav-prev"></a>
				<a href="#next" onclick="fcnav('next');" class="fcnav fcnav-next"></a>
				<div class="container">
					<div class="thelist">
						<dl class="vocab fcnav-curr">
							<dd>
								<b><?=($rownum)?></b> flash cards in this set.<p></p>
								<div style="width:30px; margin:0 auto; background:green url(/assets/img/mark_check.png) no-repeat center center;">&nbsp;</div>
								I know this one<br/>(decrease frequency of this card)<p></p>
								<div style="width:30px; margin:0 auto; background:#e10909 url(/assets/img/mark_question.png) no-repeat center center;">&nbsp;</div>
								I'm not too sure about this one<br/>(increase frequency)<p></p>
								<a href="#init" class="preventdefault" onclick="fcnav('next')">Begin session</a>
							</dd>
						</dl>
						<?
						foreach($rows as $row) outputVocab($row);
						?>
					</div>
				</div>
			</div>
			<?
		} else {
			//list
			foreach($rows as $row) {
				outputVocab($row);
			}
			if($rownum < $num) echo '<div style="padding:3px 10px; font-size:18px; border:1px solid #DDD; background-color:#EEE;">Showing 50 of '.$num.' vocab entries. <b><a href="#loadvocab">Load all vocab</a></b></div>';
		}
		?>
	</div>
	<?
}

include __DIR__."/../templates/page_header.php";