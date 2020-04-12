<?php

namespace Pced;

class Zhongwen {

    /**
     * A dictionary language object
     * 
     */
    public function __construct()
    {

    }

    public static function getByZid(int $zid, $pdo): array
    {
        $sql = "SELECT * FROM zhongwen WHERE zid=? LIMIT 1";
        $statement = $pdo->prepare($sql);
        $statement->execute([$zid]);
        if (!$row = $statement->fetchAll(\PDO::FETCH_CLASS, "self"))
            throw new \InvalidArgumentException("The zid `$zid` is not defined.");

        return $row;
    }

    public static function getByHanzi(string $hanzi, $pdo): array
    {
        $sql = "SELECT * FROM zhongwen WHERE (hanzi_jt=:hanzi OR hanzi_ft=:hanzi)";
        $statement = $pdo->prepare($sql);
        $statement->bindValue(":hanzi", $hanzi);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_CLASS, "self");
    }
}