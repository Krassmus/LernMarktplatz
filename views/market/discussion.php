<a href="<?= PluginEngine::getLink($plugin, array(), "market/details/".$review['material_id']) ?>">
    <?= Assets::img("icons/16/blue/arr_1left", array('class' => "text-bottom")) ?>
    <?= _("Zurück") ?>
</a>

<div style="margin-top: 30px; margin-bottom: 30px;">
    <?= formatReady($review['review']) ?>
</div>


<ol class="reviews">
    <? foreach ($review->comments as $comment) : ?>
        <li id="comment_<?= $comment->getId() ?>" class="review">
            <div class="avatar">
                <img width="50px" height="50px" src="<?= htmlReady($comment['host_id'] ? MarketUser::find($comment['user_id'])->avatar : Avatar::getAvatar($comment['user_id'])->getURL(Avatar::MEDIUM)) ?>">
            </div>
            <div class="content">
                <div class="timestamp">
                    <?= date("j.n.Y G:i", $comment['chdate']) ?>
                </div>
                <strong><?= htmlReady($comment['host_id'] ? MarketUser::find($comment['user_id'])->name : get_fullname($comment['user_id'])) ?></strong>
                <span class="origin">(<?= htmlReady($comment['host_id'] ? $comment->host['name'] : $GLOBALS['UNI_NAME_CLEAN']) ?>)</span>
                <div class="review_text">
                    <?= formatReady($comment['comment']) ?>
                </div>
            </div>
        </li>
    <? endforeach ?>
</ol>

<form action="<?= PluginEngine::getLink($plugin, array(), "market/discussion/".$review->getId()) ?>" method="post" class="default">
    <textarea name="comment"></textarea>
    <?= \Studip\Button::create(_("Abschicken")) ?>
</form>