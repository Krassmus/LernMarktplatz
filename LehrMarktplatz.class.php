<?php

require_once __DIR__."/lib/MarketIdentity.php";
require_once __DIR__."/lib/MarketHost.php";
require_once __DIR__."/lib/MarketUser.php";
require_once __DIR__."/lib/MarketMaterial.php";
require_once __DIR__."/lib/MarketTag.php";
require_once __DIR__."/lib/LehrmarktplatzReview.php";
require_once __DIR__."/lib/LehrmarktplatzComment.php";

$GLOBALS['LEHRMARKTPLATZ_HEADER_PUBLIC_KEY_HASH'] = "X-RASMUS";    //MD5-hash of the armored public key of the server
$GLOBALS['LEHRMARKTPLATZ_HEADER_SIGNATURE']       = "X-SIGNATURE"; //the base64 encoded signature provided by the public key over the body of the message

class LehrMarktplatz extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        if ($GLOBALS['perm']->have_perm("autor")) {
            $topicon = new Navigation(_("Lernmaterialien"), PluginEngine::getURL($this, array(), "market/overview"));
            $topicon->setImage(Assets::image_path("icons/lightblue/service.svg"));
            Navigation::addItem("/lernmarktplatz", $topicon);
            Navigation::addItem("/lernmarktplatz/overview", new Navigation(_("Lernmarktplatz"), PluginEngine::getURL($this, array(), "market/overview")));
        }
        if ($GLOBALS['perm']->have_perm("root")) {
            $tab = new Navigation(_("Lernmarktplatz"), PluginEngine::getURL($this, array(), "admin/hosts"));
            Navigation::addItem("/admin/config/lernmarktplatz", $tab);
        }
        if ($GLOBALS['i_page'] === "folder.php" && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            NotificationCenter::addObserver($this, "addToFolderSidebar", "SidebarWillRender");
        }
        if (UpdateInformation::isCollecting()
                && stripos(Request::get("page"), "plugins.php/lehrmarktplatz/market/discussion/") !== false) {
            $data = Request::getArray("page_info");
            $last_update = Request::get("server_timestamp", time() - 30);
            $review_id = $data['Lehrmarktplatz']['review_id'];
            $output = array('comments' => array());
            $comments = LehrmarktplatzComment::findBySQL("review_id = :review_id AND mkdate >= :last_update ORDER BY mkdate ASC", array(
                'last_update' => $last_update,
                'review_id' => $review_id
            ));
            $tf = new Flexi_TemplateFactory(__DIR__ . "/views");
            foreach ($comments as $comment) {
                $template = $tf->open("market/_comment.php");
                $template->set_attribute('comment', $comment);
                $output['comments'][] = array(
                    'comment_id' => $comment->getId(),
                    'html' => $template->render()
                );
            }
            UpdateInformation::setInformation("Lehrmarktplatz.update", $output);
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
        $this->addStylesheet("assets/lernmarktplatz.less");
        PageLayout::addScript($this->getPluginURL()."/assets/lernmarktplatz.js");
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