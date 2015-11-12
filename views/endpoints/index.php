<h1><?= _("Lehrmarktplatz") ?></h1>

<h2><?= _("Version") ?></h2>
<?= _("Stud.IP") ?>: <?= htmlReady($GLOBALS['SOFTWARE_VERSION']) ?>
<? $manifest = $this->plugin->getMetadata() ?>
<br>
<?= htmlReady($manifest['pluginname']) ?>: <?= htmlReady($manifest['version']) ?>

<h2><?= _("Public-Key-Hash") ?></h2>
<?= md5(MarketHost::thisOne()->public_key) ?>

<h2><?= _("Statistik") ?></h2>
<table class="default nohover">
    <tr>
        <td><?= _("Anzahl verbundener Server") ?></td>
        <td><?= MarketHost::countBySQL("1=1") - 1  ?></td>
    </tr>
    <tr>
        <td><?= _("Anzahl eigener Materialien") ?></td>
        <td><?= MarketMaterial::countBySQL("host_id IS NULL") ?></td>
    </tr>
    <tr>
        <td><?= _("Anzahl Materialien auf anderen Servern") ?></td>
        <td><?= MarketMaterial::countBySQL("host_id IS NOT NULL") ?></td>
    </tr>
</table>

<h2><?= _("API") ?></h2>

<ul class="clean">
<? foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) : ?>
    <? if (stripos($method->name, "_action") === strlen($method->name) - 7) : ?>
        <? $name = substr($method->name, 0, stripos($method->name, "_action")) ?>
        <? if (!in_array($name, array("render", "map", "index"))) : ?>
            <li style="margin-bottom: 20px;">
                <? $name = substr($method->name, 0, stripos($method->name, "_action")) ?>
                <div style="font-weight: bold;"><?= htmlReady($name) ?></div>
                <? $comment = $method->getDocComment() ?>
                <div><?= nl2br(htmlReady($comment)) ?></div>
            </li>
        <? endif ?>
    <? endif ?>
<? endforeach ?>
</ul>