<ul>
  <li class="feed_reader_delete">
    <a href="#">
      <?= Assets::img('trash.gif', array('alt' => 'Löschen', 'size' => '11@11')) ?>
    </a>
  </li>

  <li class="feed_reader_edit">
    <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">
      <span>Editieren</span>
    </a>
  </li>

  <li class="feed_reader_drag">
    <a href="#">
      <?= Assets::img('drag_handle.gif', array('alt' => 'Sortieren', 'class' => 'drag_handle')) ?>
    </a>
  </li>
</ul>

