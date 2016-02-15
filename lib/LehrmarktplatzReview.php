<?php

class LehrmarktplatzReview extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_reviews';
        $config['belongs_to']['material'] = array(
            'class_name' => 'MarketMaterial',
            'foreign_key' => 'material_id'
        );
        parent::configure($config);
    }

}