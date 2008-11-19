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
        <? if ($plugin->is_authorized()) : ?>
          <?= $this->render_partial('overview/my_nubbin', compact($feed)) ?>
        <? else : ?>
          <?= $this->render_partial('overview/other_nubbin', compact($feed)) ?>
        <? endif ?>

      </div>
    </div>

  </div>

  <?= $this->render_partial_collection('overview/item', $feed->get_items(0, $limit)) ?>

  <? else : ?>

    TODO: could not find: <? var_dump($feed->feed_url) ?>

  <? endif ?>
</li>

