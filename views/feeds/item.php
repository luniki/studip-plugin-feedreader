<div class="feed_reader_item">
  <a target="_blank" href="<?= $item->get_permalink() ?>">
    <img src="<?= $plugin->getPluginURL() ?>/img/icon-item.gif" alt="">
    <span class="feed_reader_item_title"><?= $item->get_title() ?></span>
  </a>
  <span class="feed_reader_item_snippet"><?= FeedReader::shortdesc(strip_tags($item->get_description()), 250) ?></span>
  <? if ($item->get_date() !== NULL) : ?>
    vor <?= distance_of_time_in_words($item->get_date('U')) ?>
  <? endif ?>

  <? $enclosure = $item->get_enclosure(); ?>
  <? if ($enclosure && $enclosure->get_link()) : ?>
    <a href="<?= htmlReady($enclosure->get_link()) ?>" target="_blank">
      <?= Assets::img("icons/16/grey/link-extern", array("align" => "absmiddle")) ?>
    </a>
  <? endif ?>
</div>

