<? if (isset($message) && strlen($message)) : ?>
    <div class="feed_reader_message"><?= htmlReady($message) ?></div>
<? endif ?>

<div class="feed_reader_subscription_bar">
    <?= $this->render_partial('_subscribe') ?>
</div>


<? if (sizeof($feeds)) : ?>
    <h3>Ihre Newsfeeds</h3>
    <ul id="feed_reader_list">
        <? $index = 0; $len = sizeof($feeds); ?>
        <? foreach ($feeds as $feed) : ?>
            <li id="feed_reader_list_feed_<?= $feed->id ?>">

                <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'visibility') ?>">
                    <? if ($feed->visibility) { ?>
                        <?= Icon::create('visibility-visible')->asImg(['title' => 'Newsfeed ist für alle sichtbar. Klicken Sie, um ihn zu verstecken!']) ?>
                    <? } else { ?>
                        <?= Icon::create('visibility-invisible')->asImg(['title' => 'Newsfeed ist nur für Sie sichtbar. Klicken Sie, um ihn für alle sichtbar zu machen!']) ?>
                    <? } ?>
                </a>

                <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'delete') ?>">
                    <?= Icon::create('trash') ?>
                </a>

                <a href="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">
                    <?= Icon::create('edit') ?>
                </a>

                <img src="<?= $plugin->getPluginURL() ?>/img/drag_handle.gif" alt="Sortieren" class="drag_handle">

                <span class="feed_reader_sort_arrows">

                    <? if ($index > 0) : ?>
                        <form action="<?= PluginEngine::getLink($plugin, array(), 'up') ?>" method="post" style="display: inline;">
                            <input type="hidden" name="feed_id" value="<?= $feed->id ?>">
                            <?= Icon::create('arr_1up')->asInput() ?>
                        </form>
                    <? else : ?>
                        <?= Assets::img('blank.gif', array('size' => '16@16', 'alt' => '')) ?>
                    <? endif ?>

                    <? if ($index + 1 < $len) : ?>
                        <form action="<?= PluginEngine::getLink($plugin, array(), 'down') ?>" method="post" style="display: inline;">
                            <input type="hidden" name="feed_id" value="<?= $feed->id ?>">
                            <?= Icon::create('arr_1down')->asInput() ?>
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
 jQuery("#feed_reader_list").sortable({
     axis: 'y'
     , handle: '.drag_handle'
     , update:
    function(event, ui) {
        var sorted = jQuery("#feed_reader_list").sortable("toArray");
        var order = _.map($("#feed_reader_list").sortable("toArray"),
                          function (item) {
                              return item.match(/_(\d+)$/)[1];
                          });
        jQuery.post("<?= PluginEngine::getLink($plugin, array(), 'sort') ?>", {feeds: order});
    }
 });
</script>
