<ul>
  <li class="feed_reader_delete">
    <a href="#">
      <img src="<?= $plugin->getPluginURL() ?>/img/trash.gif" alt="Löschen"/>
    </a>
  </li>

  <li class="feed_reader_edit">
    <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">
      <span>Editieren</span>
    </a>
  </li>
</ul>
