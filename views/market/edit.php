<form action="<?= PluginEngine::getLink($plugin, array(), 'market/edit/'.$material->getId()) ?>" method="post" class="default" enctype="multipart/form-data">
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

    <div style="margin-top: 10px;">
        <?= _("Themen (am besten mindestens 5)") ?>
        <ul class="clean lehrmarktplatz_tags" style="margin-top: 10px;">
            <? foreach ($material->getTags() as $tag) : ?>
            <li>
                <?= Assets::img("icons/20/black/topic", array('class' => "text-bottom")) ?>
                <input type="text" name="tags[]" value="<?= htmlReady($tag['name']) ?>">
            </li>
            <? endforeach ?>
            <li>
                <?= Assets::img("icons/20/black/topic", array('class' => "text-bottom")) ?>
                <input type="text" name="tags[]" value="<?= htmlReady($tag['name']) ?>">
            </li>
        </ul>
    </div>

    <label class="file-upload" style="margin-top: 20px;">
        <?= _("Datei (gerne auch eine ZIP) auswählen") ?>
        <input type="file" name="file" required>
    </label>

    <? if ($material->isNew()) : ?>
        <div style="margin-top: 20px;">
            <?= sprintf(
                _("Ich erkläre mich bereit, dass meine Lehrmaterialien unter der %s Lizenz an alle Nutzer freigegeben werden. Ich bestätige zudem, dass ich das Recht habe, diese Dateien frei zu veröffentlichen, weil entweder ich selbst sie angefertigt habe, oder sie von anderen Quellen mit ähnlicher Lizenz stammen."),
                '<a href="http://creativecommons.org/licenses/by/4.0/" target="_blank">'.Assets::img("icons/16/blue/link-extern", array('class' => "text-bottom")).' CC BY 4.0</a>'
            ) ?>
        </div>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create($material->isNew() ? _("Hochladen") : _("Speichern")) ?>
    </div>
</form>