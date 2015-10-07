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
                `last_updated` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_material` (
                `material_id` varchar(32) NOT NULL PRIMARY KEY,
                `name` varchar(64) NOT NULL,
                `filename` varchar(64) NOT NULL,
                `short_description` VARCHAR(100) NULL,
                `description` text NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `content_type` varchar(64) NOT NULL,
                `structure` text NULL,
                `host_id` varchar(32) NULL,
                `foreign_material_id` VARCHAR( 32 ) NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` int(11) NOT NULL
            ) ENGINE=InnoDB
        ");
    }

    function down() {

    }
}