<?php

# Copyright (c)  2008 - Marcus Lunzenauer <mlunzena@uos.de>
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

function distance_of_time_in_words($from_time, $to_time = null, $include_seconds = false)
{

    $to_time = $to_time ? $to_time: time();

    $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
    $distance_in_seconds = floor(abs($to_time - $from_time));

    $string = '';
    $parameters = array();

    if ($distance_in_minutes <= 1) {
        if (!$include_seconds) {
            $string = $distance_in_minutes == 0 ? _('weniger als 1 Minute') : _('1 Minute');
        } else {
            if ($distance_in_seconds <= 5) {
                $string = _('weniger als 5 Sekunden');
            } else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10) {
                $string = _('weniger als 10 Sekunden');
            } else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20) {
                $string = _('weniger als 20 Sekunden');
            } else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40) {
                $string = _('½ Minute');
            } else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59) {
                $string = _('weniger als 1 Minute');
            } else {
                $string = _('1 Minute');
            }
        }
    } else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44) {
        $string = _('%minutes% Minuten');
        $parameters['%minutes%'] = $distance_in_minutes;
    } else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89) {
        $string = _('ca. 1 Stunde');
    } else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439) {
        $string = _('ca. %hours% Stunden');
        $parameters['%hours%'] = round($distance_in_minutes / 60);
    } else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879) {
        $string = _('1 Tag');
    } else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199) {
        $string = _('%days% Tagen');
        $parameters['%days%'] = round($distance_in_minutes / 1440);
    } else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399) {
        $string = _('ca. 1 Monat');
    } else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959) {
        $string = _('%months% Monaten');
        $parameters['%months%'] = round($distance_in_minutes / 43200);
    } else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919) {
        $string = _('ca. 1 Jahr');
    } else {
        $string = _('über %years% Jahren');
        $parameters['%years%'] = round($distance_in_minutes / 525960);
    }

    return strtr($string, $parameters);
}
/**
 * TODO
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: feed.php 931 2008-11-19 16:52:21Z mlunzena $
 */

class FeedReader_Feed
{


    public
        $id,
        $user_id,
        $url,
        $position,
        $visibility;


    function get_columns()
    {
        return array('id'         => 'integer',
                     'user_id'    => 'string',
                     'url'        => 'string',
                     'position'   => 'integer',
                     'visibility' => 'boolean');
    }


    function from_record($row)
    {
        foreach ($this->get_columns() as $key => $type) {
            $this->$key = $row[$key];
            settype($this->$key, $type);
        }
    }


    function find($id)
    {

        $db = new DB_Seminar();
        $db->queryf('SELECT * FROM feed_reader_feeds WHERE id = %d', $id);
        if (!$db->next_record())
            return NULL;

        $feed = new FeedReader_Feed();
        $feed->from_record($db->Record);
        return $feed;
    }


    function find_all($user_id)
    {

        $db = new DB_Seminar();
        $db->queryf('SELECT * FROM feed_reader_feeds '.
                    'WHERE user_id = "%s" '.
                    'ORDER BY position',
                    $user_id);

        for ($feeds = array(); $db->next_record();) {
            $feed = new FeedReader_Feed();
            $feed->from_record($db->Record);
            $feeds[$feed->id] = $feed;
        }

        return $feeds;
    }


    function save()
    {

        if (FALSE === $this->validate()) {
            return FALSE;
        }

        if (isset($this->id)) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }


    function validate()
    {

        if (!preg_match('/^[0-9a-f]{32}$/', $this->user_id)) {
            return FALSE;
        }

        if (!preg_match('@^https?://@', $this->url)) {
            return FALSE;
        }

        return TRUE;
    }


    function update()
    {

        $sql = sprintf('UPDATE feed_reader_feeds '.
                       'SET user_id = "%s", url = "%s", position = %d, visibility = %s '.
                       'WHERE id = %d',
                       $this->user_id, $this->url, $this->position, $this->visibility ? 'TRUE' : 'FALSE', $this->id);

        $db = new DB_Seminar();
        $db->query($sql);

        return !$db->Errno;
    }


    function insert()
    {

        $db = new DB_Seminar();
        $db->queryf('SELECT MAX(position) AS position FROM feed_reader_feeds '.
                    'WHERE user_id = "%s"', $this->user_id);
        if (!$db->next_record()) {
            return FALSE;
        }

        $position = $db->f("position");

        $sql = sprintf('INSERT INTO feed_reader_feeds '.
                       '(id, user_id, url, position, visibility) '.
                       'VALUES (NULL, "%s", "%s", %d, %s)',
                       $this->user_id, $this->url, $position + 1, $this->visibility ? 'TRUE' : 'FALSE');

        $db->query($sql);
        if (1 !== $db->affected_rows()) {
            return FALSE;
        }

        $this->position = $position;

        return TRUE;
    }


    function delete()
    {

        if (!isset($this->id))
            return FALSE;

        $db = new DB_Seminar();
        $db->queryf('DELETE FROM feed_reader_feeds WHERE id = %d', $this->id);

        return !$db->Errno;
    }


    static function sort($user_id, $order)
    {

        $feeds = self::find_all($user_id);

        $diff = array_diff($order, array_keys($feeds));

        if (!empty($diff)) {
            # TODO (mlunzena) error gibt's doch gar nicht
            $error = 'different size';
            var_dump($this->error);
            exit;
            return FALSE;
        }

        $i = 0;
        foreach ($order as $feed_id) {
            $feeds[$feed_id]->position = $i++;
            if (!$feeds[$feed_id]->save()) {
                # TODO (mlunzena) error gibt's doch gar nicht
                $error = var_export($feeds[$feed_id], 1) . ' konnte nicht gespeichert werden.';
                return FALSE;
            }
        }

        return TRUE;
    }


    static function sort_up($user_id, $feed_id)
    {
        return self::sort_up_or_down($user_id, $feed_id, TRUE);
    }


    static function sort_down($user_id, $feed_id)
    {
        return self::sort_up_or_down($user_id, $feed_id, FALSE);
    }

    static private function sort_up_or_down($user_id, $feed_id, $sort_up)
    {

        $feeds = FeedReader_Feed::find_all($user_id);
        if (!array_key_exists($feed_id, $feeds)) {
            throw new Exception("Newsfeed existiert nicht.");
        }

        $ids = array_keys($feeds);
        $key = array_search($feed_id, $ids);

        if ($sort_up) {
            if ($key === 0) {
                throw new Exception("Schon ganz oben");
            }
            $ids[$key] = $ids[$key - 1];
            $ids[$key - 1] = $feed_id;
        }

        else {
            if ($key === sizeof($ids) - 1) {
                throw new Exception("Schon ganz unten");
            }
            $ids[$key] = $ids[$key + 1];
            $ids[$key + 1] = $feed_id;
        }

        return FeedReader_Feed::sort($user_id, $ids);
    }

    function is_visible($user_id)
    {
        return $this->user_id === $user_id || $this->visibility;
    }
}
