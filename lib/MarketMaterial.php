<?php

class MarketMaterial extends SimpleORMap {

    static public function findAll()
    {
        return self::findBySQL("1=1");
    }

    static public function getFileDataPath() {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/lehrmarktplatz";
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

    public function getFilePath() {
        if (!file_exists(self::getFileDataPath())) {
            mkdir(self::getFileDataPath());
        }
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getFileDataPath()."/".$this->getId();
    }

    public function delete()
    {
        $success = parent::delete();
        @unlink($this->getFilePath());
        return $success;
    }
}