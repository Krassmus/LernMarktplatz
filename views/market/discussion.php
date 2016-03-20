<ol>
    <? foreach ($review->comments as $comment) : ?>
        <li id="comment_<?= $comment->getId() ?>">
            <?= formatReady($comment['comment']) ?>
        </li>
    <? endforeach ?>
</ol>
<form action="<?= PluginEngine::getLink($plugin, array(), "market/discussion/".$review->getId()) ?>" method="post" class="default">
    <textarea name="comment"></textarea>
    <?= \Studip\Button::create(_("Abschicken")) ?>
</form>