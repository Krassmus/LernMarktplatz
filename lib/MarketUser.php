<?php

class MarketUser extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_user';
        parent::configure($config);
    }
}