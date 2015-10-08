<?php

class MarketTag extends SimpleORMap {

    static public function findBest($number = 9, $raw = false)
    {
        $statement = DBManager::get()->prepare("
            SELECT lehrmarktplatz_tags.*
            FROM (
                SELECT tags.tag_hash, COUNT(*) AS position
                FROM lehrmarktplatz_tags_material AS tags
                GROUP BY tags.tag_hash
                ) AS best_tags
                INNER JOIN lehrmarktplatz_tags ON (best_tags.tag_hash = lehrmarktplatz_tags.tag_hash)
            ORDER BY position DESC
            LIMIT ".(int) $number."
        ");
        $statement->execute();
        $best = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($raw) {
            return $best;
        } else {
            $tags = array();
            foreach ($best as $tag_data) {
                $tags[] = self::buildExisting($tag_data);
            }
            return $tags;
        }
    }

    static public function findRelated($tag_hash, $but_not = array(), $limit = 6, $raw = false) {
        $statement = DBManager::get()->prepare("
            SELECT lehrmarktplatz_tags.*
            FROM (
                SELECT tags1.tag_hash, COUNT(*) AS position
                FROM lehrmarktplatz_tags_material AS tags1
                    INNER JOIN lehrmarktplatz_tags_material AS tags2 ON (tags1.material_id = tags2.material_id AND tags1.tag_hash != tags2.tag_hash)
                WHERE lehrmarktplatz_tags.tag_hash NOT IN (:excluded_tags)
                    AND tags2.tag_hash = :tag_hash
                GROUP BY tags1.tag_hash
                ) AS best_tags
                INNER JOIN lehrmarktplatz_tags ON (best_tags.tag_hash = lehrmarktplatz_tags.tag_hash)
            ORDER BY position DESC
            LIMIT ".(int) $limit."
        ");
        $statement->execute(array(
            'tag_hash' => $tag_hash,
            'excluded_tags' => $but_not
        ));
        $best = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($raw) {
            return $best;
        } else {
            $tags = array();
            foreach ($best as $tag_data) {
                $tags[] = self::buildExisting($tag_data);
            }
            return $tags;
        }
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'lehrmarktplatz_tags';
        parent::configure($config);
    }
}