<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pced\Vocab;

require __DIR__."/../config/config_db.php";
require __DIR__."/../config/config_logger_tests.php";

define("TEST_USER_ID", 1);

class VocabTest extends TestCase
{
    public function testClearPreviousTestVocab()
    {
        $get_params = [
            "zid" => 3, // Obscure character
            "user_id" => TEST_USER_ID,
        ];
        if ($vocab = Vocab::get($get_params, $GLOBALS['pdo'], $GLOBALS['logger_tests'])[0]) {
            $this->assertTrue($vocab->delete());
        } else {
            $this->assertTrue(true);
        }
    }

    public function testInsertVocabEmptyException()
    {
        $this->expectException(Exception::class);
        $insert_params = [
            "zid" => '',
            "user_id" => '',
        ];
        $vocab = new Vocab($insert_params, $GLOBALS['pdo'], $GLOBALS['logger_tests']);
        $vocab->insert();
    }

    public function testInsertVocab()
    {
        $insert_params = [
            "zid" => 3, // Obscure character
            "user_id" => TEST_USER_ID,
        ];
        $vocab = new Vocab($insert_params, $GLOBALS['pdo'], $GLOBALS['logger_tests']);
        $this->assertTrue($vocab->insert());

        return $vocab;
    }

    /**
     * @depends testInsertVocab
     */
    public function testInsertVocabDuplicateException(Vocab $vocab)
    {
        $this->expectException(Exception::class);
        $vocab->insert();
    }

    /**
     * @depends testInsertVocab
     */
    public function testSaveVocab(Vocab $vocab)
    {
        $vocab->frequency += 1;
        $vocab->memorized += 1;
        $this->assertTrue($vocab->save());

        $vocab_check = Vocab::get(["vocab_id"=>$vocab->getId()], $GLOBALS['pdo'], $GLOBALS['logger_tests'])[0];
        $this->assertEquals($vocab->frequency, $vocab_check->frequency);
        $this->assertEquals($vocab->memorized, $vocab_check->memorized);
    }

    public function testGetVocab()
    {
        $get_params = [
            "zid" => 3, // Obscure character
            "user_id" => TEST_USER_ID,
        ];
        $vocab = Vocab::get($get_params, $GLOBALS['pdo'], $GLOBALS['logger_tests'])[0];
        $this->assertEquals((int) $vocab->getZid(), 3);

        return $vocab;
    }

    /**
     * @depends testGetVocab
     */
    public function testDeleteVocab(Vocab $vocab)
    {
        $this->assertTrue($vocab->delete());
    }

    public function testGetVocabEmpty()
    {
        $get_params = [
            "user_id" => 999999,
            "zid" => 999999,
        ];
        $this->assertNull(Vocab::get($get_params, $GLOBALS['pdo'], $GLOBALS['logger_tests']));
    }

    public function testGetVocabTags()
    {
        $sql = "SELECT tag FROM tags LIMIT 10";
        $statement = $GLOBALS['pdo']->query($sql);
        $tags = $statement->fetchAll(PDO::FETCH_COLUMN);
        $this->assertEquals(count($tags), 10);
    }
}