<?php

class LehrmarktplatzReview extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_reviews';
        $config['belongs_to']['material'] = array(
            'class_name' => 'MarketMaterial',
            'foreign_key' => 'material_id'
        );
        $config['has_one']['host'] = array(
            'class_name' => 'MarketHost',
            'foreign_key' => 'host_id',
            'assoc_foreign_key' => 'host_id'
        );
        parent::configure($config);
    }

    function __construct($id = null)
    {
        $this->registerCallback('after_store', 'afterStoreCallback');
        parent::__construct($id);
    }

    public function afterStoreCallback()
    {
        if (!$this->material['host_id'] && $this->material['user_id'] !== $GLOBALS['user']->id) {
            PersonalNotifications::add(
                $this->material['user_id'],
                URLHelper::getURL("plugins.php/lehrmarktplatz/market/details/".$this->material->getId()."#review_".$this->getId()),
                sprintf(_("%s hat ein Review zu '%s' geschrieben."), $this['host_id'] ? MarketUser::find($this['user_id'])->name : get_fullname($this['user_id']), $this->material['name']),
                "review_".$this->getId(),
                Assets::image_path("icons/16/blue/support.svg")
            );
        }
        //only pus if the comment is from this server and the material-server is different
        if ($this->material['host_id'] && !$this['host_id']) {
            $remote = new MarketHost($this->material['host_id']);
            $myHost = MarketHost::thisOne();
            $data = array();
            $data['host'] = array(
                'name' => $myHost['name'],
                'url' => $myHost['url'],
                'public_key' => $myHost['public_key']
            );
            $data['data'] = $this->toArray();
            $data['data']['foreign_review_id'] = $data['data']['review_id'];
            unset($data['data']['review_id']);
            unset($data['data']['id']);
            unset($data['data']['user_id']);
            unset($data['data']['host_id']);
            $user_description_datafield = DataField::find(get_config("LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
            if ($user_description_datafield) {
                $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($this['user_id'], $user_description_datafield->getId()));
            }
            $data['user'] = array(
                'user_id' => $this['user_id'],
                'name' => get_fullname($this['user_id']),
                'avatar' => Avatar::getAvatar($this['user_id'])->getURL(Avatar::NORMAL),
                'description' => $datafield_entry ? $datafield_entry['content'] : null
            );

            if (!$remote->isMe()) {
                $remote->pushDataToEndpoint("add_review/".$this->material['foreign_material_id'], $data);
            }
        }
    }

}