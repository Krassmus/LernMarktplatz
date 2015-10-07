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
            NotificationCenter::addObserver($this, "addToFolderSidebar", "SidebarWillRender");
        }
    }

    public function addToFolderSidebar() {
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


    public function get_file_icon($ext) {
        $extension = strtolower($ext);
        //Icon auswaehlen
        switch ($extension){
            case 'rtf':
            case 'doc':
            case 'docx':
            case 'odt':
                $icon = 'icons/20/black/file-text.png';
                break;
            case 'xls':
            case 'xlsx':
            case 'ods':
            case 'csv':
            case 'ppt':
            case 'pptx':
            case 'odp':
                $icon = 'icons/20/black/file-office.png';
                break;
            case 'zip':
            case 'tgz':
            case 'gz':
            case 'bz2':
                $icon = 'icons/20/black/file-archive.png';
                break;
            case 'pdf':
                $icon = 'icons/20/black/file-pdf.png';
                break;
            case 'gif':
            case 'jpg':
            case 'jpe':
            case 'jpeg':
            case 'png':
            case 'bmp':
                $icon = 'icons/20/black/file-pic.png';
                break;
            default:
                $icon = 'icons/20/black/file-generic.png';
                break;
        }
        return $icon;
    }
    
}