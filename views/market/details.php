<h1><?= htmlReady($material['name']) ?></h1>

<div>
    <?= formatReady($material['description'] ?: $material['short_description']) ?>
</div>

<a class="download_link" href="<?= $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : PluginEngine::getLink($plugin, array(), "market/download/".$material->getId()) ?>">
    <?= Assets::img("icons/40/blue/download") ?>
    <div class="filename"><?= htmlReady($material['filename']) ?></div>
</a>

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
<div class="author_information">
    <? if ($material['host_id']) : ?>
        <? $user = $material['host_id'] ? MarketUser::find($material['user_id']) : User::find($material['user_id']) ?>
        <? $image = $material['host_id'] ? $user['avatar'] : Avatar::getAvatar($material['user_id']) ?>
        <div class="avatar" style="background-image: url('<?= $image ?>');"></div>
        <div>
            <strong><?= htmlReady($user['name']) ?></strong>
            <div><i><?= htmlReady($material->host->name) ?></i></div>
            <div class="description"><?= formatReady($user['description']) ?></div>
        </div>
    <? else : ?>
        <? $user = User::find($material['user_id']) ?>
        <? $image = Avatar::getAvatar($material['user_id'])->getURL(Avatar::MEDIUM) ?>
        <div class="avatar" style="background-image: url('<?= $image ?>');"></div>
        <div>
            <div><?= htmlReady($user->getFullName()) ?></div>
            <div><i><?= htmlReady($GLOBALS['UNI_NAME_CLEAN']) ?></i></div>
            <div class="description"><?
                $user_description_datafield = DataField::find(get_config("LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LEHRMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
                if ($user_description_datafield) {
                    $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($user['user_id'], $user_description_datafield->getId()));
                    echo $datafield_entry && $datafield_entry['content'] ? formatReady($datafield_entry['content']) : "";
                }
                ?></div>
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