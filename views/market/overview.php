<? if (!Request::get("tags") && !Request::get("search") && !Request::get("tag") && !UserConfig::get($GLOBALS['user']->id)->LERNMARKTPLATZ_DISABLE_MAININFO) : ?>
    <?= $this->render_partial("market/_maininfo.php") ?>
<? endif ?>
<? if (Request::get("tags")) : ?>

    <div class="material_navigation">
        <?= $this->render_partial("market/_breadcrumb.php", array('plugin' => $plugin, 'tag_history' => $tag_history)) ?>
        <?= $this->render_partial("market/_matrix.php", array('tags' => $breadcrump_tags, 'topics' => $more_tags, 'tag_history' => $tag_history)) ?>
    </div>

    <? if ($materialien) : ?>
    <ul class="material_overview">
        <?= $this->render_partial("market/_materials.php", compact("material", "plugin")) ?>
    </ul>
    <? else : ?>
        <?= MessageBox::info(_("Keine Materialien gefunden")) ?>
    <? endif ?>
<? elseif ($materialien) : ?>
    <ul class="material_overview">
        <?= $this->render_partial("market/_materials.php", compact("material", "plugin")) ?>
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

    <? if (count($best_nine_tags)) : ?>
        <div class="material_navigation">
            <?= $this->render_partial("market/_breadcrumb.php", array('plugin' => $plugin, 'tag_history' => $tag_history)) ?>
            <?= $this->render_partial("market/_matrix.php", array('topics' => $best_nine_tags)) ?>
        </div>
    <? endif ?>

    <ul class="material_overview"></ul>

<? endif ?>






<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _("Eigenes Lernmaterial hochladen"),
        PluginEngine::getURL($plugin, array(), "mymaterial/edit"),
        Assets::image_path("icons/blue/add"),
        array('data-dialog' => "1")
    );
    Sidebar::Get()->addWidget($actions);
}