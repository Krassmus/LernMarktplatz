<?php

class AddMaterialCategories extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmarktplatz_material`
            ADD COLUMN `category` varchar(64) NOT NULL DEFAULT '' AFTER `name`,
            ADD KEY `host_id` (`host_id`),
            ADD KEY `category` (`category`),
            ADD KEY `foreign_material_id` (`foreign_material_id`)
        ");
        DBManager::get()->exec("
            UPDATE `lernmarktplatz_material`
            SET `category` = 'video'
            WHERE SUBSTRING(`content_type`, 0, 5) = 'video'
        ");
        DBManager::get()->exec("
            UPDATE `lernmarktplatz_material`
            SET `category` = 'audio'
            WHERE SUBSTRING(`content_type`, 0, 5) = 'audio'
        ");
        DBManager::get()->exec("
            UPDATE `lernmarktplatz_material`
            SET `category` = 'presentation'
            WHERE `content_type` IN ('application/pdf', 'application/x-iwork-keynote-sffkey', 'application/vnd.apple.keynote', 'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.presentation-template')
                OR content_type LIKE 'application/vnd.openxmlformats-officedocument.presentationml.%'
                OR `content_type` LIKE 'application/vnd.ms-powerpoint%'
        ");
        DBManager::get()->exec("
            UPDATE `lernmarktplatz_material`
            SET `category` = 'elearning'
            WHERE `player_url` IS NOT NULL
                AND `player_url` != ''
        ");
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `category`
        ");
        SimpleORMap::expireTableScheme();
    }
}
