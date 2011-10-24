<div id="feed_reader_feed_id_<?= $feed->id ?>" class="feed_reader_feed<?= $last ? ' last' : '' ?>">

  <? if ($feed->get_title()) : ?>

     <div class="feed_reader_feed_header">
        <a target="_blank" class="feed_reader_feed_title" href="<?= $feed->get_permalink() ?>">
            <span class="favicon" style="background-image: url(<?= $feed->get_favicon() ?>)"></span>
            <?= $feed->get_title() ?>
        </a>

        <div style="display: none; opacity: 0;" class="feed_reader_nubbin">
                <? if ($plugin->is_authorized()) : ?>
                    <?= $this->render_partial('overview/my_nubbin', compact($feed)) ?>
                <? else : ?>
                    <?= $this->render_partial('overview/other_nubbin', compact($feed)) ?>
                <? endif ?>
        </div>
    </div>

    <?= $this->render_partial_collection('overview/item', $feed->get_items(0, $limit)) ?>

  <? else : ?>

TODO: could not find: <? var_dump($feed->feed_url) ?>

  <? endif ?>
</div>
