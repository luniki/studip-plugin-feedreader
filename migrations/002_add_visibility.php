<?php

class AddVisibility extends Migration
{
    public function up()
    {
        \DBManager::get()->query('ALTER TABLE feed_reader_feeds ADD visibility BOOLEAN NOT NULL');
    }

    public function down()
    {
        \DBManager::get()->query('ALTER TABLE feed_reader_feeds DROP visibility');
    }
}
