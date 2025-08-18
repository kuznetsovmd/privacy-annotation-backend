<?php

namespace App\Models;

use Engine\Services\RawSQLService as SQL;

/**
 * Selection.php
 *
 * Class that provides selection model.
 */
class Selection
{

    /**
     * Adds new selections into database.
     *
     * @param array $rows - Set of selections
     * @return bool
     */
    public static function create(array $rows): bool
    {
        $sql = <<<SQL

        INSERT INTO `selections` (
            `starts_on`,
            `ends_on`,
            `selection_class`,
            `selection_content`,
            `user_id`,
            `hash`
        ) VALUES

        SQL;

        $instances = [];
        foreach ($rows as $content) {
            $instances[] = "\n(?, ?, ?, ?, ?, ?)";
            $values[]    = $content['starts_on'];
            $values[]    = $content['ends_on'];
            $values[]    = $content['selection_class'];
            $values[]    = $content['selection_content'];
            $values[]    = $content['user_id'];
            $values[]    = $content['hash'];
        }

        return null !== SQL::set($sql . implode(",", $instances), $values);
    }

    /**
     * Gives annotation results.
     *
     * @return null|array
     */
    public static function packWithUsers(): ?array
    {
        $sql = <<<SQL
        
        SELECT 
            `selections`.`id`,
            `selections`.`starts_on`,
            `selections`.`ends_on`,
            `selections`.`selection_class`,
            `selections`.`selection_content`,
            `selections`.`user_id`,
            `selections`.`hash`,
            `selections`.`created_at`,
            `users`.`name`,
            `users`.`email`
         FROM `selections`
            INNER JOIN `users` ON `user_id` = `users`.`id`
        
        SQL;

        return SQL::get($sql);
    }

}
