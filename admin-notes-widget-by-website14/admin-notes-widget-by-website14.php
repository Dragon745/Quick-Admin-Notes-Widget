<?php
/**
 * Plugin Name: Admin Notes Widget By Website14
 * Plugin URI: https://wordpress.org/plugins/admin-notes-widget-by-website14/
 * Description: A simple dashboard widget that allows users to quickly add sticky notes directly to their WordPress dashboard for reminders, to-do lists, or quick notes.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.0
 * Author: Website14
 * Author URI: https://website14.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-notes-widget-by-website14
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QANW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QANW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('QANW_VERSION', '1.0.0');

class AdminNotesWidgetByWebsite14 {
    
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));

        add_action('wp_ajax_qanw_save_note', array($this, 'save_note'));
        add_action('wp_ajax_qanw_delete_note', array($this, 'delete_note'));
        add_action('wp_ajax_qanw_get_notes', array($this, 'get_notes'));
        add_action('wp_ajax_qanw_send_note', array($this, 'send_note'));
        add_action('wp_ajax_qanw_get_admin_users', array($this, 'get_admin_users'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_nopriv_qanw_save_note', array($this, 'save_note'));
        add_action('wp_ajax_nopriv_qanw_delete_note', array($this, 'delete_note'));
        add_action('wp_ajax_nopriv_qanw_get_notes', array($this, 'get_notes'));
        add_action('wp_ajax_nopriv_qanw_send_note', array($this, 'send_note'));
        add_action('wp_ajax_nopriv_qanw_get_admin_users', array($this, 'get_admin_users'));
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'admin_notes_widget_by_website14',
            '<span class="dashicons dashicons-sticky" style="margin-right: 8px; color: #0073aa;"></span>' . 
            __('Admin Notes Widget By Website14', 'admin-notes-widget-by-website14'),
            array($this, 'dashboard_widget_content')
        );
    }
    
    /**
     * Dashboard widget content
     */
    public function dashboard_widget_content() {
        ?>
        <div id="qanw-container">
            <div id="qanw-notes-list">
                <!-- Notes will be loaded here via AJAX -->
            </div>
            
            <div id="qanw-form">
                <div class="qanw-editor-toolbar">
                    <button type="button" class="qanw-format-btn" data-format="bold" title="<?php esc_attr_e('Bold', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-editor-bold"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="italic" title="<?php esc_attr_e('Italic', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-editor-italic"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="underline" title="<?php esc_attr_e('Underline', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-editor-underline"></span>
                    </button>
                    <span class="qanw-toolbar-separator">|</span>
                    <button type="button" class="qanw-format-btn" data-format="ul" title="<?php esc_attr_e('Bullet List', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-editor-ul"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="ol" title="<?php esc_attr_e('Numbered List', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-editor-ol"></span>
                    </button>
                    <span class="qanw-toolbar-separator">|</span>
                    <button type="button" class="qanw-format-btn" data-format="link" title="<?php esc_attr_e('Insert Link', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-admin-links"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="code" title="<?php esc_attr_e('Code', 'admin-notes-widget-by-website14'); ?>">
                        <span class="dashicons dashicons-editor-code"></span>
                    </button>
                </div>
                <div id="qanw-editor" contenteditable="true" placeholder="<?php esc_attr_e('Enter your note here...', 'admin-notes-widget-by-website14'); ?>"></div>
                <div class="qanw-form-controls">
                    <select id="qanw-note-color">
                        <option value="yellow"><?php esc_html_e('Yellow', 'admin-notes-widget-by-website14'); ?></option>
                        <option value="blue"><?php esc_html_e('Blue', 'admin-notes-widget-by-website14'); ?></option>
                        <option value="green"><?php esc_html_e('Green', 'admin-notes-widget-by-website14'); ?></option>
                        <option value="red"><?php esc_html_e('Red', 'admin-notes-widget-by-website14'); ?></option>
                    </select>
                    <button type="button" id="qanw-add-note" class="button button-primary">
                        <?php esc_html_e('Add Note', 'admin-notes-widget-by-website14'); ?>
                    </button>
                    <button type="button" id="qanw-send-note" class="button button-secondary">
                        <?php esc_html_e('Send to Admin', 'admin-notes-widget-by-website14'); ?>
                    </button>
                </div>
                
                <!-- Send Note Modal -->
                <div id="qanw-send-modal" class="qanw-modal" style="display: none;">
                    <div class="qanw-modal-content">
                        <div class="qanw-modal-header">
                            <h3><?php esc_html_e('Send Note to Admin', 'admin-notes-widget-by-website14'); ?></h3>
                            <button type="button" class="qanw-modal-close">&times;</button>
                        </div>
                        <div class="qanw-modal-body">
                            <p><?php esc_html_e('Select admin users to send this note to:', 'admin-notes-widget-by-website14'); ?></p>
                            <div id="qanw-admin-users-list">
                                <!-- Admin users will be loaded here -->
                            </div>
                        </div>
                        <div class="qanw-modal-footer">
                            <button type="button" id="qanw-confirm-send" class="button button-primary">
                                <?php esc_html_e('Send Note', 'admin-notes-widget-by-website14'); ?>
                            </button>
                            <button type="button" class="qanw-modal-cancel button button-secondary">
                                <?php esc_html_e('Cancel', 'admin-notes-widget-by-website14'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Support Section -->
            <div class="qanw-support-section">
                <hr>
                <p style="text-align: center; margin: 20px 0 10px 0;">
                    <a href="https://buymeacoffee.com/contact9rg" target="_blank" class="button button-secondary">
                        <span class="dashicons dashicons-heart" style="color: #e74c3c;"></span>
                        <?php esc_html_e('Buy us a coffee', 'admin-notes-widget-by-website14'); ?>
                    </a>

                </p>
            </div>
            
        </div>
        <?php
    }
    

    

    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'index.php') {
            return;
        }
        
        wp_enqueue_script(
            'qanw-script',
            QANW_PLUGIN_URL . 'assets/js/qanw-script.js',
            array('jquery'),
            QANW_VERSION,
            true
        );
        
        wp_enqueue_style(
            'qanw-style',
            QANW_PLUGIN_URL . 'assets/css/qanw-style.css',
            array(),
            QANW_VERSION
        );
        
        wp_localize_script('qanw-script', 'qanw_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('qanw_nonce'),
            'strings' => array(
                'confirm_delete' => esc_html__('Are you sure you want to delete this note?', 'admin-notes-widget-by-website14'),
                'note_added' => esc_html__('Note added successfully!', 'admin-notes-widget-by-website14'),
                'note_deleted' => esc_html__('Note deleted successfully!', 'admin-notes-widget-by-website14'),
                'error' => esc_html__('An error occurred. Please try again.', 'admin-notes-widget-by-website14')
            )
        ));
    }
    
    /**
     * Save note via AJAX
     */
    public function save_note() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'admin-notes-widget-by-website14'));
        }
        
        $note_text = wp_kses($_POST['note_text'], array(
            'b' => array(),
            'strong' => array(),
            'i' => array(),
            'em' => array(),
            'u' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'a' => array(
                'href' => array(),
                'target' => array()
            ),
            'code' => array(),
            'br' => array(),
            'p' => array(),
            'div' => array(),
            'span' => array()
        ));
        $note_color = sanitize_text_field($_POST['note_color']);
        
        if (empty($note_text)) {
            wp_send_json_error(esc_html__('Note text cannot be empty.', 'admin-notes-widget-by-website14'));
        }
        
        $allowed_colors = array('yellow', 'blue', 'green', 'red');
        if (!in_array($note_color, $allowed_colors)) {
            $note_color = 'yellow';
        }
        
        $notes = $this->get_notes_data();
        $new_note = array(
            'id' => uniqid(),
            'text' => $note_text,
            'color' => $note_color,
            'created' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );
        
        $notes[] = $new_note;
        $this->save_notes_data($notes);
        
        wp_send_json_success(array(
            'message' => esc_html__('Note added successfully!', 'admin-notes-widget-by-website14'),
            'note' => $new_note
        ));
    }
    
    /**
     * Delete note via AJAX
     */
    public function delete_note() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'admin-notes-widget-by-website14'));
        }
        
        $note_id = sanitize_text_field($_POST['note_id']);
        
        if (empty($note_id)) {
            wp_send_json_error(esc_html__('Note ID is required.', 'admin-notes-widget-by-website14'));
        }
        
        $notes = $this->get_notes_data();
        $user_id = get_current_user_id();
        
        // Find and remove the note
        foreach ($notes as $key => $note) {
            if ($note['id'] === $note_id && $note['user_id'] == $user_id) {
                unset($notes[$key]);
                $this->save_notes_data(array_values($notes));
                wp_send_json_success(esc_html__('Note deleted successfully!', 'admin-notes-widget-by-website14'));
            }
        }
        
        wp_send_json_error(esc_html__('Note not found or you do not have permission to delete it.', 'admin-notes-widget-by-website14'));
    }
    
    /**
     * Get notes via AJAX
     */
    public function get_notes() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'admin-notes-widget-by-website14'));
        }
        
        $notes = $this->get_notes_data();
        $user_notes = array();
        
        foreach ($notes as $note) {
            if ($note['user_id'] == get_current_user_id() || 
                (isset($note['recipient_id']) && $note['recipient_id'] == get_current_user_id())) {
                $user_notes[] = $note;
            }
        }
        
        wp_send_json_success($user_notes);
    }
    
    /**
     * Get admin users via AJAX
     */
    public function get_admin_users() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'admin-notes-widget-by-website14'));
        }
        
        $admin_users = get_users(array(
            'role__in' => array('administrator'),
            'exclude' => array(get_current_user_id()),
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));
        
        $users_data = array();
        foreach ($admin_users as $user) {
            $users_data[] = array(
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            );
        }
        
        wp_send_json_success($users_data);
    }
    
    /**
     * Send note to admin users via AJAX
     */
    public function send_note() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'admin-notes-widget-by-website14'));
        }
        
        $note_text = wp_kses($_POST['note_text'], array(
            'b' => array(),
            'strong' => array(),
            'i' => array(),
            'em' => array(),
            'u' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'a' => array(
                'href' => array(),
                'target' => array()
            ),
            'code' => array(),
            'br' => array(),
            'p' => array(),
            'div' => array(),
            'span' => array()
        ));
        
        $note_color = sanitize_text_field($_POST['note_color']);
        $recipient_ids = array_map('intval', $_POST['recipient_ids']);
        
        if (empty($note_text) || $note_text === '<br>' || $note_text === '') {
            wp_send_json_error(esc_html__('Note text cannot be empty.', 'admin-notes-widget-by-website14'));
        }
        
        if (empty($recipient_ids)) {
            wp_send_json_error(esc_html__('Please select at least one admin user.', 'admin-notes-widget-by-website14'));
        }
        
        $allowed_colors = array('yellow', 'blue', 'green', 'red');
        if (!in_array($note_color, $allowed_colors)) {
            $note_color = 'yellow';
        }
        
        $notes = $this->get_notes_data();
        $sent_count = 0;
        
        foreach ($recipient_ids as $recipient_id) {
            // Verify the recipient is an admin
            $recipient = get_user_by('id', $recipient_id);
            if ($recipient && in_array('administrator', $recipient->roles)) {
                $new_note = array(
                    'id' => uniqid(),
                    'text' => $note_text,
                    'color' => $note_color,
                    'created' => current_time('mysql'),
                    'user_id' => get_current_user_id(),
                    'recipient_id' => $recipient_id,
                    'sender_name' => get_userdata(get_current_user_id())->display_name,
                    'is_sent_note' => true
                );
                
                $notes[] = $new_note;
                $sent_count++;
            }
        }
        
        if ($sent_count > 0) {
            $this->save_notes_data($notes);
            wp_send_json_success(array(
                'message' => sprintf(esc_html__('Note sent to %d admin(s) successfully!', 'admin-notes-widget-by-website14'), $sent_count)
            ));
        } else {
            wp_send_json_error(esc_html__('No valid admin users selected.', 'admin-notes-widget-by-website14'));
        }
    }
    

    
    /**
     * Get notes data from options
     */
    private function get_notes_data() {
        $notes = get_option('qanw_notes', array());
        return is_array($notes) ? $notes : array();
    }
    
    /**
     * Save notes data to options
     */
    private function save_notes_data($notes) {
        update_option('qanw_notes', $notes);
    }
}

// Initialize the plugin
new AdminNotesWidgetByWebsite14();

// Activation hook
register_activation_hook(__FILE__, 'qanw_activate');
function qanw_activate() {
    // Create default options
    if (!get_option('qanw_notes')) {
        add_option('qanw_notes', array());
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'qanw_deactivate');
function qanw_deactivate() {
    // Clean up if needed
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'qanw_uninstall');
function qanw_uninstall() {
    // Remove all plugin data
    delete_option('qanw_notes');
} 