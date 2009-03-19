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

class FeedReader extends AbstractStudIPHomepagePlugin {


  var $factory;


  function FeedReader() {

    parent::AbstractStudIPHomepagePlugin();

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


  function getAdminLink() {
    return PluginEngine::getLink($this);
  }


  function is_authorized() {
    return $this->getRequestedUser()->getUserid() ===
           $GLOBALS['auth']->auth['uid'];
  }


  function has_to_be_authorized() {
    if (!$this->is_authorized()) {
      throw new Exception('Access denied.');
    }
  }


  function showList($message = '') {
    return $this->factory->render('show', array(
      'feeds'   => FeedReader_Feed::find_all($GLOBALS['auth']->auth['uid']),
      'message' => $message,
      'plugin'  => $this
    ));
  }


  function actionShow() {
    $this->has_to_be_authorized();
    echo $this->showList();
  }


  function actionInsert() {

    $this->has_to_be_authorized();

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


  function actionEdit() {

    $this->has_to_be_authorized();

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
      echo $this->factory->render('edit', compact('feed', 'plugin'));
    }
  }


  function actionDelete() {

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
            'success' => $feed->delete()));
      }

      ob_start(create_function('', 'return "";'));
    }

    # NON AJAX
    else {

      if (is_null($feed))
        $message = 'Newsfeed existiert nicht.';
      else if ($feed->delete())
        $message = "Newsfeed wurde gelöscht.";
      else
        $message = "Newsfeed konnte nicht gelöscht werden.";

      echo $this->showList($message);
    }
  }


  function actionSort() {
    // this hack is necessary to disable the standard Stud.IP layout
    ob_end_clean();

    if (!$this->is_authorized()) {
      header('HTTP/1.1 403 Forbidden', TRUE, 403);
      exit;
    }

    if (!FeedReader_Feed::sort($this->getRequestedUser()->getUserid(),
                               $_REQUEST['feeds'])) {
      header('HTTP/1.1 404 Not found', TRUE, 404);
      var_dump($this->error);
      exit;
    }

    ob_start(create_function('', 'return "";'));
  }


  function actionUp() {

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


  function actionDown() {
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


  function showOverview() {

    $feeds = array();
    $user = $this->getRequestedUser();
    foreach (FeedReader_Feed::find_all($user->getUserid()) as $f) {
      $feeds[] = $this->get_simplepie_from_user_feed($f);
    }

    $limit = 5;
    $plugin = $this;

    echo $this->factory->render('overview/feeds',
      compact('feeds', 'limit', 'plugin'));
  }


  function get_simplepie_from_user_feed($user_feed) {

    if (!class_exists('SimplePie'))
      require_once dirname(__FILE__) . '/vendor/SimplePie 1.1.3/simplepie.inc';

    $feed = new SimplePie($user_feed->url, $GLOBALS['TMP_PATH']);
    $feed->set_output_encoding('ISO-8859-1');
    $feed->init();

    $feed->id = $user_feed->id;

    return $feed;
  }


  function shortdesc($string, $length) {
      if (strlen($string) <= $length)
        return $string;
      $short_desc = trim(str_replace(array("\r","\n", "\t"), ' ',
                                     strip_tags($string)));
      $desc = trim(substr($short_desc, 0, $length));
      return $desc . (in_array(substr($desc, -1, 1), array('.', '!', '?'))
                      ? '' : '...');
  }
}

