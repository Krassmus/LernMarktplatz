<?php

require_once 'app/controllers/plugin_controller.php';

class MarketController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Lernmaterialien"));
    }

    public function overview_action() {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        $tag_matrix_entries_number = 9;
        $tag_subtags_number = 6;

        if (Request::get("disable_maininfo")) {
            UserConfig::get($GLOBALS['user']->id)->store("LERNMARKTPLATZ_DISABLE_MAININFO", 1);
            $this->redirect("market/overview");
        }

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
        } elseif(Request::get("search")) {
            $this->materialien = LernmarktplatzMaterial::findByText(Request::get("search"));
        } elseif(Request::get("tag")) {
            $this->materialien = LernmarktplatzMaterial::findByTag(Request::get("tag"));
        } else {
            $this->best_nine_tags = LernmarktplatzTag::findBest($tag_matrix_entries_number);
        }
        $this->new_ones = LernmarktplatzMaterial::findBySQL("1=1 ORDER BY mkdate DESC LIMIT 3");
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

    public function details_action($material_id)
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        if (Navigation::hasItem($main_navigation."/lernmarktplatz/overview")) {
            Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        }
        $this->material = new LernmarktplatzMaterial($material_id);
        if ($this->material['host_id']) {
            $success = $this->material->fetchData();
            if ($success === false) {
                PageLayout::postMessage(MessageBox::info(_("Dieses Material stammt von einem anderen Server, der zur Zeit nicht erreichbar ist.")));
            } elseif ($success === "deleted") {
                $material = clone $this->material;
                $this->material->delete();
                $this->material = $material;
                PageLayout::postMessage(MessageBox::error(_("Dieses Material ist gelÃ¶scht worden und wird gleich aus dem Cache verschwinden.")));
            }
        }
        $this->material['rating'] = $this->material->calculateRating();
        $this->material->store();
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
            PageLayout::postMessage(MessageBox::success(_("Danke fÃ¼r das Review!")));
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
        if ($_SERVER['HTTPS'] == "on"){
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

        die();
    }


    public function licenseinfo_action()
    {

    }

    public function add_to_course_action($material_id)
    {
        $this->material = new LernmarktplatzMaterial($material_id);
        if (Request::isPost() && Request::option("seminar_id") && $GLOBALS['perm']->have_studip_perm("autor", Request::option("seminar_id"))) {
            //$course = new Course(Request::option("seminar_id"));
            $query = "SELECT folder_id FROM folder WHERE range_id = ? ORDER BY name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(Request::option("seminar_id")));
            $folder_id = $statement->fetch(PDO::FETCH_COLUMN, 0);
            if ($folder_id && ($GLOBALS['perm']->have_studip_perm("tutor", Request::option("seminar_id")) || in_array("writable", DocumentFolder::find($folder_id)->getPermissions()))) {
                if ($this->material['host_id']) {
                    $path = $GLOBALS['TMP_PATH']."/tmp_download_".md5(uniqid());
                    file_put_contents($path, file_get_contents($this->material->host->url."download/".$this->material['foreign_material_id']));
                } else {
                    $path = $this->material->getFilePath();
                }
                $document = StudipDocument::createWithFile($path, array(
                    'name' => $this->material['name'],
                    'range_id' => $folder_id,
                    'user_id' => $GLOBALS['user']->id,
                    'seminar_id' => Request::option("seminar_id"),
                    'description' => $this->material['description'] ?: $this->material['short_description'],
                    'filename' => $this->material['filename'],
                    'filesize' => filesize($path),
                    'author_name' => get_fullname()
                ));
                PageLayout::postMessage(MessageBox::success(_("Datei wurde erfolgreich kopiert.")));
                $this->redirect(URLHelper::getURL("folder.php#anker", array(
                    'cid' => Request::option("seminar_id"),
                    'data' => array(
                        'cmd' => "tree",
                        'open' => array(
                            $folder_id => 1,
                            $document->getId() => 1
                        )
                    ),
                    'open' => $document->getId()
                )));
                if ($this->material['host_id']) { //cleanup
                    @unlink($path);
                }
            } else {
                PageLayout::postMessage(MessageBox::error(_("Veranstaltung hat keinen allgemeinen Dateiordner.")));
                $this->redirect(PluginEngine::getURL($this->plugin, array(), "market/details/".$material_id));
            }
        }
        $this->courses = Course::findBySQL("INNER JOIN seminar_user USING (Seminar_id) WHERE seminar_user.user_id = ? ORDER BY seminare.mkdate DESC", array($GLOBALS['user']->id));
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
