<style type="text/css" media="screen">
/* <![CDATA[ */

#feed_reader_feeds {
  margin: 0 0 0 0;
  padding: 0 0 0 0;
  list-style: none;
}

#feed_reader_feeds img {
  border: none;
}

#feed_reader_feeds > li {
  margin-bottom: 1.5em;
}

.feed_reader_header {
  margin: 0 0.5em 0.5em 0;
}

.feed_reader_title {
  font-size: 120%;
  font-weight:bold;
}

.feed_reader_title span.favicon {
  padding-left: 32px;
  margin-left: -32px;
  background-repeat: no-repeat;
  background-position: center top;
}

.feed_reader_title, .feed_reader_nubbin,  .feed_reader_wrapper {
  display: inline;
}

div.feed_reader_nubbin {
  padding-left: 0.5em;
  font-family: Verdana, sans-serif;
}

div.feed_reader_nubbin ul, div.feed_reader_nubbin li {
  display: inline;
  margin: 0;
  padding: 0;
}

div.feed_reader_nubbin li a {
  background: transparent none repeat scroll 0%;
  color: dark-blue;
  font-family: "Lucida Grande",Tahoma,sans-serif;
  font-size: 11px;
  padding: 0;
  text-decoration: none;
}

div.feed_reader_nubbin li a span {
  text-decoration: underline;
}

.feed_reader_item {
  font-size: 90%;
  margin: 0 0 0.5em 0.5em;
}

.feed_reader_item_title {
  color: #555555;
  font-size: 105%;
  font-weight: bold;
}

.feed_reader_item_snippet {
  color:#777777;
}

.feed_reader_subscription_bar {
  font-size: 0.75em;
  background-color: #EDF3FE;
  padding: 7px 25px 9px 20px;
  margin-bottom: 1em;
}

/* ]]> */
</style>

<? if ($plugin->is_authorized()) : ?>
<div class="feed_reader_subscription_bar">
  <?= $this->render_partial('_subscribe') ?>
</div>
<? endif ?>

<ul id="feed_reader_feeds">
  <?= $this->render_partial_collection('overview/feed', $feeds) ?>
</ul>

<script type="text/javascript">

var FeedReaderPlugin = function() {

  var activeNubbin;

  var hideNubbins = function () {
    if (!activeNubbin) { return; }
    $$('.feed_reader_nubbin').without(activeNubbin).invoke('setStyle', { display: 'none', opacity: 0 });
  };

  return {
    showNubbin: function (title, event) {
        var nubbin = title.next('.feed_reader_nubbin');
        if (activeNubbin == nubbin) {
          return;
        }
        activeNubbin = nubbin;
        hideNubbins();
        nubbin.setStyle({ opacity: 0 }).show().morph('opacity: 1', { duration: .5 });
        event.stop();
      },
    deleteFeed: function (anchor, event) {
        if (confirm('Diesen Newsfeed wirklich löschen?')) {
          new Ajax.Request("<?= PluginEngine::getLink($plugin, array(), 'delete') ?>", {
            parameters: { feed_id: anchor.up('.feed_reader_feed').readAttribute('id').split('_').pop() }
          });
        }
        event.stop();
      }
    }
}();

document.observe("dom:loaded", function() {

  $$(".feed_reader_title").each(function (title) {
    title.observe('mouseover', FeedReaderPlugin.showNubbin.curry(title));
  });

  <? if ($plugin->is_authorized()) : ?>

    Sortable.create("feed_reader_feeds", {
      handle: "drag_handle",
      constraint: "vertical",
      onUpdate: function() {
        new Ajax.Request("<?= PluginEngine::getLink($plugin, array(), 'sort') ?>",
          { parameters: Sortable.serialize("feed_reader_feeds", { name: 'feeds' }) });
      }
    });


    $$(".feed_reader_delete > a").each(function (anchor) {
      anchor.observe('click', FeedReaderPlugin.deleteFeed.curry(anchor));
    });

  <? else : ?>

    $$(".feed_reader_delete > a").each(function (anchor) {
      anchor.observe('click', FeedReaderPlugin.deleteFeed.curry(anchor));
    });
  <? endif ?>

});

</script>

