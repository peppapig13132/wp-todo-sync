<?php

class WP_Todo_Sync {
  public static function init() {
    add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
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

    // Handle search
    $search_query = '';
    if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
      $search_query = esc_sql( like_escape( $_GET['s'] ) );
      $todos = $wpdb->get_results( "SELECT * FROM $table_name WHERE title LIKE '%$search_query%'" );
    } else {
      $todos = $wpdb->get_results( "SELECT * FROM $table_name" );
    }

    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline">Todos</h1>
      <form method="get" action="">
        <input type="hidden" name="page" value="wp-todo-sync" />
        <p class="search-box">
          <label class="screen-reader-text" for="post-search-input">Search Todos:</label>
          <input type="search" id="post-search-input" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>">
          <input type="submit" id="search-submit" class="button" value="Search Todos">
        </p>
      </form>
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
          <?php if ( $todos ) : ?>
            <?php foreach ( $todos as $todo ) : ?>
              <tr>
                <td hidden><?php echo $todo->id; ?></td>
                <td><?php echo $todo->user_id; ?></td>
                <td><?php echo $todo->task_id; ?></td>
                <td><?php echo esc_html( $todo->title ); ?></td>
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
}
