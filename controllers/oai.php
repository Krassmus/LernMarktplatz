<?php

require_once 'app/controllers/plugin_controller.php';

/*
* This controller tells a client we provide OAI-PMH protocol so he can harvest data
*/
class OaiController extends PluginController 

{
    
    public function index_action() 
    {
        $request = Request::getInstance();
        $verb = $request->offsetGet('verb');

        $callRequest = lcfirst($verb);
        $this->startRequest($callRequest);
        
    }
    public function startRequest($requested) {
        $this->render_template("oai/".$requested);
    }
}
