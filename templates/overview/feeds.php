<?php
$title = _("Feed Reader");
$icon_url = $plugin->getPluginUrl() . '/img/plugin.png';
if ($plugin->is_authorized()) {
    $admin_url = PluginEngine::getURL($plugin, null, '');
    $admin_title = 'foo!';
}
?>

<? if ($plugin->is_authorized()) : ?>
<div class="feed_reader_subscription_bar">
  <?= $this->render_partial('_subscribe') ?>
</div>
<? endif ?>

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
