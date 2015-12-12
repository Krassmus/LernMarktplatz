<?php

class MarketMaterial extends SimpleORMap {

    static public function findAll()
    {
        return self::findBySQL("1=1");
    }

    static public function findByTag($tag_name)
    {
        self::fetchRemoteSearch($tag_name, true);
        $statement = DBManager::get()->prepare("
            SELECT lehrmarktplatz_material.*
            FROM lehrmarktplatz_material
                INNER JOIN lehrmarktplatz_tags_material USING (material_id)
                INNER JOIN lehrmarktplatz_tags USING (tag_hash)
            WHERE lehrmarktplatz_tags.name = :tag
            GROUP BY lehrmarktplatz_material.material_id
        ");
        $statement->execute(array('tag' => $tag_name));
        $material_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $materials = array();
        foreach ($material_data as $data) {
            $materials[] = MarketMaterial::buildExisting($data);
        }
        return $materials;
    }

    static public function findByText($text)
    {
        self::fetchRemoteSearch($text);
        $statement = DBManager::get()->prepare("
            SELECT lehrmarktplatz_material.*
            FROM lehrmarktplatz_material
                LEFT JOIN lehrmarktplatz_tags_material USING (material_id)
                LEFT JOIN lehrmarktplatz_tags USING (tag_hash)
            WHERE lehrmarktplatz_material.name LIKE :text
                OR description LIKE :text
                OR short_description LIKE :text
                OR lehrmarktplatz_tags.name LIKE :text
            GROUP BY lehrmarktplatz_material.material_id
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
        $tag = MarketTag::find($tag_hash);
        if ($tag) {
            self::fetchRemoteSearch($tag['name'], true);
        }
        return self::findBySQL("INNER JOIN lehrmarktplatz_tags_material USING (material_id) WHERE lehrmarktplatz_tags_material.tag_hash = ?", array($tag_hash));
    }

    static public function getFileDataPath() {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/lehrmarktplatz";
    }

    /**
     * Searches on remote hosts for the text.
     * @param $text
     * @param bool|false $tag
     */
    static protected function fetchRemoteSearch($text, $tag = false) {
        $cache_name = "Lehrmarktplatz_remote_searched_for_".md5($text)."_".($tag ? 1 : 0);
        $already_searched = (bool) StudipCacheFactory::getCache()->read($cache_name);
        if (!$already_searched) {
            $host = MarketHost::findOneBySQL("index_server = '1' AND allowed_as_index_server = '1' ORDER BY RAND()");
            if ($host) {
                $host->fetchRemoteSearch($text, $tag);
            }
            StudipCacheFactory::getCache()->read($cache_name, "1", 60);
        }
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

    public function getTags()
    {
        $statement = DBManager::get()->prepare("
            SELECT lehrmarktplatz_tags.*
            FROM lehrmarktplatz_tags
                INNER JOIN lehrmarktplatz_tags_material ON (lehrmarktplatz_tags_material.tag_hash = lehrmarktplatz_tags.tag_hash)
            WHERE lehrmarktplatz_tags_material.material_id = :material_id
            ORDER BY lehrmarktplatz_tags.name ASC
        ");
        $statement->execute(array('material_id' => $this->getId()));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setTags($tags) {
        $statement = DBManager::get()->prepare("
            DELETE FROM lehrmarktplatz_tags_material
            WHERE material_id = :material_id
        ");
        $statement->execute(array('material_id' => $this->getId()));
        $insert_tag = DBManager::get()->prepare("
            INSERT IGNORE INTO lehrmarktplatz_tags
            SET name = :tag,
                tag_hash = MD5(:tag)
        ");
        $add_tag = DBManager::get()->prepare("
            INSERT IGNORE INTO lehrmarktplatz_tags_material
            SET tag_hash = MD5(:tag),
                material_id = :material_id
        ");
        foreach ($tags as $tag) {
            $insert_tag->execute(array(
                'tag' => $tag
            ));
            $add_tag->execute(array(
                'tag' => $tag,
                'material_id' => $this->getId()
            ));
        }
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
        } elseif($this->isPresentation()) {
            return Assets::image_path("icons/$color/file-ppt.svg");
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

    protected function getFileEnding()
    {
        if (strpos(".", $this['filename']) !== false) {
            return strtolower(substr($this['filename'], strpos(".", $this['filename'])));
        } else {
            return "";
        }
    }

    public function isPresentation()
    {
        return in_array($this->getFileEnding(), array(
            "odp", "keynote", "ppt", "pptx"
        ));
    }

    public function isStudipQuestionnaire()
    {
        return $this['content_type'] === "application/json+studipquestionnaire";
    }

    public function addTag($tag_name) {
        $tag_hash = md5($tag_name);
        if (!MarketTag::find($tag_hash)) {
            $tag = new MarketTag();
            $tag->setId($tag_hash);
            $tag['name'] = $tag_name;
            $tag->store();
        }
        $statement = DBManager::get()->prepare("
            INSERT IGNORE INTO lehrmarktplatz_tags_material
            SET tag_hash = :tag_hash,
                material_id = :material_id
        ");
        return $statement->execute(array(
            'tag_hash' => $tag_hash,
            'material_id' => $this->getId()
        ));
    }

    public function pushDataToIndexServers()
    {
        $myHost = MarketHost::thisOne();
        $data = array();
        $data['host'] = array(
            'name' => $myHost['name'],
            'url' => $myHost['url'],
            'public_key' => $myHost['public_key']
        );
        $data['data'] = $this->toArray();
        unset($data['data']['material_id']);
        unset($data['data']['user_id']);
        $data['user'] = array(
            'user_id' => $this['user_id'],
            'name' => get_fullname($this['user_id'])
        );

        foreach (MarketHost::findBySQL("index_server = '1' AND allowed_as_index_server = '1' ") as $index_server) {
            if (!$index_server->isMe()) {
                $index_server->pushDataToIndex($data);
            }
        }
    }

    public function fetchData()
    {
        if ($this['host_id']) {
            $host = new MarketHost($this['host_id']);
            if ($host) {
                $data = $host->fetchItemData($this['foreign_foreign_material_id']);
                unset($data['material_id']);
                unset($data['user_id']);
                unset($data['mkdate']);
                $this->setData($data);
                $this->store();
                //topics:

                //user:
            }
        }
    }
}