<?php

class AddOptionDisableLicense extends Migration {

    function up() {
        Config::get()->create(
            "LERNMARKTPLATZ_DISABLE_LICENSE",
            array(
                'value' => "0",
                'type' => "boolean",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "Sollen die Lizenzen deaktiviert werden?"
            )
        );
    }

    function down() {
        Config::get()->delete("LERNMARKTPLATZ_DISABLE_LICENSE");
    }
}