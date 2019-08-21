<form action="<?= PluginEngine::getLink($plugin, array(), "market/search") ?>" method="GET" style="text-align: center; margin: 30px;">
    <div>
        <input type="text"
               name="search"
               value="<?= htmlReady(Request::get("search")) ?>" style="line-height: 130%; display: inline-block; border: 1px solid #28497c; vertical-align: middle; padding: 5px 5px; font-size: 14px; width: 250px;"
               placeholder="<?= htmlReady(Config::get()->LERNMARKTPLATZ_PLACEHOLDER_SEARCH) ?>">
        <?= \Studip\Button::create(_("Suchen"), '') ?>

        <?
        $params = array();
        if (Request::get("difficulty")) {
            $params['difficulty'] = Request::get("difficulty");
        }
        if (Request::get("search")) {
            $params['search'] = Request::get("search");
        }
        ?>
        <a href="<?= PluginEngine::getLink($plugin, $params, "market/adjust_filter") ?>"
           title="<?= _("Suchfilter hinzufügen") ?>"
           data-dialog>
            <?= Icon::create("filter", "clickable")->asImg(20,  ['class' => "text-bottom"]) ?>
        </a>
    </div>

    <div>
        <? if (Request::get("difficulty")) : ?>
            <? $difficulty = explode(",", Request::get("difficulty")) ?>
            <span><?= _("Niveau") ?> <?= htmlReady($difficulty[0] . " - ".$difficulty[1]) ?></span>
            <input type="hidden" name="difficulty" value="<?= htmlReady(Request::get("difficulty")) ?>">
        <? endif ?>
    </div>
</form>