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

class SubscriptionsController extends FeedReader_Controller
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        # stop performing and let the plugin return a null template
        if (!$this->plugin->is_authorized()) {
            return false;
        }
    }

    function showList($message = '')
    {
        $this->feeds   = FeedReader_Feed::find_all($GLOBALS['auth']->auth['uid']);
        $this->message = $message;
        $this->render_template('subscriptions/index', 'layouts/application');
    }


    function index_action()
    {
        $this->showList();
    }


    function insert_action()
    {

        $message = '';

        if (isset($_REQUEST['url']) && '' !== $_REQUEST['url']) {
            $feed = new FeedReader_Feed();
            $feed->user_id = $GLOBALS['auth']->auth['uid'];
            $feed->url = $_REQUEST['url'];

            $pie = @FeedReader::get_simplepie_from_user_feed($feed);
            $error = $pie->error();

            if ($error) {
                $message = 'Newsfeed konnte nicht abonniert werden. (' . $error . ')';
            }
            else {
                $message = $feed->save()
                    ? 'Newsfeed abonniert.' : 'Newsfeed konnte nicht abonniert werden.';
            }
        }

        return $this->showList($message);
    }


    function edit_action($feed_id)
    {

        $plugin = $this;

        $feed = FeedReader_Feed::find((int) $feed_id);

        if (is_null($feed) || $feed->user_id !== $GLOBALS['auth']->auth['uid']) {
            return $this->showList('No such feed');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $feed->url = $_POST['url'];

            $pie = FeedReader::get_simplepie_from_user_feed($feed);
            $error = $pie->error();

            if ($error) {
                $message = $error;
            }
            else {
                $message = $feed->save()
                    ? 'Newsfeed wurde geändert.'
                    : 'Newsfeed konnte nicht geändert werden.';
            }

            return $this->showList($message);
        }

        else {
            $this->feed = $feed;
            $this->set_layout('layouts/application');
        }
    }


    function delete_action($feed_id)
    {

        $feed = FeedReader_Feed::find((int) $feed_id);

        # AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

            header('Content-Type: text/javascript');

            if (is_null($feed)) {
                header('HTTP/1.1 404 Not found', TRUE, 404);
            }
            else {
                return $this->factory->render('delete',
                                              array(
                                                  'plugin'  => $this,
                                                  'feed'    => $feed,
                                                  'success' => $feed->delete()));
            }
        }

        # NON AJAX
        else {

            if (is_null($feed))
                $message = 'Newsfeed existiert nicht.';
            else if ($feed->delete())
                $message = "Newsfeed wurde gelöscht.";
            else
                $message = "Newsfeed konnte nicht gelöscht werden.";

            return $this->showList($message);
        }
    }


    function sort_action()
    {

        if (!FeedReader_Feed::sort($GLOBALS['auth']->auth['uid'],
                                   $_REQUEST['feeds'])) {
            header('HTTP/1.1 404 Not found', TRUE, 404);
            var_dump($this->error);
            exit;
        }
    }


    function up_action($feed_id)
    {
        $message = '';
        try {
            FeedReader_Feed::sort_up($GLOBALS['auth']->auth['uid'], (int) $feed_id);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return $this->showList($message);
    }


    function down_action($feed_id)
    {
        $message = '';
        try {
            FeedReader_Feed::sort_down($GLOBALS['auth']->auth['uid'], (int) $feed_id);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return $this->showList($message);
    }

    function visibility_action($feed_id)
    {

        $feed = FeedReader_Feed::find((int) $feed_id);

        if (is_null($feed) || $feed->user_id !== $GLOBALS['auth']->auth['uid']) {
            return $this->showList('No such feed');
        }

        $feed->visibility = $feed->visibility ? false : true;
        $message = $feed->save()
            ? 'Sichtbarkeit des Newsfeeds wurde geändert.'
            : 'Sichtbarkeit des Newsfeeds konnte nicht geändert werden.';

        return $this->showList($message);
    }
}
