<?= $this->render_partial("market/_searcharea.php") ?>

<? if ($materialien) : ?>
    <ul class="material_overview mainlist">
        <?= $this->render_partial("market/_materials.php", compact("material", "plugin")) ?>
    </ul>
<? else : ?>
    <?= MessageBox::info(_("Keine Materialien gefunden")) ?>
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
