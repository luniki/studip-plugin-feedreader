<h3>Newsfeed editieren</h3>

<form method="post" action="<?= PluginEngine::getLink($plugin, array('feed_id' => $feed->id), 'edit') ?>">
  <label for="feed_reader_item_url">URL des Newsfeeds:</label>
  <input id="feed_reader_item_url" type="url" name="url" size="100" value="<?= htmlReady($feed->url) ?>">
  <input type="submit" value="OK">
</form>
