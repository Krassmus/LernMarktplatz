<h1><?= htmlReady($material['name']) ?></h1>

<div>
    <?= formatReady($material['description'] ?: $material['short_description']) ?>
</div>

<div style="text-align: center;">
    <a href="<?= $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : PluginEngine::getLink($plugin, array(), "market/download/".$material->getId()) ?>"><?= Assets::img("icons/40/blue/download") ?></a>
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
                <li>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $tag), "market/overview") ?>">
                        <?= Assets::img("icons/20/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($tag['name']) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ul>
    </div>
<? endif ?>

<h2><?= _("Zum Autor") ?></h2>
<div style="display: flex;">
    <? if ($material['host_id']) : ?>
        <? $user = $material['host_id'] ? MarketUser::find($material['user_id']) : User::find($material['user_id']) ?>
        <? $image = $material['host_id'] ? $user['avatar'] : Avatar::getAvatar($material['user_id']) ?>
        <div style="background: url('<?= $image ?>') center center no-repeat; background-size: 100% 100%; width: 100px; height: 100px;"></div>
        <div>
            <?= htmlReady($user['name']) ?>
            <div><i><?= htmlReady($material->host->name) ?></i></div>
        </div>
    <? else : ?>
        <? $user = User::find($material['user_id']) ?>
        <? $image = Avatar::getAvatar($material['user_id'])->getURL(Avatar::MEDIUM) ?>
        <div style="background: url('<?= $image ?>') center center no-repeat; background-size: 100% 100%; width: 100px; height: 100px;"></div>
        <div>
            <div><?= htmlReady($user->getFullName()) ?></div>
            <div><i><?= htmlReady($GLOBALS['UNI_NAME_CLEAN']) ?></i></div>
        </div>
    <? endif ?>
</div>


<div class="license" style="text-align: center;">
    <?= _("Lizenz:") ?>
    <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank">
        <img src="<?= $plugin->getPluginURL()."/assets/cc-by.png" ?>">
    </a>
</div>

<div style="text-align: center;">
    <? if (!$material['host_id'] && $material['user_id'] === $GLOBALS['user']->id) : ?>
        <?= \Studip\LinkButton::create(_("Bearbeiten"), PluginEngine::getURL($plugin, array(), "market/edit/".$material->getId()), array('data-dialog' => "1")) ?>
    <? endif ?>
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