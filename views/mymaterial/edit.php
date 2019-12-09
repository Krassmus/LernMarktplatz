<form action="<?= PluginEngine::getLink($plugin, array(), 'mymaterial/edit/'.$material->getId()) ?>"
      method="post"
      class="default oercampus_editmaterial"
      enctype="multipart/form-data">
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
        <input type="hidden" name="data[draft]" value="0">
        <input type="checkbox" name="data[draft]" value="1"<?= $material['draft'] ? " checked" : "" ?>>
        <?= _("Entwurf (nicht veröffentlicht)") ?>
    </label>

    <label>
        <?= _("Vorschau-URL (optional)") ?>
        <input type="text" name="data[player_url]" value="<?= htmlReady($material['player_url'] ?: $template['player_url']) ?>">
    </label>

    <? if (!$material->isNew()) : ?>
    <div style="margin-top: 10px;">
        <?= _("Autoren") ?>
        <ul class="clean autoren" style="margin-top: 10px;">
            <? foreach ($material->users as $materialuser) : ?>
                <li>
                    <? if ($materialuser['external_contact']) : ?>
                        <? $user = $materialuser['lernmarktplatzuser'] ?>
                        <? $image = $user['avatar'] ?>
                        <label>
                            <? if (count($material->users) > 1) : ?>
                                <input type="checkbox" name="remove_users[]" value="1_<?= htmlReady($user->getId()) ?>">
                            <? endif ?>
                            <div>
                                <span class="avatar" style="background-image: url('<?= $image ?>');"></span>
                                <span class="author_name">
                                    <?= htmlReady($user['name']) ?>
                                </span>
                            </div>
                        </label>
                    <? else : ?>
                        <? $user = User::find($materialuser['user_id']) ?>
                        <? $image = Avatar::getAvatar($materialuser['user_id'])->getURL(Avatar::SMALL) ?>
                        <label>
                            <? if (count($material->users) > 1) : ?>
                                <input type="checkbox" name="remove_users[]" value="0_<?= htmlReady($user->getId()) ?>">
                            <? endif ?>
                            <div>
                                <span class="avatar" style="background-image: url('<?= $image ?>');"></span>
                                <span class="author_name">
                                    <?= htmlReady($user ? $user->getFullName() : _("unbekannt")) ?>
                                </span>
                            </div>
                        </label>
                    <? endif ?>
                </li>
            <? endforeach ?>
            <li>
                <?= QuickSearch::get("new_user", $usersearch)->render() ?>
            </li>
        </ul>
    </div>
    <? endif ?>

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

    <div style="margin-top: 13px;">
        <?= _("Niveau") ?>

        <input type="hidden" id="difficulty_start" name="data[difficulty_start]" value="<?= htmlReady($material['difficulty_start']) ?>">
        <input type="hidden" id="difficulty_end" name="data[difficulty_end]" value="<?= htmlReady($material['difficulty_end']) ?>">

        <div style="display: flex; justify-content: space-between; font-size: 0.8em; color: grey;">
            <div><?= _("Kindergarten") ?></div>
            <div><?= _("Aktuelle Forschung") ?></div>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <? for ($i = 1; $i <= 12; $i++) : ?>
                <div><?= ($i < 10 ? "&nbsp;" : "").$i ?></div>
            <? endfor ?>
        </div>
        <div id="difficulty_slider_edit" style="margin-left: 5px; margin-right: 9px;"></div>

        <script>
            jQuery(function () {
                jQuery("#difficulty_slider_edit").slider({
                    range: true,
                    min: 1,
                    max: 12,
                    values: [jQuery("#difficulty_start").val(), jQuery("#difficulty_end").val()],
                    change: function (event, ui) {
                        jQuery("#difficulty_start").val(ui.values[0]);
                        jQuery("#difficulty_end").val(ui.values[1]);
                    }
                });
            });
        </script>
    </div>

    <? if ($template['tmp_file']) : ?>
        <input type="hidden" name="tmp_file" value="<?= htmlReady($template['tmp_file']) ?>">
        <input type="hidden" name="mime_type" value="<?= htmlReady($template['mime_type']) ?>">
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
        <? if (!Config::get()->LERNMARKTPLATZ_DISABLE_LICENSE) : ?>
            <div style="margin-top: 20px;">
                <?= sprintf(
                    _("Ich erkläre mich bereit, dass meine Lernmaterialien unter der %s Lizenz an alle Nutzenden freigegeben werden. Ich bestätige zudem, dass ich das Recht habe, diese Dateien frei zu veröffentlichen, weil entweder ich selbst sie angefertigt habe, oder sie von anderen Quellen mit ähnlicher Lizenz stammen."),
                    '<a href="https://creativecommons.org/licenses/by-sa/3.0/de/" target="_blank">'.Icon::create("link-extern", "clickable")->asImg("20px", array('class' => "text-bottom")).' CC BY SA 3.0</a>'
                ) ?>
            </div>
        <? endif ?>
        <? if ($template['redirect_url']) : ?>
            <input type="hidden" name="redirect_url" value="<?= htmlReady($template['redirect_url']) ?>">
        <? endif ?>
    <? endif ?>

    <div data-dialog-button>
        <?= \Studip\Button::create($material->isNew() ? _("Hochladen") : _("Speichern")) ?>
    </div>
</form>