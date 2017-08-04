<?php

class AddToNavigation extends Migration {

    function up() {
        DBManager::get()->exec("
            INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
            VALUES
                (MD5('LERNMARKTPLATZ_MAIN_NAVIGATION'), '', 'LERNMARKTPLATZ_MAIN_NAVIGATION', '/', '1', 'string', 'global', 'LERNMARKTPLATZ', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Unter welchem Navigationspunkt soll der Lernmarktplatz eingehängt werden z.B. / oder /tools ', '', '')
        ");
    }

    function down() {
        DBManager::get()->exec("
            DELETE FROM `config` WHERE `field` = 'LERNMARKTPLATZ_MAIN_NAVIGATION';
        ");
    }
}