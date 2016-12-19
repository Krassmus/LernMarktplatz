<?= $this->render_partial("mymaterial/_material_list.php") ?>

<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
$actions = new ActionsWidget();
$actions->addLink(
    _("Eigenes Lernmaterial hochladen"),
    PluginEngine::getURL($plugin, array(), "mymaterial/edit"),
    Assets::image_path("icons/blue/add"),
    array('data-dialog' => "1")
);

Sidebar::Get()->addWidget($actions);