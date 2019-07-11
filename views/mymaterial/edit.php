<form action="<?= PluginEngine::getLink($plugin, array(), 'mymaterial/edit/'.$material->getId()) ?>" method="post" class="default" enctype="multipart/form-data">
    <label>
        <?= _("Name") ?>
        <input type="text" name="data[name]" value="<?= htmlReady($material['name'] ?: $template['name']) ?>" maxlength="64">
    </label>

    <label>
        <?= _("Kurzbeschreibung") ?>
        <input type="text" name="data[short_description]" value="<?= htmlReady($material['short_description'] ?: $template['short_description']) ?>">
    </label>

    <label>
        <?= _("Beschreibung") ?>
        <textarea name="data[description]"><?= htmlReady($material['description'] ?: $template['description']) ?></textarea>
    </label>

    <label>
        <?= _("Vorschau-URL (optional)") ?>
        <input type="text" name="data[player_url]" value="<?= htmlReady($material['player_url'] ?: $template['player_url']) ?>">
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
            <? foreach ((array) $template['tags'] as $tag) : ?>
                <li>
                    <?= Icon::create("topic", "info")->asImg("20px", array('class' => "text-bottom")) ?>
                    <input type="text" name="tags[]" value="<?= htmlReady($tag) ?>" style="max-width: calc(100% - 30px);">
                </li>
            <? endforeach ?>
            <li>
                <?= Icon::create("topic", "info")->asImg("20px", array('class' => "text-bottom")) ?>
                <input type="text" name="tags[]" value="<?= htmlReady($tag['name']) ?>" style="max-width: calc(100% - 30px);">
            </li>
        </ul>
    </div>

    <? if ($template['tmp_file']) : ?>
        <input type="hidden" name="tmp_file" value="<?= htmlReady($template['tmp_file']) ?>">
        <input type="hidden" name="filename" value="<?= htmlReady($template['filename']) ?>">
    <? else : ?>
        <label class="file-upload" style="margin-top: 20px;">
            <?= _("Datei (gerne auch eine ZIP) auswählen") ?>
            <input type="file" name="file">
        </label>
    <? endif ?>

    <? if ($template['tmp_file']) : ?>
        <input type="hidden" name="logo_tmp_file" value="<?= htmlReady($template['logo_tmp_file']) ?>">
    <? else : ?>
        <label class="file-upload" style="margin-top: 20px;">
            <?= _("Logo-Bilddatei (optional)") ?>
            <input type="file" name="image" accept="image/*">
        </label>
    <? endif ?>

    <? if ($template['module_id']) : ?>
        <input type="hidden" name="module_id" value="<?= htmlReady($template['module_id']) ?>">
    <? endif ?>

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