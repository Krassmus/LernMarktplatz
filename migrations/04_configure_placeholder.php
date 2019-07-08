<?php

class ConfigurePlaceholder extends Migration {

    function up() {
        Config::get()->create(
            "LERNMARKTPLATZ_PLACEHOLDER_SEARCH",
            array(
                'value' => "Mathematik, Jura, ...",
                'type' => "string",
                'range' => "global",
                'section' => "LERNMARKTPLATZ",
                'description' => "Was soll im Suchfeld für ein Platzhalter stehen?"
            )
        );
    }

    function down() {
        Config::get()->delete("LERNMARKTPLATZ_PLACEHOLDER_SEARCH");
    }
}