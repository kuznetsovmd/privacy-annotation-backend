<?php

namespace App\Models;

use Engine\Services\RawSQLService as SQL;

/**
 * Policy.php
 *
 * Class that provides policy model.
 */
class Policy
{

    /**
     * Adds new policies into database.
     *
     * @param array $rows - Set of policies
     * @return bool
     */
    public static function create(array $rows): bool
    {
        $sql = <<<SQL

        INSERT IGNORE INTO `policies` (
            `hash`,
            `content`
        ) VALUES

        SQL;

        $instances = [];
        $values = [];
        foreach ($rows as $hash => $content) {
            $instances[] = "\n(?, ?)";
            $values[]    = $hash;
            $values[]    = $content;
        }

        return null !== SQL::set($sql . implode(",", $instances), $values);
    }

    /**
     * Gives policy for annotation.
     *
     * @param string $hash - Policy hash
     * @return null|array
     */
    public static function getExact(string $hash): ?array
    {
        $sql1 = <<<SQL

        SELECT * FROM `policies` 
        WHERE `hash` = ?

        SQL;

        return SQL::get($sql1, [$hash], all: false);
    }

    /**
     * Gives policy for annotation.
     *
     * @return null|array
     */
    public static function getRandom(): ?array
    {
        $sql1 = <<<SQL

        SELECT * FROM `policies` 
        WHERE `content` <> '' AND
              `requested` = (SELECT MIN(`requested`) FROM `policies`)
        ORDER BY RAND() LIMIT 1

        SQL;

        $r = SQL::get($sql1, all: false);
        if (!isset($r)) return null;

        $sql2 = <<<SQL

        UPDATE `policies` 
        SET `requested` = `requested` + 1
        WHERE `id` = ?

        SQL;

        if (null == SQL::set($sql2, [$r['id']])) return null; else return $r;
    }

}
