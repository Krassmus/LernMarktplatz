<?php

require_once __DIR__."/lib/LernmarktplatzIdentity.php";
require_once __DIR__."/lib/LernmarktplatzHost.php";
require_once __DIR__."/lib/LernmarktplatzUser.php";
require_once __DIR__."/lib/LernmarktplatzMaterial.php";
require_once __DIR__."/lib/LernmarktplatzTag.php";
require_once __DIR__."/lib/LernmarktplatzReview.php";
require_once __DIR__."/lib/LernmarktplatzComment.php";
require_once __DIR__."/lib/LernmarktplatzLog.php";

//These two HTTP-headers are non-conformant custom HTTP-headers for requests
$GLOBALS['LERNMARKTPLATZ_HEADER_PUBLIC_KEY_HASH'] = "Publickey-Hash";    //MD5-hash of the armored public key of the server
$GLOBALS['LERNMARKTPLATZ_HEADER_SIGNATURE']       = "RSA-Signature-Base64"; //the base64 encoded signature provided by the public key over the body of the message

class LernMarktplatz extends StudIPPlugin implements SystemPlugin, HomepagePlugin {

    public function __construct() {
        parent::__construct();
        if ($GLOBALS['perm']->have_perm("autor")) {
            $topicon = new Navigation(_("Lernmaterialien"), PluginEngine::getURL($this, array(), "market/overview"));
            $topicon->setImage(Icon::create('service', 'navigation'));
            Navigation::addItem("/lernmarktplatz", $topicon);
            Navigation::addItem("/lernmarktplatz/overview", new Navigation(_("Lernmarktplatz"), PluginEngine::getURL($this, array(), "market/overview")));
            Navigation::addItem("/lernmarktplatz/mymaterial", new Navigation(_("Meine Materialien"), PluginEngine::getURL($this, array(), "mymaterial/overview")));
        }
        if ($GLOBALS['perm']->have_perm("root")) {
            $tab = new Navigation(_("Lernmarktplatz"), PluginEngine::getURL($this, array(), "admin/hosts"));
            Navigation::addItem("/admin/config/lernmarktplatz", $tab);
        }
        if (UpdateInformation::isCollecting()
                && stripos(Request::get("page"), "plugins.php/lernmarktplatz/market/discussion/") !== false) {
            $data = Request::getArray("page_info");
            $last_update = Request::get("server_timestamp", time() - 30);
            $review_id = $data['Lernmarktplatz']['review_id'];
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
                $icon = 'file-text';
                break;
            case 'xls':
            case 'xlsx':
            case 'ods':
            case 'csv':
            case 'ppt':
            case 'pptx':
            case 'odp':
                $icon = 'file-office';
                break;
            case 'zip':
            case 'tgz':
            case 'gz':
            case 'bz2':
                $icon = 'file-archive';
                break;
            case 'pdf':
                $icon = 'file-pdf';
                break;
            case 'gif':
            case 'jpg':
            case 'jpe':
            case 'jpeg':
            case 'png':
            case 'bmp':
                $icon = 'file-pic';
                break;
            default:
                $icon = 'file-generic';
                break;
        }
        return Icon::create($icon, "info");
    }

    public function getHomepageTemplate($user_id) {
        $materialien = LernmarktplatzMaterial::findMine($user_id);
        if (count($materialien)) {
            $template_factory = new Flexi_TemplateFactory(__DIR__."/views");
            $template = $template_factory->open("mymaterial/_material_list");
            $template->set_attribute("plugin", $this);
            $template->set_attribute("materialien", $materialien);
            $template->set_attribute("title", _("Lernmaterialien"));
            $template->set_attribute("icon_url", Icon::create("service", "clickable")->asImagePath());
            return $template;
        } else {
            return null;
        }
    }
    
}
