<?php

require_once 'app/controllers/plugin_controller.php';

class EndpointsController extends PluginController {

    public function index_action() {
        $this->reflection = new ReflectionClass($this);
    }

    /**
     * Returns the public key.
     */
    public function fetch_public_host_key_action() {
        $host = MarketHost::thisOne();
        if (Request::get("from")) {
            $this->refreshHost(studip_utf8decode(Request::get("from")));
        }
        $this->render_json(array(
            'name' => $GLOBALS['UNI_NAME_CLEAN'],
            'public_key' => $host['public_key'],
            'url' => $GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lehrmarktplatz/endpoints/",
            'index_server' => $host['index_server']
        ));
    }


    /**
     * Returns a json with all known hosts.
     * If there is a "from" GET-parameter, this host will
     * fetch the public key of the from-host and saves it to its database.
     */
    public function fetch_known_hosts_action() {
        $output = array();

        if (Request::get("from")) {
            $this->refreshHost(studip_utf8decode(Request::get("from")));
        }

        if (get_config("LEHRMARKTPLATZ_SHOW_KNOWN_HOSTS")) {
            foreach (MarketHosts::findAll() as $host) {
                if (!$host->isMe() && $host['active']) {
                    $output['hosts'][] = array(
                        'name' => $host['name'],
                        'url' => $host['url']
                    );
                }
            }
        }

        $this->render_json($output);
    }

    protected function refreshHost($url)
    {
        $host_data = file_get_contents($url."fetch_public_host_key");
        if ($host_data) {
            $host_data = studip_utf8decode(json_decode($host_data, true));
            if ($host_data) {
                $host = MarketHost::findOneByPublic_key($host_data['public_key']);
                if (!$host) {
                    $host = new MarketHost();
                }
                $host['name'] = $host_data['name'];
                $host['url'] = Request::get("from");
                $host['public_key'] = $host_data['public_key'];
                $host['last_updated'] = time();
                if ($host->isNew()) {
                    $host['active'] = get_config("LEHRMARKTPLATZ_ACTIVATE_NEW_HOSTS") ? 1 : 0;
                }
                $host->store();
            }
        }
    }

    public function search_items_action() {
        $host = MarketHost::thisOne();
        if (Request::get("text")) {
            $this->materialien = MarketMaterial::findByText(studip_utf8decode(Request::get("text")));
        } elseif (Request::get("tag")) {
            $this->materialien = MarketMaterial::findByTag(studip_utf8decode(Request::get("tag")));
        }

        $output = array('results' => array());
        foreach ($this->materialien as $material) {
            $data = array();
            $data['host'] = array(
                'name' => $host['name'],
                'url' => $host['url'],
                'public_key' => $host['public_key']
            );
            $data['data'] = $material->toArray();
            unset($data['data']['material_id']);
            $data['user'] = array(
                'user_id' => $material['user_id'],
                'name' => get_fullname($material['user_id']),
                'avatar' => ""
            );
            $data['topics'] = array();
            foreach ($material->getTopics() as $topic) {
                $data['topics'][] = $topic['name'];
            }
            $output['results'][] = $data;
        }
        $this->render_json($output);
    }

    /**
     * Returns data of a given item including where to download it and the structure, decription, etc.
     * If item is not hosted on this server, just relocate the request to the real server.
     *
     * This endpoint should be called by a remote whenever a client wants to view the details of an item.
     *
     * @param $item_id : ID of the item on this server.
     */
    public function get_item_data_action($item_id)
    {
        $material = new MarketMaterial($item_id);
        if (!$material['foreign_material_id']) {
            $topics = array();
            foreach ($material->getTopics() as $topic) {
                $topics[] = $topic['name'];
            }
            $user_description_datafield = DataField::find(get_config("LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
            if ($user_description_datafield) {
                $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($material['user_id'], $user_description_datafield->getId()));
            }

            $reviews = array();
            foreach ($material->reviews as $review) {
                if ($review['host_id']) {
                    $user = MarketUser::findOneBySQL("user_id = ?", array($review['user_id']));
                    $user = array(
                        'user_id' => $review['user_id'],
                        'name' => $user['name'],
                        'avatar' => $user['avatar'],
                        'description' => $user['description']
                    );
                } else {
                    if ($user_description_datafield) {
                        $user_description = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($review['user_id'], $user_description_datafield->getId()));
                    }
                    $user = array(
                        'user_id' => $review['user_id'],
                        'name' =>get_fullname($review['user_id']),
                        'avatar' => Avatar::getAvatar($review['user_id'])->getURL(Avatar::NORMAL),
                        'description' => $user_description['content'] ?: null
                    );
                }
                $reviews[] = array(
                    'foreign_review_id' => $review['foreign_review_id'] ?: $review->getId(),
                    'review' => $review['review'],
                    'rating' => $review['rating'],
                    'user' => $user,
                    'host' => !$review['host_id'] ? null : array(
                        'name' => $review->host['name'],
                        'url' => $review->host['url'],
                        'public_key' => $review->host['public_key']
                    ),
                    'mkdate' => $review['mkdate'],
                    'chkdate' => $review['chdate']
                );
            }
            $this->render_json(array(
                'data' => array(
                    'name' => $material['name'],
                    'short_description' => $material['short_description'],
                    'description' => $material['description'],
                    'content_type' => $material['content_type'],
                    'url' => ($GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP'])."/plugins.php/lehrmarktplatz/market/download/".$item_id,
                    'structure' => $material['structure']
                ),
                'user' => array(
                    'user_id' => $material['user_id'],
                    'name' => User::find($material['user_id'])->getFullName(),
                    'avatar' => Avatar::getAvatar($material['user_id'])->getURL(Avatar::NORMAL),
                    'description' => $datafield_entry ? $datafield_entry['content'] : null
                ),
                'topics' => $topics,
                'reviews' => $reviews
            ));
        } else {
            $host = new MarketHost($material['host_id']);
            header("Location: ".$host['url']."get_item_data/".$item_id);
            return;
        }
    }

    /**
     * Update data of an item via POST-request.
     */
    public function push_data_action()
    {
        if (Request::isPost()) {
            $public_key_hash = $_SERVER['HTTP_X_RASMUS'];
            $signature = base64_decode($_SERVER['HTTP_X_SIGNATURE']);
            $host = MarketHost::findOneBySQL("MD5(public_key) = ?", array($public_key_hash));
            if ($host && !$host->isMe()) {
                $body = file_get_contents('php://input');
                if ($host->verifySignature($body, $signature)) {
                    $data = studip_utf8decode(json_decode($body, true));
                    $material = MarketMaterial::findOneBySQL("host_id = ? AND foreign_material_id = ?", array(
                        $host->getId(),
                        $data['data']['foreign_material_id']
                    ));
                    if (!$material) {
                        $material = new MarketMaterial();
                    }
                    $material->setData($data['data']);
                    $material['host_id'] = $host->getId();

                    //update user
                    $user = MarketUser::findOneBySQL("host_id = ? AND foreign_user_id = ?", array(
                        $host->getId(),
                        $data['user']['user_id']
                    ));
                    if (!$user) {
                        $user = new MarketUser();
                        $user['host_id'] = $host->getId();
                        $user['foreign_user_id'] = $data['user']['user_id'];
                    }
                    $user['name'] = $data['user']['name'];
                    $user['avatar'] = $data['user']['avatar'];
                    $user['description'] = $data['user']['description'] ?: null;
                    $user->store();

                    $material['user_id'] = $user->getId();
                    $material->store();
                    $material->setTopics($data['topics']);
                    echo "stored ";
                } else {
                    throw new Exception("Wrong signature, sorry.");
                }
            }
            $this->render_text("");
        } else {
            throw new Exception("USE POST TO PUSH.");
        }
    }

    /**
     * Download an item from this server. The ##material_id## of the item must be given.
     * @param $material_id : material_id from this server or foreign_material_id from another server.
     */
    public function download_action($material_id)
    {
        $this->material = new MarketMaterial($material_id);
        $this->set_content_type($this->material['content_type']);
        $this->response->add_header('Content-Disposition', 'inline;filename="' . addslashes($this->material['filename']) . '"');
        $this->response->add_header('Content-Length', filesize($this->material->getFilePath()));
        $this->render_text(file_get_contents($this->material->getFilePath()));
    }

    /**
     * Adds or edits a review to the material on this server from a client of another server.
     * Use this request only as a POST request, the body must be a JSON-object that carries all the
     * necessary variables.
     * @param $material_id : ID of the item on this server.
     */
    public function add_review_action($material_id)
    {
        if (Request::isPost()) {
            $public_key_hash = $_SERVER['HTTP_X_RASMUS'];
            $signature = base64_decode($_SERVER['HTTP_X_SIGNATURE']);
            $host = MarketHost::findOneBySQL("MD5(public_key) = ?", array($public_key_hash));
            if ($host && !$host->isMe()) {
                $body = file_get_contents('php://input');
                if ($host->verifySignature($body, $signature)) {
                    $data = studip_utf8decode(json_decode($body, true));
                    $material = new MarketMaterial($material_id);
                    if ($material->isNew() || $material['host_id']) {
                        throw new Exception("Unknown material.");
                    }

                    $user = MarketUser::findOneBySQL("host_id = ? AND foreign_user_id = ?", array(
                        $host->getId(),
                        $data['user']['user_id']
                    ));
                    if (!$user) {
                        $user = new MarketUser();
                        $user['host_id'] = $host->getId();
                        $user['foreign_user_id'] = $data['user']['user_id'];
                    }
                    $user['name'] = $data['user']['name'];
                    $user['avatar'] = $data['user']['avatar'];
                    $user['description'] = $data['user']['description'] ?: null;
                    $user->store();

                    $review = LehrmarktplatzReview::findOneBySQL("foreign_review_id = ? AND host_id = ?", array(
                        $data['data']['foreign_review_id'],
                        $host->getId()
                    ));
                    if (!$review) {
                        $review = new LehrmarktplatzReview();
                        $review['user_id'] = $user->getId();
                        $review['foreign_review_id'] = $data['data']['foreign_review_id'];
                        $review['host_id'] = $host->getId();
                    }
                    $review['review'] = $data['data']['review'];
                    $review['rating'] = $data['data']['rating'];
                    $review['mkdate'] = $data['data']['mkdate'];
                    $review['chdate'] = $data['data']['chdate'];
                    $review->store();

                    echo "stored ";
                } else {
                    throw new Exception("Wrong signature, sorry.");
                }
            }
            $this->render_text("");
        } else {
            throw new Exception("USE POST TO PUSH.");
        }
    }

}