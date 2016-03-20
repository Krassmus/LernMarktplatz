<?php

class InitPlugin extends Migration {

    function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_hosts` (
                `host_id` varchar(32) NOT NULL PRIMARY KEY,
                `name` varchar(64) NOT NULL,
                `url` varchar(200) NOT NULL,
                `public_key` text NOT NULL,
                `private_key` text NULL,
                `active` tinyint(4) NOT NULL DEFAULT '1',
                `index_server` TINYINT NOT NULL DEFAULT '0',
                `allowed_as_index_server` TINYINT NOT NULL DEFAULT '1',
                `last_updated` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                UNIQUE KEY `url` (`url`)
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
                `front_image_content_type` VARCHAR(64) NULL,
                `structure` text NULL,
                `rating` DOUBLE NULL,
                `license` VARCHAR( 64 ) NOT NULL DEFAULT 'CC BY 4.0',
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
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_user` (
                `user_id` varchar(32) NOT NULL,
                `foreign_user_id` varchar(32) NOT NULL,
                `host_id` varchar(32) NOT NULL,
                `name` varchar(100) NOT NULL,
                `avatar` varchar(256) DEFAULT NULL,
                `description` TEXT NULL,
                `chdate` int(11) NOT NULL,
                `mkdate` int(11) NOT NULL,
                PRIMARY KEY (`user_id`),
                UNIQUE KEY `unique_users` (`foreign_user_id`,`host_id`),
                KEY `foreign_user_id` (`foreign_user_id`),
                KEY `host_id` (`host_id`)
            ) ENGINE=InnoDB
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_reviews` (
                `review_id` varchar(32) NOT NULL,
                `material_id` varchar(32) NOT NULL,
                `foreign_review_id` varchar(32) NULL,
                `user_id` varchar(32) NOT NULL,
                `host_id` varchar(32) NULL,
                `rating` int(11) NOT NULL,
                `review` TEXT NOT NULL,
                `chdate` int(11) NOT NULL,
                `mkdate` int(11) NOT NULL,
                PRIMARY KEY (`review_id`),
                UNIQUE KEY `unique_users` (`user_id`,`host_id`, `material_id`),
                KEY `material_id` (`material_id`),
                KEY `foreign_review_id` (`foreign_review_id`),
                KEY `user_id` (`user_id`),
                KEY `host_id` (`host_id`)
            ) ENGINE=InnoDB
        ");

        DBmanager::get()->exec("
            CREATE TABLE IF NOT EXISTS `lehrmarktplatz_comments` (
                `comment_id` varchar(32) NOT NULL,
                `review_id` varchar(32) NOT NULL,
                `foreign_comment_id` varchar(32) DEFAULT NULL,
                `comment` text NOT NULL,
                `host_id` varchar(32) DEFAULT NULL,
                `user_id` varchar(32) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`comment_id`),
                KEY `review_id` (`review_id`),
                KEY `foreign_comment_id` (`foreign_comment_id`),
                KEY `host_id` (`host_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB
        ");

        DBManager::get()->exec("
            INSERT IGNORE INTO datafields
            SET datafield_id = MD5('Lehrmarktplatz-Beschreibung'),
                name = 'Lehrmarktplatz-Beschreibung',
                object_type = 'user',
                edit_perms = 'user',
                view_perms = 'user',
                priority = 0,
                mkdate = UNIX_TIMESTAMP(),
                chdate = UNIX_TIMESTAMP(),
                `type` = 'textarea',
                typeparam = '',
                description = 'Geben Sie eine kurze Beschreibung für Sich ab, die auf Ihrem Profil im Lehrmarktplatz sichtbar ist.'
        ");
        DBManager::get()->exec("
            INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
            VALUES
                (MD5('LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD'), '', 'LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD', MD5('Lehrmarktplatz-Beschreibung'), MD5('Lehrmarktplatz-Beschreibung'), 'string', 'global', 'LEHRMARKTPLATZ', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'MD5-Hash or name of datafield that is representing the user-description', '', '')
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
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lehrmarktplatz_user`;
        ");
    }
}