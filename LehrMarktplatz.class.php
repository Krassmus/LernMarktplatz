<?php

require_once __DIR__."/lib/MarketIdentity.php";
require_once __DIR__."/lib/MarketHost.php";
require_once __DIR__."/lib/MarketUser.php";

class LehrMarktplatz extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        if ($GLOBALS['perm']->have_perm("autor")) {
            $topicon = new Navigation(_("Lehrmarktplatz"), PluginEngine::getURL($this, array(), "market/overview"));
            $topicon->setImage(Assets::image_path("icons/lightblue/service.svg"));
            Navigation::addItem("/lehrmarktplatz", $topicon);
            Navigation::addItem("/lehrmarktplatz/overview", new Navigation(_("Lehrmarktplatz"), PluginEngine::getURL($this, array(), "market/overview")));
        }
        if ($GLOBALS['perm']->have_perm("root")) {
            $tab = new Navigation(_("Lehrmarktplatz"), PluginEngine::getURL($this, array(), "admin/hosts"));
            Navigation::addItem("/admin/config/lehrmarktplatz", $tab);
        }
    }
    
}