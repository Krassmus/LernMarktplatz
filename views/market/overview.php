<table>
    <tbody>
        <tr>
            <td><?= htmlReady($best_nine_tags[0]['name']) ?></td>
            <td><?= htmlReady($best_nine_tags[1]['name']) ?></td>
            <td><?= htmlReady($best_nine_tags[2]['name']) ?></td>
        </tr>
        <tr>
            <td><?= htmlReady($best_nine_tags[3]['name']) ?></td>
            <td><?= htmlReady($best_nine_tags[4]['name']) ?></td>
            <td><?= htmlReady($best_nine_tags[5]['name']) ?></td>
        </tr>
        <tr>
            <td><?= htmlReady($best_nine_tags[6]['name']) ?></td>
            <td><?= htmlReady($best_nine_tags[7]['name']) ?></td>
            <td><?= htmlReady($best_nine_tags[8]['name']) ?></td>
        </tr>
    </tbody>
</table>

<ul class="material_overview">
    <? foreach ($materialien as $material) : ?>
        <?= $this->render_partial("market/_material_short.php", compact("material", "plugin")) ?>
    <? endforeach ?>
</ul>


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