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

## 4. Extraneous Plugin Assets Files

**Issue**: Plugin assets should not be included in the plugin itself, but committed to SVN repository after deployment.

**Files Found**:

- `icon-256x256.png`
- `icon-128x128.png`

**Required Action**: Remove these files from the plugin package. They should be committed to SVN repository separately.

## 5. Plugin Check Report Issues

**Status**: Plugin Check Report generated with the above findings.

**Required Actions**:

1. Fix all identified issues
2. Test on clean WordPress installation with WP_DEBUG set to true
3. Upload corrected version
4. Reply to review email

## 6. Review Process Notes

**Review ID**: R admin-notes-widget-by-website14/website14/27Jul25/T2 14Aug25/3.6B

**Next Steps**:

1. Fix all issues based on feedback
2. Test thoroughly on clean WordPress installation
3. Upload updated version
4. Reply to review email (be concise, no need to list changes)

**Important**: Volunteers will review the entire plugin again, so ensure all issues are addressed before resubmission.
