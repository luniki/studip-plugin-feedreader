<ul>
  <li class="feed_reader_delete">
    <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'delete') ?>">
      <?= Assets::img('icons/16/grey/trash') ?>
    </a>
  </li>

  <li class="feed_reader_visibility">
     <?= $this->render_partial("_visibility") ?>
  </li>

  <li class="feed_reader_edit">
    <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">
      <span>Editieren</span>
    </a>
  </li>
</ul>
