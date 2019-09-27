<? if (!Request::get("tags") && !Request::get("search") && !Request::get("tag")) : ?>
    <div class="shortcuts file_select_possibilities">
        <a href="<?= PluginEngine::getLink($plugin, array('type' => "audio"), "market/search") ?>">
            <?= Icon::create($plugin->getPluginURL()."/assets/audio.svg", "clickable")->asImg(50) ?>
            <?= _("Audio") ?>
        </a>
        <a href="<?= PluginEngine::getLink($plugin, array('type' => "video"), "market/search") ?>">
            <?= Icon::create($plugin->getPluginURL()."/assets/video.svg", "clickable")->asImg(50) ?>
            <?= _("Videos") ?>
        </a>
        <a href="<?= PluginEngine::getLink($plugin, array('type' => "presentation"), "market/search") ?>">
            <?= Icon::create($plugin->getPluginURL()."/assets/presentation.svg", "clickable")->asImg(50) ?>
            <?= _("Folien") ?>
        </a>
        <a href="<?= PluginEngine::getLink($plugin, array('type' => "learningmodules"), "market/search") ?>">
            <?= Icon::create($plugin->getPluginURL()."/assets/eLearning.svg", "clickable")->asImg(50) ?>
            <?= _("Lernmodule") ?>
        </a>
        <a href="<?= PluginEngine::getLink($plugin, array('search' => "%"), "market/search") ?>">
            <?= Icon::create($plugin->getPluginURL()."/assets/dateien.svg", "clickable")->asImg(50) ?>
            <?= _("Alles") ?>
        </a>
    </div>
<? endif ?>

<? if (Request::get("tags")) : ?>

    <div class="material_navigation">
        <?= $this->render_partial("market/_breadcrumb.php", array('plugin' => $plugin, 'tag_history' => $tag_history)) ?>
        <?= $this->render_partial("market/_matrix.php", array('tags' => $breadcrump_tags, 'topics' => $more_tags, 'tag_history' => $tag_history)) ?>
    </div>

    <? if ($materialien) : ?>
    <ul class="material_overview mainlist">
        <?= $this->render_partial("market/_materials.php", compact("material", "plugin")) ?>
    </ul>
    <? else : ?>
        <?= MessageBox::info(_("Keine Materialien gefunden")) ?>
    <? endif ?>
<? elseif ($materialien) : ?>

    <?= $this->render_partial("market/_searcharea.php") ?>

    <ul class="material_overview mainlist">
        <?= $this->render_partial("market/_materials.php", compact("material", "plugin")) ?>
    </ul>
<? else : ?>

    <?= $this->render_partial("market/_searcharea.php") ?>

    <? if (count($best_nine_tags)) : ?>
        <div class="material_navigation">
            <?= $this->render_partial("market/_breadcrumb.php", array('plugin' => $plugin, 'tag_history' => $tag_history)) ?>
            <?= $this->render_partial("market/_matrix.php", array('topics' => $best_nine_tags)) ?>
        </div>
    <? endif ?>

    <ul class="material_overview mainlist">
    </ul>

    <? if ($new_ones) : ?>
        <div id="new_ones">
            <h2><?= _("Neuste Materialien") ?></h2>
            <ul class="material_overview">
                <?= $this->render_partial("market/_materials.php", array('materialien' => $new_ones, 'plugin' => $plugin)) ?>
            </ul>
        </div>
    <? endif ?>

<? endif ?>






<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _("Eigenes Lernmaterial hochladen"),
        PluginEngine::getURL($plugin, array(), "mymaterial/edit"),
        Icon::create("add", "clickable"),
        array('data-dialog' => "1")
    );
    Sidebar::Get()->addWidget($actions);
}