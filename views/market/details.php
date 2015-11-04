<h1><?= htmlReady($material['name']) ?></h1>

<div style="text-align: center;">
    <a href="<?= PluginEngine::getLink($plugin, array(), "market/download/".$material->getId()) ?>"><?= Assets::img("icons/40/blue/download") ?></a>
</div>

<? if ($material->isFolder()) : ?>
    <h2><?= _("Verzeichnisstruktur") ?></h2>
    <ol class="lehrmarktplatz structure">
        <? foreach ($material['structure'] as $filename => $file) : ?>
            <?= $this->render_partial("market/_details_file.php", array('name' => $filename, 'file' => $file)) ?>
        <? endforeach ?>
    </ol>
<? endif ?>

<? $tags = $material->getTags() ?>
<? if (count($tags) > 0) : ?>
    <div class="tags">
        <h2><?= _("Themen") ?></h2>
        <ul class="clean">
            <? foreach ($tags as $tag) : ?>
                <li><?= htmlReady($tag['name']) ?></li>
            <? endforeach ?>
        </ul>
    </div>
<? endif ?>

<div class="license" style="text-align: center;">
    <?= _("Lizenz:") ?>
    <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank">
        <img src="<?= $plugin->getPluginURL()."/assets/cc-by.png" ?>">
    </a>
</div>

<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
$actions = new ActionsWidget();
$actions->addLink(
    _("Eigenes Lehrmaterial hochladen"),
    PluginEngine::getURL($plugin, array(), "market/edit"),
    Assets::image_path("icons/blue/add"),
    array('data-dialog' => "1")
);
if ($material['user_id'] === $GLOBALS['user']->id) {
    $actions->addLink(
        _("Schlagworte oder Themen hinzufügen"),
        PluginEngine::getURL($plugin, array('material_id' => $material->getId()), "mymaterial/add_tags"),
        Assets::image_path("icons/blue/add"),
        array('data-dialog' => "1")
    );
}

Sidebar::Get()->addWidget($actions);