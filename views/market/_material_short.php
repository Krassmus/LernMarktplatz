<article class="contentbox">
    <a href="<?= $controller->url_for('market/details/' . $material->getId()) ?>">
        <header>
            <h1><?= htmlReady($material['name']) ?></h1>
        </header>
        <div class="image" style="background-image: url(<?= $material->getLogoURL() ?>);<?= (!$material['front_image_content_type']) ? " background-size: 60% auto;" : "" ?>"></div>
        <p class="shortdescription">
            <?= htmlReady($material['short_description'] ?: $material['description']) ?>
        </p>
    </a>
<? $tags = $material->getTopics(); ?>
<? if (count($tags)) : ?>
    <footer class="tags">
    <? foreach ($tags as $tag): ?>
        <a href="<?= PluginEngine::getLink($plugin, array('tag' => $tag['name']), 'market/overview') ?>">
            <?= htmlReady($tag['name']) ?>
        </a>
    <? endforeach; ?>
    </footer>
<? endif; ?>
</article>