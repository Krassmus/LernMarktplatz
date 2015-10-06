<div class="material_overview">
    <? foreach ($materialien as $material) : ?>
        <?= $this->render_partial("presenting/_material_short.php", compact("material", "plugin")) ?>
    <? endforeach ?>
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