<form action="<?= PluginEngine::getLink($plugin, array(), 'mymaterial/edit/'.$material->getId()) ?>" method="post" class="default" enctype="multipart/form-data">
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
        <ul class="clean lernmarktplatz_tags" style="margin-top: 10px;">
            <? foreach ($material->getTopics() as $tag) : ?>
            <li>
                <?= Icon::create("topic", "info")->asImg("20px", array('class' => "text-bottom")) ?>
                <input type="text" name="tags[]" value="<?= htmlReady($tag['name']) ?>" style="max-width: calc(100% - 30px);">
            </li>
            <? endforeach ?>
            <li>
                <?= Icon::create("topic", "info")->asImg("20px", array('class' => "text-bottom")) ?>
                <input type="text" name="tags[]" value="<?= htmlReady($tag['name']) ?>" style="max-width: calc(100% - 30px);">
            </li>
        </ul>
    </div>

    <label class="file-upload" style="margin-top: 20px;">
        <?= _("Datei (gerne auch eine ZIP) auswählen") ?>
        <input type="file" name="file"<? $material->isNew() ? "required" : "" ?>>
    </label>

    <label class="file-upload" style="margin-top: 20px;">
        <?= _("Logo-Bilddatei (optional)") ?>
        <input type="file" name="image" accept="image/*">
    </label>

    <? if ($material['front_image_content_type']) : ?>
        <label>
            <input type="checkbox" name="delete_front_image" value="1">
            <?= _("Logo löschen") ?>
        </label>
    <? endif ?>

    <? if ($material->isNew()) : ?>
        <div style="margin-top: 20px;">
            <?= sprintf(
                _("Ich erkläre mich bereit, dass meine Lernmaterialien unter der %s Lizenz an alle Nutzer freigegeben werden. Ich bestätige zudem, dass ich das Recht habe, diese Dateien frei zu veröffentlichen, weil entweder ich selbst sie angefertigt habe, oder sie von anderen Quellen mit ähnlicher Lizenz stammen."),
                '<a href="https://creativecommons.org/licenses/by-sa/3.0/de/" target="_blank">'.Icon::create("link-extern", "clickable")->asImg("20px", array('class' => "text-bottom")).' CC BY SA 3.0</a>'
            ) ?>
        </div>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create($material->isNew() ? _("Hochladen") : _("Speichern")) ?>
    </div>
</form>