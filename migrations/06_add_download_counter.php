<?php

class AddDownloadCounter extends Migration {

    function up() {
        DBManager::get()->exec("
            CREATE TABLE `lernmarktplatz_downloadcounter` (
                `counter_id` varchar(32) NOT NULL DEFAULT '',
                `material_id` varchar(32) NOT NULL,
                `longitude` double DEFAULT NULL,
                `latitude` double DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`counter_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
        ");
        Config::get()->create(
            "LERNMARKTPLATZ_GEOLOCATOR_API",
            array(
                'value' => "",
                'type' => "string",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "URL, um aus einer IP-Adresse ein JSON-Objekt mit Ortsangaben zu bekommen. Beispiel: https://myurl.de/geo.php?ip=%s lon lat"
            )
        );
    }

    function down() {
        DBManager::get()->exec("
            DROP TABLE `lernmarktplatz_downloadcounter`
        ");
        Config::get()->delete("LERNMARKTPLATZ_GEOLOCATOR_API");
    }
}
