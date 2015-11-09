<?php

class InitPlugin extends Migration {

    function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_hosts` (
                `host_id` varchar(32) NOT NULL PRIMARY KEY,
                `name` varchar(64) NOT NULL,
                `url` varchar(64) NOT NULL,
                `public_key` text NOT NULL,
                `private_key` text NULL,
                `active` tinyint(4) NOT NULL DEFAULT '1',
                `index_server` TINYINT NOT NULL DEFAULT '0',
                `allowed_as_index_server` TINYINT NOT NULL DEFAULT '1',
                `last_updated` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_material` (
                `material_id` varchar(32) NOT NULL PRIMARY KEY,
                `foreign_material_id` VARCHAR( 32 ) NULL,
                `host_id` varchar(32) NULL,
                `name` varchar(64) NOT NULL,
                `filename` varchar(64) NOT NULL,
                `short_description` VARCHAR(100) NULL,
                `description` text NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `content_type` varchar(64) NOT NULL,
                `structure` text NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` int(11) NOT NULL
            ) ENGINE=InnoDB
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_tags_material` (
                `material_id` varchar(32) NOT NULL,
                `tag_hash` varchar(32) NOT NULL,
                UNIQUE KEY `unique_tags` (`material_id`,`tag_hash`),
                KEY `tag_hash` (`tag_hash`),
                KEY `material_id` (`material_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_tags` (
                `tag_hash` varchar(32) NOT NULL,
                `name` varchar(64) NOT NULL,
                PRIMARY KEY (`tag_hash`)
            ) ENGINE=InnoDB
        ");
    }

    function down() {
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lehrmarktplatz_hosts`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lehrmarktplatz_material`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lehrmarktplatz_tags_material`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lehrmarktplatz_tags`;
        ");
    }
}