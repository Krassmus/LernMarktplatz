<? foreach ($materialien as $material) : ?>
    <?= $this->render_partial("market/_material_short.php", compact("material", "plugin")) ?>
<? endforeach ?>