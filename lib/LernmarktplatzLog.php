<?php

class LernmarktplatzLog extends SimpleORMap {

    static protected $log_text = array();

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_logs';
        parent::configure($config);
    }

    public static function add($text)
    {
        self::$log_text[] = $text;
    }

    public static function finish()
    {
        $log = new LernmarktplatzLog();
        $log['log_text'] = implode("\n", self::$log_text);
        $log['user_id'] = $GLOBALS['user']->id;
        $log->store();
        self::$log_text = array();
        return $log;
    }

}