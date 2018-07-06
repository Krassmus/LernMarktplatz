<?php

class MakePublic extends Migration {

    function up() {
        Config::get()->create(
            "LERNMARKTPLATZ_PUBLIC_STATUS",
            array(
                'value' => "autor",
                'type' => "string",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "Ab welchem Nutzerstatus (nobody, user, autor, tutor, dozent) darf man den Marktplatz sehen?"
            )
        );
    }

    function down() {
        Config::get()->delete("LERNMARKTPLATZ_PUBLIC_STATUS");
    }
}