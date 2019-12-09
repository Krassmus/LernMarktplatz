<?php

require_once __DIR__."/lib/LernmarktplatzIdentity.php";
require_once __DIR__."/lib/LernmarktplatzHost.php";
require_once __DIR__."/lib/LernmarktplatzMaterialUser.php";
require_once __DIR__."/lib/LernmarktplatzUser.php";
require_once __DIR__."/lib/LernmarktplatzMaterial.php";
require_once __DIR__."/lib/LernmarktplatzTag.php";
require_once __DIR__."/lib/LernmarktplatzReview.php";
require_once __DIR__."/lib/LernmarktplatzComment.php";
require_once __DIR__."/lib/LernmarktplatzLog.php";
require_once __DIR__."/lib/LernmarktplatzDownloadcounter.php";
require_once __DIR__."/lib/SQLQuery.php";

//These two HTTP-headers are non-conformant custom HTTP-headers for requests
$GLOBALS['LERNMARKTPLATZ_HEADER_PUBLIC_KEY_HASH'] = "Publickey-Hash";    //MD5-hash of the armored public key of the server
$GLOBALS['LERNMARKTPLATZ_HEADER_SIGNATURE']       = "RSA-Signature-Base64"; //the base64 encoded signature provided by the public key over the body of the message

class LernMarktplatz extends StudIPPlugin implements SystemPlugin, HomepagePlugin, FilesystemPlugin
{

    public function __construct() {
        parent::__construct();
        if ($GLOBALS['perm']->have_perm(Config::get()->LERNMARKTPLATZ_PUBLIC_STATUS)) {
            $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
                ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
                : "";
            if (Navigation::hasItem($main_navigation)) {
                $topicon = new Navigation(Config::get()->LERNMARKTPLATZ_TITLE, PluginEngine::getURL($this, array(), "market/overview"));
                if (!$main_navigation) {
                    $topicon->setImage(Icon::create('service', 'navigation'));
                }
                Navigation::addItem($main_navigation . "/lernmarktplatz", $topicon);
                Navigation::addItem($main_navigation . "/lernmarktplatz/overview", new Navigation(Config::get()->LERNMARKTPLATZ_TITLE, PluginEngine::getURL($this, array(), "market/overview")));
                if ($GLOBALS['perm']->have_perm("autor")) {
                    Navigation::addItem($main_navigation . "/lernmarktplatz/mymaterial", new Navigation(_("Meine Materialien"), PluginEngine::getURL($this, array(), "mymaterial/overview")));
                }
            }
        }
        if ($GLOBALS['perm']->have_perm("root")) {
            $tab = new Navigation(Config::get()->LERNMARKTPLATZ_TITLE, PluginEngine::getURL($this, array(), "admin/hosts"));
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
        $this->addStylesheet("assets/lernmarktplatz.less");
        PageLayout::addScript($this->getPluginURL()."/assets/lernmarktplatz.js");
        StudipFormat::addStudipMarkup('oerembedder','\[oermaterial\](.*?)(?=\s|$)', null, 'LernMarktplatz::embedOERMaterial', 'links');
    }

    static public function embedOERMaterial($markup, $matches, $contents)
    {
        $id = $matches[1];
        $material = LernmarktplatzMaterial::find($id);
        $url = $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : URLHelper::getURL("plugins.php/lernmarktplatz/market/download/".$material->getId());

        $actions = ActionMenu::get();
        if ($material['filename']) {
            $actions->addLink(
                $url,
                sprintf(_("Download %s"), $material['name']),
                Icon::create("download", "clickable"),
                []
            );
        }
        $actions->addLink(
            URLHelper::getURL("plugins.php/lernmarktplatz/market/details/".$id),
            _("Quelle anzeigen"),
            Icon::create("service", "clickable"),
            []
        );
        $right_link = '<div style="text-align: right;">';
        $right_link .= '<a href="'.URLHelper::getLink("plugins.php/lernmarktplatz/market/details/".$id).'">'.Icon::create("service", "clickable")->asImg(16, array('class' => "text-bottom")).htmlReady($material['name']).'</a>';


        if ($material['player_url'] || $material->isPDF()) {
            if ($material['player_url']) {
                LernmarktplatzDownloadcounter::addCounter($material->id);
                $url = $material['player_url'];
            }
            $htmlid = "oercampus_".$material->id."_".uniqid();
            $output = "<iframe id='".$htmlid."' src=\"". htmlReady($url). "\" style=\"width: 100%; height: 70vh; border: none;\"></iframe>";
            $actions->addLink(
                "#",
                _("Vollbild"),
                Icon::create(PluginManager::getInstance()->getPlugin("LernMarktplatz")->getPluginURL()."/assets/resize-full-screen.svg"),
                ['onClick' => "STUDIP.Lernmarktplatz.requestFullscreen('#".$htmlid."'); return false;"]
            );
            $right_link .= $actions->render();
            $right_link .= '</div>';
            $output .= $right_link;
            return $output;
        } elseif ($material->isVideo()) {
            $output = "<video controls ".($material['front_image_content_type'] ? 'poster="'.htmlReady($material->getLogoURL()).'"' : ""). "
               crossorigin=\"anonymous\"
               src=\"". htmlReady($url) ."\"
               style=\"width: 100%; height: 70vh; border: none;\"></video>";
            $output .= $right_link;
            return $output;
        } elseif ($material->isAudio()) {
            $output = "<div>
                        <a href=\"". htmlReady($url) ."\" onClick=\"var player = jQuery('#audioplayer')[0]; if (player.paused == false) { player.pause(); } else { player.play(); }; return false;\">
                            <img src=\"". htmlReady($material->getLogoURL()) ."\" class=\"lernmarktplatz_player\">
                        </a>
                    </div>
                    <div style=\"text-align: center;\">
                        <audio controls
                               id=\"audioplayer\"
                               crossorigin=\"anonymous\"
                               src=\"". htmlReady($url) ."\"></audio>";
            $output .= $right_link;
            return $output;
        } else {
            $output = '<a href="'.htmlReady($url).'">'.Icon::create("service", "clickable")->asImg(16, array('class' => "text-bottom")).htmlReady($material['name']).'</a>';

            $output .= $actions->render();
            return $output;
        }
        return $material['name'];
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

    /**
     * Returns a Navigation-object. Only the title and the image will be used.
     *
     * @return null|Navigation with title and image
     */
    public function getFileSelectNavigation() {
        $nav = new Navigation(Config::get()->LERNMARKTPLATZ_TITLE);
        $nav->setImage(Icon::create('service', 'clickable'));
        return $nav;
    }

    /**
     * Returns an URL to a page, where the filesystem can be configured.
     *
     * @return mixed
     */
    public function filesystemConfigurationURL() {
        return null;
    }

    /**
     * Determines if this filesystem plugin should be a source for copying or a search.
     * This may be dependend on the current user and his/her configurations.
     *
     * @return boolean
     */
    public function isSource() {
        return true;
    }

    /**
     * Determines if this filesystem-plugin should show up as a personal file-area and be a destination
     * for copied files.
     * This may be dependend on the current user and his/her configurations.
     *
     * @return boolean
     */
    public function isPersonalFileArea() {
        return false;
    }

    /**
     * This method is used to get a folder-object for this plugin.
     * Not recommended but still possible is to return a Flexi_Template for the folder, if you want to
     * take care of the frontend of displaying the folder as well.
     *
     * @param null $folder_id : folder_id of folder to get or null if you want the top-folder
     * @return FolderType|Flexi_Template
     */
    public function getFolder($folder_id = null) {

    }

    /**
     * @param $file_id : The id for the file in the given filesystem of the plugin.
     * @return array : the already prepared File just like a file-upload-array
     */
    public function getPreparedFile($file_id, $with_blob = false) {

    }

    /**
     * Defines if the filesystem-plugin has a search-function.
     *
     * @return mixed
     */
    public function hasSearch() {
        return true;
    }

    /**
     * Returns an array for each special search parameter. Each parameter is itself represented by as associative array
     * like
     *     array(
     *         'name' => "name of this parameter in the form",
     *         'type' => "one of 'text', 'checkbox', 'select'",
     *         'options' => array() //only neccesary if type is 'select' - a key-value array with the key key as the value of the select and the value as the label of the option
     *         'placeholder' => "only possible for type 'text' but not mandatory"
     *     )
     * This method can also return an empty array or null if no search parameters are needed or no search is provided at all.
     *
     * @return null|array(array(), ...)
     */
    public function getSearchParameters() {
        return array();
    }

    /**
     * Returns a virtual folder that 'contains' all the files as a search-result. Only return null
     * if search is not implemented.
     *
     * @param string $text a string
     * @param array $parameters : an associative array of additional search parameters as defined in getSearchParameters()
     * @return FolderType|null
     */
    public function search($text, $parameters = []) {
        $folder = new VirtualFolderType();
        $this->materialien = LernmarktplatzMaterial::findByText($text);
        foreach ($this->materialien as $material) {
            $url = $material['host_id']
                ? $material->host->url."download/".$material['foreign_material_id']
                : PluginEngine::getURL($this, array(), "market/download/".$material->getId());
            $folder->createFile(array(
                'id' => $material->getId(),
                'name' => $material['name'],
                'description' => $material['short_description'],
                'url' => $url
            ));
        }
        return $folder;
    }
    
}
