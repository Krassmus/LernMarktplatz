<form action="<?= PluginEngine::getLink($plugin, array(), "market/search") ?>" method="get">
    <? if (Request::get("search")) : ?>
        <input type="hidden" name="search" value="<?= htmlReady(Request::get("search")) ?>">
    <? endif ?>

    <div style="margin-top: 13px;">
        <?= _("Niveau") ?>
        <div style="display: flex; justify-content: space-between; font-size: 0.8em; color: grey;">
            <div><?= _("Kindergarten") ?></div>
            <div><?= _("Experte") ?></div>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <? for ($i = 1; $i <= 12; $i++) : ?>
                <div><?= ($i < 10 ? "&nbsp;" : "").$i ?></div>
            <? endfor ?>
        </div>
        <div id="difficulty_slider" style="margin-left: 5px; margin-right: 9px;"></div>

        <? $difficulty = Request::get("difficulty") ? explode(",", Request::get("difficulty")) : array(1, 12) ?>
        <script>
            jQuery(function () {
                jQuery("#difficulty_slider").slider({
                    range: true,
                    min: 1,
                    max: 12,
                    values: [<?= (int) $difficulty[0] ?>, <?= (int) $difficulty[1] ?>],
                    change: function (event, ui) {
                        jQuery("#difficulty").val(ui.values[0] + "," + ui.values[1])
                    }
                });
            });
        </script>
        <input type="hidden" id="difficulty" name="difficulty" value="">
    </div>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Filter anwenden")) ?>
    </div>
</form>