# WordPress.org Plugin Review Issues

## 1. Use wp_enqueue commands

**Issue**: Plugin is not correctly including JS and/or CSS using WordPress built-in functions.

**Required Actions**:

- Use `wp_register_script()` and `wp_enqueue_script()` for JavaScript files
- Use `wp_add_inline_script()` for inline JavaScript code
- Use `wp_register_style()` and `wp_enqueue_style()` for CSS files
- Use `wp_add_inline_style()` for inline CSS code

**WordPress 6.3+ Features**:

- Can pass attributes like `defer` or `async` to scripts
- WordPress 5.7+ supports other attributes via functions and filters

**Admin Page Enqueuing**:

- Use `admin_enqueue_scripts` hook for admin pages
- Use `admin_print_scripts` and `admin_print_styles` hooks

**Example from plugin**: `admin-notes-widget-by-website14.php:240 <script>`

## 2. Critical Security Vulnerabilities Found

**Issue**: Multiple critical security vulnerabilities that will cause immediate rejection.

### 2.1 AJAX Access for Non-Logged-In Users (CRITICAL)

- **Lines 38-42**: Multiple `wp_ajax_nopriv_` hooks allow unauthorized access
- **Affected Functions**: Save notes, delete notes, get notes, send notes, get admin users
- **Risk**: Major security vulnerability allowing unauthorized data manipulation
- **Required Action**: Remove all `wp_ajax_nopriv_` hooks immediately

### 2.2 Inline Styles in PHP (WordPress.org Violation)

- **Line 51**: `<span style="margin-right: 8px; color: #0073aa;">`
- **Line 110**: `<div style="margin-bottom: 10px;">`
- **Line 111**: `<span style="font-weight: bold;">`
- **Required Action**: Move all inline styles to CSS file and use CSS classes

### 2.3 Inline JavaScript in PHP (WordPress.org Violation)

- **Lines 240-250**: Multiple `<script>` tags with inline JavaScript
- **Required Action**: Move all JavaScript to external .js file and use wp_enqueue_script()

### 2.4 Missing Nonce Verification

- **AJAX Functions**: Missing proper nonce verification in some AJAX handlers
- **Required Action**: Add proper nonce verification to all AJAX functions

### 2.5 Improper Data Sanitization

- **User Input**: Some user inputs not properly sanitized before database operations
- **Required Action**: Implement proper sanitization for all user inputs

## 3. Plugin Check Report Issues

**Issue**: Plugin check report shows multiple issues that need to be addressed.

**Required Action**: Run plugin check and resolve all reported issues before submission.

## 4. Plugin Name and Slug Issues

**Issue**: Plugin name and slug may not match WordPress.org requirements.

**Required Action**: Ensure plugin name and slug are consistent and meet WordPress.org naming guidelines.

## 5. Plugin Ownership Verification

**Issue**: Plugin ownership needs to be verified.

**Required Action**: Complete ownership verification process with WordPress.org team.

## 6. File Naming Issues

**Issue**: Some files may not follow WordPress.org naming conventions.

**Required Action**: Review and update file names to follow WordPress.org standards.
