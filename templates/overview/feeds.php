<?php
$title = _("Feed Reader");
$icon_url = Assets::image_path("icons/16/white/rss");
if ($plugin->is_authorized()) {
    $admin_url = PluginEngine::getURL($plugin, null, '');
    $admin_title = 'Feed-Reader-Verwaltung';
}
?>

<? if ($plugin->is_authorized()) : ?>
<div class="feed_reader_subscription_bar">
  <?= $this->render_partial('_subscribe') ?>
</div>
<? endif ?>

<? if (sizeof($feeds)) { ?>
  <div id="feed_reader_feeds" data-url="<?= htmlReady(PluginEngine::getURL($plugin, array(), '<%= action %>')) ?>">
      <? foreach (array_chunk($feeds, 2) as $row) { ?>
          <div class="feed_reader_row">
              <?= $this->render_partial('overview/feed', array('feed' => $row[0])) ?>

              <? if ($row[1]) { ?>
                  <?= $this->render_partial('overview/feed', array('feed' => $row[1], 'last' => true)) ?>
              <? } ?>
          </div>
      <? } ?>
  </div>
<? } elseif ($plugin->is_authorized()) { ?>

  <div class="feed_reader_no_feeds">
  <p>
    Sie haben derzeit noch keine Newsfeeds abboniert.
    <br>
    Sobald Sie einen oder mehrere Newsfeed abboniert haben, können Sie und Ihre
    Gäste diese Newsfeeds auf Ihrer <a href="<?= URLHelper::getLink("about.php") ?>">Stud.IP-Homepage</a> lesen.

    Außerdem erhalten Sie Ihre Newsfeeds auch auf ihrer
    <a href="<?= URLHelper::getLink("index.php") ?>">Stud.IP-Startseite</a>.
  </p>
  </div>

<? } ?>
