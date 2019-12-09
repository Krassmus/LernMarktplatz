<?php

class LernmarktplatzMaterialUser extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'lernmarktplatz_material_users';
        $config['belongs_to']['lernmarktplatzuser'] = array(
            'class_name' => 'LernmarktplatzUser',
            'foreign_key' => 'user_id'
        );
        parent::configure($config);
    }

    public function getJSON()
    {
        if ($this['external_contact']) {
            $user = $this['lernmarktplatzuser'];
            return [
                'user_id' => $user['foreign_user_id'],
                'name' => $user['name'],
                'avatar' => $user['avatar'],
                'description' => $user['description'],
                'host_url' => $user->host['url']
            ];
        } else {
            $user = User::find($this['user_id']);
            $user_description_datafield = DataField::find(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
            if ($user_description_datafield) {
                $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", [$user['user_id'], $user_description_datafield->getId()]);
            }
            return [
                'user_id' => $user['user_id'],
                'name' => $user ? $user->getFullName() : _("unbekannt"),
                'avatar' => Avatar::getAvatar($user['user_id'])->getURL(Avatar::NORMAL),
                'description' => $datafield_entry['content'],
                'host_url' => LernmarktplatzHost::thisOne()->url
            ];
        }
    }
}
