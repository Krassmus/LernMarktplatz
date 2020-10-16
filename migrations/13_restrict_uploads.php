<?php

class RestrictUploads extends Migration
{

    function up() {
        Config::get()->create(
            "LERNMARKTPLATZ_UPLOAD_STATUS",
            array(
                'value' => "autor",
                'type' => "string",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "Ab welchem Nutzerstatus (autor, tutor, dozent, admin, root) darf man Materialien hinzufügen?"
            )
        );
    }

    function down()
    {
        Config::get()->delete("LERNMARKTPLATZ_UPLOAD_STATUS");
    }
}
