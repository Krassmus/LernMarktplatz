<?= $this->render_partial("mymaterial/_material_list.php") ?>

<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
$actions = new ActionsWidget();
$actions->addLink(
    _("Eigenes Lernmaterial hochladen"),
    PluginEngine::getURL($plugin, array(), "mymaterial/edit"),
    Icon::create("add", "clickable"),
    array('data-dialog' => "1")
);

Sidebar::Get()->addWidget($actions);