<?php

class LernmarktplatzHost extends LernmarktplatzIdentity {

    static public function thisOne()
    {
        $host = self::findOneBySQL("private_key IS NOT NULL LIMIT 1");
        if ($host) {
            $host['url'] = $GLOBALS['LERNMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lernmarktplatz/endpoints/";
            if ($host->isFieldDirty("url")) {
                $host->store();
            }
            return $host;
        } else {
            $host = new LernmarktplatzHost();
            $host['name'] = $GLOBALS['UNI_NAME_CLEAN'];
            $host['url'] = $GLOBALS['LERNMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lernmarktplatz/endpoints/";
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
        $config['db_table'] = 'lernmarktplatz_hosts';
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
            $endpoint_url .= "?from=".urlencode(studip_utf8encode($GLOBALS['LERNMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lernmarktplatz/endpoints/"));
        }
        $host_data = @file_get_contents($endpoint_url);
        if ($host_data) {
            $host_data = studip_utf8decode(json_decode($host_data, true));
            if ($host_data) {
                $this['name'] = $host_data['name'];
                $this['public_key'] = preg_replace("/\r/", "", $host_data['public_key']);
                $this['url'] = $host_data['url'];
                $this['index_server'] = $host_data['index_server'];
                $host['last_updated'] = time();
                if ($this->isNew()) {
                    $host['active'] = get_config("LERNMARKTPLATZ_ACTIVATE_NEW_HOSTS") ? 1 : 0;
                }
            }
        }
    }

    public function askKnownHosts() {
        $endpoint_url = $this['url']."fetch_known_hosts"
            ."?from=".urlencode(studip_utf8encode($GLOBALS['LERNMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/lernmarktplatz/endpoints/"));
        $output = @file_get_contents($endpoint_url);
        if ($output) {
            $output = studip_utf8decode(json_decode($output, true));
            foreach ((array) $output['hosts'] as $host_data) {
                $host = LernmarktplatzHost::findByPublic_key($host_data['public_key']);
                if (!$host) {
                    $host = new LernmarktplatzHost();
                    $host['public_key'] = preg_replace("/\r/", "", $host_data['public_key']);
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
                $host = LernmarktplatzHost::findOneBySQL("public_key = ?", array($material_data['host']['public_key']));
                if (!$host) {
                    $host = new LernmarktplatzHost();
                    $host['url'] = $material_data['host']['url'];
                    $host->fetchPublicKey();
                    $host->store();
                }
                if (!$host->isMe()) {
                    //set user:
                    $user = LernmarktplatzUser::findOneBySQL("foreign_user_id", array($material_data['user']['user_id'], $host->getId()));
                    if (!$user) {
                        $user = new LernmarktplatzUser();
                        $user['foreign_user_id'] = $material_data['user']['user_id'];
                        $user['host_id'] = $host->getId();
                    }
                    $user['name'] = $material_data['user']['name'];
                    $user['avatar'] = $material_data['user']['avatar'] ?: null;
                    $user->store();

                    //set material:
                    $material_data['data']['foreign_material_id'] = $material_data['data']['id'];
                    $material = LernmarktplatzMaterial::findOneBySQL("foreign_material_id = ? AND host_id = ?", array(
                        $material_data['data']['foreign_material_id'],
                        $host->getId()
                    ));
                    if (!$material) {
                        $material = new LernmarktplatzMaterial();
                    }
                    unset($material_data['data']['id']);
                    $material->setData($material_data['data']);
                    $material['host_id'] = $host->getId();
                    $material['user_id'] = $user->getId();
                    $material->store();

                    //set topics:
                    $material->setTopics($material_data['topics']);
                }
            }
        }
    }


    public function pushDataToEndpoint($endpoint, $data, $curl_multi_request = false) {
        $data = studip_utf8encode($data);
        $payload = json_encode($data);

        $myHost = LernmarktplatzHost::thisOne();
        $endpoint_url = $this['url'].$endpoint;

        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $endpoint_url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_VERBOSE, 0);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);

        $header = array(
            $GLOBALS['LERNMARKTPLATZ_HEADER_SIGNATURE'].": ".base64_encode($myHost->createSignature($payload)),
            $GLOBALS['LERNMARKTPLATZ_HEADER_PUBLIC_KEY_HASH'].": ".md5($myHost['public_key'])
        );
        curl_setopt($request, CURLOPT_HTTPHEADER, $header);


        if ($curl_multi_request) {
            return $request;
        } else {
            $result = curl_exec($request);
            $response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);
            return $response_code < 300;
        }
    }

    public function fetchItemData($foreign_material_id)
    {
        $endpoint_url = $this['url']."get_item_data/".urlencode(studip_utf8encode($foreign_material_id));
        $output = @file_get_contents($endpoint_url);
        if ($output) {
            $output = studip_utf8decode(json_decode($output, true));
            if ($output) {
                return $output;
            }
        }
    }
}