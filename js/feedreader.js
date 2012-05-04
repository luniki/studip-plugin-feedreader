jQuery(function ($) {

    var $feeds = $("#feed_reader_feeds");
    
    if (!$feeds.length) {
        return;
    }
        
    var urltmpl = _.template($feeds.attr("data-url"));

    var showNubbin = function (feed) {
        var $feed = $(feed),
            nubbin = $feed.find('.feed_reader_nubbin');
        if (nubbin.is(":not(:visible)")) {
            $('.feed_reader_nubbin').css({display: 'none', opacity: 0});
            nubbin.show().animate({opacity: 1}, 200);
        }
    };

    var deleteFeed = function (anchor) {
        if (confirm('Diesen Newsfeed wirklich löschen?')) {
            var feed = $(anchor).parents(".feed_reader_feed");
            $.post(
                urltmpl({action: "delete"}),
                {
                    feed_id: _.last(feed.attr("id").split("_"))
                }
            ).success(function () {
                feed.remove();
            });
        }
    };

    $feeds
        /*
        .delegate(".feed_reader_feed", "mouseenter", function (event) {
            showNubbin(this);
        })
*/
        .delegate(".feed_reader_delete > a", "click", function (event) {
            deleteFeed(this);
            return false;
        });
});
