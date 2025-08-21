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

// Clean up rate limiting transients for all users
global $wpdb;

// Clean up rate limiting transients
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_qanw_rate_limit_%'));
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_qanw_rate_limit_%'));

// Clean up any other transients that might exist
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_qanw_%'));
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_qanw_%'));

// Clean up any user meta if we stored any (for future use)
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", 'qanw_%'));

// Clean up any post meta if we stored any (for future use)
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", 'qanw_%'));

// Clean up any term meta if we stored any (for future use)
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", 'qanw_%'));

// Log the uninstall for debugging (optional)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Admin Notes Widget By Website14: Plugin uninstalled and all data removed.');
} 