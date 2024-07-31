<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WP_Todo_Sync_Deactivator {

  public static function deactivate() {
    global $wpdb;
    $table_name_todos = $wpdb->prefix . 'todos';

    // Optionally: Backup table data before deletion
    self::backup_table_data($table_name_todos);

    $sql_delete_todos = "DROP TABLE IF EXISTS $table_name_todos";
    $wpdb->query($sql_delete_todos);
  }

  private static function backup_table_data($table_name) {
    global $wpdb;

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    if (!empty($results)) {
      $upload_dir = wp_upload_dir();
      $backup_file = $upload_dir['basedir'] . '/todos_backup_' . time() . '.csv';

      $csv_output = fopen($backup_file, 'w');
      fputcsv($csv_output, array_keys($results[0])); // Write header
      foreach ($results as $row) {
        fputcsv($csv_output, $row);
      }
      fclose($csv_output);
    }
  }
}
