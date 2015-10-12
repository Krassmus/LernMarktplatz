<? if ($more_tags) : ?>
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
            <caption><?= _("Schlagwortsuche") ?></caption>
            <tbody>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[0]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[0]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[1]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[1]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[2]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[2]['name']) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[3]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[3]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[4]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[4]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[5]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[5]['name']) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[6]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[6]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[7]['name']), "market/overview") ?>">
                        <?= htmlReady($best_nine_tags[7]['name']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $best_nine_tags[8]['name']), "market/overview") ?>">
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