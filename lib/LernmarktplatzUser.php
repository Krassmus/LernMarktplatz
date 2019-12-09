<?php

class LernmarktplatzUser extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_user';
        $config['belongs_to']['host'] = array(
            'class_name' => 'LernmarktplatzHost',
            'foreign_key' => 'host_id'
        );
        parent::configure($config);
    }
}