<h1><?= htmlReady($material['name']) ?></h1>

<div style="text-align: center;">
    <a href="<?= PluginEngine::getLink($plugin, array(), "market/download/".$material->getId()) ?>"><?= Assets::img("icons/40/blue/download") ?></a>
</div>

<? if ($material->isFolder()) : ?>
<ol class="lehrmarktplatz structure">
    <? foreach ($material['structure'] as $filename => $file) : ?>
        <?= $this->render_partial("market/_details_file.php", array('name' => $filename, 'file' => $file)) ?>
    <? endforeach ?>
</ol>
<? endif ?>

<div class="license" style="text-align: center;">
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

Sidebar::Get()->addWidget($actions);