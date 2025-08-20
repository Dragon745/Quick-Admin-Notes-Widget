<?php
/**
 * Uninstall Admin Notes Widget By Website14
 * 
 * This file is executed when the plugin is deleted from WordPress.
 * It removes all plugin data from the database.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options
delete_option('qanw_notes');

// Remove any transients created by the plugin
delete_transient('qanw_notes_cache');

// Clean up any user meta if we stored any (for future use)
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'qanw_%'");

// Log the uninstall for debugging (optional)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Admin Notes Widget By Website14: Plugin uninstalled and all data removed.');
} 