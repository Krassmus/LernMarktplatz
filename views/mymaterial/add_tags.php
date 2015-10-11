<form action="<?= PluginEngine::getLink($plugin, array(), "mymaterial/add_tags") ?>" method="post" class="studip_form">
    <input type="text" name="tag" placeholder="<?= _("Thema oder Schlagwort ...") ?>">
    <input type="hidden" name="material_id" value="<?= $this->material->getId() ?>">

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Hinzufügen")) ?>
    </div>
</form>