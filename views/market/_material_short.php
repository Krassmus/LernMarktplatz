<article class="contentbox">
    <a href="<?= $controller->url_for('presenting/details/' . $material->getId()) ?>">
        <header>
            <h1><?= htmlReady($material['name']) ?></h1>
        </header>
        <div class="image" style="background-image: url(<?= $marketplugin->getLogoURL() ?>);"></div>
        <p class="shortdescription">
            <?= htmlReady($material['description']) ?>
        </p>
    </a>
<? $tags = $material->getTopics(); ?>
<? if (count($tags)) : ?>
    <footer class="tags">
    <? foreach ($tags as $tag): ?>
        <a href="<?= $controller->url_for('presenting/all', compact('tag')) ?>">
            <?= htmlReady($tag) ?>
        </a>
    <? endforeach; ?>
    </footer>
<? endif; ?>
</article>