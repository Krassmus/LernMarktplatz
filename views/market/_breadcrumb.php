<ol class="breadcrumb"<?= !count($tag_history) ? ' style="visibility: hidden;"' : "" ?>>
    <? $breadcrump_tags = array() ?>
    <li>
        <a href="<?= PluginEngine::getLink($plugin, array(), "market/overview") ?>" data-tags="">
            <?= _("Zum Anfang") ?>
        </a>
    </li>
    <? foreach ((array) $tag_history as $key => $tag) : ?>
        <li>
            <? $breadcrump_tags[] = $tag ?>
            <? $new_tag_history = implode(",", $breadcrump_tags) ?>
            <a href="<?= PluginEngine::getLink($plugin, array('tags' => $new_tag_history), "market/overview") ?>"  data-tags="<?= $new_tag_history ?>">
                <?= htmlReady(LernmarktplatzTag::find($tag)->name) ?>
            </a>
        </li>
    <? endforeach ?>
</ol>