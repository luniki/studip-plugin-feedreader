<?php

class CreateFeedTable extends DBMigration {

  function up() {
    $sql = <<<SQL
  CREATE TABLE feed_reader_feeds (
    id int(10) unsigned NOT NULL auto_increment,
    user_id varchar(32) NOT NULL,
    url text NOT NULL,
    position int(10) unsigned NOT NULL,
    PRIMARY KEY  (id)
  );
SQL;
    $this->db->query($sql);

  }

  function down() {
    $this->db->query('DROP TABLE IF EXISTS feed_reader_feeds');
  }
}
