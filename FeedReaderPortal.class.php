<?php

# Copyright (c)  2009 - Marcus Lunzenauer <mlunzena@uos.de>
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


require_once 'FeedReader.class.php';
require_once 'models/feed.php';


/**
 * This Stud.IP portal plugin displays the current user's newsfeeds on the
 * portal page.
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class FeedReaderPortal extends AbstractStudIPPortalPlugin {


  var $factory;


  function FeedReaderPortal() {

    parent::AbstractStudIPPortalPlugin();

    $this->setPluginiconname("img/plugin.png");

    $navigation =& new PluginNavigation();
    $navigation->setDisplayname(_("Newsfeeds"));
    $navigation->addLinkParam('action', 'main');
    $this->setNavigation($navigation);

    $this->factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
  }


  function getPluginname() {
    return _("FeedReader");
  }

  /**
   * Override the original method to always get a link to the admin pages of
   * this plugin's homepage part.
   *
   * @return string     the name of the class
   */
  function getPluginclassname() {
    return "feedreader";
  }


  /**
   * Returns the URI to the administration page of this plugin. Override this
   * method, if you want another URI, or return NULL to signal, that there is
   * no such page.
   *
   * @return mixed  if this plugin has an administration page return its URI,
   *                return NULL otherwise
   */
  function getAdminLink() {
    return PluginEngine::getLink("feedreader");
  }

  /**
   * Does this plugin have an administration page, which should be shown?
   * This default implementation only shows it for admin or root user.
   *
   * @return boolean    <description>
   */
  function hasAdministration() {
    return TRUE;
  }

  /**
   * Used to show an overview on the start page or portal page
   *
   * @param  boolean    is the user already logged in? optional, default: true
   *
   * @return type       <description>
   */
  function showOverview($authorizedview = TRUE) {

    if (!$authorizedview) return;

    $feeds = array();
    foreach (FeedReader_Feed::find_all($GLOBALS['auth']->auth['uid']) as $f) {
      $feeds[] = FeedReader::get_simplepie_from_user_feed($f);
    }

    $limit = 15;
    $plugin = PluginEngine::getPlugin('FeedReader');
    $thats_me = new StudIPUser();
    $thats_me->setUserid($GLOBALS['auth']->auth['uid']);
    $plugin->setRequestedUser($thats_me);

    echo $this->factory->render('overview/feeds',
      compact('feeds', 'limit', 'plugin'));
  }
}
