<?php

class MarketHost extends MarketIdentity {

    static public function thisOne()
    {
        $host = self::findOneBySQL("private_key IS NOT NULL LIMIT 1");
        if ($host) {
            $host['url'] = $GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lehrmarktplatz/endpoints/";
            if ($host->isFieldDirty("url")) {
                $host->store();
            }
            return $host;
        } else {
            $host = new MarketHost();
            $host['name'] = $GLOBALS['UNI_NAME_CLEAN'];
            $host['url'] = $GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lehrmarktplatz/endpoints/";
            $host['last_updated'] = time();
            $host->store();
            return $host;
        }
    }

    static public function findAll()
    {
        return self::findBySQL("1=1 ORDER BY name ASC");
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_hosts';
        parent::configure($config);
    }

    public function isMe()
    {
        return (bool) $this['private_key'];
    }

    public function fetchPublicKey()
    {
        $endpoint_url = $this['url']."fetch_public_host_key";
        if (true) {
            $endpoint_url .= "?from=".urlencode(studip_utf8encode($GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lehrmarktplatz/endpoints/"));
        }
        $host_data = @file_get_contents($endpoint_url);
        if ($host_data) {
            $host_data = studip_utf8decode(json_decode($host_data, true));
            if ($host_data) {
                $this['name'] = $host_data['name'];
                $this['public_key'] = $host_data['public_key'];
                $this['url'] = $host_data['url'];
                $this['index_server'] = $host_data['index_server'];
                $host['last_updated'] = time();
                if ($this->isNew()) {
                    $host['active'] = get_config("LEHRMARKTPLATZ_ACTIVATE_NEW_HOSTS") ? 1 : 0;
                }
            }
        }
    }

    public function askKnownHosts() {
        $endpoint_url = $this['url']."fetch_known_hosts"
            ."?from=".urlencode(studip_utf8encode($GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lehrmarktplatz/endpoints/"));
        $output = @file_get_contents($endpoint_url);
        if ($output) {
            $output = studip_utf8decode(json_decode($output, true));
            foreach ((array) $output['hosts'] as $host_data) {
                $host = MarketHost::findByPublic_key($host_data['public_key']);
                if (!$host) {
                    $host = new MarketHost();
                    $host['public_key'] = $host_data['public_key'];
                    $host['url'] = $host_data['url'];
                }
                if ($host['last_updated'] < time() - 60 * 60 * 8) {
                    $host->fetchPublicKey();
                }
                $host->store();
            }
        }
    }

    public function fetchRemoteSearch($text, $tag = false) {
        $endpoint_url = $this['url']."search_items";
        if ($tag) {
            $endpoint_url .= "?tag=".urlencode(studip_utf8encode($text));
        } else {
            $endpoint_url .= "?text=".urlencode(studip_utf8encode($text));
        }
        $output = @file_get_contents($endpoint_url);
        if ($output) {
            $output = studip_utf8decode(json_decode($output, true));
            foreach ((array) $output['results'] as $material_data) {
                $host = MarketHost::findOneBySQL("public_key = ?", array($material_data['host']['public_key']));
                if (!$host) {
                    $host = new MarketHost();
                    $host['url'] = $material_data['host']['url'];
                    $host->fetchPublicKey();
                    $host->store();
                }
                if (!$host->isMe()) {
                    $material_data['data']['foreign_material_id'] = $material_data['data']['id'];
                    $material = MarketMaterial::findOneBySQL("foreign_material_id = ? AND host_id = ?", array(
                        $material_data['foreign_material_id'],
                        $host->getId()
                    ));
                    if (!$material) {
                        $material = new MarketMaterial();
                        $material['host_id'] = $host->getId();
                    }
                    unset($material_data['data']['id']);
                    $material->setData($material_data['data']);
                    $material->store();

                    //set user:
                }
            }
        }
    }

    public function pushDataToIndex($data) {
        $data = studip_utf8encode($data);
        $payload = json_encode($data);

        $myHost = MarketHost::thisOne();
        $endpoint_url = $this['url']."push_data";

        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $this->getUrl($endpoint_url));
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_VERBOSE, 0);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);

        $header = array(
            "X-HOST_PUBLIC_KEY_HASH" => md5($myHost['public_key']),
            "X-SIGNATURE: ".$myHost->createSignature($payload)
        );
        curl_setopt($request, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($request);
        $response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        return $response_code < 300;
    }
}