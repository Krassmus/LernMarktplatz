<h1><?= htmlReady($material['name']) ?></h1>

<? if (!$material->isVideo() && $material['front_image_content_type']) : ?>
    <img src="<?= htmlReady($material->getLogoURL()) ?>" style="display: block; max-width: 100%; max-height: 200px; height: 200px; margin-left: auto; margin-right: auto;">
<? endif ?>

<div style="text-align: center;">
    <? $url = $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : PluginEngine::getURL($plugin, array(), "market/download/".$material->getId()) ?>
    <a class="button download_link" href="<?= htmlReady($url) ?>" title="<?= _("Download") ?>">
        <?= Assets::img("icons/35/blue/download", array('class' => "blue")) ?>
        <?= Assets::img("icons/35/white/download", array('class' => "whitebutton")) ?>
        <div class="filename"><?= htmlReady($material['filename']) ?></div>
    </a>
    <? if ($GLOBALS['perm']->have_perm("tutor")) : ?>
        <div>
            <a href="<?= PluginEngine::getLink($plugin, array(), "market/add_to_course/".$material->getId()) ?>" data-dialog>
                <?= Assets::img("icons/16/blue/move_down/seminar", array('class' => "text-bottom")) ?>
                <?= _("Zu Veranstaltung hinzufügen") ?>
            </a>
        </div>
    <? endif ?>
</div>

<? if ($material->isVideo()) : ?>
    <video controls
            <?= $material['front_image_content_type'] ? 'poster="'.htmlReady($material->getLogoURL()).'"' : "" ?>
           crossorigin="anonymous"
           src="<?= htmlReady($url) ?>"
           style="display: block; margin-left: auto; margin-right: auto; width: 500px; max-width: 96vw;"></video>
<? endif ?>

<div style="margin-top: 17px;">
    <?= formatReady($material['description'] ?: $material['short_description']) ?>
</div>


<? if ($material->isFolder()) : ?>
    <h2><?= _("Verzeichnisstruktur") ?></h2>
    <ol class="lernmarktplatz structure">
        <? foreach ($material['structure'] as $filename => $file) : ?>
            <?= $this->render_partial("market/_details_file.php", array('name' => $filename, 'file' => $file)) ?>
        <? endforeach ?>
    </ol>
<? endif ?>

<? $tags = $material->getTopics() ?>
<? if (count($tags) > 0) : ?>
    <div class="tags">
        <h2><?= _("Themen") ?></h2>
        <ul class="clean">
            <? foreach ($tags as $tag) : ?>
                <li>
                    <a href="<?= PluginEngine::getLink($plugin, array('tag' => $tag['name']), "market/overview") ?>">
                        <?= Assets::img("icons/20/blue/topic", array('class' => "text-bottom")) ?>
                        <?= htmlReady($tag['name']) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ul>
    </div>
<? endif ?>

<h2><?= _("Zum Autor") ?></h2>
<div class="author_information">
    <? if ($material['host_id']) : ?>
        <? $user = $material['host_id'] ? LernmarktplatzUser::find($material['user_id']) : User::find($material['user_id']) ?>
        <? $image = $material['host_id'] ? $user['avatar'] : Avatar::getAvatar($material['user_id']) ?>
        <div class="avatar" style="background-image: url('<?= $image ?>');"></div>
        <div>
            <div class="author_name"><?= htmlReady($user['name']) ?></div>
            <div class="author_host">(<?= htmlReady($material->host->name) ?>)</div>
            <div class="description"><?= formatReady($user['description']) ?></div>
        </div>
    <? else : ?>
        <? $user = User::find($material['user_id']) ?>
        <? $image = Avatar::getAvatar($material['user_id'])->getURL(Avatar::MEDIUM) ?>
        <div class="avatar" style="background-image: url('<?= $image ?>');"></div>
        <div>
            <div class="author_name"><?= htmlReady($user->getFullName()) ?></div>
            <div class="author_host">(<?= htmlReady($GLOBALS['UNI_NAME_CLEAN']) ?>)</div>
            <div class="description"><?
                $user_description_datafield = DataField::find(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")) ?: DataField::findOneBySQL("name = ?", array(get_config("LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD")));
                if ($user_description_datafield) {
                    $datafield_entry = DatafieldEntryModel::findOneBySQL("range_id = ? AND datafield_id = ?", array($user['user_id'], $user_description_datafield->getId()));
                    echo $datafield_entry && $datafield_entry['content'] ? formatReady($datafield_entry['content']) : "";

                    if ($material['user_id'] === $GLOBALS['user']->id) : ?>
                        <a href="<?= URLHelper::getLink("dispatch.php/settings/details#datafields_".$user_description_datafield->getId()) ?>" title="<?= _("Text bearbeiten") ?>">
                            <?= Assets::img("icons/20/blue/edit", array('class' => "text-bottom")) ?>
                        </a>
                    <? endif;
                }
                ?>
            </div>
        </div>
    <? endif ?>
</div>


<div class="license" style="text-align: center; margin-top: 20px;">
    <?= _("Lizenz:") ?>
    <a href="https://creativecommons.org/licenses/by-sa/3.0/de/" target="_blank">
        <img src="<?= $plugin->getPluginURL()."/assets/cc-by-sa.svg" ?>" width="80px">
    </a>
    <a href="<?= PluginEngine::getLink($plugin, array(), "market/licenseinfo") ?>" data-dialog>
        <?= _("Was heißt das?") ?>
    </a>
</div>

<div style="text-align: center;">
    <? if (!$material['host_id'] && $material['user_id'] === $GLOBALS['user']->id) : ?>
        <?= \Studip\LinkButton::create(_("Bearbeiten"), PluginEngine::getURL($plugin, array(), "mymaterial/edit/".$material->getId()), array('data-dialog' => "1")) ?>
        <form action="<?= PluginEngine::getLink($plugin, array(), "mymaterial/edit/".$material->getId()) ?>" method="post" style="display: inline;">
            <?= \Studip\Button::create(_("Löschen"), "delete", array('value' => 1, 'onclick' => "return window.confirm('"._("Wirklich löschen?")."');")) ?>
        </form>
    <? endif ?>
</div>


<h2><?= _("Reviews") ?></h2>
<div>
    <div style="text-align: center;">
        <? if ($material['rating'] === null) : ?>
            <? if ($material['host_id'] || $material['user_id'] !== $GLOBALS['user']->id) : ?>
                <a style="opacity: 0.3;" title="<?= $GLOBALS['perm']->have_perm("autor") ? _("Geben Sie die erste Bewertung ab.") : _("Noch keine bewertung abgegeben.") ?>" href="<?= PluginEngine::getLink($plugin, array(), 'market/review/' . $material->getId()) ?>" data-dialog>
            <? endif ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star.svg", array('width' => "50px")) ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star.svg", array('width' => "50px")) ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star.svg", array('width' => "50px")) ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star.svg", array('width' => "50px")) ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star.svg", array('width' => "50px")) ?>
            <? if ($material['host_id'] || $material['user_id'] !== $GLOBALS['user']->id) : ?>
                </a>
            <? endif ?>
        <? else : ?>
            <? if ($material['host_id'] || $material['user_id'] !== $GLOBALS['user']->id) : ?>
                <a href="<?= PluginEngine::getLink($plugin, array(), 'market/review/' . $material->getId()) ?>" data-dialog title="<?= sprintf(_("%s von 5 Sternen"), round($material['rating'] / 2, 1)) ?>">
            <? endif ?>
            <? $material['rating'] = round($material['rating'], 1) / 2 ?>
            <? $v = $material['rating'] >= 0.75 ? 3 : ($material['rating'] >= 0.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "50px")) ?>
            <? $v = $material['rating'] >= 1.75 ? 3 : ($material['rating'] >= 1.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "50px")) ?>
            <? $v = $material['rating'] >= 2.75 ? 3 : ($material['rating'] >= 2.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "50px")) ?>
            <? $v = $material['rating'] >= 3.75 ? 3 : ($material['rating'] >= 3.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "50px")) ?>
            <? $v = $material['rating'] >= 4.75 ? 3 : ($material['rating'] >= 4.25 ? 2 : "") ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "50px")) ?>
            <? if ($material['host_id'] || $material['user_id'] !== $GLOBALS['user']->id) : ?>
                </a>
            <? endif ?>
        <? endif ?>
    </div>

    <ul class="reviews">
        <? foreach ($material->reviews as $review) : ?>
            <li id="review_<?= $review->getId() ?>" class="review">
                <div class="avatar">
                    <img width="50px" height="50px" src="<?= htmlReady($review['host_id'] ? LernmarktplatzUser::find($review['user_id'])->avatar : Avatar::getAvatar($review['user_id'])->getURL(Avatar::MEDIUM)) ?>">
                </div>
                <div class="content">
                    <div class="timestamp">
                        <a href="<?= PluginEngine::getLink($plugin, array(), "market/discussion/".$review->getId()) ?>" title="<?= _("Schreiben Sie einen Kommentar dazu.") ?>">
                            <?= Assets::img("icons/14/grey/comment", array('class' => "text-bottom")) ?>
                        </a>
                        <?= date("j.n.Y G:i", $review['chdate']) ?>
                    </div>
                    <strong><?= htmlReady($review['host_id'] ? LernmarktplatzUser::find($review['user_id'])->name : get_fullname($review['user_id'])) ?></strong>
                    <span class="origin">(<?= htmlReady($review['host_id'] ? $review->host['name'] : $GLOBALS['UNI_NAME_CLEAN']) ?>)</span>
                    <div class="review_text">
                        <?= formatReady($review['review']) ?>
                    </div>
                    <div class="stars">
                        <? $rating = round($review['rating'], 1) ?>
                        <? $v = $rating >= 0.75 ? 3 : ($rating >= 0.25 ? 2 : "") ?>
                        <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "16px")) ?>
                        <? $v = $rating >= 1.75 ? 3 : ($rating >= 1.25 ? 2 : "") ?>
                        <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "16px")) ?>
                        <? $v = $rating >= 2.75 ? 3 : ($rating >= 2.25 ? 2 : "") ?>
                        <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "16px")) ?>
                        <? $v = $rating >= 3.75 ? 3 : ($rating >= 3.25 ? 2 : "") ?>
                        <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "16px")) ?>
                        <? $v = $rating >= 4.75 ? 3 : ($rating >= 4.25 ? 2 : "") ?>
                        <?= Assets::img($plugin->getPluginURL()."/assets/star$v.svg", array('width' => "16px")) ?>
                    </div>
                    <div class="comments" style="text-align: center;">
                        <? if (count($review->comments)) : ?>
                            <a href="<?= PluginEngine::getLink($plugin, array(), "market/discussion/".$review->getId()) ?>">
                                <?= Assets::img("icons/16/blue/comment", array('class' => "text-bottom")) ?>
                                <?= sprintf(_("%s Kommentare dazu"), count($review->comments)) ?>
                            </a>
                        <? elseif ($material['user_id'] === $GLOBALS['user']->id) : ?>
                            <a href="<?= PluginEngine::getLink($plugin, array(), "market/discussion/".$review->getId()) ?>">
                                <?= Assets::img("icons/16/blue/comment", array('class' => "text-bottom")) ?>
                                <?= _("Dazu einen Kommentar schreiben") ?>
                            </a>
                        <? endif ?>
                    </div>
                </div>
            </li>
        <? endforeach ?>
    </ul>

    <div style="text-align: center;">
        <? if ($material['host_id'] || $material['user_id'] !== $GLOBALS['user']->id) : ?>
            <?= \Studip\LinkButton::create(_("Review schreiben"), PluginEngine::getLink($plugin, array(), 'market/review/' . $material->getId()), array('data-dialog' => 1)) ?>
        <? endif ?>
    </div>

</div>


<?
Sidebar::Get()->setImage($plugin->getPluginURL()."/assets/sidebar-service.png");
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _("Eigenes Lernmaterial hochladen"),
        PluginEngine::getURL($plugin, array(), "mymaterial/edit"),
        Assets::image_path("icons/blue/add"),
        array('data-dialog' => "1")
    );
    Sidebar::Get()->addWidget($actions);
}
