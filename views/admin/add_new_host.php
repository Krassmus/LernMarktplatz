<form class="studip_form" action="<?= PluginEngine::getLink($plugin, array(), "admin/add_new_host") ?>" method="post">
    <label>
        <?= _("Adresse des Servers plugin.php....") ?>
        <input type="text" name="url" placeholder="http://www.myserver.de/studip/plugins.php/lehrmarktplatz/endpoints/">
    </label>

    <div style="text-align: center;" data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>