<?php

class MarketController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Helpbar::Get()->addPlainText(
            _("Lernmaterialien"),
            _("Übungszettel, Musterlösungen, Vorlesungsmitschriften oder gar Folien, selbsterstellte Lernkarteikarten oder alte     Klausuren. Das alles wird einmal erstellt und dann meist vergessen. Auf dem Lernmaterialienmarktplatz bleiben sie     erhalten. Jeder kann was hochladen und jeder kann alles herunterladen. Alle Inhalte hier sind frei verfügbar für jeden.")
        );
        if ($GLOBALS['perm']->have_perm(Config::get()->LERNMARKTPLATZ_PUBLIC_STATUS)) {
            $search_widget = new SearchWidget(PluginEngine::getURL($this->plugin, array(), "market/search"));
            $search_widget->addNeedle(
                Config::get()->LERNMARKTPLATZ_PLACEHOLDER_SEARCH,
                "search",
                Config::get()->LERNMARKTPLATZ_PLACEHOLDER_SEARCH
            );
            Sidebar::Get()->addWidget($search_widget);
        }
        PageLayout::setTitle(_("Lernmaterialien"));
    }

    public function overview_action() {
        if (!$GLOBALS['perm']->have_perm(Config::get()->LERNMARKTPLATZ_PUBLIC_STATUS)) {
            throw new AccessDeniedException();
        }
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        $tag_matrix_entries_number = 9;
        $tag_subtags_number = 6;

        if (Request::get("tags")) {
            $tags = $this->tag_history = explode(",", Request::get("tags"));
            $this->without_tags = array();
            $tag_to_search_for = array_pop($tags);
            foreach (LernmarktplatzTag::findBest($tag_matrix_entries_number, true) as $related_tag) {
                if ($related_tag['tag_hash'] !== $this->tag_history[0]) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            //array_shift($this->tag_history);
            foreach ($tags as $tag) {
                foreach (LernmarktplatzTag::findRelated($tag, $this->without_tags, $tag_subtags_number, true) as $related_tag) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            $this->more_tags = LernmarktplatzTag::findRelated(
                $tag_to_search_for,
                $this->without_tags,
                $tag_subtags_number
            );
            $this->materialien = LernmarktplatzMaterial::findByTagHash($tag_to_search_for);
        } elseif(Request::get("tag")) {
            $this->materialien = LernmarktplatzMaterial::findByTag(Request::get("tag"));
        } else {
            $this->best_nine_tags = LernmarktplatzTag::findBest($tag_matrix_entries_number);
        }
        $this->new_ones = LernmarktplatzMaterial::findBySQL("draft = '0' ORDER BY mkdate DESC LIMIT 3");
    }

    public function matrixnavigation_action()
    {
        $tag_matrix_entries_number = 9;
        $tag_subtags_number = 6;

        if (!Request::get("tags")) {
            $this->topics = LernmarktplatzTag::findBest($tag_matrix_entries_number);
            $this->materialien = array();
        } else {
            $tags = $this->tag_history = explode(",", Request::get("tags"));
            $this->without_tags = array();
            $tag_to_search_for = array_pop($tags);
            foreach (LernmarktplatzTag::findBest($tag_matrix_entries_number, true) as $related_tag) {
                if ($related_tag['tag_hash'] !== $this->tag_history[0]) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            //array_shift($this->tag_history);
            foreach ($tags as $tag) {
                foreach (LernmarktplatzTag::findRelated($tag, $this->without_tags, $tag_subtags_number, true) as $related_tag) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            $this->topics = LernmarktplatzTag::findRelated(
                $tag_to_search_for,
                $this->without_tags,
                $tag_subtags_number
            );
            $this->materialien = LernmarktplatzMaterial::findByTagHash($tag_to_search_for);
        }

        $output = array();
        $output['breadcrumb'] = $this->render_template_as_string("market/_breadcrumb");
        $output['matrix'] = $this->render_template_as_string("market/_matrix");
        $output['materials'] = $this->render_template_as_string("market/_materials");

        $this->render_json($output);
    }

    public function search_action()
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        if (Request::get("search") || Request::get("type") || Request::get("difficulty")) {
            $search = \Lernmarktplatz\SQLQuery::table("lernmarktplatz_material", "lernmarktplatz_material")
                ->where("draft = '0'")
                ->orderBy("mkdate DESC");
            if (Request::get("type")) {
                switch (Request::get("type")) {
                    case "audio":
                        $search->where("content_type LIKE 'audio/%'");
                        break;
                    case "video":
                        $search->where("content_type LIKE 'video/%'");
                        break;
                    case "presentation":
                        $search->where("content_type IN ('application/pdf', 'application/x-iwork-keynote-sffkey', 'application/vnd.apple.keynote', 'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.presentation-template') OR content_type LIKE 'application/vnd.openxmlformats-officedocument.presentationml.%' OR content_type LIKE 'application/vnd.ms-powerpoint%'");
                        break;
                    case "learningmodules":
                        $search->where("player_url IS NOT NULL AND player_url != ''");
                        break;
                    default:
                        throw new Exception("Kein gültiger Typ angegeben.");
                }
            }
            if (Request::get("search")) {
                //Tags
                $search->where(
                    "textsearch",
                    "(name LIKE :search OR description LIKE :search OR short_description LIKE :search)",
                    array('search' => '%'.Request::get("search").'%')
                );
            }
            if (Request::get("difficulty")) {
                $difficulty = explode(",", Request::get("difficulty"));
                $search->where(
                    "difficulty",
                    "((difficulty_start <= :difficulty_start AND difficulty_end >= :difficulty_start) OR (difficulty_start <= :difficulty_end AND difficulty_end >= :difficulty_end) OR (difficulty_start <= :difficulty_start AND difficulty_end >= :difficulty_end) OR (difficulty_start >= :difficulty_start AND difficulty_end <= :difficulty_end))",
                    array('difficulty_start' => $difficulty[0], 'difficulty_end' => $difficulty[1])
                );
            }

            $this->materialien = $search->fetchAll("LernmarktplatzMaterial");
        } else {
            $this->redirect("market/overview");
        }
    }

    public function adjust_filter_action()
    {

    }

    public function details_action($material_id)
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        $this->material = new LernmarktplatzMaterial($material_id);

        //OpenGraph tags:
        PageLayout::addHeadElement("meta", array('og:title' => $this->material['name']));

        $base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        PageLayout::addHeadElement("meta", array('og:url' => PluginEngine::getLink($this->plugin, array(), "market/details/".$this->material->getId())));
        PageLayout::addHeadElement("meta", array('og:description' => $this->material['short_description']));
        PageLayout::addHeadElement("meta", array('og:image' => $this->material->getLogoURL()));
        if ($this->material->isVideo()) {
            PageLayout::addHeadElement("meta", array('og:type' => "video"));
            $url = $this->material['host_id'] ? $this->material->host->url."download/".$this->material['foreign_material_id'] : URLHelper::getURL("plugins.php/lernmarktplatz/market/download/".$this->material->getId());
            PageLayout::addHeadElement("meta", array('og:video' => $url));
            PageLayout::addHeadElement("meta", array('og:video:type' => $this->material['content_type']));
        } elseif($this->material->isAudio()) {
            PageLayout::addHeadElement("meta", array('og:type' => "audio"));
            $url = $this->material['host_id'] ? $this->material->host->url."download/".$this->material['foreign_material_id'] : URLHelper::getURL("plugins.php/lernmarktplatz/market/download/".$this->material->getId());
            PageLayout::addHeadElement("meta", array('og:audio' => $url));
            PageLayout::addHeadElement("meta", array('og:audio:type' => $this->material['content_type']));
        } else {
            PageLayout::addHeadElement("meta", array('og:type' => "article"));
        }
        URLHelper::setBaseURL($base);

        if ($this->material['host_id']) {
            $success = $this->material->fetchData();
            if ($success === false) {
                PageLayout::postMessage(MessageBox::info(_("Dieses Material stammt von einem anderen Server, der zur Zeit nicht erreichbar ist.")));
            } elseif ($success === "deleted") {
                $material = clone $this->material;
                $this->material->delete();
                $this->material = $material;
                PageLayout::postMessage(MessageBox::error(_("Dieses Material ist gelöscht worden und wird gleich aus dem Cache verschwinden.")));
            }
        }
        $this->material['rating'] = $this->material->calculateRating();
        $this->material->store();
    }

    public function embed_action($material_id)
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        $this->material = new LernmarktplatzMaterial($material_id);
    }

    public function review_action($material_id = null)
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        $this->material = new LernmarktplatzMaterial($material_id);
        $this->review = LernmarktplatzReview::findOneBySQL("material_id = ? AND user_id = ? AND host_id IS NULL", array($material_id, $GLOBALS['user']->id));
        if (!$this->review) {
            $this->review = new LernmarktplatzReview();
            $this->review['material_id'] = $this->material->getId();
            $this->review['user_id'] = $GLOBALS['user']->id;
        }
        if (Request::isPost()) {
            $this->review['review'] = Request::get("review");
            $this->review['rating'] = Request::get("rating");
            $this->review->store();

            $this->material['rating'] = $this->material->calculateRating();
            $this->material->store();
            PageLayout::postMessage(MessageBox::success(_("Danke für das Review!")));
            $this->redirect("market/details/".$material_id);
        }
    }

    public function discussion_action($review_id)
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        $this->review = new LernmarktplatzReview($review_id);
        if (Request::isPost() && Request::get("comment")) {
            $comment = new LernmarktplatzComment();
            $comment['review_id'] = $review_id;
            $comment['comment'] = Request::get("comment");
            $comment['user_id'] = $GLOBALS['user']->id;
            $comment->store();
        }
    }

    public function comment_action($review_id)
    {
        $this->review = new LernmarktplatzReview($review_id);
        if (Request::isPost() && Request::get("comment")) {
            $this->comment = new LernmarktplatzComment();
            $this->comment['review_id'] = $review_id;
            $this->comment['comment'] = Request::get("comment");
            $this->comment['user_id'] = $GLOBALS['user']->id;
            $this->comment->store();
            $comment_html = $this->render_template_as_string("market/_comment");
            $this->render_json(array('html' => $comment_html));
        }
    }


    public function download_action($material_id, $disposition = "inline")
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        page_close();
        $this->material = new LernmarktplatzMaterial($material_id);

        $filesize = filesize($this->material->getFilePath());
        header("Accept-Ranges: bytes");
        $start = 0;
        $end = $filesize - 1;
        $length = $filesize;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end   = $end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (mb_strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$filesize");
                exit;
            }
            if ($range[0] == '-') {
                $c_start = $filesize - mb_substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $filesize;
            }
            $c_end = ($c_end > $end) ? $end : $c_end;
            if ($c_start > $c_end || $c_start > $filesize - 1 || $c_end >= $filesize) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$filesize");
                exit;
            }
            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1;
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$filesize");
        }

        header("Content-Length: $length");

        header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
        header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
        if ($_SERVER['HTTPS'] == "on") {
            header("Pragma: public");
            header("Cache-Control: private");
        } else {
            header("Pragma: no-cache");
            header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
        }
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Content-Type: ".$this->material['content_type']);
        header("Content-Disposition: " . ($disposition ?: "inline") . "; " . $this->encode_header_parameter('filename', $this->material['filename']));

        readfile_chunked($this->material->getFilePath(), $start, $end);

        if (!$start) {
            LernmarktplatzDownloadcounter::addCounter($material_id);
        }

        die();
    }


    public function licenseinfo_action()
    {

    }

    public function add_to_course_action($material_id)
    {
        $this->material = new LernmarktplatzMaterial($material_id);
        if (Request::isPost() && Request::option("seminar_id") && $GLOBALS['perm']->have_studip_perm("autor", Request::option("seminar_id"))) {
            $course = new Course(Request::option("seminar_id"));

            $already_installed = false;
            foreach (PluginManager::getInstance()->getPlugins(null) as $plugin) {
                $method = "lernmarktplatzInstallMaterialToCourse";
                if (method_exists($plugin, $method)) {
                    $already_installed = $plugin->$method($this->material, $course);
                    //Diese Methode sollte entweder false oder eine URL zurückgegeben haben.
                    if ($already_installed) {
                        break;
                    }
                }
            }

            if ($already_installed) {
                $this->redirect($already_installed);
            } else {
                //in den Dateibereich legen:
                $folder = Folder::findTopFolder($course->id);
                $folder = $folder->getTypedFolder();
                $uploaded_files = array(
                    'name' => array($this->material['filename']),
                    'tmp_name' => array($this->material->getFilePath()),
                    'size' => array(filesize($this->material->getFilePath())),
                    'type' => array($this->material['content_type'])
                );
                $output = FileManager::handleFileUpload($uploaded_files, $folder, $GLOBALS['user']->id);
                if (count($output['errors'])) {
                    PageLayout::postError(_("Es sind Fehler beim Kopieren aufgetreten:"), $output['errors']);
                    $this->redirect("market/details/".$material_id);
                } else {
                    PageLayout::postSuccess(_("Das Lernmaterial wurde kopiert."));
                    $this->redirect(URLHelper::getURL("dispatch.php/course/files", array('cid' => $course->id)));
                }

            }
        }
        if (!$GLOBALS['perm']->have_perm("admin")) {
            $this->courses = Course::findBySQL("INNER JOIN seminar_user USING (Seminar_id) WHERE seminar_user.user_id = ? ORDER BY seminare.mkdate DESC", array($GLOBALS['user']->id));
        }
    }

    public function profile_action($external_user_id) {
        $this->user = new LernmarktplatzUser($external_user_id);
        if ($this->user->isNew()) {
            throw new Exception(_("Nutzer ist nicht erfasst."));
        }
        $this->materials = LernmarktplatzMaterial::findBySQL("user_id = ? AND host_id IS NOT NULL ORDER BY mkdate DESC", array(
            $external_user_id
        ));
    }

    protected function getFolderStructure($folder) {
        $structure = array();
        foreach (scandir($folder) as $file) {
            if (!in_array($file, array(".", ".."))) {
                $attributes = array(
                    'is_folder' => is_dir($folder."/".$file) ? 1 : 0
                );
                if (is_dir($folder."/".$file)) {
                    $attributes['structure'] = $this->getFolderStructure($folder."/".$file);
                } else {
                    $attributes['size'] = filesize($folder."/".$file);
                }
                $structure[$file] = $attributes;
            }
        }
        return $structure;
    }

    private function encode_header_parameter($name, $value)
    {
        if (preg_match('/[\200-\377]/', $value)) {
            // use RFC 5987 encoding (ext-parameter)
            return $name . "*=UTF-8''" . rawurlencode($value);
        } else {
            // use RFC 2616 encoding (quoted-string)
            return $name . '="' . addslashes($value) . '"';
        }
    }

}
