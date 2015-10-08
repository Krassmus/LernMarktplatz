<?php

class MarketMaterial extends SimpleORMap {

    static public function findAll()
    {
        return self::findBySQL("1=1");
    }

    static public function findByText($text)
    {
        $statement = DBManager::get()->prepare("
            SELECT lehrmarktplatz_material.*
            FROM lehrmarktplatz_material
                LEFT JOIN lehrmarktplatz_tags_material USING (material_id)
                LEFT JOIN lehrmarktplatz_tags USING (tag_hash)
            WHERE lehrmarktplatz_material.name LIKE :text
                OR description LIKE :text
                OR short_description LIKE :text
                OR lehrmarktplatz_tags.name LIKE :text
        ");
        $statement->execute(array('text' => $text));
        $material_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $materials = array();
        foreach ($material_data as $data) {
            $materials[] = MarketMaterial::buildExisting($data);
        }
        return $materials;
    }

    static public function findByTagHash($tag_hash)
    {
        return self::findBySQL("INNER JOIN lehrmarktplatz_tags_material USING (material_id) WHERE lehrmarktplatz_tags_material.tag_hash = ?", array($tag));
    }

    static public function getFileDataPath() {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/lehrmarktplatz";
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_material';

        parent::configure($config);
    }

    function __construct($id = null)
    {
        $this->registerCallback('before_store', 'cbSerializeData');
        $this->registerCallback('after_store after_initialize', 'cbUnserializeData');
        parent::__construct($id);
    }

    function cbSerializeData()
    {
        $this->content['structure'] = json_encode(studip_utf8encode($this->content['structure']));
        $this->content_db['structure'] = json_encode(studip_utf8encode($this->content_db['structure']));
        return true;
    }

    function cbUnserializeData()
    {
        $this->content['structure'] = (array) studip_utf8decode(json_decode($this->content['structure'], true));
        $this->content_db['structure'] = (array) studip_utf8decode(json_decode($this->content_db['structure'], true));
        return true;
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

    public function getLogoURL($color = "blue")
    {
        if ($this->isFolder()) {
            return Assets::image_path("icons/$color/folder-full.svg");
        } elseif($this->isImage()) {
            return Assets::image_path("icons/$color/file-pic.svg");
        } elseif($this->isStudipQuestionnaire()) {
            return Assets::image_path("icons/$color/vote.svg");
        } else {
            return Assets::image_path("icons/$color/file.svg");
        }

    }

    public function isFolder() {
        return (bool) $this['structure'];
    }

    public function isImage()
    {
        return stripos($this['content_type'], "image") === 0;
    }

    public function isStudipQuestionnaire()
    {
        return $this['content_type'] === "application/json+studipquestionnaire";
    }
}