# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2024-01-XX

### Added

- Initial release of Quick Admin Notes Widget
- Dashboard widget integration with WordPress admin
- Rich text editor with formatting options (bold, italic, underline, lists, links, code)
- Color-coded notes system (yellow, blue, green, red)
- Send notes to other administrators functionality
- AJAX operations for seamless user experience
- User-specific notes (each user sees only their own notes and notes sent to them)
- Responsive design for desktop and mobile devices
- Complete uninstall functionality with data cleanup
- Feature suggestion system with dedicated admin page
- Buy me a coffee support link
- Internationalization support with translation template
- Security features (nonce verification, capability checks, input sanitization)

### Technical Features

- WordPress Dashboard Widget API integration
- AJAX handlers for all note operations
- WordPress Options API for data persistence
- Content sanitization using wp_kses()
- User capability verification
- Cross-site request forgery (CSRF) protection
- XSS prevention through proper HTML escaping
- Complete uninstall cleanup
- Translation-ready with POT file

### Files Structure

- `quick-admin-notes-widget.php` - Main plugin file
- `uninstall.php` - Cleanup script
- `readme.txt` - WordPress.org readme
- `readme.md` - GitHub readme
- `changelog.md` - This changelog file
- `LICENSE` - GPL v2 license
- `assets/css/qanw-style.css` - Plugin styling
- `assets/js/qanw-script.js` - JavaScript functionality
- `languages/quick-admin-notes-widget.pot` - Translation template
