# WordPress.org Plugin Review Issues

## 1. Use wp_enqueue commands

- Plugin is not correctly including JS and/or CSS
- Should use built-in WordPress functions:
  - `wp_register_script()` and `wp_enqueue_script()` for JavaScript
  - `wp_add_inline_script()` for inline JavaScript
  - `wp_register_style()` and `wp_enqueue_style()` for CSS
  - `wp_add_inline_style()` for inline CSS
- Example from plugin: `admin-notes-widget-by-website14.php:240 <script>`

## 2. Undocumented use of a 3rd Party / external service

- Plugin reaches out to external services without proper documentation
- Must disclose in readme file:
  - What the service is and what it is used for
  - What data is sent and when
  - Links to service's terms of service and privacy policy
- Example from plugin: `admin-notes-widget-by-website14.php:560 $api_url = 'http://api.syedqutubuddin.in/suggestions_api.php'`

## 3. Plugin Readme Issues

- Short Description section too long (maximum 150 characters supported)
- Too many tags (limit to 5 tags)

## 4. Security Issues - Improper Escaping

- All output should be run through escaping functions
- Found `_e` function usage (should use `esc_html_e`, `esc_attr_e`, etc.)
- Found unescaped output from functions like `admin_url`
- Total of 40 incidences of unsafe printing functions
- Total of 8 incidences of unescaped output

## 5. Plugin Name and Slug Issues

- Plugin name "Quick Admin Notes Widget" is very generic
- Conflicts with existing "Admin dashboard notes" plugins
- Can confuse users
- Slug "quick-admin-notes-widget" is too generic

## 6. Plugin Ownership Verification

- Email domain "psychebot.pro" doesn't seem related to plugin URLs/names
- Author URI "https://www.website14.com" could not resolve host
- Need to clarify ownership or change plugin details

## 7. File Naming Issues

- Plugin zip filename is incorrect
- Found: "Quick-Admin-Notes-Widget.zip"
- Expected: "quick-admin-notes-widget.zip"
