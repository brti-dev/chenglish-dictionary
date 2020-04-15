<?php

namespace Pced;

use Pced\PrimezeroTools;

require_once __DIR__."/../config/config_db.php";

class Vocab {

    private $initialized = false;

    private $pdo;
    
    private $logger;

    private $vocab_id;

    private $user_id;

    private $zid;

    /**
     * User-determined switch to indicate she memorized this vocab item
     * @var integer True=1; False=0;
     */
    public $memorized = 0;

    /**
     * Indicates familiarity and frequency with which the vocab item should appear
     * @var integer
     */
    public $frequency = 0;

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
            if ($key == "frequency" || $key == "memorized") {
                $this->{$key} = (int) $value;
            } else {
                $this->{$key} = $value;
            }
        }

        $this->initialized = true;
        $this->pdo = $pdo;

        if (!empty($logger)) {
            $this->logger = $logger;
            $this->logger->debug("Vocab object construction", $row);
        }

        return $this;
    }

    public static function get($params, $pdo, $logger=[]): ?array
    {
        $where = array();
        foreach ($params as $key => $value) {
            $where[] = "`$key`=:$key";
        }
        $sql = sprintf("SELECT * FROM vocab RIGHT JOIN zhongwen USING (zid) WHERE %s;", implode(" AND ", $where));
        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $rows = $statement->fetchAll();
        if (empty($rows)) {
            if (!empty($logger)) $logger->debug(sprintf("Vocab::get Empty result [%s]", $sql), $params);
            return null;
        }
        if (!empty($logger)) $logger->debug(sprintf("Vocab::get [%s]", $sql), $rows);

        foreach ($rows as &$row) {
            $row = new self($row, $pdo, $logger);
        }
        
        return $rows;
    }

    public function getId()
    {
        return $this->vocab_id;
    }

    public function getZid()
    {
        return $this->zid;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getTags(): ?array
    {
        $sql = "SELECT tag FROM tags WHERE vocab_id=?";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$this->vocab_id]);
        $tags = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($tags)) return null;

        return $tags;
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
                $tag = trim($tag);
                $tag = filter_var($tag, FILTER_SANITIZE_SPECIAL_CHARS);
                if (empty($tag))
                    continue;
                
                $sql = "INSERT INTO tags (tag, vocab_id, user_id) VALUES (?, ?, ?);";
                $statement = $this->pdo->prepare($sql);
                $statement->execute([$tag, $this->vocab_id, $this->user_id]);
            }
        }
        
        if (isset($this->logger)) $this->logger->debug(sprintf("Vocab::insert OK (vocab_id:%d)", $this->vocab_id), get_object_vars($this));

        return true;
    }
    
    /**
     * Update the current user in the database
     * @return Boolean    
     */
    public function save(): bool
    {
        if (!$this->vocab_id)
            throw new Exception("Couldn't save Vocab: The vocab id hasn't been set.");

        $save_fields = [
            "memorized" => $this->memorized, 
            "frequency" => $this->frequency
        ];

        $sql = "UPDATE `vocab` SET %s WHERE `vocab_id`=%d";
        $sql_set = array();
        foreach ($save_fields as $key => $value) {
            $sql_set[] = "`$key`=:$key";
        }
        $sql = sprintf($sql, implode(",", $sql_set), $this->vocab_id);
        $statement = $this->pdo->prepare($sql);
        if (!$statement->execute($save_fields)) {
            throw new Exception("Error saving Vocab data");
            if ($this->logger) $this->logger->error("Error saving Vocab data at Vocab::save()", $save_fields);
        }

        if ($this->logger) $this->logger->info("Save Vocab data ", $save_fields);

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

    public function renderHTML()
    {        
        if (!$this->initialized) {}

        $this->definitions = preg_replace("@^/|/$@", "", $this->definitions);
        $this->definitions = str_replace("/", ' &nbsp;<span style="color:#AAA;">/</span>&nbsp; ', $this->definitions);
        
        if (isset($this->vocab_id) && $tags = $this->getTags()) {
            $tags = array_map(function($tag) {
                return sprintf("<a href=\"/vocab.php?tag=%s\" title=\"view all entries tagged '%s'\">%s</a>", urlencode($tag), htmlspecialchars($tag), $tag);
            }, $tags);
        }

        $pz = new PrimezeroTools;
        $this->pinyin = trim($this->pinyin);
        $this->pinyin = $pz->pzpinyin_tonedisplay_convert_to_mark($this->pinyin);

        ?>
        <dl id="vocab-<?=$this->vocab_id?>" class="vocab <?=$this->class?>" data-vocab_id="<?=$this->vocab_id?>">
            <dt>
                <div class="num"><?=$vcount?> of <?=$rownum?></div>
                <big class="hz hz-jt" lang="zh-Hans"><?=$this->hanzi_jt?></big>
                <big class="hz hz-ft" lang="zh-Hant"><?=$this->hanzi_ft?></big>
            </dt>
            <dd class="pinyin"><span><?=str_replace(" ", '</span><span>', $this->pinyin)?></span></dd>
            <dd class="definitions"><?=$this->definitions?></dd>
            <?
            //compounds
            if (isset($this->user_id)) {
                $sql = "SELECT hanzi_jt, pinyin, definitions FROM zhongwen WHERE hanzi_jt LIKE '%".$this->hanzi_jt."%' AND hanzi_jt != '".$this->hanzi_jt."'";
                $statement = $this->pdo->query($sql);
                if ($rows = $statement->fetchAll(\PDO::FETCH_CLASS, "Pced\\Zhongwen")) {
                    echo '<dd class="compounds hz">';
                    foreach ($rows as $zhongwen_compound) {
                        $def = substr($zhongwen_compound->definitions, 1, -1);
                        $def = htmlspecialchars($def);
                        echo '<a href="/search.php?query=*'.$zhongwen_compound->hanzi_jt.'*" title="'.$zhongwen_compound->pinyin.'&lt;br/&gt;'.$def.'" class="tooltip">'.$zhongwen_compound->hanzi_jt.'</a> &nbsp;&nbsp; ';
                    }
                    echo '</dd>';
                }
            }

            //tags
            if (!empty($tags)) {
                echo '<dd class="extras"><ul class="tags"><li>'.implode("</li><li>", $tags).'</li></ul></dd>';
            }
            
            //controls
            if (isset($this->vocab_id)) {
                ?>
                <dd class="extras">
                    <ul class="controls">
                        <li class="mark known" rel="check"><a href="#check" title="mark this entry as known and show it less frequently" onclick="markVocab(this,'check');return false;">&#10004;</a></li>
                        <li class="mark unknown" rel="question"><a href="#question" title="mark this entry as unknown and show it more frequently" onclick="markVocab(this,'question');return false;">?</a></li>
                        <li><a href="#edit" title="edit this entry" class="editvocab" rel="<?=$this->vocab_id?>" onclick="editVocab(<?=$this->vocab_id?>);return false;">edit</a></li>
                        <li class="exlink mdbg"><a href="http://www.mdbg.net/chindict/chindict.php?wdqb=*<?=$this->hanzi_jt?>*&wdrst=0" target="_blank" title="search for this on MDGB Chinese-English Dictionary">MDBG</a></li>
                    </ul>
                </dd>
                <?
            }
            ?>
        </dl>
        <?php
    }

    public static function renderError($message)
    {
        $message = sprintf("/Error/%s/", $message);
        $vocab = new self(["hanzi_ft"=>"故障", "hanzi_jt"=>"故障", "pinyin"=>"gu4 zhang4", "definitions"=>$message, "class"=>"error"], null);
        $vocab->renderHTML();
    }
}