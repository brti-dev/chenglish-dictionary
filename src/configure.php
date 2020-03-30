<?php

ini_set("error_reporting", 6135);
ini_set('session.save_path', __DIR__.'/../var/sessions');

use Pced\DB;
use Pced\Session;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define("TEMPLATE_PATH", "templates");
define("APP_NAME", "PCE Dictionary");

date_default_timezone_set('America/New_York');

$default_email = "mat.berti@gmail.com";

require __DIR__."/../config/config_db.php";

session_start();

//login from cookies
if(isset($_SESSION['session_id'])) {
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

function outputVocab($row) {
    
    global $vcount, $rownum, $ver, $db;
    $vcount++;
    
    $row['definitions'] = preg_replace("@^/|/$@", "", $row['definitions']);
    $row['definitions'] = str_replace("/", ' &nbsp;<span style="color:#AAA;">/</span>&nbsp; ', $row['definitions']);
    
    $query2 = "SELECT tag FROM tags WHERE vocabid='".$row['vocabid']."'";
    $res2   = mysqli_query($db['link'], $query2);
    $row['tags'] = array();
    while($row2 = mysqli_fetch_assoc($res2)){
        $row['tags'][] = '<a href="/vocab.php?tag='.urlencode($row2['tag']).'" title="view all entries tagged \''.htmlSC($row2['tag']).'\'">'.$row2['tag'].'</a>';
    }
    
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
            
            <? /*        <td><a href="vocab.php?edit=<?=$row['vocabid']?>" title="edit this entry and associated lists" style="background:url(/assets/img/edit.gif) no-repeat center center; text-decoration:none;">&nbsp;&nbsp;&nbsp;&nbsp;</a></td>
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
            </table>*/
            ?>
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
    $r  = mysqli_query($db['link'], $q) or die ( "Query failed: " . mysqli_error() );
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