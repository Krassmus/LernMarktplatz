<?php

require_once 'app/controllers/plugin_controller.php';

class MarketController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Lehrmaterialien"));
    }

    public function overview_action() {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $tag_matrix_entries_number = 9;
        $tag_subtags_number = 6;

        if (Request::get("tags")) {
            $this->tag_history = $tags = explode(",", Request::get("tags"));
            $this->without_tags = array();
            foreach (MarketTag::findBest($tag_matrix_entries_number, true) as $related_tag) {
                if (!$related_tag['tag_hash'] === $this->tag_history[0]) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            foreach ($this->tag_history as $tag) {
                $tag_to_search_for = $tag;
                foreach (MarketTag::findRelated($tag, $this->without_tags, $tag_subtags_number, true) as $related_tag) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            $this->more_tags = MarketTag::findRelated(
                $tag_to_search_for,
                $this->without_tags,
                $tag_subtags_number
            );
            $this->materialien = MarketMaterial::findByTagHash($tag_to_search_for);
        } elseif(Request::get("search")) {
            $this->materialien = MarketMaterial::findByTag(Request::get("search"));
        } elseif(Request::get("tag")) {
            $this->materialien = MarketMaterial::findByTag(Request::get("tag"));
        } else {
            $this->best_nine_tags = MarketTag::findBest($tag_matrix_entries_number);
        }
    }

    public function details_action($material_id)
    {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $this->material = new MarketMaterial($material_id);
    }


    public function download_action($material_id, $disposition = "inline")
    {
        $this->material = new MarketMaterial($material_id);
        $this->set_content_type($this->material['content_type']);
        $this->response->add_header('Content-Disposition', $disposition.';filename="' . addslashes($this->material['filename']) . '"');
        $this->response->add_header('Content-Length', filesize($this->material->getFilePath()));
        $this->render_text(file_get_contents($this->material->getFilePath()));
    }


    public function edit_action($material_id = null) {
        $this->material = new MarketMaterial($material_id);
        if ($this->material['user_id'] && $this->material['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $this->material->setData(Request::getArray("data"));
            $this->material['user_id'] = $GLOBALS['user']->id;
            $this->material['host_id'] = null;
            if ($_FILES['file']['tmp_name']) {
                $this->material['content_type'] = $_FILES['file']['type'];
                if (in_array($this->material['content_type'], array("application/x-zip-compressed", "application/zip", "application/x-zip"))) {
                    $tmp_folder = $GLOBALS['TMP_PATH']."/temp_folder_".md5(uniqid());
                    mkdir($tmp_folder);
                    unzip_file($_FILES['file']['tmp_name'], $tmp_folder);
                    $this->material['structure'] = $this->getFolderStructure($tmp_folder);
                    rmdirr($tmp_folder);
                } else {
                    $this->material['structure'] = null;
                }
                $this->material['filename'] = $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $this->material->getFilePath());
            }
            $this->material->store();
            PageLayout::postMessage(MessageBox::success(_("Lehrmaterial erfolgreich gespeichert.")));
            $this->redirect("market/overview");
        }
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

}