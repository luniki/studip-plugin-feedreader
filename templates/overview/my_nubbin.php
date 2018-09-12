<ul>
    <li class="feed_reader_delete">
        <a href="#">
            <?= Icon::create('trash') ?>
        </a>
    </li>

    <li class="feed_reader_edit">
        <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">
            <span><?= _('Ändern') ?></span>
        </a>
    </li>
</ul>
