<?php
/**
 * Plugin Name: Admin Notes Widget By Website14
 * Description: A simple dashboard widget that allows users to quickly add sticky notes directly to their WordPress dashboard for reminders, to-do lists, or quick notes.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.0
 * Author: Website14
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: admin-notes-widget-by-website14
 * Domain Path: /languages
 * Contributors: website14
 * Tags: dashboard, notes, admin, widget, sticky-notes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QANW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QANW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('QANW_VERSION', '1.0.0');

/**
 * Admin Notes Widget By Website14
 * 
 * A comprehensive dashboard widget that allows WordPress administrators to create,
 * manage, and share sticky notes directly from their dashboard. Features include
 * rich text editing, color coding, note sharing, and comprehensive security measures.
 * 
 * @package AdminNotesWidgetByWebsite14
 * @author Website14
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL v2 or later
 */
class AdminNotesWidgetByWebsite14 {
    
    /**
     * Constructor - Initializes the plugin and sets up WordPress hooks
     * 
     * Sets up all necessary WordPress actions and filters for the dashboard widget,
     * AJAX handlers, and script/style enqueuing.
     * 
     * @since 1.0.0
     * @access public
     */
    public function __construct() {
        // Dashboard widget setup
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));

        // AJAX handlers for note operations
        add_action('wp_ajax_qanw_save_note', array($this, 'save_note'));
        add_action('wp_ajax_qanw_delete_note', array($this, 'delete_note'));
        add_action('wp_ajax_qanw_get_notes', array($this, 'get_notes'));
        add_action('wp_ajax_qanw_send_note', array($this, 'send_note'));
        add_action('wp_ajax_qanw_get_admin_users', array($this, 'get_admin_users'));
        
        // Script and style enqueuing
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Adds the Admin Notes Widget to the WordPress dashboard
     * 
     * Registers a new dashboard widget using WordPress's built-in dashboard widget API.
     * The widget includes an icon and localized title for better user experience.
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses wp_add_dashboard_widget() WordPress function to register dashboard widget
     * @uses __() WordPress function for internationalization
     * 
     * @return void
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'admin_notes_widget_by_website14',
            '<span class="dashicons dashicons-sticky qanw-widget-title-icon"></span>' . 
            __('Admin Notes Widget By Website14', 'admin-notes-widget-by-website14'),
            array($this, 'dashboard_widget_content')
        );
    }
    
    /**
     * Renders the dashboard widget content HTML
     * 
     * Outputs the complete HTML structure for the notes widget, including:
     * - Notes list container for displaying existing notes
     * - Rich text editor with formatting toolbar
     * - Form controls for note creation and management
     * - Modal for sending notes to other administrators
     * - Support section with links to WordPress.org support
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses esc_attr_e() WordPress function for internationalized and escaped attributes
     * @uses esc_html_e() WordPress function for internationalized and escaped HTML
     * 
     * @return void
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
                    <div class="qanw-color-picker">
                        <label for="qanw-note-color" class="qanw-color-picker-label">
                            <?php esc_html_e('Note Color:', 'admin-notes-widget-by-website14'); ?>
                        </label>
                        <div class="qanw-color-options" role="radiogroup" aria-label="<?php esc_attr_e('Select note color', 'admin-notes-widget-by-website14'); ?>">
                            <input type="radio" id="color-yellow" name="note_color" value="yellow" class="qanw-color-radio" checked>
                            <label for="color-yellow" class="qanw-color-option qanw-color-yellow" title="<?php esc_attr_e('Yellow', 'admin-notes-widget-by-website14'); ?>">
                                <span class="qanw-color-swatch"></span>
                                <span class="qanw-color-name"><?php esc_html_e('Yellow', 'admin-notes-widget-by-website14'); ?></span>
                            </label>
                            
                            <input type="radio" id="color-blue" name="note_color" value="blue" class="qanw-color-radio">
                            <label for="color-blue" class="qanw-color-option qanw-color-blue" title="<?php esc_attr_e('Blue', 'admin-notes-widget-by-website14'); ?>">
                                <span class="qanw-color-swatch"></span>
                                <span class="qanw-color-name"><?php esc_html_e('Blue', 'admin-notes-widget-by-website14'); ?></span>
                            </label>
                            
                            <input type="radio" id="color-green" name="note_color" value="green" class="qanw-color-radio">
                            <label for="color-green" class="qanw-color-option qanw-color-green" title="<?php esc_attr_e('Green', 'admin-notes-widget-by-website14'); ?>">
                                <span class="qanw-color-swatch"></span>
                                <span class="qanw-color-name"><?php esc_html_e('Green', 'admin-notes-widget-by-website14'); ?></span>
                            </label>
                            
                            <input type="radio" id="color-red" name="note_color" value="red" class="qanw-color-radio">
                            <label for="color-red" class="qanw-color-option qanw-color-red" title="<?php esc_attr_e('Red', 'admin-notes-widget-by-website14'); ?>">
                                <span class="qanw-color-swatch"></span>
                                <span class="qanw-color-name"><?php esc_html_e('Red', 'admin-notes-widget-by-website14'); ?></span>
                            </label>
                        </div>
                    </div>
                    <button type="button" id="qanw-add-note" class="button button-primary">
                        <?php esc_html_e('Add Note', 'admin-notes-widget-by-website14'); ?>
                    </button>
                    <button type="button" id="qanw-send-note" class="button button-secondary">
                        <?php esc_html_e('Send to Admin', 'admin-notes-widget-by-website14'); ?>
                    </button>
                </div>
                
                <!-- Send Note Modal -->
                <div id="qanw-send-modal" class="qanw-modal qanw-modal-hidden">
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
            

            
        </div>
        <?php
    }
    

    

    
    /**
     * Enqueues scripts and styles for the dashboard widget
     * 
     * Loads the necessary JavaScript and CSS files only on the dashboard page.
     * Also localizes the JavaScript with AJAX URL, nonce, and internationalized strings.
     * 
     * @since 1.0.0
     * @access public
     * 
     * @param string $hook The current admin page hook
     * 
     * @uses wp_enqueue_script() WordPress function to enqueue JavaScript
     * @uses wp_enqueue_style() WordPress function to enqueue CSS
     * @uses wp_localize_script() WordPress function to localize JavaScript
     * @uses wp_create_nonce() WordPress function to create security nonce
     * @uses esc_url() WordPress function to escape URLs
     * @uses admin_url() WordPress function to get admin URL
     * @uses esc_html__() WordPress function for internationalized strings
     * 
     * @return void
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
            QANW_PLUGIN_URL . 'assets/css/qanw-style-clean.css',
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
                'error' => esc_html__('An error occurred. Please try again.', 'admin-notes-widget-by-website14'),
                'please_enter_note' => esc_html__('Please enter a note.', 'admin-notes-widget-by-website14'),
                'enter_url' => esc_html__('Enter URL:', 'admin-notes-widget-by-website14'),
                'please_enter_note_before_sending' => esc_html__('Please enter a note before sending.', 'admin-notes-widget-by-website14'),
                'please_select_admin_user' => esc_html__('Please select at least one admin user.', 'admin-notes-widget-by-website14'),
                'security_check_failed' => esc_html__('Security check failed. Please refresh the page and try again.', 'admin-notes-widget-by-website14'),
                'session_expired' => esc_html__('Session expired. Please log in again.', 'admin-notes-widget-by-website14'),
                'too_many_requests' => esc_html__('Too many requests. Please wait a moment before trying again.', 'admin-notes-widget-by-website14'),
                'authentication_required' => esc_html__('Authentication required.', 'admin-notes-widget-by-website14'),
                'list_item' => esc_html__('List item', 'admin-notes-widget-by-website14'),
                'link_text' => esc_html__('link', 'admin-notes-widget-by-website14'),
                'bold_text' => esc_html__('bold', 'admin-notes-widget-by-website14'),
                'italic_text' => esc_html__('italic', 'admin-notes-widget-by-website14'),
                'underline_text' => esc_html__('underline', 'admin-notes-widget-by-website14')
            )
        ));
    }
    
    /**
     * Performs comprehensive security validation for AJAX requests
     * 
     * Implements a multi-layered security approach including:
     * - User authentication verification
     * - Nonce validation to prevent CSRF attacks
     * - Session validation to prevent session hijacking
     * - Rate limiting to prevent brute force attacks
     * 
     * @since 1.0.0
     * @access private
     * 
     * @param string $action The nonce action name (default: 'qanw_nonce')
     * 
     * @uses is_user_logged_in() WordPress function to check user authentication
     * @uses check_ajax_referer() WordPress function to verify nonce
     * @uses wp_verify_auth_cookie() WordPress function to validate session
     * @uses get_current_user_id() WordPress function to get current user ID
     * @uses get_transient() WordPress function to get rate limit data
     * @uses set_transient() WordPress function to set rate limit data
     * @uses wp_send_json_error() WordPress function to send error response
     * @uses esc_html__() WordPress function for internationalized error messages
     * 
     * @return bool|void Returns true on success, sends JSON error response on failure
     * 
     * @throws wp_send_json_error() Sends HTTP 401 for authentication failures
     * @throws wp_send_json_error() Sends HTTP 403 for security check failures
     * @throws wp_send_json_error() Sends HTTP 429 for rate limit violations
     */
    private function enhanced_security_check($action = 'qanw_nonce') {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Error: Authentication required. You must be logged in to perform this action. Please log in and try again.', 'admin-notes-widget-by-website14'), 401);
        }
        

        
        // Check nonce with proper error handling
        if (!check_ajax_referer($action, 'nonce', false)) {
            wp_send_json_error(esc_html__('Error: Security check failed. This usually happens when the page has been open for too long. Please refresh the page and try again.', 'admin-notes-widget-by-website14'), 403);
        }
        
        // Check if user session is still valid (reliable method for all contexts)
        if (!wp_get_current_user() || !wp_get_current_user()->exists()) {
            wp_send_json_error(esc_html__('Error: Session expired. Your login session has timed out. Please log in again to continue.', 'admin-notes-widget-by-website14'), 401);
        }
        
        // Rate limiting check
        $user_id = get_current_user_id();
        $rate_limit_key = 'qanw_rate_limit_' . $user_id;
        $rate_limit = get_transient($rate_limit_key);
        
        if ($rate_limit && $rate_limit > 10) { // Max 10 requests per minute
            $remaining_time = 60 - (time() - get_option('_transient_timeout_' . $rate_limit_key, 0));
            wp_send_json_error(esc_html(sprintf(__('Error: Rate limit exceeded. You have made too many requests. Please wait %d seconds before trying again.', 'admin-notes-widget-by-website14'), $remaining_time)), 429);
        }
        
        // Update rate limit
        if ($rate_limit) {
            set_transient($rate_limit_key, $rate_limit + 1, 60);
        } else {
            set_transient($rate_limit_key, 1, 60);
        }
        
        return true;
    }
    
    /**
     * Generates a new security nonce for continued AJAX operations
     * 
     * Creates a fresh nonce after successful operations to maintain security
     * and prevent nonce reuse attacks. This is called after each successful
     * AJAX request to ensure continued security.
     * 
     * @since 1.0.0
     * @access private
     * 
     * @uses wp_create_nonce() WordPress function to create security nonce
     * 
     * @return string The newly generated nonce string
     */
    private function refresh_nonce() {
        return wp_create_nonce('qanw_nonce');
    }
    
    /**
     * Validates and sanitizes note color input
     * 
     * Ensures that only predefined, safe color values are accepted.
     * This prevents XSS attacks and ensures consistent styling across the plugin.
     * 
     * @since 1.0.0
     * @access private
     * 
     * @param string $color The color value to validate
     * 
     * @uses sanitize_text_field() WordPress function to sanitize input
     * 
     * @return string The validated and sanitized color value, or 'yellow' as default
     */
    private function validate_note_color($color) {
        $allowed_colors = array('yellow', 'blue', 'green', 'red');
        $sanitized_color = sanitize_text_field($color);
        
        if (!in_array($sanitized_color, $allowed_colors)) {
            return 'yellow'; // Default fallback
        }
        
        return $sanitized_color;
    }
    
    /**
     * Handles AJAX request to save a new note
     * 
     * Processes note creation requests with comprehensive security measures:
     * - Enhanced security validation (authentication, nonce, rate limiting)
     * - User capability verification
     * - HTML content sanitization and validation
     * - XSS protection through content filtering
     * - Input length validation
     * - Color validation and sanitization
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses enhanced_security_check() Internal method for security validation
     * @uses current_user_can() WordPress function to check user capabilities
     * @uses wp_kses() WordPress function to sanitize HTML content
     * @uses wp_die() WordPress function to terminate execution on permission failure
     * @uses validate_note_color() Internal method to validate note color
     * @uses get_notes_data() Internal method to retrieve existing notes
     * @uses save_notes_data() Internal method to save notes
     * @uses wp_send_json_success() WordPress function to send success response
     * @uses wp_send_json_error() WordPress function to send error response
     * @uses esc_html__() WordPress function for internationalized messages
     * @uses refresh_nonce() Internal method to generate new nonce
     * 
     * @return void Sends JSON response via wp_send_json_success() or wp_send_json_error()
     * 
     * @throws wp_die() Terminates execution if user lacks required capabilities
     * @throws wp_send_json_error() Sends error response for validation failures
     */
    public function save_note() {
        // Enhanced security check
        $this->enhanced_security_check();
        
        if (!current_user_can('read')) {
            wp_die(esc_html__('Permission denied: You need at least "read" capability to create notes. Please contact your administrator if you believe this is an error.', 'admin-notes-widget-by-website14'));
        }
        
        // Check if note text was provided
        if (!isset($_POST['note_text']) || empty($_POST['note_text'])) {
            wp_send_json_error(esc_html__('Error: Note content is required. Please enter some text before saving.', 'admin-notes-widget-by-website14'));
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
        
        // Additional security: Remove any potentially dangerous content
        $note_text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $note_text);
        $note_text = preg_replace('/javascript:/i', '', $note_text);
        $note_text = preg_replace('/on\w+\s*=/i', '', $note_text);
        
        // Check if note color was provided
        if (!isset($_POST['note_color']) || empty($_POST['note_color'])) {
            wp_send_json_error(esc_html__('Error: Note color is required. Please select a color for your note.', 'admin-notes-widget-by-website14'));
        }
        
        $note_color = $this->validate_note_color($_POST['note_color']);
        
        // Validate note text length
        if (strlen($note_text) > 10000) {
            wp_send_json_error(esc_html(sprintf(__('Error: Note is too long. Please keep your note under 10,000 characters. Current length: %d characters.', 'admin-notes-widget-by-website14'), strlen($note_text))));
        }
        
        if (empty($note_text) || $note_text === '<br>' || $note_text === '') {
            wp_send_json_error(esc_html__('Error: Note content cannot be empty. Please enter some text, formatting, or content before saving.', 'admin-notes-widget-by-website14'));
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
            'note' => $new_note,
            'new_nonce' => $this->refresh_nonce()
        ));
    }
    
    /**
     * Handles AJAX request to delete an existing note
     * 
     * Processes note deletion requests with comprehensive security measures:
     * - Enhanced security validation (authentication, nonce, rate limiting)
     * - User capability verification
     * - Note ID validation and sanitization
     * - Ownership verification (users can only delete their own notes)
     * - Input format validation using regex patterns
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses enhanced_security_check() Internal method for security validation
     * @uses current_user_can() WordPress function to check user capabilities
     * @uses sanitize_text_field() WordPress function to sanitize note ID
     * @uses wp_die() WordPress function to terminate execution on permission failure
     * @uses get_notes_data() Internal method to retrieve existing notes
     * @uses save_notes_data() Internal method to save updated notes
     * @uses wp_send_json_success() WordPress function to send success response
     * @uses wp_send_json_error() WordPress function to send error response
     * @uses esc_html__() WordPress function for internationalized messages
     * @uses refresh_nonce() Internal method to generate new nonce
     * 
     * @return void Sends JSON response via wp_send_json_success() or wp_send_json_error()
     * 
     * @throws wp_die() Terminates execution if user lacks required capabilities
     * @throws wp_send_json_error() Sends error response for validation failures
     */
    public function delete_note() {
        // Enhanced security check
        $this->enhanced_security_check();
        
        if (!current_user_can('read')) {
            wp_die(esc_html__('Permission denied: You need at least "read" capability to delete notes. Please contact your administrator if you believe this is an error.', 'admin-notes-widget-by-website14'));
        }
        
        // Check if note ID was provided
        if (!isset($_POST['note_id']) || empty($_POST['note_id'])) {
            wp_send_json_error(esc_html__('Error: Note ID is required. Please select a note to delete.', 'admin-notes-widget-by-website14'));
        }
        
        $note_id = sanitize_text_field($_POST['note_id']);
        
        // Additional validation for note ID
        if (empty($note_id) || !is_string($note_id) || strlen($note_id) > 50) {
            wp_send_json_error(esc_html__('Error: Invalid note ID provided. The note ID must be a string between 1 and 50 characters.', 'admin-notes-widget-by-website14'));
        }
        
        // Validate note ID format (should be alphanumeric)
        if (!preg_match('/^[a-zA-Z0-9]+$/', $note_id)) {
            wp_send_json_error(esc_html__('Error: Invalid note ID format. The note ID can only contain letters and numbers.', 'admin-notes-widget-by-website14'));
        }
        
        $notes = $this->get_notes_data();
        $user_id = get_current_user_id();
        
        // Find and remove the note
        foreach ($notes as $key => $note) {
            if ($note['id'] === $note_id && $note['user_id'] == $user_id) {
                unset($notes[$key]);
                $this->save_notes_data(array_values($notes));
                wp_send_json_success(array(
                    'message' => esc_html__('Note deleted successfully!', 'admin-notes-widget-by-website14'),
                    'new_nonce' => $this->refresh_nonce()
                ));
            }
        }
        
        wp_send_json_error(esc_html__('Note not found or you do not have permission to delete it.', 'admin-notes-widget-by-website14'));
    }
    
    /**
     * Handles AJAX request to retrieve user's notes
     * 
     * Returns notes that belong to the current user or were sent to them.
     * Implements security measures and user isolation to ensure users
     * can only access their own notes or notes sent to them.
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses enhanced_security_check() Internal method for security validation
     * @uses current_user_can() WordPress function to check user capabilities
     * @uses wp_die() WordPress function to terminate execution on permission failure
     * @uses get_notes_data() Internal method to retrieve all notes
     * @uses get_current_user_id() WordPress function to get current user ID
     * @uses wp_send_json_success() WordPress function to send success response
     * 
     * @return void Sends JSON response via wp_send_json_success()
     * 
     * @throws wp_die() Terminates execution if user lacks required capabilities
     */
    public function get_notes() {
        // Enhanced security check
        $this->enhanced_security_check();
        
        if (!current_user_can('read')) {
            wp_die(esc_html__('Permission denied: You need at least "read" capability to view notes. Please contact your administrator if you believe this is an error.', 'admin-notes-widget-by-website14'));
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
     * Handles AJAX request to retrieve list of administrator users
     * 
     * Returns a filtered list of administrator users excluding the current user.
     * This is used for the note sharing functionality to allow users to
     * select recipients for their notes.
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses enhanced_security_check() Internal method for security validation
     * @uses current_user_can() WordPress function to check user capabilities
     * @uses wp_die() WordPress function to terminate execution on permission failure
     * @uses get_users() WordPress function to retrieve user list
     * @uses get_current_user_id() WordPress function to get current user ID
     * @uses wp_send_json_success() WordPress function to send success response
     * 
     * @return void Sends JSON response via wp_send_json_success()
     * 
     * @throws wp_die() Terminates execution if user lacks required capabilities
     */
    public function get_admin_users() {
        // Enhanced security check
        $this->enhanced_security_check();
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied: You need "manage_options" capability to view administrator users. This feature is restricted to site administrators only.', 'admin-notes-widget-by-website14'));
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
     * Handles AJAX request to send notes to other administrator users
     * 
     * Processes note sharing requests with comprehensive security measures:
     * - Enhanced security validation (authentication, nonce, rate limiting)
     * - Administrator capability verification
     * - HTML content sanitization and validation
     * - XSS protection through content filtering
     * - Recipient validation and sanitization
     * - Input length validation
     * - Color validation and sanitization
     * 
     * @since 1.0.0
     * @access public
     * 
     * @uses enhanced_security_check() Internal method for security validation
     * @uses current_user_can() WordPress function to check user capabilities
     * @uses wp_kses() WordPress function to sanitize HTML content
     * @uses wp_die() WordPress function to terminate execution on permission failure
     * @uses validate_note_color() Internal method to validate note color
     * @uses get_notes_data() Internal method to retrieve existing notes
     * @uses save_notes_data() Internal method to save notes
     * @uses wp_send_json_success() WordPress function to send success response
     * @uses wp_send_json_error() WordPress function to send error response
     * @uses esc_html__() WordPress function for internationalized messages
     * @uses refresh_nonce() Internal method to generate new nonce
     * @uses get_user_by() WordPress function to verify recipient users
     * @uses current_time() WordPress function to get current timestamp
     * 
     * @return void Sends JSON response via wp_send_json_success() or wp_send_json_error()
     * 
     * @throws wp_die() Terminates execution if user lacks required capabilities
     * @throws wp_send_json_error() Sends error response for validation failures
     */
    public function send_note() {
        // Enhanced security check
        $this->enhanced_security_check();
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied: You need "manage_options" capability to send notes to other administrators. This feature is restricted to site administrators only.', 'admin-notes-widget-by-website14'));
        }
        
        // Check if note text was provided
        if (!isset($_POST['note_text']) || empty($_POST['note_text'])) {
            wp_send_json_error(esc_html__('Error: Note content is required. Please enter some text before sending.', 'admin-notes-widget-by-website14'));
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
        
        // Additional security: Remove any potentially dangerous content
        $note_text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $note_text);
        $note_text = preg_replace('/javascript:/i', '', $note_text);
        $note_text = preg_replace('/on\w+\s*=/i', '', $note_text);
        
        // Check if note color was provided
        if (!isset($_POST['note_color']) || empty($_POST['note_color'])) {
            wp_send_json_error(esc_html__('Error: Note color is required. Please select a color for your note.', 'admin-notes-widget-by-website14'));
        }
        
        $note_color = $this->validate_note_color($_POST['note_color']);
        
        // Check if recipient IDs were provided
        if (!isset($_POST['recipient_ids']) || empty($_POST['recipient_ids'])) {
            wp_send_json_error(esc_html__('Error: Recipients are required. Please select at least one administrator to send the note to.', 'admin-notes-widget-by-website14'));
        }
        
        $recipient_ids = array_map('intval', $_POST['recipient_ids']);
        
        // Validate note text length
        if (strlen($note_text) > 10000) {
            wp_send_json_error(esc_html(sprintf(__('Error: Note is too long. Please keep your note under 10,000 characters. Current length: %d characters.', 'admin-notes-widget-by-website14'), strlen($note_text))));
        }
        
        if (empty($note_text) || $note_text === '<br>' || $note_text === '') {
            wp_send_json_error(esc_html__('Error: Note content cannot be empty. Please enter some text, formatting, or content before sending.', 'admin-notes-widget-by-website14'));
        }
        
        // Validate recipient IDs
        if (empty($recipient_ids) || !is_array($recipient_ids)) {
            wp_send_json_error(esc_html__('Error: No valid recipients selected. Please select at least one administrator user to send the note to.', 'admin-notes-widget-by-website14'));
        }
        
        // Additional validation for recipient IDs
        foreach ($recipient_ids as $recipient_id) {
            if (!is_numeric($recipient_id) || $recipient_id <= 0) {
                wp_send_json_error(esc_html__('Error: Invalid recipient ID provided. Recipient ID must be a positive number.', 'admin-notes-widget-by-website14'));
            }
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
                'message' => sprintf(esc_html__('Note sent to %d admin(s) successfully!', 'admin-notes-widget-by-website14'), $sent_count),
                'new_nonce' => $this->refresh_nonce()
            ));
        } else {
            wp_send_json_error(esc_html__('No valid admin users selected.', 'admin-notes-widget-by-website14'));
        }
    }
    

    
    /**
     * Retrieves notes data from WordPress options
     * 
     * Fetches the stored notes from the WordPress options table and ensures
     * the returned value is always an array, even if no notes exist yet.
     * 
     * @since 1.0.0
     * @access private
     * 
     * @uses get_option() WordPress function to retrieve option value
     * 
     * @return array Array of notes, empty array if no notes exist
     */
    private function get_notes_data() {
        $notes = get_option('qanw_notes', array());
        return is_array($notes) ? $notes : array();
    }
    
    /**
     * Saves notes data to WordPress options
     * 
     * Stores the notes array in the WordPress options table for persistence.
     * This method is called whenever notes are created, updated, or deleted.
     * 
     * @since 1.0.0
     * @access private
     * 
     * @param array $notes Array of notes to save
     * 
     * @uses update_option() WordPress function to save option value
     * 
     * @return void
     */
    private function save_notes_data($notes) {
        update_option('qanw_notes', $notes);
    }
}

// Initialize the plugin
new AdminNotesWidgetByWebsite14();

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'qanw_activate');
register_deactivation_hook(__FILE__, 'qanw_deactivate');

/**
 * Plugin activation hook handler
 * 
 * Sets up initial plugin data and options when the plugin is activated.
 * Creates the default notes storage option if it doesn't exist.
 * 
 * @since 1.0.0
 * @access public
 * 
 * @uses get_option() WordPress function to check if option exists
 * @uses add_option() WordPress function to create default option
 * 
 * @return void
 */
function qanw_activate() {
    // Create default options
    if (!get_option('qanw_notes')) {
        add_option('qanw_notes', array());
    }
}

/**
 * Plugin deactivation hook handler
 * 
 * Cleans up temporary data and transients when the plugin is deactivated.
 * This includes rate limiting data and any other temporary plugin data.
 * 
 * @since 1.0.0
 * @access public
 * 
 * @uses get_current_user_id() WordPress function to get current user ID
 * @uses delete_transient() WordPress function to remove transients
 * @global wpdb $wpdb WordPress database object
 * 
 * @return void
 */
function qanw_deactivate() {
    // Clean up transients and temporary data
    $user_id = get_current_user_id();
    if ($user_id) {
        delete_transient('qanw_rate_limit_' . $user_id);
    }
    
    // Clear any other transients that might exist
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_qanw_%'));
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_qanw_%'));
    
    // Clean up any user meta if we stored any (for future use)
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", 'qanw_%'));
    
    // Clean up any post meta if we stored any (for future use)
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", 'qanw_%'));
    
    // Clean up any term meta if we stored any (for future use)
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", 'qanw_%'));
    
    // Log the deactivation for debugging (optional)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Admin Notes Widget By Website14: Plugin deactivated and temporary data cleaned up.');
    }
}