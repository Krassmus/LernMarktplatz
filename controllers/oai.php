<?php

require_once 'app/controllers/plugin_controller.php';

/*
* This controller tells a client we provide OAI-PMH protocol so he can harvest data
*/
class OaiController extends PluginController 

{
    
    public function index_action() 
    {
        $this->currentDate = date(DATE_ATOM, time());
        $request = Request::getInstance();
        $this->from = $request->offsetGet('from');
        $metadataPrefix = $request->offsetGet('metadataPrefix');
        if (!empty($metadataPrefix)) {
            $this->metadataPrefix = $metadataPrefix; //has to be lom --> validate
        }
        $set = $request->offsetGet('set');
        if (!empty($set)) {
            $this->records = LernMarktplatzMaterial::findByTag($set);
        }
        $verb = $request->offsetGet('verb');

        if (!empty($verb)) {
            $this->verb = $verb;
            $verb = lcfirst($verb);
            $this->startRequest($verb);
        } else {
            //THROW smth
        }
        
        
    }
    public function prepareLomOutput(){} //TODO
    public function startRequest($verb) 
    {
        $this->prepareLomOutput($verb);
        $this->render_template("oai/".$verb);
    }
}
