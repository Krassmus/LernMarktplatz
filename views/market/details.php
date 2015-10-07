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

<div class="license" style="text-align: center;">
    <?= _("Lizenz:") ?>
    <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank">
        <img src="<?= $plugin->getPluginURL()."/assets/cc-by.png" ?>">
    </a>
    <div>
        <?= _("Frei zum Benutzen, Weitergeben, Verändern und Wiederveröffentlichen, wenn der Autor jeweils genannt wird.") ?>
    </div>
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

Sidebar::Get()->addWidget($actions);