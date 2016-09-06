<?php

require_once __DIR__."/lib/LernmarktplatzIdentity.php";
require_once __DIR__."/lib/LernmarktplatzHost.php";
require_once __DIR__."/lib/LernmarktplatzUser.php";
require_once __DIR__."/lib/LernmarktplatzMaterial.php";
require_once __DIR__."/lib/LernmarktplatzTag.php";
require_once __DIR__."/lib/LernmarktplatzReview.php";
require_once __DIR__."/lib/LernmarktplatzComment.php";

$GLOBALS['LERNMARKTPLATZ_HEADER_PUBLIC_KEY_HASH'] = "X-RASMUS";    //MD5-hash of the armored public key of the server
$GLOBALS['LERNMARKTPLATZ_HEADER_SIGNATURE']       = "X-SIGNATURE"; //the base64 encoded signature provided by the public key over the body of the message

class LernMarktplatz extends StudIPPlugin implements SystemPlugin, ScorePlugin {

    public function __construct() {
        parent::__construct();
        if ($GLOBALS['perm']->have_perm("autor")) {
            $topicon = new Navigation(_("Lernmaterialien"), PluginEngine::getURL($this, array(), "market/overview"));
            $topicon->setImage(Assets::image_path("icons/lightblue/service.svg"));
            Navigation::addItem("/lernmarktplatz", $topicon);
            Navigation::addItem("/lernmarktplatz/overview", new Navigation(_("Lernmarktplatz"), PluginEngine::getURL($this, array(), "market/overview")));
            Navigation::addItem("/lernmarktplatz/mymaterial", new Navigation(_("Meine Materialien"), PluginEngine::getURL($this, array(), "mymaterial/overview")));
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
            $comments = LernmarktplatzComment::findBySQL("review_id = :review_id AND mkdate >= :last_update ORDER BY mkdate ASC", array(
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
            UpdateInformation::setInformation("Lernmarktplatz.update", $output);
        }
    }

    function getPluginActivityTables() {
        return array(
            array(
                'table' => "lernmarktplatz_material",
                'user_id_column' => "user_id",
                'date_column' => "mkdate",
                'where' => "host_id IS NULL"
            ),
            array(
                'table' => "lernmarktplatz_reviews",
                'user_id_column' => "user_id",
                'date_column' => "mkdate",
                'where' => "host_id IS NULL"
            ),
            array(
                'table' => "lernmarktplatz_comments",
                'user_id_column' => "user_id",
                'date_column' => "mkdate",
                'where' => "host_id IS NULL"
            )
        );
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