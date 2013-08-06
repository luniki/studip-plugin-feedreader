<?php

# Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
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


require_once 'models/feed.php';


/**
 * TODO
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class FeedReader extends StudipPlugin implements HomepagePlugin, PortalPlugin
{


    public $factory;


    function __construct()
    {

        parent::__construct();

        if (Navigation::hasItem('/profile') && $this->is_authorized()) {
            Navigation::addItem('/profile/feed_reader',
                                new AutoNavigation('Feed Reader',
                                                   PluginEngine::getUrl($this, null, "")));
        }

        $this->factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');

        PageLayout::addStylesheet($this->getPluginUrl() . '/css/style.css');
        PageLayout::addScript($this->getPluginUrl() . '/js/feedreader.js');

    }

    function is_authorized()
    {
        $name = Request::quoted('username');
        $id = $name ? get_userid($name) : $GLOBALS['auth']->auth['uid'];

        return $id === $GLOBALS['auth']->auth['uid'];
    }

    function has_to_be_authorized()
    {
        if (!$this->is_authorized()) {
            throw new Exception('Access denied.');
        }
    }

    function showList($message = '')
    {
        return $this->factory->render('show',
                                      array(
                                          'feeds'   => FeedReader_Feed::find_all($GLOBALS['auth']->auth['uid']),
                                          'message' => $message,
                                          'plugin'  => $this
                                      ),
                                      $GLOBALS['template_factory']->open('layouts/base_without_infobox')
        );
    }


    function show_action()
    {
        echo $this->showList();
    }


    function insert_action()
    {
        $this->has_to_be_authorized();
        Navigation::activateItem('/profile/feed_reader');

        $message = '';

        if (isset($_REQUEST['url']) && '' !== $_REQUEST['url']) {
            $feed = new FeedReader_Feed();
            $feed->user_id = $GLOBALS['auth']->auth['uid'];
            $feed->url = $_REQUEST['url'];

            $pie = @$this->get_simplepie_from_user_feed($feed);
            $error = $pie->error();

            if ($error) {
                $message = 'Newsfeed konnte nicht abonniert werden. (' . $error . ')';
            }
            else {
                $message = $feed->save()
                    ? 'Newsfeed abonniert.' : 'Newsfeed konnte nicht abonniert werden.';
            }
        }

        echo $this->showList($message);
    }


    function edit_action()
    {

        $this->has_to_be_authorized();
        Navigation::activateItem('/profile/feed_reader');

        if (!isset($_REQUEST['feed_id']) || '' === $_REQUEST['feed_id']) {
            echo $this->showList();
            return;
        }

        $plugin = $this;

        $id = (int) $_REQUEST['feed_id'];
        $feed = FeedReader_Feed::find($id);

        if (is_null($feed) || $feed->user_id !== $GLOBALS['auth']->auth['uid']) {
            echo $this->showList('No such feed');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $feed->url = $_POST['url'];

            $pie = $this->get_simplepie_from_user_feed($feed);
            $error = $pie->error();

            if ($error) {
                $message = $error;
            }
            else {
                $message = $feed->save()
                    ? 'Newsfeed wurde geändert.'
                    : 'Newsfeed konnte nicht geändert werden.';
            }

            echo $this->showList($message);
            return;
        }

        else {
            echo $this->factory->render('edit',
                                        compact('feed', 'plugin'),
                                        $GLOBALS['template_factory']->open('layouts/base_without_infobox'));
        }
    }


    function delete_action()
    {

        $this->has_to_be_authorized();

        if (isset($_REQUEST['feed_id']) && '' !== $_REQUEST['feed_id']) {
            $feed = FeedReader_Feed::find($_REQUEST['feed_id']);
        }

        # AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

            ob_end_clean();

            header('Content-Type: text/javascript');

            if (is_null($feed)) {
                header('HTTP/1.1 404 Not found', TRUE, 404);
            }
            else {
                echo $this->factory->render('delete',
                                            array(
                                                'plugin'  => $this,
                                                'feed'    => $feed,
                                                'success' => $feed->delete()),
                                            $GLOBALS['template_factory']->open('layouts/base_without_infobox'));
            }

            ob_start(create_function('', 'return "";'));
        }

        # NON AJAX
        else {

            Navigation::activateItem('/profile/feed_reader');

            if (is_null($feed))
                $message = 'Newsfeed existiert nicht.';
            else if ($feed->delete())
                $message = "Newsfeed wurde gelöscht.";
            else
                $message = "Newsfeed konnte nicht gelöscht werden.";

            echo $this->showList($message);
        }
    }


    function sort_action()
    {
        if (!$this->is_authorized()) {
            header('HTTP/1.1 403 Forbidden', TRUE, 403);
            exit;
        }

        if (!FeedReader_Feed::sort($GLOBALS['auth']->auth['uid'],
                                   $_REQUEST['feeds'])) {
            header('HTTP/1.1 404 Not found', TRUE, 404);
            var_dump($this->error);
            exit;
        }
    }


    function up_action()
    {

        $this->has_to_be_authorized();

        if (!isset($_POST['feed_id']) || '' === $_POST['feed_id']) {
            echo $this->showList();
            return;
        }

        $feed_id = (int) $_POST['feed_id'];

        $message = '';
        try {
            FeedReader_Feed::sort_up($GLOBALS['auth']->auth['uid'], $feed_id);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        echo $this->showList($message);
    }


    function down_action()
    {
        $this->has_to_be_authorized();

        if (!isset($_POST['feed_id']) || '' === $_POST['feed_id']) {
            echo $this->showList();
            return;
        }

        $feed_id = (int) $_POST['feed_id'];

        $message = '';
        try {
            FeedReader_Feed::sort_down($GLOBALS['auth']->auth['uid'], $feed_id);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        echo $this->showList($message);
    }

    function visibility_action()
    {
        $this->has_to_be_authorized();
        Navigation::activateItem('/profile/feed_reader');

        if (!isset($_REQUEST['feed_id']) || '' === $_REQUEST['feed_id']) {
            echo $this->showList();
            return;
        }

        $id = (int) $_REQUEST['feed_id'];
        $feed = FeedReader_Feed::find($id);

        if (is_null($feed) || $feed->user_id !== $GLOBALS['auth']->auth['uid']) {
            echo $this->showList('No such feed');
            return;
        }

        $feed->visibility = $feed->visibility ? false : true;
        $message = $feed->save()
            ? 'Sichtbarkeit des Newsfeeds wurde geändert.'
            : 'Sichtbarkeit des Newsfeeds konnte nicht geändert werden.';

        echo $this->showList($message);
        return;
    }

    function get_simplepie_from_user_feed($user_feed)
    {

        if (!class_exists('SimplePie'))
            require_once dirname(__FILE__) . '/vendor/SimplePie 1.1.3/simplepie.inc';

        $feed = new SimplePie($user_feed->url, $GLOBALS['TMP_PATH']);
        $feed->set_output_encoding('ISO-8859-1');
        $feed->init();

        $feed->id = $user_feed->id;

        return $feed;
    }


    function shortdesc($string, $length)
    {
        if (strlen($string) <= $length)
            return $string;
        $short_desc = trim(str_replace(array("\r","\n", "\t"), ' ',
                                       strip_tags($string)));
        $desc = trim(substr($short_desc, 0, $length));
        return $desc . (in_array(substr($desc, -1, 1), array('.', '!', '?'))
                        ? '' : '...');
    }

    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the given user's home page. Return NULL to
     * render nothing for this plugin.
     *
     * The template will automatically get a standard layout, which
     * can be configured via attributes set on the template:
     *
     *  title        title to display, defaults to plugin name
     *  icon_url     icon for this plugin (if any)
     *  admin_url    admin link for this plugin (if any)
     *  admin_title  title for admin link (default: Administration)
     *
     * @return object   template object to render or NULL
     */
    function getHomepageTemplate($user_id)
    {
        $feeds = $this->getFeeds($user_id);
        return (sizeof($feeds) || $this->is_authorized())
            ? $this->getFeedsTemplate($feeds)
            : NULL;
    }

    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the start or portal page. Return NULL to
     * render nothing for this plugin.
     *
     * The template will automatically get a standard layout, which
     * can be configured via attributes set on the template:
     *
     *  title        title to display, defaults to plugin name
     *  icon_url     icon for this plugin (if any)
     *  admin_url    admin link for this plugin (if any)
     *  admin_title  title for admin link (default: Administration)
     *
     * @return object   template object to render or NULL
     */
    function getPortalTemplate()
    {
        $feeds = $this->getFeeds($GLOBALS['auth']->auth['uid']);

        return (sizeof($feeds) || $this->is_authorized())
            ? $this->getFeedsTemplate($feeds)
            : NULL;

        return $this->getFeedsTemplate($GLOBALS['auth']->auth['uid']);
    }

    function getFeeds($user_id)
    {
        $feeds = array();
        foreach (FeedReader_Feed::find_all($user_id) as $f) {
            if ($f->is_visible($GLOBALS['auth']->auth['uid'])) {
                $feeds[] = FeedReader::get_simplepie_from_user_feed($f);
            }
        }
        return $feeds;
    }


    function getFeedsTemplate($feeds)
    {
        $limit = 5;
        $plugin = $this;

        $tmpl = $this->factory->open('overview/feeds');
        $tmpl->set_attributes(compact('feeds', 'limit', 'plugin'));

        return $tmpl;
    }
}
