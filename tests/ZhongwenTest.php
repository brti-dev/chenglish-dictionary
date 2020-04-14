<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pced\Zhongwen;

require __DIR__."/../config/config_db.php";

class ZhongwenTest extends TestCase
{
    public function testGetZhongwenException()
    {
        $this->expectException(InvalidArgumentException::class);
        $zw = Zhongwen::getByZid(99999, $GLOBALS['pdo']);
    }

    public function testGetZhongwenByZid()
    {
        $zw = Zhongwen::getByZid(5401, $GLOBALS['pdo'])[0];
        $this->assertEquals($zw->pinyin, "ni3 hao3");
        $this->assertEquals($zw, Zhongwen::getByHanzi("你好", $GLOBALS['pdo'])[0]);
    }

    public function testGetZhongwenByHanzi()
    {
        $zw = Zhongwen::getByHanzi("你好", $GLOBALS['pdo'])[0];
        $this->assertEquals($zw->pinyin, "ni3 hao3");
    }
}