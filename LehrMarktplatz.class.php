<?php

require_once __DIR__."/lib/MarketIdentity.php";
require_once __DIR__."/lib/MarketHost.php";
require_once __DIR__."/lib/MarketUser.php";
require_once __DIR__."/lib/MarketMaterial.php";

class LehrMarktplatz extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        if ($GLOBALS['perm']->have_perm("tutor")) {
            $topicon = new Navigation(_("Lehrmarktplatz"), PluginEngine::getURL($this, array(), "market/overview"));
            $topicon->setImage(Assets::image_path("icons/lightblue/service.svg"));
            Navigation::addItem("/lehrmarktplatz", $topicon);
            Navigation::addItem("/lehrmarktplatz/overview", new Navigation(_("Lehrmarktplatz"), PluginEngine::getURL($this, array(), "market/overview")));
        }
        if ($GLOBALS['perm']->have_perm("root")) {
            $tab = new Navigation(_("Lehrmarktplatz"), PluginEngine::getURL($this, array(), "admin/hosts"));
            Navigation::addItem("/admin/config/lehrmarktplatz", $tab);
        }
        if ($GLOBALS['i_page'] === "folder.php" && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            NotificationCenter::addObserver($this, "addToSidebar", "SidebarWillRender");
        }
    }

    public function addToSidebar() {
        $links = new LinksWidget();
        $links->setTitle(_("Lehrmarktplatz"));
        $links->addLink(
            _("Lehrmaterialien herunterladen"),
            PluginEngine::getURL($this, array(), "market/overview"),
            null,
            array('data-dialog' => "1")
        );
        $links->addLink(
            _("Ordner als Lehrmaterialien bereitstellen"),
            PluginEngine::getURL($this, array(), "market/provide_folder"),
            null,
            array('data-dialog' => "1")
        );
        Sidebar::Get()->addWidget($links);
    }

    public function perform($unconsumed_path) {
        $this->addStylesheet("assets/lehrmarktplatz.less");
        parent::perform($unconsumed_path);
    }
    
}