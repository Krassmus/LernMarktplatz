<?php

class AddMultipleOwners extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `lernmarktplatz_material_users` (
                `material_id` varchar(32) NOT NULL DEFAULT '',
                `user_id` varchar(32) NOT NULL DEFAULT '',
                `external_contact` int(11) NOT NULL DEFAULT 0,
                `position` int(11) NOT NULL DEFAULT 1,
                `chdate` int(11) NOT NULL,
                `mkdate` int(11) NOT NULL,
                PRIMARY KEY (`material_id`,`user_id`,`external_contact`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        DBManager::get()->exec("
            INSERT INTO `lernmarktplatz_material_users` (`material_id`, `user_id`, `external_contact`, `position`, `chdate`, `mkdate`)
            SELECT `material_id`, `user_id`, IF(`host_id` IS NULL, '0', '1'), '1', `mkdate`, `mkdate`
            FROM `lernmarktplatz_material`
        ");
        DBManager::get()->exec("
            ALTER TABLE `lernmarktplatz_material` 
            DROP COLUMN `user_id`
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            DROP TABLE `lernmarktplatz_material_user`
        ");
    }
}