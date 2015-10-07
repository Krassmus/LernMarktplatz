<? if ($file['is_folder']) : ?>
    <li class="folder">
        <?= Assets::img("icons/20/black/folder-full", array('class' => "text-bottom")) ?>
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
        <?= Assets::img($plugin->get_file_icon(substr($name, strrpos($name, ".") + 1)), array('class' => "text-bottom")) ?>
        <?= htmlReady($name) ?>
    </li>
<? endif ?>
