<form action="<?= PluginEngine::getLink($plugin, array(), 'market/edit/'.$material->getId()) ?>" method="post" class="studip_form" enctype="multipart/form-data">
    <label>
        <?= _("Name") ?>
        <input type="text" name="data[name]" value="<?= htmlReady($material['name']) ?>">
    </label>

    <label>
        <?= _("Kurzbeschreibung") ?>
        <input type="text" name="data[short_description]" value="<?= htmlReady($material['short_description']) ?>">
    </label>

    <label>
        <?= _("Beschreibung") ?>
        <textarea name="data[description]"><?= htmlReady($material['description']) ?></textarea>
    </label>

    <label style="cursor: pointer;">
        <?= Assets::img("icons/40/blue/upload") ?>
        <?= _("Datei (gerne auch eie ZIP) auswählen") ?>
        <input type="file" name="file" style="display: none;" required>
    </label>

    <? if ($material->isNew()) : ?>
        <label>
            <input type="checkbox" name="agreement" required value="1">
            <?= sprintf(
                _("Ich erkläre mich bereit, dass meine Lehrmaterialien unter der %s Lizenz an alle Nutzer freigegeben werden. Ich bestätige zudem, dass ich das Recht habe, diese Dateien frei zu veröffentlichen, weil entweder ich selbst sie angefertigt habe, oder sie von anderen Quellen mit ähnlicher Lizenz stammen."),
                '<a href="http://creativecommons.org/licenses/by/4.0/" target="_blank">'.Assets::img("icons/16/blue/link-extern", array('class' => "text-bottom")).' CC BY 4.0</a>'
            ) ?>

        </label>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create($material->isNew() ? _("Hochladen") : _("Speichern")) ?>
    </div>
</form>