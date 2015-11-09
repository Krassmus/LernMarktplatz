<?php

require_once 'app/controllers/plugin_controller.php';

class MymaterialController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Lehrmaterialien"));
    }

    public function overview_action() {
        Navigation::activateItem("/lehrmarktplatz/overview");

    }

    public function details_action($material_id)
    {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $this->material = new MarketMaterial($material_id);
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


            //Topics:
            $this->material->setTags(Request::getArray("tags"));

            $this->material->pushDataToIndexServers();

            PageLayout::postMessage(MessageBox::success(_("Lehrmaterial erfolgreich gespeichert.")));
            $this->redirect("market/details/".$this->material->getId());
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

    public function add_tags_action()
    {
        $this->material = new MarketMaterial(Request::option("material_id"));
        if (Request::isPost()) {
            $this->material->addTag(Request::get("tag"));
            PageLayout::postMessage(MessageBox::success(_("Thema hinzugefügt")));
            $this->redirect("market/details/".$this->material->getId());
        }
     }

    public function add_tag_action()
    {
        if (!Request::isPost()) {
            throw new AccessDeniedException();
        }
        $this->material = new MarketMaterial(Request::option("material_id"));
        $this->render_nothing();
    }

}