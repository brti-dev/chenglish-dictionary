<?php

namespace Pced;

require_once __DIR__."/../config/config_db.php";

class Vocab {

    private $pdo;
    private $logger;

    /**
     * Class constructor
     * 
     * @param  array $row Data row from db
     * @param  object $pdo Database object
     * @param  object $logger Logger object
     */
    public function __construct(array $row, $pdo, $logger=[]) 
    {
        foreach ($row as $key => $value) {
            $this->{$key} = $value;
        }

        $this->pdo = $pdo;

        if (!empty($logger)) {
            $this->logger = $logger;
            $this->logger->debug("Vocab object construction", $row);
        }
    }

    public function renderHTML()
    {        
        $this->definitions = preg_replace("@^/|/$@", "", $this->definitions);
        $this->definitions = str_replace("/", ' &nbsp;<span style="color:#AAA;">/</span>&nbsp; ', $this->definitions);
        
        $tags = array();
        $sql = sprintf("SELECT tag FROM tags WHERE vocab_id=%d", (int) $this->vocab_id);
        $statement = $GLOBALS['pdo']->query($sql);
        while ($tag = $statement->fetchColumn()) {
            $tags[] = sprintf("<a href=\"/vocab.php?tag=%s\" title=\"view all entries tagged '%s'\">%s</a>", urlencode($tag), htmlspecialchars($tag), $tag);
        }
        
        $this->pinyin = trim($this->pinyin);
        $py = '<table border="0" cellpadding="0" cellspacing="0"><tr><td>'.str_replace(" ", '</td><td>', $this->pinyin).'</td></tr></table>';
        ?>
        <dl id="vocab-<?=$this->vocab_id?>" class="vocab<?=$posclass?>">
            <dt>
                <div class="num"><?=$vcount?> of <?=$rownum?></div>
                <big class="hz hz-jt"><?=$this->hanzi_jt?></big>
                <big class="hz hz-ft"><?=$this->hanzi_ft?></big>
            </dt>
            <dd class="pinyin"><?=$py?></dd>
            <dd class="definitions"><?=$this->definitions?></dd>
            <?
            //compounds
            $sql = "SELECT hanzi_jt, pinyin, definitions FROM zhongwen WHERE hanzi_jt LIKE '%".$this->hanzi_jt."%' AND hanzi_jt != '".$this->hanzi_jt."'";
            $statement = $GLOBALS['pdo']->query($sql);
            if ($rows = $statement->fetchAll(\PDO::FETCH_CLASS, "Pced\\Zhongwen")) {
                echo '<dd class="compounds hz">';
                foreach ($rows as $zhongwen_compound) {
                    $def = substr($zhongwen_compound->definitions, 1, -1);
                    $def = htmlspecialchars($def);
                    echo '<a href="/search.php?query=*'.$zhongwen_compound->hanzi_jt.'*" title="'.$zhongwen_compound->pinyin.'&lt;br/&gt;'.$def.'" class="tooltip">'.$zhongwen_compound->hanzi_jt.'</a> &nbsp;&nbsp; ';
                }
                echo '</dd>';
            }
            ?>
            <dd class="extras">
                
                <? if (count($tags) > 0) echo '<ul class="tags"><li>'.implode("</li><li>", $tags).'</li></ul>'; ?>
                
                <ul class="controls">
                    <li class="mark known" rel="check"><a href="#check" title="mark this entry as known and show it less frequently">&#10004;</a></li>
                    <li class="mark unknown" rel="question"><a href="#question" title="mark this entry as unknown and show it more frequently">?</a></li>
                    <li><a href="#edit" title="edit this entry" class="editvocab" rel="<?=$this->vocab_id?>">edit</a></li>
                    <li class="exlink mdbg"><a href="http://www.mdbg.net/chindict/chindict.php?wdqb=*<?=$this->hanzi_jt?>*&wdrst=0" target="_blank" title="search for this on MDGB Chinese-English Dictionary">MDBG</a></li>
                </ul>
            </dd>
        </dl>
        <?php
    }

    public static function get($params, $pdo, $logger=[]): ?array
    {
        $where = array();
        $execute = array();
        foreach ($params as $key => $value) {
            $where[] = "`$key`=:$key";
            $execute[$key] = $value;
        }
        $sql = sprintf("SELECT * FROM vocab INNER JOIN zhongwen USING (zid) WHERE %s;", implode(" AND ", $where));
        $statement = $pdo->prepare($sql);
        $statement->execute($execute);

        $rows = $statement->fetchAll();
        if (empty($rows)) {
            if (!empty($logger)) $logger->debug(sprintf("Vocab::get Empty result [%s]", $sql), $execute);
            return null;
        }
        if (!empty($logger)) $logger->debug(sprintf("Vocab::get [%s]", $sql), $rows);

        foreach ($rows as &$row) {
            $row = new self($row, $pdo, $logger);
        }
        
        return $rows;
    }

    public function insert()
    {
        if (empty($this->user_id))
            throw new Exception("Insert vocab requres user_id param");
        if (empty($this->zid))
            throw new Exception("Insert vocab requres zid param");

        // Check if already in vocab
        $get_params = [
            "zid" => $this->zid, 
            "user_id" => $this->user_id,
        ];
        $check_existing = static::get($get_params, $this->pdo, $this->logger);
        if ($check_existing != null) {
            if (isset($this->logger)) $this->logger->debug("Vocab::insert fail: Object already exists.", $get_params);
            throw new Exception("Vocab insert fail: Object already exists.");
        }

        $sql = "INSERT INTO vocab (zid, user_id) VALUES (?, ?);";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$this->zid, $this->user_id]);

        $this->vocab_id = $this->pdo->lastInsertId();

        if (isset($this->tags)) {
            foreach ($this->tags as $tag) {
                $sql = "INSERT INTO tags (tag, vocab_id, user_id) VALUES (?, ?, ?);";
                $statement = $this->pdo->prepare($sql);
                $statement->execute([$tag, $this->vocab_id, $this->user_id]);
            }
        }
        
        if (isset($this->logger)) $this->logger->debug(sprintf("Vocab::insert OK (vocab_id:%d)", $this->vocab_id), get_object_vars($this));

        return true;
    }
    
    public function delete(): bool
    {
        if (!$this->vocab_id)
            throw new Exception("Couldn't delete Vocab: vocab_id hasn't been set.");

        $sql = "DELETE FROM vocab WHERE vocab_id=? LIMIT 1";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$this->vocab_id]);

        if (isset($this->logger)) $this->logger->info("DELETE vocab vocab_id:".$this->vocab_id);

        return true;
    }
}