<?php

class LehrmarktplatzComment extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_comments';
        $config['belongs_to']['review'] = array(
            'class_name' => 'LehrmarktplatzReview',
            'foreign_key' => 'review_id'
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
        if (!$this->review['host_id'] && $this->review['user_id'] !== $GLOBALS['user']->id) {
            PersonalNotifications::add(
                $this->review['user_id'],
                URLHelper::getURL("plugins.php/lehrmarktplatz/market/details/".$this->material->getId()."#review_".$this->getId()),
                sprintf(_("%s hat einen Kommentar zu Ihrem Review geschrieben."), $this['host_id'] ? MarketUser::find($this['user_id'])->name : get_fullname($this['user_id'])),
                "comment_".$this->getId(),
                Assets::image_path("icons/16/blue/support.svg")
            );
        }
        //only push if the comment is from this server and the material-server is different
        if ($this->review['host_id'] && !$this['host_id'] && $this->isDirty()) {
            $remote = new MarketHost($this->review->material['host_id']);
            $myHost = MarketHost::thisOne();
            $data = array();
            $data['host'] = array(
                'name' => $myHost['name'],
                'url' => $myHost['url'],
                'public_key' => $myHost['public_key']
            );
            $data['data'] = $this->toArray();
            $data['data']['foreign_comment_id'] = $data['data']['comment_id'];
            unset($data['data']['comment_id']);
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
                $remote->pushDataToEndpoint("add_comment/".$this->review['foreign_review_id'], $data);
            }
        }
    }

}