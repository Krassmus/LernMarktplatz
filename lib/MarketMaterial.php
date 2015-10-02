<?php

class MarketMaterial extends SimpleORMap {

    static public function findAll()
    {
        return self::findBySQL("1=1");
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_material';
        parent::configure($config);
    }

    public function getTopics()
    {
        return array();
    }
}