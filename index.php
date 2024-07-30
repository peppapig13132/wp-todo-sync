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


/**
 * Plugin activation hooks
 */
register_activation_hook(__FILE__, 'wp_todo_sync_activation');

function wp_todo_sync_activation() {}


/**
 * Plugin deactivation hooks
 */
register_deactivation_hook(__FILE__, 'wp_todo_sync_deactivation');

function wp_todo_sync_deactivation() {}
