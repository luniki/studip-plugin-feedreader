<style type="text/css" media="screen">
.drag_handle {
  cursor: pointer;
}

.feed_reader_message {
  margin-bottom: 1em;
  border: 2px solid #FFD324;
  padding: 0.8em;
  background: #FFF6BF;
  color: #817134;
}

.feed_reader_subscription_bar {
  font-size: 0.75em;
  background-color: #EDF3FE;
  padding: 7px 25px 9px 20px;
  margin: 1em;
}

#feed_reader_list {
  margin-bottom: 1em;
}
</style>

<? if (isset($message) && strlen($message)) : ?>
  <div class="feed_reader_message"><?= $message ?></div>
<? endif ?>

<div class="feed_reader_subscription_bar">
  <?= $this->render_partial('_subscribe') ?>
</div>

<h3>Deine Newsfeeds</h3>

<? if (sizeof($feeds)) : ?>
  <ul id="feed_reader_list">
  <? $index = 0; $len = sizeof($feeds); ?>
  <? foreach ($feeds as $feed) : ?>
    <li id="feed_reader_list_feed_<?= $feed->id ?>">
      <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'delete') ?>">
        <img src="<?= $plugin->getPluginURL() ?>/img/trash.gif" alt="Löschen"/>
      </a>
      <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">ändern</a>
        <img src="<?= $plugin->getPluginURL() ?>/img/drag_handle.gif" alt="Sortieren" class="drag_handle" style="display:none;"/>

      <noscript>

        <? if ($index > 0) : ?>
          <form action="<?= PluginEngine::getLink($plugin, array(), 'up') ?>" method="post" style="display: inline;">
            <input type="hidden" name="feed_id" value="<?= $feed->id ?>">
            <input type="image" src="<?= Assets::image_path('move_up.gif') ?>">
          </form>
        <? else : ?>
          <?= Assets::img('blank.gif', array('size' => '13@11', 'alt' => '')) ?>
        <? endif ?>

        <? if ($index + 1 < $len) : ?>
          <form action="<?= PluginEngine::getLink($plugin, array(), 'down') ?>" method="post" style="display: inline;">
            <input type="hidden" name="feed_id" value="<?= $feed->id ?>">
            <input type="image" src="<?= Assets::image_path('move_down.gif') ?>">
          </form>
        <? else : ?>
          <?= Assets::img('blank.gif', array('size' => '13@11', 'alt' => '')) ?>
        <? endif ?>

        <? $index++ ?>
      </noscript>

      <?= $feed->url ?>
    </li>
  <? endforeach ?>
  </ul>
<? endif ?>

<script type="text/javascript">

  $$('.drag_handle').invoke('toggle');

  Sortable.create("feed_reader_list", {
    handle: "drag_handle",
    constraint: "vertical",
    onUpdate: function() {
      new Ajax.Request("<?= PluginEngine::getLink($plugin, array(), 'sort') ?>",
        { parameters: Sortable.serialize("feed_reader_list", { name: 'feeds' }) });
    }
  });
</script>

