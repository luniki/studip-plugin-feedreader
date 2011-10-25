<div id="feed_reader_feed_id_<?= $feed->id ?>" class="feed_reader_feed<?= $last ? ' last' : '' ?>">

    <div class="feed_reader_feed_header">
        <? if ($feed->get_title()) : ?>
            <a target="_blank" class="feed_reader_feed_title" href="<?= $feed->get_permalink() ?>">
                <span class="favicon" style="background-image: url(<?= $feed->get_favicon() ?>)"></span>
                <?= $feed->get_title() ?>
            </a>
        <? else : ?>
            <a target="_blank" class="feed_reader_feed_title feed_reader_error" href="#">
                <span class="favicon" style="background-image: url(<?= Assets::image_path('icons/16/red/exclaim.png') ?>)"></span>
                Fehler
            </a>
        <? endif ?>

        <div class="feed_reader_nubbin">
                <? if ($plugin->is_authorized()) : ?>
                    <?= $this->render_partial('overview/my_nubbin', compact($feed)) ?>
                <? else : ?>
                    <?= $this->render_partial('overview/other_nubbin', compact($feed)) ?>
                <? endif ?>
        </div>
    </div>

    <? if (sizeof($items = $feed->get_items(0, $limit))) : ?>
        <?= $this->render_partial_collection('overview/item', $feed->get_items(0, $limit)) ?>
    <? elseif ($feed->get_title()) : ?>
        <div class="feed_reader_item"> Der Newsfeed enthält keine Artikel. </div>
    <? else : ?>
        <div class="feed_reader_item">
            Der Newsfeed <a target="_blank" href="<?= htmlReady($feed->feed_url) ?>"><?= htmlReady($feed->feed_url) ?></a> ist fehlerhaft.
        </div>
    <? endif ?>

</div>
