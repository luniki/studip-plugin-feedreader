<?php

# Copyright (c)  2011 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
class FeedsController extends FeedReader_Controller
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        # only if there are feeds for the current context
        # or if I am authorized
        $this->feeds = $this->getFeeds($this->plugin->user_id);
        if (!(sizeof($this->feeds) || $this->plugin->is_authorized())) {
            # stop performing and let the plugin return a null template
            return false;
        }
    }


    function index_action()
    {
        $this->limit = 5;
    }


    function getFeeds($user_id)
    {
        $feeds = array();
        $me = $GLOBALS['auth']->auth['uid'];
        foreach (FeedReader_Feed::find_all($user_id) as $f) {
            if ($f->is_visible($me)) {
                $feeds[] = FeedReader::get_simplepie_from_user_feed($f);
            }
        }
        return $feeds;
/*
        return @array_map('FeedReader::get_simplepie_from_user_feed',
                          array_filter(FeedReader_Feed::find_all($user_id),
                                       function ($f) use ($me) {
                                           $f->is_visible($me);
                                       }));
*/
    }
}
