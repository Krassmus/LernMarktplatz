<?php

class LernmarktplatzMaterial extends SimpleORMap {

    static public function findAll()
    {
        return self::findBySQL("1=1");
    }

    static public function findByTag($tag_name)
    {
        self::fetchRemoteSearch($tag_name, true);
        $statement = DBManager::get()->prepare("
            SELECT lernmarktplatz_material.*
            FROM lernmarktplatz_material
                INNER JOIN lernmarktplatz_tags_material USING (material_id)
                INNER JOIN lernmarktplatz_tags USING (tag_hash)
            WHERE lernmarktplatz_tags.name = :tag
            GROUP BY lernmarktplatz_material.material_id
        ");
        $statement->execute(array('tag' => $tag_name));
        $material_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $materials = array();
        foreach ($material_data as $data) {
            $materials[] = LernmarktplatzMaterial::buildExisting($data);
        }
        return $materials;
    }

    static public function findByText($text)
    {
        self::fetchRemoteSearch($text);
        $statement = DBManager::get()->prepare("
            SELECT lernmarktplatz_material.*
            FROM lernmarktplatz_material
                LEFT JOIN lernmarktplatz_tags_material USING (material_id)
                LEFT JOIN lernmarktplatz_tags USING (tag_hash)
            WHERE lernmarktplatz_material.name LIKE :text
                OR description LIKE :text
                OR short_description LIKE :text
                OR lernmarktplatz_tags.name LIKE :text
            GROUP BY lermarktplatz_material.material_id
        ");
        $statement->execute(array(
            'text' => "%".$text."%"
        ));
        $material_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $materials = array();
        foreach ($material_data as $data) {
            $materials[] = LernmarktplatzMaterial::buildExisting($data);
        }
        return $materials;
    }

    static public function findByTagHash($tag_hash)
    {
        $tag = LernmarktplatzTag::find($tag_hash);
        if ($tag) {
            self::fetchRemoteSearch($tag['name'], true);
        }
        return self::findBySQL("INNER JOIN lernmarktplatz_tags_material USING (material_id) WHERE lernmarktplatz_tags_material.tag_hash = ?", array($tag_hash));
    }

    static public function getFileDataPath() {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/lehrmarktplatz";
    }

    static public function getImageFileDataPath() {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/lehrmarktplatz_images";
    }

    /**
     * Searches on remote hosts for the text.
     * @param $text
     * @param bool|false $tag
     */
    static protected function fetchRemoteSearch($text, $tag = false) {
        $cache_name = "Lernmarktplatz_remote_searched_for_".md5($text)."_".($tag ? 1 : 0);
        $already_searched = (bool) StudipCacheFactory::getCache()->read($cache_name);
        if (!$already_searched) {
            $host = LernmarktplatzHost::findOneBySQL("index_server = '1' AND allowed_as_index_server = '1' ORDER BY RAND()");
            if ($host && !$host->isMe()) {
                $host->fetchRemoteSearch($text, $tag);
            }
            StudipCacheFactory::getCache()->read($cache_name, "1", 60);
        }
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_material';
        $config['belongs_to']['host'] = array(
            'class_name' => 'LernmarktplatzHost',
            'foreign_key' => 'host_id'
        );
        $config['has_many']['reviews'] = array(
            'class_name' => 'LernmarktplatzReview',
            'order_by' => 'ORDER BY mkdate DESC',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
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

    public function delete()
    {
        $success = parent::delete();
        if ($success) {
            $this->setTopics(array());
            @unlink($this->getFilePath());
        }
        return $success;
    }

    public function getTopics()
    {
        $statement = DBManager::get()->prepare("
            SELECT lernmarktplatz_tags.*
            FROM lernmarktplatz_tags
                INNER JOIN lernmarktplatz_tags_material ON (lernmarktplatz_tags_material.tag_hash = lernmarktplatz_tags.tag_hash)
            WHERE lernmarktplatz_tags_material.material_id = :material_id
            ORDER BY lernmarktplatz_tags.name ASC
        ");
        $statement->execute(array('material_id' => $this->getId()));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setTopics($tags) {
        $statement = DBManager::get()->prepare("
            DELETE FROM lernmarktplatz_tags_material
            WHERE material_id = :material_id
        ");
        $statement->execute(array('material_id' => $this->getId()));
        $insert_tag = DBManager::get()->prepare("
            INSERT IGNORE INTO lernmarktplatz_tags
            SET name = :tag,
                tag_hash = MD5(:tag)
        ");
        $add_tag = DBManager::get()->prepare("
            INSERT IGNORE INTO lernmarktplatz_tags_material
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

    public function getFilePath()
    {
        if (!file_exists(self::getFileDataPath())) {
            mkdir(self::getFileDataPath());
        }
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getFileDataPath()."/".$this->getId();
    }

    public function getFrontImageFilePath()
    {
        if (!file_exists(self::getImageFileDataPath())) {
            mkdir(self::getImageFileDataPath());
        }
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getImageFileDataPath()."/".$this->getId();
    }

    public function getLogoURL($color = "blue")
    {
        if ($this['front_image_content_type']) {
            if ($this['host_id']) {
                return $this->host['url']."download_front_image/".$this['foreign_material_id'];
            } else {
                return URLHelper::getURL("plugins.php/lehrmarktplatz/endpoints/download_front_image/".$this->getId());
            }
        } elseif ($this->isFolder()) {
            return Assets::image_path("icons/$color/folder-full.svg");
        } elseif($this->isImage()) {
            return Assets::image_path("icons/$color/file-pic.svg");
        } elseif($this->isPDF()) {
            return Assets::image_path("icons/$color/file-pdf.svg");
        } elseif($this->isPresentation()) {
            return Assets::image_path("icons/$color/file-ppt.svg");
        } elseif($this->isStudipQuestionnaire()) {
            return Assets::image_path("icons/$color/vote.svg");
        } else {
            return Assets::image_path("icons/$color/file.svg");
        }

    }

    public function isFolder()
    {
        return (bool) $this['structure'];
    }

    public function isImage()
    {
        return stripos($this['content_type'], "image") === 0;
    }

    public function isPDF()
    {
        return $this['content_type'] === "application/pdf";
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
        if (!LernmarktplatzTag::find($tag_hash)) {
            $tag = new LernmarktplatzTag();
            $tag->setId($tag_hash);
            $tag['name'] = $tag_name;
            $tag->store();
        }
        $statement = DBManager::get()->prepare("
            INSERT IGNORE INTO lernmarktplatz_tags_material
            SET tag_hash = :tag_hash,
                material_id = :material_id
        ");
        return $statement->execute(array(
            'tag_hash' => $tag_hash,
            'material_id' => $this->getId()
        ));
    }

    public function pushDataToIndexServers($delete = false)
    {
        $myHost = LernmarktplatzHost::thisOne();
        $data = array();
        $data['host'] = array(
            'name' => $myHost['name'],
            'url' => $myHost['url'],
            'public_key' => $myHost['public_key']
        );
        $data['data'] = $this->toArray();
        $data['data']['foreign_material_id'] = $data['data']['material_id'];
        unset($data['data']['material_id']);
        unset($data['data']['id']);
        unset($data['data']['user_id']);
        unset($data['data']['host_id']);
        $user_description_datafield = DataField::find(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
        if ($user_description_datafield) {
            $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($this['user_id'], $user_description_datafield->getId()));
        }
        $data['user'] = array(
            'user_id' => $this['user_id'],
            'name' => get_fullname($this['user_id']),
            'avatar' => Avatar::getAvatar($this['user_id'])->getURL(Avatar::NORMAL),
            'description' => $datafield_entry ? $datafield_entry['content'] : null
        );
        $data['topics'] = array();
        foreach ($this->getTopics() as $tag) {
            if ($tag['name']) {
                $data['topics'][] = $tag['name'];
            }
        }
        if ($delete) {
            $data['delete_material'] = 1;
        }

        foreach (LernmarktplatzHost::findBySQL("index_server = '1' AND allowed_as_index_server = '1' ") as $index_server) {
            if (!$index_server->isMe()) {
                $index_server->pushDataToEndpoint("push_data", $data);
            }
        }
    }

    public function fetchData()
    {
        if ($this['host_id']) {
            $host = new LernmarktplatzHost($this['host_id']);
            if ($host) {
                $data = $host->fetchItemData($this['foreign_material_id']);

                if (!$data) {
                    return false;
                }

                if ($data['deleted']) {
                    return "deleted";
                }

                //user:
                $user = LernmarktplatzUser::findOneBySQL("foreign_user_id", array($data['user']['user_id'], $host->getId()));
                if (!$user) {
                    $user = new LernmarktplatzUser();
                    $user['foreign_user_id'] = $data['user']['user_id'];
                    $user['host_id'] = $host->getId();
                }
                $user['name'] = $data['user']['name'];
                $user['avatar'] = $data['user']['avatar'] ?: null;
                $user['description'] = $data['user']['description'] ?: null;
                $user->store();

                //material:
                $material_data = $data['data'];
                unset($material_data['material_id']);
                unset($material_data['user_id']);
                unset($material_data['mkdate']);
                $this->setData($material_data);
                $this->store();

                //topics:
                $this->setTopics($data['topics']);

                foreach ((array) $data['reviews'] as $review_data) {
                    $currenthost = LernmarktplatzHost::findOneByUrl(trim($review_data['host']['url']));
                    if (!$currenthost) {
                        $currenthost = new LernmarktplatzHost();
                        $currenthost['url'] = trim($review_data['host']['url']);
                        $currenthost['last_updated'] = time();
                        $currenthost->fetchPublicKey();
                        if ($currenthost['public_key']) {
                            $currenthost->store();
                        }
                    }
                    if ($currenthost && $currenthost['public_key'] && !$currenthost->isMe()) {
                        $review = LernmarktplatzReview::findOneBySQL("foreign_review_id = ? AND host_id = ?", array(
                            $review_data['foreign_review_id'],
                            $currenthost->getId()
                        ));
                        if (!$review) {
                            $review = new LernmarktplatzReview();
                            $review['foreign_review_id'] = $review_data['foreign_review_id'];
                            $review['material_id'] = $this->getId();
                            $review['host_id'] = $currenthost->getId();
                        }
                        $review['review'] = $review_data['review'];
                        $review['rating'] = $review_data['rating'];
                        if ($review_data['chdate']) {
                            $review['chdate'] = $review_data['chdate'];
                        }
                        if ($review_data['mkdate']) {
                            $review['mkdate'] = $review_data['mkdate'];
                        }

                        $user = LernmarktplatzUser::findOneBySQL("foreign_user_id", array($review_data['user']['user_id'], $currenthost->getId()));
                        if (!$user) {
                            $user = new LernmarktplatzUser();
                            $user['foreign_user_id'] = $review_data['user']['user_id'];
                            $user['host_id'] = $currenthost->getId();
                        }
                        $user['name'] = $review_data['user']['name'];
                        $user['avatar'] = $review_data['user']['avatar'] ?: null;
                        $user['description'] = $review_data['user']['description'] ?: null;
                        $user->store();

                        $review['user_id'] = $user->getId();
                        $review->store();
                    }
                }
            }
        }
        return true;
    }

    public function calculateRating() {
        $rating = 0;
        $factors = 0;
        foreach ($this->reviews as $review) {
            $age = time() - $review['chdate'];
            $factor = (pi() - 2 * atan($age / (86400 * 180))) / pi();
            $rating += $review['rating'] * $factor * 2;
            $factors += $factor;
        }
        if ($factors > 0) {
            $rating /= $factors;
        } else {
            return $rating = null;
        }
        return $rating;
    }
}