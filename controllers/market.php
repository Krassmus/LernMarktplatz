<?php

require_once 'app/controllers/plugin_controller.php';

class MarketController extends PluginController {

    public function overview_action() {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $this->materialien = MarketMaterial::findAll();
    }


    public function edit_action($material_id = null) {
        $this->material = new MarketMaterial($material_id);
        if ($this->material['user_id'] && $this->material['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
    }

}