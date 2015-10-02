<div class="material_overview">
    <? foreach ($materialien as $material) : ?>
        <?= $this->render_partial("presenting/_material_short.php", compact("material", "plugin")) ?>
    <? endforeach ?>
</div>