<?php

class AddRenaming extends Migration {

    function up() {
        Config::get()->create(
            "LERNMARKTPLATZ_TITLE",
            array(
                'value' => _("OER Campus"),
                'type' => "string",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "Title of the Lernmarktplatz."
            )
        );
    }

    function down() {
        Config::get()->delete(
            "LERNMARKTPLATZ_TITLE"
        );
    }
}