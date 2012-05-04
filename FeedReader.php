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


require_once 'vendor/trails/trails.php';
require_once 'models/feed.php';


/**
 * TODO
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class FeedReader extends StudipPlugin implements HomepagePlugin, PortalPlugin
{

    function __construct()
    {
        parent::__construct();

        PageLayout::addStylesheet($this->getPluginUrl() . '/css/style.css');
        PageLayout::addScript($this->getPluginUrl() . '/js/feedreader.js');
    }

    function is_authorized()
    {
        global $auth;
        return $this->user_id === $auth->auth['uid'];
    }

    function has_to_be_authorized()
    {
        if (!$this->is_authorized()) {
            throw new Exception('Access denied.');
        }
    }

    function get_simplepie_from_user_feed($user_feed)
    {

        if (!class_exists('SimplePie'))
            require_once dirname(__FILE__) . '/vendor/SimplePie 1.2.1/simplepie.inc';

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
        return $this->performBox($user_id);
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
        $name = Request::quoted('username');
        $user_id = $name ? get_userid($name) : $GLOBALS['auth']->auth['uid'];

        return $this->performBox($user_id);
    }

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     */
    function perform($unconsumed_path)
    {
        # TODO mlunzena: this is wrong
        $this->user_id = $GLOBALS['auth']->auth['uid'];

        $response = $this->_perform($unconsumed_path);
        echo $response->body;
    }

    function performBox($user_id)
    {
        # TODO mlunzena: context auf diese Weise?
        $this->user_id = $user_id;

        $response = $this->_perform(preg_replace(':[^\w\/]:', '',
                                                 Request::get("fr")));

        # The controller wants to redirect or got an code.
        if ($response->status >= 300) {
            # TODO what to do?
        }

        # if the response's body is empty, do not show the plugin's box
        if ($response->body === '') {
            return NULL;
        }


        # prepare magic attributes
        $title = _("Feed Reader");
        $icon_url = Assets::image_path("icons/16/white/rss");
        if ($this->is_authorized()) {
            $admin_url = URLHelper::getURL('', array('fr' => 'subscriptions'));
            $admin_title = 'Feed-Reader-Verwaltung';
        }

        return new StringTemplate($response->body,
                                  compact(words('title icon_url admin_url admin_title')));
    }

    function _perform($unconsumed_path)
    {
        global $ABSOLUTE_PATH_STUDIP;
        $trails_root = $ABSOLUTE_PATH_STUDIP . $this->getPluginPath();
        $dispatcher = new FeedReader_Dispatcher($trails_root, NULL, 'feeds');
        $dispatcher->plugin = $this;
        return $dispatcher->dispatch($unconsumed_path);
    }
}


class StringTemplate extends Flexi_Template
{

    public $_content;

    function __construct($content, $attributes = array())
    {
        global $template_factory;

        $this->_content = $content;
        $this->set_attributes($attributes);
        $this->_factory = $template_factory;
    }

    function _render()
    {
        return $this->_layout
            ? $this->_layout->render($this->get_attributes() + array('content_for_layout' => $this->_content))
            : $this->_content;
    }
}


class FeedReader_Dispatcher extends Trails_Dispatcher
{

    function dispatch($uri) {

        # E_USER_ERROR|E_USER_WARNING|E_USER_NOTICE|E_RECOVERABLE_ERROR = 5888
        $old_handler = set_error_handler(array($this, 'error_handler'), 5888);

        ob_start();
        $level = ob_get_level();

        $response = $this->map_uri_to_response($this->clean_request_uri((string) $uri));

        while (ob_get_level() >= $level) {
            ob_end_flush();
        }

        if (isset($old_handler)) {
            set_error_handler($old_handler);
        }
        return $response;
    }
}

class FeedReader_Controller extends Trails_Controller
{

    function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;
    }

    function url_for($to/*, ...*/)
    {

        # urlencode all but the first argument
        $args = func_get_args();
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return URLHelper::getURL('', array('fr' => join('/', $args)));
    }
}
