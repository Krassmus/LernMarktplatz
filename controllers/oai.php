<?php

require_once 'app/controllers/plugin_controller.php';

/** 
* This controller tells a client we provide OAI-PMH protocol and delivers oai-lom-data to harvest.
* Gets initialized due to requests. Validates metadata-prefix and used verb
* and calls a response-template with specified values.
*/
class OaiController extends PluginController 

{
 
    public function index_action() 
    {
        $this->set_content_type('text/xml;charset=utf-8');
        $this->request_url = Request::url();
        $allowed_verbs = ['GetRecord', 'Identify', 'ListIdentifiers', 'ListMetadataFormats', 'ListRecords', 'ListSets'];
        $allowed_prefix = ['oai_lom-de'];
        $request = Request::getInstance();
        URLHelper::setBaseUrl($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $verb = $request->offsetGet('verb');
        if (!empty($verb) && in_array($verb, $allowed_verbs)) {
            $verb = lcfirst($verb);
            $this->verb = $verb;   
        } else {
            $this->render_template("oai/badVerb");
        }

        $metadataPrefix = $request->offsetGet('metadataPrefix');
        if (empty($metadataPrefix) || in_array($metadataPrefix, $allowed_prefix)) {
            $this->metadataPrefix = $metadataPrefix; 
        } else {
            $this->render_template("oai/badPrefix");
        }

        if ($this->verb) {
            $this->prepareRequest($request, $verb, $metadataPrefix);
        }  
    }

    public function prepareRequest($request, $verb, $metadataPrefix)
    {
        $this->currentDate = gmdate(DATE_ATOM);
        $this->from = $request->offsetGet('from');
        $set = $request->offsetGet('set');

        switch ($verb) {
            case 'getRecord':
                $this->prepareGetRecord($request);
                break;
            case 'identify':
                $this->prepareIdentifier();
                break;
            case 'listIdentifiers':
                $this->prepareListIdentifiers($set);
                break;
            case 'listMetadataFormats':
                $this->prepareListMetadataFormats($request);
                break;
            case 'listRecords': 
                $this->prepareListRecords($set);
                break;
            case 'listSets':
                $this->prepareListSets();
                break;        
        }
    } 

    public function prepareGetRecord($request) 
    {
        $identifier = $request->offsetGet('identifier');
        
        if($targetMaterial = LernMarktplatzMaterial::find($identifier)) {
            $this->targetMaterial = $targetMaterial;
            $this->tags = $targetMaterial->getTopics();
            $this->vcard = vCard::export(User::find($targetMaterial->user_id));
            $this->renderResponse($this->verb);
        } else {
            $this->render_template("oai/idNotExists");
        }
    }

    public function prepareListRecords($set) 
    {

        $this->vcards = [];
        if ($this->records = LernMarktplatzMaterial::findByTag($set)) {
            foreach ($this->records as $targetRecord) {
                $this->tags = $targetRecord->getTopics();
                $this->set = $set;
                $this->vcards[] = vCard::export(User::find($targetRecord->user_id));
            }
            $this->renderResponse($this->verb);
        } else {
            $this->render_template("oai/noRecordsMatch");
        }
    }

    public function prepareIdentifier() 
    {
        if ($identifier = LernmarktplatzTag::findBySQL('1')) {
            $this->identifier = $identifier;
            $this->renderResponse($this->verb);
        } else {
            $this->render_template("oai/noSets");
        }
    }

    public function prepareListIdentifiers($set) 
    {
        if (!empty($set)) {
            $this->set = $set;
            $this->records = LernMarktplatzMaterial::findByTag($set);
            $this->renderResponse($this->verb);
        } else {
            $this->render_template("oai/noSets");
        }
    }

    public function prepareListMetadataFormats($request) 
    {
        $identifier = $request->offsetGet('identifier');
        if($targetMaterial = LernMarktplatzMaterial::find($identifier)) {
            $this->targetMaterial = $targetMaterial;
            $this->renderResponse($this->verb);
        } else {
            $this->render_template("oai/idNotExists");
        }
    }
    
    public function prepareListSets() 
    {
        if ($tags = LernmarktplatzTag::findBySQL('1')) {
            $this->tags = $tags;
            $this->renderResponse($this->verb);
        } else {
            $this->render_template("oai/noSets");
        }
    }

    public function renderResponse($verb) 
    {
        $this->render_template("oai/".$verb);
    }

}
