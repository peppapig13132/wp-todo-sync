<?php

// Autoload dependencies
require_once plugin_dir_path(__FILE__) . '../../vendor/autoload.php';

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class WP_Todo_Sync {
  private static $logger;

  public static function init() {
    self::initialize_logger();
    add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
  }

  private static function initialize_logger() {
    // Ensure that the logs directory exists and is writable
    $log_dir = __DIR__ . '/../../logs';
    if (!file_exists($log_dir)) {
      mkdir($log_dir, 0755, true);
    }

    self::$logger = new Logger('wp-todo-sync');
    self::$logger->pushHandler(new StreamHandler($log_dir . '/plugin.log', Logger::DEBUG));
  }

  public static function add_admin_menu() {
    add_menu_page(
      'Todos',
      'Todos',
      'manage_options',
      'wp-todo-sync',
      array( __CLASS__, 'display_todos_page' ),
      'dashicons-list-view',
      6
    );
  }

  public static function display_todos_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'todos';
  
    // Handle sync action
    if (isset($_POST['sync_todos'])) {
      self::sync_todos();
      echo '<div id="message" class="updated notice is-dismissible"><p>Todos synced successfully.</p></div>';
    }
  
    // Handle search
    $search_query = '';
    if (isset($_GET['s']) && !empty($_GET['s'])) {
      $search_query = esc_sql(like_escape($_GET['s']));
      $todos = $wpdb->get_results("SELECT * FROM $table_name WHERE title LIKE '%$search_query%'");
    } else {
      $todos = $wpdb->get_results("SELECT * FROM $table_name");
    }
  
    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline">Todos</h1>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
          <form method="post" action="">
            <?php wp_nonce_field('sync_todos_nonce', 'sync_todos_nonce_field'); ?>
            <input type="submit" name="sync_todos" id="sync-todos" class="button action" value="Sync Todos">
          </form>
        </div>
        <div>
          <form method="get" action="">
            <input type="hidden" name="page" value="wp-todo-sync" />
            <p class="search-box">
              <label class="screen-reader-text" for="post-search-input">Search Todos:</label>
              <input type="search" id="post-search-input" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>">
              <input type="submit" id="search-submit" class="button" value="Search Todos">
            </p>
          </form>
        </div>
      </div>
      <table class="wp-list-table widefat fixed striped">
        <thead>
          <tr>
            <th scope="col" hidden>ID</th>
            <th scope="col">User ID</th>
            <th scope="col">Task ID</th>
            <th scope="col">Title</th>
            <th scope="col">Completed</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($todos) : ?>
            <?php foreach ($todos as $todo) : ?>
              <tr>
                <td hidden><?php echo $todo->id; ?></td>
                <td><?php echo $todo->user_id; ?></td>
                <td><?php echo $todo->task_id; ?></td>
                <td><?php echo esc_html($todo->title); ?></td>
                <td><?php echo $todo->completed ? 'Yes' : 'No'; ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="4">No todos found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php
  }
  
  public static function sync_todos() {
    // Verify nonce
    if (!isset($_POST['sync_todos_nonce_field']) || !wp_verify_nonce($_POST['sync_todos_nonce_field'], 'sync_todos_nonce')) {
      self::$logger->warning('Nonce verification failed during todos sync.');
      return;
    }
  
    // Fetch data from the API
    $response = wp_remote_get('https://jsonplaceholder.typicode.com/todos');
    if (is_wp_error($response)) {
      self::$logger->error('Failed to fetch todos from API.', ['error' => $response->get_error_message()]);
      return;
    }
  
    $todos = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($todos)) {
      self::$logger->info('No todos found in the API response.');
      return;
    }
  
    global $wpdb;
    $table_name = $wpdb->prefix . 'todos';
  
    foreach ($todos as $todo) {
      $result = $wpdb->replace(
        $table_name,
        array(
          'id' => $todo['id'],
          'user_id' => $todo['userId'],
          'task_id' => $todo['id'],
          'title' => $todo['title'],
          'completed' => $todo['completed'] ? 1 : 0
        ),
        array('%d', '%d', '%d', '%s', '%d')
      );

      if ($result === false) {
        self::$logger->error('Database insertion failed.', ['todo' => $todo]);
      }
    }

    self::$logger->info('Todos synced successfully.');
  }
}
