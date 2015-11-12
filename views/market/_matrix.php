<? $tags || $tags = array() ?>
<div class="matrix">
    <? foreach ($topics as $topic) : ?>
        <? $new_history = implode(",", array_merge((array) $tag_history, array($topic['tag_hash']))) ?>
        <a data-tags="<?= $new_history ?>" href="<?= PluginEngine::getLink($plugin, array('tags' => $new_history), "market/overview") ?>">
            <?= Assets::img("icons/20/blue/topic", array('class' => "text-bottom")) ?>
            <?= htmlReady($topic['name']) ?>
        </a>
    <? endforeach ?>
</div>