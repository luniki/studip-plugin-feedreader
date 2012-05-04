<div class="feed_reader_subscription_bar">
<form id="feed_reader_subscription_form" method="post" action="<?= $controller->url_for('subscription/insert') ?>#feed_reader">
  <label>Newsfeed abbonieren:
  <input type="text" name="url" />
  </label>
  <input type="submit" value="Go" />
</form>
</div>
