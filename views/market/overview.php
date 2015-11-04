<? if (Request::get("tags")) : ?>

    <div class="material_navigation">
        <ol class="breadcrumb">
            <? $breadcrump_tags = array() ?>
            <li>
                <a href="<?= PluginEngine::getLink($plugin, array(), "market/overview") ?>">
                    <?= _("Zum Anfang") ?>
                </a>
            </li>
            <? foreach ($tag_history as $key => $tag) : ?>
                <li>
                    <? $breadcrump_tags[] = $tag ?>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => implode(",", $breadcrump_tags)), "market/overview") ?>">
                        <?= htmlReady(MarketTag::find($tag)->name) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ol>

        <ul class="matrix">
            <? foreach ($more_tags as $tag) : ?>
                <li>
                    <? $new_breadcrump_tags = $breadcrump_tags;
                    $new_breadcrump_tags[] = $tag['tag_hash'];
                    ?>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => implode(",", $new_breadcrump_tags)), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($tag['name']) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ul>
    </div>

    <? if ($materialien) : ?>
    <ul class="material_overview">
        <? foreach ($materialien as $material) : ?>
            <?= $this->render_partial("market/_material_short.php", compact("material", "plugin")) ?>
        <? endforeach ?>
    </ul>
    <? else : ?>
        <?= MessageBox::info(_("Keine Materialien gefunden")) ?>
    <? endif ?>
<? elseif ($materialien) : ?>
    <ul class="material_overview">
        <? foreach ($materialien as $material) : ?>
            <?= $this->render_partial("market/_material_short.php", compact("material", "plugin")) ?>
        <? endforeach ?>
    </ul>
<? else : ?>
    <form action="<?= PluginEngine::getLink($plugin, array(), "market/overview") ?>" method="GET" style="text-align: center;">
        <div>
            <input type="text" name="search" value="" style="line-height: 130%; display: inline-block; border: 1px solid #28497c; vertical-align: middle; padding: 5px 5px; font-size: 14px;" placeholder="<?= _("Mathematik, Jura ...") ?>">
            <?= \Studip\Button::create(_("Suchen")) ?>
        </div>

        <a href="<?= PluginEngine::getLink($plugin, array('search' => "%"), "market/overview") ?>">
            <?= _("alle anzeigen") ?>
        </a>
    </form>

    <? if ($best_nine_tags[0]['name']) : ?>
        <table class="default nohover">
            <caption><?= _("Themenmatrix") ?></caption>
            <tbody>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[0]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[0]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[1]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[1]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[2]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[2]['name']) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[3]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[3]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[4]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[4]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[5]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[5]['name']) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[6]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[6]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[7]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[7]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tags' => $best_nine_tags[8]['tag_hash']), "market/overview") ?>">
                        <?= Assets::img("icons/16/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($best_nine_tags[8]['name']) ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    <? endif ?>
<? endif ?>






<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
$actions = new ActionsWidget();
$actions->addLink(
    _("Eigenes Lehrmaterial hochladen"),
    PluginEngine::getURL($plugin, array(), "market/edit"),
    Assets::image_path("icons/blue/add"),
    array('data-dialog' => "1")
);

Sidebar::Get()->addWidget($actions);