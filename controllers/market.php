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
                move_uploaded_file($_FILES['file']['tmp_name'], $this->material->getFilePath());
                $this->material['content_type'] = $_FILES['file']['type'];
                $this->material['structure'] = "";
            }
            $this->material->store();
            PageLayout::postMessage(MessageBox::success(_("Lehrmaterial erfolgreich gespeichert.")));
            $this->redirect("market/overview");
        }
    }

}