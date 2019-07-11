<table class="default nohover">
    <tbody>
        <tr>
            <td><?= _("Gesamtzugriffe") ?></td>
            <td><?= htmlReady($counter) ?></td>
        </tr>
        <tr>
            <td><?= _("Heute") ?></td>
            <td><?= htmlReady($counter_today) ?></td>
        </tr>
    </tbody>
</table>


<div data-dialog-button>
    <?= \Studip\LinkButton::create(
        _("Exportieren"),
        PluginEngine::getURL($plugin, array('export' => 1), "mymaterial/statistics/".$material->getId())
    ) ?>
</div>
