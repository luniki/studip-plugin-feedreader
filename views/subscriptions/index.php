<? if (isset($message) && strlen($message)) : ?>
    <div class="feed_reader_message"><?= htmlReady($message) ?></div>
<? endif ?>

<?= $this->render_partial('shared/_subscribe') ?>

<? if (sizeof($feeds)) : ?>
  <h3>Ihre Newsfeeds</h3>
  <ul id="feed_reader_list">
  <? $index = 0; $len = sizeof($feeds); ?>
  <? foreach ($feeds as $feed) : ?>
    <li id="feed_reader_list_feed_<?= $feed->id ?>">
      <?= $this->render_partial("shared/_visibility", compact('feed')) ?>

      <a href="<?= $controller->url_for('subscriptions/delete', $feed->id) ?>#feed_reader">
          <?= Assets::img('icons/16/grey/trash') ?>
      </a>

      <a href="<?= $controller->url_for('subscriptions/edit', $feed->id) ?>#feed_reader">
        ändern
      </a>

      <img src="<?= $plugin->getPluginURL() ?>/img/drag_handle.gif" alt="Sortieren" class="drag_handle">

      <span class="feed_reader_sort_arrows">

        <? if ($index > 0) : ?>
          <form action="<?= $controller->url_for('subscriptions/up', $feed->id) ?>#feed_reader" method="post" style="display: inline;">
            <input type="image" src="<?= Assets::image_path('icons/16/blue/arr_1up') ?>">
          </form>
        <? else : ?>
          <?= Assets::img('blank.gif', array('size' => '16@16', 'alt' => '')) ?>
        <? endif ?>

        <? if ($index + 1 < $len) : ?>
          <form action="<?= $controller->url_for('subscriptions/down', $feed->id) ?>#feed_reader" method="post" style="display: inline;">
            <input type="image" src="<?= Assets::image_path('icons/16/blue/arr_1down') ?>">
          </form>
        <? else : ?>
          <?= Assets::img('blank.gif', array('size' => '16@16', 'alt' => '')) ?>
        <? endif ?>

        <? $index++ ?>

      </span>

      <?= htmlReady($feed->url) ?>
    </li>
  <? endforeach ?>
  </ul>

<? else: ?>
   <?= $this->render_partial("blank_slate") ?>
<? endif ?>

<script type="text/javascript">
  Sortable.create("feed_reader_list", {
    handle: "drag_handle",
    constraint: "vertical",
    onUpdate: function() {
      new Ajax.Request("<?= PluginEngine::getLink($plugin, array(), 'sort') ?>",
        { parameters: Sortable.serialize("feed_reader_list", { name: 'feeds' }) });
    }
  });
</script>

