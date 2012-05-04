<ul>
  <li class="feed_reader_delete">
    <a href="<?= $controller->url_for('subscriptions/delete', $feed->id) ?>#feed_reader">
      <?= Assets::img('icons/16/grey/trash') ?>
    </a>
  </li>

  <li class="feed_reader_visibility">
     <?= $this->render_partial("shared/_visibility") ?>
  </li>

  <li class="feed_reader_edit">
    <a href="<?= $controller->url_for('subscriptions/edit', $feed->id) ?>#feed_reader">
      <span>Editieren</span>
    </a>
  </li>
</ul>
