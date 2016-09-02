<a href="<?= PluginEngine::getLink($plugin, array(), "market/details/".$review['material_id']) ?>">
    <?= Assets::img("icons/16/blue/arr_1left", array('class' => "text-bottom")) ?>
    <?= _("Zurück") ?>
</a>

<div class="mainreview">
    <div style="margin-bottom: 10px;">
        <img width="50px" height="50px" src="<?= htmlReady($review['host_id'] ? LernmarktplatzUser::find($review['user_id'])->avatar : Avatar::getAvatar($review['user_id'])->getURL(Avatar::MEDIUM)) ?>" style="vertical-align: middle;">
        <span class="stars" style="vertical-align: middle;">
            <? $rating = round($review['rating'], 1) ?>
            <? $v = $rating >= 0.75 ? 3 : ($rating >= 0.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "25px")) ?>
            <? $v = $rating >= 1.75 ? 3 : ($rating >= 1.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "25px")) ?>
            <? $v = $rating >= 2.75 ? 3 : ($rating >= 2.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "25px")) ?>
            <? $v = $rating >= 3.75 ? 3 : ($rating >= 3.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "25px")) ?>
            <? $v = $rating >= 4.75 ? 3 : ($rating >= 4.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "25px")) ?>
        </span>
    </div>
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