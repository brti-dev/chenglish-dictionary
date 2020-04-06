<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require __DIR__."/../config/config_db.php";

class SearchTest extends TestCase
{
    public function testSearchBasic()
    {
        $query = "ni_ hao_";
        $sql = "SELECT * FROM zhongwen WHERE pinyin LIKE CONCAT('%', ?, '%') LIMIT 0, 100";
        $statement = $GLOBALS['pdo']->prepare($sql);
        $statement->execute([$query]);
        $this->assertGreaterThan(0, $statement->fetchColumn());

        $sql = "SELECT * FROM hanzi WHERE user_id=1 column_name LIKE CONCAT('%', :dangerousstring, '%') LIMIT 0, 100";
    }
}