<? if ($file['is_folder']) : ?>
    <li class="folder">
        <?= Icon::create("folder-full", "info")->asImg("20px", array('class' => "text-bottom")) ?>
        <?= htmlReady($name) ?>
        <ol>
            <? foreach ($file['structure'] as $name => $subfile) : ?>
                <?= $this->render_partial("market/_details_file.php", array('name' => $name, 'file' => $subfile)) ?>
            <? endforeach ?>
        </ol>
    </li>
<? else : ?>
    <li>
        <div class="size" style="float: right"><?= htmlReady(number_format($file['size'] / 1024, 2)) ?> KB</div>
        <?= $plugin->get_file_icon(substr($name, strrpos($name, ".") + 1))->asImg("20px", array('class' => "text-bottom")) ?>
        <?= htmlReady($name) ?>
    </li>
<? endif ?>
