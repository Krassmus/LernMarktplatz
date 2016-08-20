<a href="<?= PluginEngine::getLink($plugin, array(), "market/details/".$review['material_id']) ?>">
    <?= Assets::img("icons/16/blue/arr_1left", array('class' => "text-bottom")) ?>
    <?= _("Zurück") ?>
</a>

<div style="margin-top: 30px; margin-bottom: 30px;">
    <?= formatReady($review['review']) ?>
</div>


<ol class="comments">
    <? foreach ($review->comments as $comment) : ?>
        <?= $this->render_partial("market/_comment.php", compact("comment")) ?>
    <? endforeach ?>
</ol>

<form action="<?= PluginEngine::getLink($plugin, array(), "market/discussion/".$review->getId()) ?>" method="post" class="default">
    <textarea name="comment" data-review_id="<?= htmlReady($review->getId()) ?>"></textarea>
    <div>
        <?= \Studip\LinkButton::create(_("Abschicken"), "#", array('onclick' => "return STUDIP.Lehrmarktplatz.addComment();")) ?>
    </div>
</form>