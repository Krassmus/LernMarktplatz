<?php

require_once 'app/controllers/plugin_controller.php';

class ProtocolEndpointsController extends PluginController {

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
        $host_data = file_get_contents($url."/plugins.php/lehrmarktplatz/protocol_endpoints/fetch_public_host_key");
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
            'url' => $GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'index_server' => $host['index_server']
        ));
    }

    public function search_items_action() {

    }

}