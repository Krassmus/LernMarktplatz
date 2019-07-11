<?php

class AddUrlForPresenting extends Migration {

    function up() {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            ADD COLUMN `player_url` VARCHAR(256) NULL AFTER `description`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            ADD COLUMN `tool` VARCHAR(128) NULL AFTER `player_url`
        ");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `player_url`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `tool`
        ");
    }
}