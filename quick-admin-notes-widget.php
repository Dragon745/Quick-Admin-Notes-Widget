<?php
/**
 * Plugin Name: Quick Admin Notes Widget
 * Plugin URI: https://wordpress.org/plugins/quick-admin-notes-widget/
 * Description: A simple dashboard widget that allows users to quickly add sticky notes directly to their WordPress dashboard for reminders, to-do lists, or quick notes.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.0
 * Author: Website14
 * Author URI: https://www.website14.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-admin-notes-widget
 * Domain Path: /languages
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QANW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QANW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('QANW_VERSION', '1.0.0');

class QuickAdminNotesWidget {
    
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_qanw_save_note', array($this, 'save_note'));
        add_action('wp_ajax_qanw_delete_note', array($this, 'delete_note'));
        add_action('wp_ajax_qanw_get_notes', array($this, 'get_notes'));
        add_action('wp_ajax_qanw_send_note', array($this, 'send_note'));
        add_action('wp_ajax_qanw_get_admin_users', array($this, 'get_admin_users'));
        add_action('wp_ajax_qanw_submit_suggestion', array($this, 'submit_suggestion'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_nopriv_qanw_save_note', array($this, 'save_note'));
        add_action('wp_ajax_nopriv_qanw_delete_note', array($this, 'delete_note'));
        add_action('wp_ajax_nopriv_qanw_get_notes', array($this, 'get_notes'));
        add_action('wp_ajax_nopriv_qanw_send_note', array($this, 'send_note'));
        add_action('wp_ajax_nopriv_qanw_get_admin_users', array($this, 'get_admin_users'));
        add_action('wp_ajax_nopriv_qanw_submit_suggestion', array($this, 'submit_suggestion'));
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'quick_admin_notes_widget',
            __('Quick Admin Notes', 'quick-admin-notes-widget'),
            array($this, 'dashboard_widget_content')
        );
    }
    
    /**
     * Dashboard widget content
     */
    public function dashboard_widget_content() {
        ?>
        <div id="qanw-container">
            <div id="qanw-form">
                <div class="qanw-editor-toolbar">
                    <button type="button" class="qanw-format-btn" data-format="bold" title="<?php _e('Bold', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-editor-bold"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="italic" title="<?php _e('Italic', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-editor-italic"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="underline" title="<?php _e('Underline', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-editor-underline"></span>
                    </button>
                    <span class="qanw-toolbar-separator">|</span>
                    <button type="button" class="qanw-format-btn" data-format="ul" title="<?php _e('Bullet List', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-editor-ul"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="ol" title="<?php _e('Numbered List', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-editor-ol"></span>
                    </button>
                    <span class="qanw-toolbar-separator">|</span>
                    <button type="button" class="qanw-format-btn" data-format="link" title="<?php _e('Insert Link', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-admin-links"></span>
                    </button>
                    <button type="button" class="qanw-format-btn" data-format="code" title="<?php _e('Code', 'quick-admin-notes-widget'); ?>">
                        <span class="dashicons dashicons-editor-code"></span>
                    </button>
                </div>
                <div id="qanw-editor" contenteditable="true" placeholder="<?php _e('Enter your note here...', 'quick-admin-notes-widget'); ?>"></div>
                <div class="qanw-form-controls">
                    <select id="qanw-note-color">
                        <option value="yellow"><?php _e('Yellow', 'quick-admin-notes-widget'); ?></option>
                        <option value="blue"><?php _e('Blue', 'quick-admin-notes-widget'); ?></option>
                        <option value="green"><?php _e('Green', 'quick-admin-notes-widget'); ?></option>
                        <option value="red"><?php _e('Red', 'quick-admin-notes-widget'); ?></option>
                    </select>
                    <button type="button" id="qanw-add-note" class="button button-primary">
                        <?php _e('Add Note', 'quick-admin-notes-widget'); ?>
                    </button>
                    <button type="button" id="qanw-send-note" class="button button-secondary">
                        <?php _e('Send to Admin', 'quick-admin-notes-widget'); ?>
                    </button>
                </div>
                
                <!-- Send Note Modal -->
                <div id="qanw-send-modal" class="qanw-modal" style="display: none;">
                    <div class="qanw-modal-content">
                        <div class="qanw-modal-header">
                            <h3><?php _e('Send Note to Admin', 'quick-admin-notes-widget'); ?></h3>
                            <button type="button" class="qanw-modal-close">&times;</button>
                        </div>
                        <div class="qanw-modal-body">
                            <p><?php _e('Select admin users to send this note to:', 'quick-admin-notes-widget'); ?></p>
                            <div id="qanw-admin-users-list">
                                <!-- Admin users will be loaded here -->
                            </div>
                        </div>
                        <div class="qanw-modal-footer">
                            <button type="button" id="qanw-confirm-send" class="button button-primary">
                                <?php _e('Send Note', 'quick-admin-notes-widget'); ?>
                            </button>
                            <button type="button" class="qanw-modal-cancel button button-secondary">
                                <?php _e('Cancel', 'quick-admin-notes-widget'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="qanw-notes-list">
                <!-- Notes will be loaded here via AJAX -->
            </div>
            
            <!-- Support Section -->
            <div class="qanw-support-section">
                <hr>
                <p style="text-align: center; margin: 20px 0 10px 0;">
                    <a href="https://buymeacoffee.com/contact9rg" target="_blank" class="button button-secondary">
                        <span class="dashicons dashicons-heart" style="color: #e74c3c;"></span>
                        <?php _e('Buy us a coffee', 'quick-admin-notes-widget'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=qanw-suggestions'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-lightbulb"></span>
                        <?php _e('Suggest Feature', 'quick-admin-notes-widget'); ?>
                    </a>
                </p>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            null, // No parent menu
            __('Feature Suggestions', 'quick-admin-notes-widget'),
            __('Feature Suggestions', 'quick-admin-notes-widget'),
            'manage_options',
            'qanw-suggestions',
            array($this, 'suggestions_page')
        );
    }
    
    /**
     * Suggestions page content
     */
    public function suggestions_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Feature Suggestions', 'quick-admin-notes-widget'); ?></h1>
            
            <div class="qanw-suggestions-container">
                <div class="qanw-suggestions-intro">
                    <p><?php _e('Help us improve the Quick Admin Notes Widget plugin by suggesting new features or improvements. Your feedback is valuable to us!', 'quick-admin-notes-widget'); ?></p>
                </div>
                
                <form id="qanw-suggestion-form" method="post">
                    <?php wp_nonce_field('qanw_suggestion_nonce', 'qanw_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="qanw-suggestion-text"><?php _e('Your Suggestion', 'quick-admin-notes-widget'); ?></label>
                            </th>
                            <td>
                                <textarea 
                                    id="qanw-suggestion-text" 
                                    name="suggestion" 
                                    rows="8" 
                                    cols="50" 
                                    class="large-text"
                                    placeholder="<?php _e('Describe your feature suggestion here... For example: "It would be great to have a dark mode option" or "Could you add the ability to export notes?"', 'quick-admin-notes-widget'); ?>"
                                    required
                                ></textarea>
                                <p class="description">
                                    <?php _e('Please be as detailed as possible. Your suggestion will help us improve the plugin for everyone.', 'quick-admin-notes-widget'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="qanw-submit-suggestion">
                            <?php _e('Submit Suggestion', 'quick-admin-notes-widget'); ?>
                        </button>
                        <a href="<?php echo admin_url('index.php'); ?>" class="button button-secondary">
                            <?php _e('Cancel', 'quick-admin-notes-widget'); ?>
                        </a>
                    </p>
                </form>
                
                <div class="qanw-suggestions-info">
                    <h3><?php _e('What happens next?', 'quick-admin-notes-widget'); ?></h3>
                    <ul>
                        <li><?php _e('Your suggestion will be reviewed by our development team', 'quick-admin-notes-widget'); ?></li>
                        <li><?php _e('We may contact you for more details if needed', 'quick-admin-notes-widget'); ?></li>
                        <li><?php _e('Popular suggestions may be implemented in future updates', 'quick-admin-notes-widget'); ?></li>
                        <li><?php _e('You can submit multiple suggestions', 'quick-admin-notes-widget'); ?></li>
                    </ul>
                </div>
                
                <div class="qanw-support-section">
                    <hr>
                    <p style="text-align: center; margin: 20px 0 10px 0;">
                        <a href="https://buymeacoffee.com/contact9rg" target="_blank" class="button button-secondary">
                            <span class="dashicons dashicons-heart" style="color: #e74c3c;"></span>
                            <?php _e('Buy us a coffee', 'quick-admin-notes-widget'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#qanw-suggestion-form').on('submit', function(e) {
                e.preventDefault();
                
                var suggestion = $('#qanw-suggestion-text').val().trim();
                
                if (!suggestion) {
                    alert('<?php _e('Please enter a suggestion.', 'quick-admin-notes-widget'); ?>');
                    return;
                }
                
                if (suggestion.length > 5000) {
                    alert('<?php _e('Suggestion is too long. Please keep it under 5000 characters.', 'quick-admin-notes-widget'); ?>');
                    return;
                }
                
                var submitBtn = $('#qanw-submit-suggestion');
                submitBtn.prop('disabled', true).text('<?php _e('Submitting...', 'quick-admin-notes-widget'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'qanw_submit_suggestion',
                        suggestion: suggestion,
                        nonce: $('#qanw_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Thank you for your suggestion! We will review it carefully.', 'quick-admin-notes-widget'); ?>');
                            window.location.href = '<?php echo admin_url('index.php'); ?>';
                        } else {
                            alert(response.data || '<?php _e('An error occurred. Please try again.', 'quick-admin-notes-widget'); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'quick-admin-notes-widget'); ?>');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('<?php _e('Submit Suggestion', 'quick-admin-notes-widget'); ?>');
                    }
                });
            });
        });
        </script>
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
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qanw_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this note?', 'quick-admin-notes-widget'),
                'note_added' => __('Note added successfully!', 'quick-admin-notes-widget'),
                'note_deleted' => __('Note deleted successfully!', 'quick-admin-notes-widget'),
                'error' => __('An error occurred. Please try again.', 'quick-admin-notes-widget')
            )
        ));
    }
    
    /**
     * Save note via AJAX
     */
    public function save_note() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die(__('You do not have permission to perform this action.', 'quick-admin-notes-widget'));
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
            wp_send_json_error(__('Note text cannot be empty.', 'quick-admin-notes-widget'));
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
            'message' => __('Note added successfully!', 'quick-admin-notes-widget'),
            'note' => $new_note
        ));
    }
    
    /**
     * Delete note via AJAX
     */
    public function delete_note() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die(__('You do not have permission to perform this action.', 'quick-admin-notes-widget'));
        }
        
        $note_id = sanitize_text_field($_POST['note_id']);
        
        if (empty($note_id)) {
            wp_send_json_error(__('Note ID is required.', 'quick-admin-notes-widget'));
        }
        
        $notes = $this->get_notes_data();
        $user_id = get_current_user_id();
        
        // Find and remove the note
        foreach ($notes as $key => $note) {
            if ($note['id'] === $note_id && $note['user_id'] == $user_id) {
                unset($notes[$key]);
                $this->save_notes_data(array_values($notes));
                wp_send_json_success(__('Note deleted successfully!', 'quick-admin-notes-widget'));
            }
        }
        
        wp_send_json_error(__('Note not found or you do not have permission to delete it.', 'quick-admin-notes-widget'));
    }
    
    /**
     * Get notes via AJAX
     */
    public function get_notes() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_die(__('You do not have permission to perform this action.', 'quick-admin-notes-widget'));
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
            wp_die(__('You do not have permission to perform this action.', 'quick-admin-notes-widget'));
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
            wp_die(__('You do not have permission to perform this action.', 'quick-admin-notes-widget'));
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
            wp_send_json_error(__('Note text cannot be empty.', 'quick-admin-notes-widget'));
        }
        
        if (empty($recipient_ids)) {
            wp_send_json_error(__('Please select at least one admin user.', 'quick-admin-notes-widget'));
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
                'message' => sprintf(__('Note sent to %d admin(s) successfully!', 'quick-admin-notes-widget'), $sent_count)
            ));
        } else {
            wp_send_json_error(__('No valid admin users selected.', 'quick-admin-notes-widget'));
        }
    }
    
    /**
     * Submit feature suggestion via AJAX
     */
    public function submit_suggestion() {
        check_ajax_referer('qanw_nonce', 'nonce');
        
        $suggestion = sanitize_textarea_field($_POST['suggestion']);
        
        if (empty($suggestion)) {
            wp_send_json_error(__('Suggestion cannot be empty.', 'quick-admin-notes-widget'));
        }
        
        if (strlen($suggestion) > 5000) {
            wp_send_json_error(__('Suggestion is too long. Please keep it under 5000 characters.', 'quick-admin-notes-widget'));
        }
        
        $api_url = 'http://api.syedqutubuddin.in/suggestions_api.php';
        
        $data = array(
            'url' => get_site_url(),
            'suggestion' => $suggestion,
            'version' => QANW_VERSION
        );
        
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Failed to submit suggestion. Please try again later.', 'quick-admin-notes-widget'));
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            wp_send_json_success(__('Thank you for your suggestion! We will review it carefully.', 'quick-admin-notes-widget'));
        } else {
            wp_send_json_error(__('Failed to submit suggestion. Please try again later.', 'quick-admin-notes-widget'));
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
new QuickAdminNotesWidget();

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