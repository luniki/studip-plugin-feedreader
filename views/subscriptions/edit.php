<h3>Newsfeed editieren</h3>

<form method="post" action="<?= $controller->url_for('subscriptions/edit', $feed->id) ?>#feed_reader">
  <label for="feed_reader_item_url">URL des Newsfeeds:</label>
  <input id="feed_reader_item_url" type="text" name="url" size="100" value="<?= htmlReady($feed->url) ?>">
  <input type="submit" value="OK">
</form>
