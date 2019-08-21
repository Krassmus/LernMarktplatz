<?php

class AddDifficulty extends Migration {

    function up() {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            ADD COLUMN `difficulty_start` tinyint(12) NOT NULL DEFAULT '1' AFTER `description`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            ADD COLUMN `difficulty_end` tinyint(12) NOT NULL DEFAULT '12' AFTER `difficulty_start`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            ADD COLUMN `draft` tinyint(1) NOT NULL DEFAULT '0' AFTER `name`
        ");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `difficulty_start`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `difficulty_end`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `draft`
        ");
    }
}