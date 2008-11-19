<li id="feed_reader_feed_id_<?= $feed->id ?>" class="feed_reader_feed">

  <? if ($feed->get_title()) : ?>

  <div class="feed_reader_header">
    <div class="feed_reader_title">
      <a target="_blank" href="<?= $feed->get_permalink() ?>">
        <span class="favicon"
              style="background-image: url(<?= $feed->get_favicon() ?>)">
        </span>
        <?= $feed->get_title() ?>
      </a>
    </div>

    <div style="display: none; opacity: 0;" class="feed_reader_nubbin">
      <div class="feed_reader_wrapper">
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

      </div>
    </div>

  </div>

  <?= $this->render_partial_collection('overview/item', $feed->get_items(0, $limit)) ?>

  <? else : ?>

    TODO: could not find: <? var_dump($feed->feed_url) ?>

  <? endif ?>
</li>

