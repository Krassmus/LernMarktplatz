<div style="display: flex;">
    <div>
        <img src="<?= htmlReady($user['avatar']) ?>">
    </div>
    <div>
        <h1><?= htmlReady($user['name']) ?></h1>
        <div>
            <?= formatReady($user['description']) ?>
        </div>
    </div>
</div>

<ul class="clean">
    <? foreach ($materials as $material) : ?>
        <li>
            <a href="<?= PluginEngine::getLink($plugin, array(), "market/details/".$material->getId()) ?>">
                <?= htmlReady($material['name']) ?>
            </a>
        </li>
    <? endforeach ?>
</ul>