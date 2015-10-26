<?php

require_once 'app/controllers/plugin_controller.php';

class EndpointsController extends PluginController {

    /**
     * Returns a json with all known hosts.
     * If there is a "from" GET-parameter, this host will
     * fetch the public key of the from-host and saves it to its database.
     */
    public function fetch_known_hosts_action() {
        $output = array();

        if (Request::get("from")) {
            $this->refreshHost(Request::get("from"));
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
            $host_data = studip_utf8decode(json_decode($host_data));
            if ($host_data) {
                $host = MarketHost::findByPublic_key($host_data['public_key']);
                if (!$host) {
                    $host = new MarketHost();
                }
                $host['name'] = $host_data['name'];
                $host['url'] = Request::get("from");
                $host['public_key'] = $host_data['public_key'];
                if ($host->isNew()) {
                    $host['active'] = get_config("LEHRMARKTPLATZ_ACTIVATE_NEW_HOSTS") ? 1 : 0;
                }
                $host->store();
            }
        }
    }

    /**
     * Returns the public key.
     */
    public function fetch_public_host_key_action() {
        $host = MarketHost::thisOne();
        $this->render_json(array(
            'name' => $GLOBALS['UNI_NAME_CLEAN'],
            'public_key' => $host['public_key'],
            'url' => $GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lehrmarktplatz/endpoints/",
            'index_server' => $host['index_server']
        ));
    }

    public function search_items_action() {
        $host = MarketHost::thisOne();
        if (Request::get("search")) {
            $this->materialien = MarketMaterial::findByTag(Request::get("search"));
        }

        $output = array('results' => array());
        foreach ($this->materialien as $material) {
            $data['data'] = $material->toArray();
            $data['user'] = array(
                'user_id' => $material['user_id'],
                'name' => get_fullname($material['user_id'])
            );
            $output['results'][] = $data;
        }
        $this->render_json($output);
    }

    /**
     * Returns data of a given item including where to download it and the structure, decription, etc.
     * If item is not hosted on this server, just relocate the request to the real server.
     * @param $item_id
     */
    public function get_item_data_action($item_id)
    {
        $material = new MarketMaterial($item_id);
        if (!$material['foreign_material_id']) {
            $this->render_json(array(
                'name' => $material['name'],
                'short_description' => $material['short_description'],
                'content_type' => $material['content_type'],
                'url' => ($GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP'])."/plugins.php/lehrmarktplatz/market/download/".$item_id,
                'user' => array(
                    'user_id' => $material['user_id'],
                    'name' => User::find($material['user_id'])->getFullName(),
                    'avatar' => Avatar::getAvatar($material['user_id'])->getURL(Avatar::NORMAL)
                ),
                'structure' => $material['structure']
            ));
        } else {
            $host = new MarketHost($material['host_id']);
            header("Location: ".$host['url']."get_item_data/".$item_id);
            return;
        }
    }

}