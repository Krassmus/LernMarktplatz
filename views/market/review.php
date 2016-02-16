<form action="<?= PluginEngine::getLink($plugin, array(), "market/review/".$material->getId()) ?>" method="post" class="default">
    <select name="rating">
        <option value="0">0 <?= _("Sterne") ?></option>
        <option value="2">1 <?= _("Stern") ?></option>
        <option value="4">2 <?= _("Sterne") ?></option>
        <option value="6">3 <?= _("Sterne") ?></option>
        <option value="8">4 <?= _("Sterne") ?></option>
        <option value="10">5 <?= _("Sterne") ?></option>
    </select>

    <textarea name="review"><?= htmlReady($review['review']) ?></textarea>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Absenden")) ?>
    </div>
</form>