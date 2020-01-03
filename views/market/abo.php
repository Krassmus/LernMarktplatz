<form action="<?= PluginEngine::getLink($plugin, array(), "market/abo") ?>"
      method="post">

    <input type="hidden" name="abo" value="0">

    <label>
        <input type="checkbox" name="abo" value="1"<?= $abo ? " checked" : "" ?>>
        <?= sprintf(_("Ich möchte Nachrichten bekommen über neue Inhalte im %s"), Config::get()->LERNMARKTPLATZ_TITLE) ?>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>

</form>