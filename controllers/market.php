<?php

require_once 'app/controllers/plugin_controller.php';

class MarketController extends PluginController {

    public function overview_action() {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $this->materialien = MarketMaterial::findAll();
    }

    public function details_action($material_id)
    {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $this->material = new MarketMaterial($material_id);
    }


    public function download_action($material_id)
    {
        $this->material = new MarketMaterial($material_id);
        $this->set_content_type($this->material['content_type']);
        $this->response->add_header('Content-Disposition', 'attachment;filename="' . addslashes($this->material['name']) . '"');
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