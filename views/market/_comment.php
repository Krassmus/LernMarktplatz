<li id="comment_<?= $comment->getId() ?>" class="review">
    <div class="avatar">
        <img width="50px" height="50px" src="<?= htmlReady($comment['host_id'] ? LernmarktplatzUser::find($comment['user_id'])->avatar : Avatar::getAvatar($comment['user_id'])->getURL(Avatar::MEDIUM)) ?>">
    </div>
    <div class="content">
        <div class="timestamp">
            <?= date("j.n.Y G:i", $comment['chdate']) ?>
        </div>
        <strong><?= htmlReady($comment['host_id'] ? LernmarktplatzUser::find($comment['user_id'])->name : get_fullname($comment['user_id'])) ?></strong>
        <span class="origin">(<?= htmlReady($comment['host_id'] ? $comment->host['name'] : Config::get()->UNI_NAME_CLEAN) ?>)</span>
        <div class="review_text">
            <?= formatReady($comment['comment']) ?>
        </div>
    </div>
</li>