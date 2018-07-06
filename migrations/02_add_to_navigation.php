<?php

class AddToNavigation extends Migration {

    function up() {
        Config::get()->create(
            "LERNMARKTPLATZ_MAIN_NAVIGATION",
            array(
                'value' => "/",
                'type' => "string",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "Unter welchem Navigationspunkt soll der Lernmarktplatz eingehängt werden z.B. / oder /tools "
            )
        );
    }

    function down() {
        Config::get()->delete("LERNMARKTPLATZ_MAIN_NAVIGATION");
    }
}