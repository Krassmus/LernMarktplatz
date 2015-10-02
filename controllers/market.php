<?php

require_once 'app/controllers/plugin_controller.php';

class MarketController extends PluginController {

    public function overview_action() {
        Navigation::activateItem("/lehrmarktplatz/overview");
        $this->materialien = MarketMaterial::findAll();
    }

}