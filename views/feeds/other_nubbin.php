<ul>
  <li class="feed_reader_insert">

    <form action="<?= $controller->url_for('subscriptions/insert') ?>#feed_reader" method="post" style="display: inline;">
     <input type="hidden" name="url" value="<?= htmlReady($feed->feed_url) ?>">
     <? /* TODO nötig? */ ?>
     <input type="hidden" name="username" value="">
      <span>Abbonieren</span>
    </form>
  </li>
</ul>

