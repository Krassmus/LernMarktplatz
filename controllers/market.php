<?php

class MarketController extends PluginController
{

    public function search_action()
    {
        $this->redirect(URLHelper::getURL('dispatch.php/oer/market/search', $_GET));
    }

    public function details_action($material_id)
    {
        $this->redirect(URLHelper::getURL('dispatch.php/oer/market/details/'.$material_id));
    }

}
