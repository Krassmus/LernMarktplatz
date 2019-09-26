<form class="default">
    <? $base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>
    <label>
        <?= _("Platzhalter zum Teilen in Stud.IP (Forum, Blubber, Wiki, Ankündigung)") ?>
        <input type="text" readonly value="[oermaterial]<?= htmlReady($material->getId()) ?>">
    </label>

    <? $roles = (array_map(function ($role) { return $role->getRolename(); }, RolePersistence::getAssignedPluginRoles($plugin->getPluginId()))) ?>
    <? if (in_array("Nobody", $roles)) : ?>
        <label>
            <?= _("Teilen als Link") ?>
            <input type="text" readonly value="<?= PluginEngine::getLink($plugin, array(), "market/details/".$material->getId()) ?>">
        </label>
    <? endif ?>

    <? if ($material['player_url'] || $material->isPDF()) : ?>
        <?
        if ($material['player_url']) {
            $url = $material['player_url'];
        } elseif ($material->isPDF()) {
            $url = $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : URLHelper::getURL("plugins.php/lernmarktplatz/market/download/".$material->getId());;
        }
        ?>
        <label>
            <?= _("Teilen als HTML-Schnipsel") ?>
            <textarea readonly><?= htmlReady('<iframe src="'.htmlReady($url).'"></iframe>') ?></textarea>
        </label>

    <? endif ?>
    <? URLHelper::setBaseURL($base) ?>
</form>