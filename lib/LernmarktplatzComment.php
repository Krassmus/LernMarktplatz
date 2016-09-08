<?php

class LernmarktplatzComment extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_comments';
        $config['belongs_to']['review'] = array(
            'class_name' => 'LernmarktplatzReview',
            'foreign_key' => 'review_id'
        );
        $config['has_one']['host'] = array(
            'class_name' => 'LernmarktplatzHost',
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
        if ($this->isDirty()) {
            //add notification to writer of review
            if (!$this->review['host_id'] && $this->review['user_id'] !== $this['user_id']) {
                PersonalNotifications::add(
                    $this->review['user_id'],
                    URLHelper::getURL("plugins.php/lernmarktplatz/market/discussion/" . $this['review_id'] . "#comment_" . $this->getId()),
                    sprintf(_("%s hat einen Kommentar zu Ihrem Review geschrieben."), $this['host_id'] ? LernmarktplatzUser::find($this['user_id'])->name : get_fullname($this['user_id'])),
                    "comment_" . $this->getId(),
                    Assets::image_path("icons/16/blue/support.svg")
                );
            }

            //add notification to all users of this servers who discussed this review but are neither the new
            //commentor nor the writer of the review
            $statement = DBManager::get()->prepare("
                SELECT user_id
                FROM lernmarktplatz_comments
                WHERE review_id = :review_id
                    AND host_id IS NULL
                GROUP BY user_id
            ");
            $statement->execute(array(
                'review_id' => $this->review->getId()
            ));
            foreach ($statement->fetchAll(PDO::FETCH_COLUMN, 0) as $user_id) {
                if (!in_array($user_id, array($this->review['user_id'], $this['user_id']))) {
                    PersonalNotifications::add(
                        $user_id,
                        URLHelper::getURL("plugins.php/lernmarktplatz/market/discussion/" . $this['review_id'] . "#comment_" . $this->getId()),
                        sprintf(_("%s hat auch einen Kommentar geschrieben."), $this['host_id'] ? LernmarktplatzUser::find($this['user_id'])->name : get_fullname($this['user_id'])),
                        "comment_" . $this->getId(),
                        Assets::image_path("icons/16/blue/support.svg")
                    );
                }
            }

            //only push if the comment is from this server and the material-server is different
            if (!$this['host_id']) {
                $myHost = LernmarktplatzHost::thisOne();
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
                $user_description_datafield = DataField::find(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
                if ($user_description_datafield) {
                    $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($this['user_id'], $user_description_datafield->getId()));
                }
                $data['user'] = array(
                    'user_id' => $this['user_id'],
                    'name' => get_fullname($this['user_id']),
                    'avatar' => Avatar::getAvatar($this['user_id'])->getURL(Avatar::NORMAL),
                    'description' => $datafield_entry ? $datafield_entry['content'] : null
                );

                $statement = DBManager::get()->prepare("
                    SELECT host_id
                    FROM lernmarktplatz_comments
                    WHERE review_id = :review_id
                        AND host_id IS NOT NULL
                    GROUP BY host_id
                ");
                $statement->execute(array(
                    'review_id' => $this->review->getId()
                ));
                $hosts = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
                if ($this->review['host_id'] && !in_array($this->review['host_id'], $hosts)) {
                    $hosts[] = $this->review['host_id'];
                }
                if ($this->review->material['host_id'] && !in_array($this->review->material['host_id'], $hosts)) {
                    $hosts[] = $this->review->material['host_id'];
                }
                foreach ($hosts as $host_id) {
                    $remote = new LernmarktplatzHost($host_id);
                    if (!$remote->isMe()) {
                        $review_id = ($this->review['foreign_review_id'] ?: $this->review->getId());
                        if ($this->review['foreign_review_id']) {
                            if ($this->review->host_id === $remote->getId()) {
                                $host_hash = null;
                            } else {
                                $host_hash = md5($this->review->host['public_key']);
                            }
                        } else {
                            $host_hash = md5($myHost['public_key']);
                        }
                        $remote->pushDataToEndpoint("add_comment/" . $review_id ."/".$host_hash, $data);
                    }
                }
            }
        }
    }

}