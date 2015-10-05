<table class="default">
    <caption>
        <?= _("Lehrmarktplatz-Server") ?>
    </caption>
    <thead>
        <tr>
            <th><?= _("Name") ?></th>
            <th title="<?= _("Dies ist der Hash des Public-Keys des Servers.") ?>"><?= _("Adresse") ?></th>
            <th><?= _("Key-Hash") ?></th>
            <th><?= _("Index-Server") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach (MarketHost::findAll() as $host) : ?>
            <tr id="host_<?= $host->getId() ?>">
                <td>
                    <? if ($host->isMe()) : ?>
                        <?= Assets::img("icons/16/black/home", array('class' => "text-bottom")) ?>
                    <? endif ?>
                    <?= htmlReady($host['name']) ?></td>
                <td>
                    <a href="<?= htmlReady($host['url']) ?>" target="_blank">
                        <?= Assets::img("icons/16/blue/link-extern", array('class' => "text-bottom")) ?>
                        <?= htmlReady($host['url']) ?>
                    </a>
                </td>
                <td><?= $host['public_key'] ? md5($host['public_key']) : "" ?></td>
                <td>
                    <?= Assets::img("icons/20/black/checkbox-".($host['index_server'] ? "" : "un")."checked") ?>
                </td>
                <td>
                    <? if (!$host->isMe()) : ?>
                        <a href="<?= PluginEngine::getLink($plugin, array(), "admin/ask_for_hosts/".$host->getId()) ?>" title="<?= _("Diesen Server nach weiteren bekannten Hosts fragen.") ?>">
                            <?= Assets::img("icons/16/blue/download", array('class' => "text-bottom")) ?>
                        </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>


<?
$actions = new ActionsWidget();
$actions->addLink(
    _("Server hinzufügen"),
    PluginEngine::getURL($plugin, array(), "admin/add_new_host"),
    Assets::image_path("icons/blue/add"),
    array('data-dialog' => "1")
);

Sidebar::Get()->addWidget($actions);