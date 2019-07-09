<? if ($materialien) : ?>
    <ul class="material_overview mainlist">
        <?= $this->render_partial("market/_materials.php", compact("material", "plugin")) ?>
    </ul>
<? else : ?>
    <?= MessageBox::info(_("Keine Materialien gefunden")) ?>
<? endif ?>