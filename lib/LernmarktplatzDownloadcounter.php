<?php

class LernmarktplatzDownloadcounter extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_downloadcounter';
        parent::configure($config);
    }

    static public function addCounter($material_id)
    {
        $counter = new LernmarktplatzDownloadcounter();
        $counter['material_id'] = $material_id;
        if (Config::get()->LERNMARKTPLATZ_GEOLOCATOR_API) {
            list($url, $lon, $lat) = explode(" ", Config::get()->LERNMARKTPLATZ_GEOLOCATOR_API);
            $output = json_decode(file_get_contents(sprintf($url, $_SERVER['REMOTE_ADDR'])), true);
            if (isset($output[$lon])) {
                $counter['longitude'] = $output[$lon];
            }
            if (isset($output[$lat])) {
                $counter['latitude'] = $output[$lat];
            }
        }
        $counter->store();
        return $counter;
    }
}