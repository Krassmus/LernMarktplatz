<table class="default">
    <caption>
        <?= _("Lehrmarktplatz-Server") ?>
    </caption>
    <thead>
        <tr>
            <th><?= _("Name") ?></th>
            <th><?= _("Adresse") ?></th>
            <th title="<?= _("Ein Hash des Public-Keys des Servers.") ?>"><?= _("Key-Hash") ?></th>
            <th><?= _("Index-Server") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($hosts as $host) : ?>
            <tr id="host_<?= $host->getId() ?>">
                <td>
                    <? if ($host->isMe()) : ?>
                        <?= Assets::img("icons/16/black/home", array('class' => "text-bottom", 'title' => _("Das ist Ihr Stud.IP"))) ?>
                    <? endif ?>
                    <?= htmlReady($host['name']) ?></td>
                <td>
                    <a href="<?= htmlReady($host['url']) ?>" target="_blank">
                        <?= Assets::img("icons/16/blue/link-extern", array('class' => "text-bottom")) ?>
                        <?= htmlReady($host['url']) ?>
                    </a>
                </td>
                <td><?= $host['public_key'] ? md5($host['public_key']) : "" ?></td>
                <td class="<?= $host['index_server'] ? "index_server" : "non_index_server" ?>">
                    <? if ($host->isMe()) : ?>
                        <a href="" onClick="return false;" title="<?= _("Als Index-Server aktivieren/deaktivieren") ?>">
                            <?= Assets::img("icons/20/blue/checkbox-".($host['index_server'] ? "" : "un")."checked") ?>
                        </a>
                    <? else : ?>
                        <?= Assets::img("icons/20/black/checkbox-".($host['index_server'] ? "" : "un")."checked") ?>
                    <? endif ?>
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

<? if (count($hosts) < 2) : ?>
    <div id="init_first_hosts_dialog" style="display: none;">
        <form action="<?= PluginEngine::getLink($plugin, array(), "admin/add_new_host") ?>" method="post">
            <h2><?= _("Werden Sie Teil des weltweiten Stud.IP Lehrmarktplatzes!") ?></h2>
            <div>
                <?= _("Der Lehrmarktplatz ist ein Ort des Austauschs von kostenlosen und freien Lehrmaterialien. Daher wäre es schade, wenn er nur auf Ihr einzelnes Stud.IP beschränkt wäre. Der Lehrmarktplatz ist daher als dezentrales Netzwerk konzipiert, bei dem alle Nutzer aller Stud.IPs sich gegenseitig Lehrmaterialien tauschen können und nach Lehrmaterialien anderer Nutzer suchen können. <i>Dezentral</i> heißt dieses Netzwerk, weil es nicht einen einzigen zentralen Server gibt, der wie eine große Suchmaschine alle Informationen bereit hält. Stattdessen sind im besten Fall alle Stud.IPs mit allen anderen Stud.IPs direkt vernetzt. So ein dezentrales Netz ist sehr ausfallsicher und es passt zur Opensource-Idee von Stud.IP, weil man sich von keiner zentralen Institution abhängig macht. Aber Ihr Stud.IP muss irgendwo einen ersten Kontakt zum großen Netzwerk aller Lehrmarktplätze finden, um loslegen zu können. Wählen Sie dazu irgendeinen der unten aufgeführten Server aus. Sie werden Index-Server genannt und bilden das Tor zum Rest des ganzen Netzwerks.") ?>
            </div>

            <ul class="clean" style="text-align: center;">
                <li>
                    <?= \Studip\Button::create(_("Stud.IP Entwicklungsserver"), 'url', array('value' => "https://develop.studip.de/studip/")) ?>
                </li>
                <li>
                    <?= \Studip\Button::create(_("blubber.it"), 'url', array('value' => "http://blubber.it/")) ?>
                </li>
                <li>
                    <?= \Studip\Button::create(_("Nein, danke!"), 'nothanx', array()) ?>
                </li>
            </ul>

        </form>
    </div>
    <script>
        jQuery(function () {
            jQuery('#init_first_hosts_dialog').dialog({
                'modal': true,
                'title': '<?= _("Index-Server hinzufügen") ?>',
                'width': "80%"
            });
        });
    </script>
<? endif ?>

<?
$actions = new ActionsWidget();
$actions->addLink(
    _("Server hinzufügen"),
    PluginEngine::getURL($plugin, array(), "admin/add_new_host"),
    Assets::image_path("icons/blue/add"),
    array('data-dialog' => "1")
);

Sidebar::Get()->addWidget($actions);