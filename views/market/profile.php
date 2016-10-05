<div style="display: flex;">
    <div>
        <img src="<?= htmlReady($user['avatar']) ?>">
    </div>
    <div>
        <h1><?= htmlReady($user['name']) ?></h1>
        <div>
            <?= formatReady($user['description']) ?>
        </div>
    </div>
</div>

<section class="contentbox">
    <header>
        <h1>
            <?= Assets::img("icons/16/blue/service") ?>
            <?= _("Lernmaterialien") ?>
        </h1>
    </header>
    <section>
        <?= $this->render_partial("mymaterial/_material_list.php", array('materialien' => $materials)) ?>
    </section>
</section>

