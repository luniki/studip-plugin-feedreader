<? if ($plugin->is_authorized()) : ?>
  <?= $this->render_partial('shared/_subscribe') ?>
<? endif ?>

<? if (sizeof($feeds)) { ?>
  <div id="feed_reader_feeds" data-url="<?= htmlReady(PluginEngine::getURL($plugin, array(), '<%= action %>')) ?>">
      <? foreach (array_chunk($feeds, 2) as $row) { ?>
          <div class="feed_reader_row">
              <?= $this->render_partial('feeds/feed', array('feed' => $row[0])) ?>

              <? if ($row[1]) { ?>
                  <?= $this->render_partial('feeds/feed', array('feed' => $row[1], 'last' => true)) ?>
              <? } ?>
          </div>
      <? } ?>
  </div>
<? } elseif ($plugin->is_authorized()) { ?>
   <?= $this->render_partial("feeds/_blank_slate") ?>
<? } ?>
