<?php

class MarketHost extends MarketIdentity {

    static public function thisOne()
    {
        $host = self::findOneBySQL("private_key IS NOT NULL LIMIT 1");
        if ($host) {
            return $host;
        } else {
            $host = new MarketHost();
            $host['name'] = $GLOBALS['UNI_NAME_CLEAN'];
            $host['url'] = $GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP'];
            $host->store();
            return $host;
        }
    }

    static public function findAll()
    {
        return self::findBySQL("1=1");
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
        $endpoint_url = $this['url']
            .($this['url'][strlen($this['url']) - 1] !== "/" ? "/" : "")
            ."plugins.php/lehrmarktplatz/protocol_endpoints/fetch_public_host_key";
        $host_data = file_get_contents($endpoint_url);
        if ($host_data) {
            $host_data = studip_utf8decode(json_decode($host_data));
            if ($host_data) {
                $this['name'] = $host_data['name'];
                $this['public_key'] = $host_data['public_key'];
                $this['url'] = $host_data['url'];
                $host['last_updated'] = time();
                if ($this->isNew()) {
                    $host['active'] = get_config("LEHRMARKTPLATZ_ACTIVATE_NEW_HOSTS") ? 1 : 0;
                }
            }
        }
    }

    public function askKnownHosts() {
        $endpoint_url = $this['url']
            .($this['url'][strlen($this['url']) - 1] !== "/" ? "/" : "")
            ."plugins.php/lehrmarktplatz/protocol_endpoints/fetch_known_hosts"
            ."?from=".urlencode($GLOBALS['LEHRMARKTPLATZ_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']);
        $output = file_get_contents($endpoint_url);
        if ($output) {
            $output = studip_utf8decode(json_decode($output));
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
}