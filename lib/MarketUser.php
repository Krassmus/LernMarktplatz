<?php

class MarketUser extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_user';
        parent::configure($config);
    }
}