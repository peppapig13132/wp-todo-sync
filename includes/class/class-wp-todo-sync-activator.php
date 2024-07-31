<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WP_Todo_Sync_Activator {
  public static function activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'todos';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      task_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      completed TINYINT(1) DEFAULT 0
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }
}
