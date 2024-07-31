<?php
/*
Plugin Name: WP Todo Sync
Plugin URI:
Description: This WordPress plugin is the best plugin for your practice which includes third-party API integration, MySQL interaction, shordcode development.
Version: 1.0
Author:
Author URI:
*/

if (!defined('WPINC')) {
  die;
}


require_once plugin_dir_path( __FILE__ ) . 'includes/class/class-wp-todo-sync-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class/class-wp-todo-sync-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class/class-wp-todo-sync.php';

register_activation_hook( __FILE__, array( 'WP_Todo_Sync_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Todo_Sync_Deactivator', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WP_Todo_Sync', 'init' ) );