<div style="padding: 1em;">
<div class="feed_reader_blank_slate">

     <h3> Bisher haben Sie noch keinen Newsfeed abonniert. </h3>

     <div>
     <p>
     Tragen Sie einfach die URL eines Newsfeeds in
     <span class="feed_reader_textfield">das obige Textfeld</span> ein,
     um ihn zu abonnieren.
     Ihre Newsfeeds werden dann auf Ihrem
     <a href="<?= URLHelper::getLink("about.php") ?>">Profil</a> und
     auf der <a href="<?= URLHelper::getLink("index.php") ?>">Startseite</a>
     angezeigt.
     </p>
     <p>
     Sobald Sie einen Newsfeed abonniert haben, wird er so angezeigt:
     </p>
     <img src="<?= $plugin->getPluginURL() ?>/img/blank_slate.png" alt="Beispiel für einen abbonierten Newsfeed" />
     </div>

     <script>
     jQuery(function ($) {
             $(".feed_reader_blank_slate").accordion({active: false, collapsible: true});
             $(".feed_reader_textfield").on("mouseenter mouseout", function () {
                     $("#feed_reader_subscription_form input[type=text]").toggleClass("highlight").focus();
             });
     });
     </script>

</div>
</div>
