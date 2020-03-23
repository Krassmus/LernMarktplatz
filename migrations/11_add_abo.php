<?php

class AddAbo extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `lernmarktplatz_abo` (
              `user_id` varchar(32) NOT NULL DEFAULT '',
              `material_id` varchar(32) DEFAULT NULL,
              UNIQUE KEY `user_id` (`user_id`,`material_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            DROP TABLE `lernmarktplatz_abo`
        ");
    }
}
