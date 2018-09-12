<form id="feed_reader_subscription_form" method="post"
      action="<?= PluginEngine::getLink($plugin, array(), 'insert') ?>"
      style="margin: 0;"
      class="default">
    <label>Newsfeed abonnieren:
        <input type="text" name="url" />
    </label>
    <?= Studip\Button::create(_('Hinzufügen')) ?>
</form>
