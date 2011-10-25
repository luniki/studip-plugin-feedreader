<?php

class AddVisibility extends DBMigration {

  function up() {
    $this->db->query('ALTER TABLE feed_reader_feeds ADD visibility BOOLEAN NOT NULL');
  }

  function down() {
    $this->db->query('ALTER TABLE feed_reader_feeds DROP visibility');
  }
}
