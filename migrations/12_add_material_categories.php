<?php

class AddMaterialCategories extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            ADD COLUMN `category` varchar(64) NOT NULL DEFAULT '' AFTER `name`,
            ADD KEY `host_id` (`host_id`),
            ADD KEY `category` (`category`),
            ADD KEY `foreign_material_id` (`foreign_material_id`)
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE lernmarktplatz_material
            DROP COLUMN `category`
        ");
    }
}
