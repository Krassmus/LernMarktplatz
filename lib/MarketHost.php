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
        $host_data = file_get_contents($this['url']."/plugins.php/lehrmarktplatz/protocol_endpoints/fetch_public_host_key");
        if ($host_data) {
            $host_data = studip_utf8decode(json_decode($host_data));
            if ($host_data) {

                $this['name'] = $host_data['name'];
                $this['public_key'] = $host_data['public_key'];
                $this['url'] = $host_data['url'];
                if ($this->isNew()) {
                    $host['active'] = get_config("LEHRMARKTPLATZ_ACTIVATE_NEW_HOSTS") ? 1 : 0;
                }
            }
        }
    }

    public function askKnownHosts() {
        $host_data = file_get_contents($this['url']."/plugins.php/lehrmarktplatz/protocol_endpoints/fetch_known_hosts?from=".urlencode($GLOBALS['ABSOLUTE_URI_STUDIP']));
        if ($host_data) {
            $host_data = studip_utf8decode(json_decode($host_data));
        }
    }
}