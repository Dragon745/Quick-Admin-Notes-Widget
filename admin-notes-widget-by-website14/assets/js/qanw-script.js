jQuery(document).ready(function ($) {

    // Load notes on page load
    loadNotes();

    // Add note button click
    $('#qanw-add-note').on('click', function () {
        addNote();
    });

    // Send note button click
    $('#qanw-send-note').on('click', function () {
        openSendModal();
    });

    // Enter key in editor
    $('#qanw-editor').on('keydown', function (e) {
        if (e.ctrlKey && e.keyCode === 13) {
            addNote();
        }

        // Keyboard shortcuts for formatting
        if (e.ctrlKey) {
            switch (e.keyCode) {
                case 66: // Ctrl+B for bold
                    e.preventDefault();
                    applyFormat('bold');
                    break;
                case 73: // Ctrl+I for italic
                    e.preventDefault();
                    applyFormat('italic');
                    break;
                case 85: // Ctrl+U for underline
                    e.preventDefault();
                    applyFormat('underline');
                    break;
                case 75: // Ctrl+K for link
                    e.preventDefault();
                    applyFormat('link');
                    break;
                case 90: // Ctrl+Z for undo
                    e.preventDefault();
                    if (e.shiftKey) {
                        // Ctrl+Shift+Z for redo
                        redoAction();
                    } else {
                        undoAction();
                    }
                    break;
                case 89: // Ctrl+Y for redo (alternative)
                    e.preventDefault();
                    redoAction();
                    break;
            }
        }
    });

    // Format buttons
    $('.qanw-format-btn').on('click', function () {
        var format = $(this).data('format');
        applyFormat(format);
    });

    // Modal close buttons
    $('.qanw-modal-close, .qanw-modal-cancel').on('click', function () {
        closeSendModal();
    });

    // Confirm send button
    $('#qanw-confirm-send').on('click', function () {
        sendNote();
    });

    // Close modal when clicking outside
    $(window).on('click', function (e) {
        if ($(e.target).hasClass('qanw-modal')) {
            closeSendModal();
        }
    });

    // Placeholder functionality for contenteditable
    $('#qanw-editor').on('focus', function () {
        if ($(this).text().trim() === '') {
            $(this).addClass('qanw-placeholder');
        }
    }).on('blur', function () {
        if ($(this).text().trim() === '') {
            $(this).addClass('qanw-placeholder');
        } else {
            $(this).removeClass('qanw-placeholder');
        }
    }).on('input', function () {
        if ($(this).text().trim() !== '') {
            $(this).removeClass('qanw-placeholder');
        }
    }).on('paste', function (e) {
        // Handle paste events securely
        e.preventDefault();
        var pastedText = '';

        if (e.clipboardData && e.clipboardData.getData) {
            pastedText = e.clipboardData.getData('text/html') || e.clipboardData.getData('text/plain');
        } else if (window.clipboardData && window.clipboardData.getData) {
            pastedText = window.clipboardData.getData('Text');
        }

        if (pastedText) {
            // Sanitize pasted content
            var sanitizedContent = sanitizeHtml(pastedText);

            // Insert sanitized content using modern methods
            var selection = window.getSelection();
            if (selection.rangeCount) {
                var range = selection.getRangeAt(0);
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = sanitizedContent;

                // Insert the sanitized content
                var fragment = document.createDocumentFragment();
                while (tempDiv.firstChild) {
                    fragment.appendChild(tempDiv.firstChild);
                }

                range.deleteContents();
                range.insertNode(fragment);
                range.collapse(false);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }
    });

    /**
     * Add a new note
     */
    function addNote() {
        var noteText = $('#qanw-editor').html().trim();
        var noteColor = $('input[name="note_color"]:checked').val();

        if (!noteText || noteText === '<br>' || noteText === '') {
            alert(qanw_ajax.strings.please_enter_note);
            return;
        }

        // Sanitize the HTML before sending
        noteText = sanitizeHtml(noteText);

        $.ajax({
            url: qanw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qanw_save_note',
                note_text: noteText,
                note_color: noteColor,
                nonce: qanw_ajax.nonce
            },
            beforeSend: function () {
                $('#qanw-add-note').prop('disabled', true).text('Adding...');
            },
            success: function (response) {
                if (response.success) {
                    $('#qanw-editor').html('').addClass('qanw-placeholder');
                    loadNotes();
                    showMessage(response.data.message, 'success');

                    // Update nonce if provided
                    if (response.data.new_nonce) {
                        qanw_ajax.nonce = response.data.new_nonce;
                    }
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function (xhr, status, error) {
                // Handle specific HTTP error codes with detailed messages
                var errorMessage = '';
                var errorType = 'error';

                switch (xhr.status) {
                    case 0:
                        errorMessage = 'Network error: Unable to connect to the server. Please check your internet connection and try again.';
                        break;
                    case 400:
                        errorMessage = 'Bad request: The server could not understand your request. Please check your input and try again.';
                        break;
                    case 401:
                        errorMessage = qanw_ajax.strings.authentication_required;
                        errorType = 'warning';
                        break;
                    case 403:
                        errorMessage = qanw_ajax.strings.security_check_failed;
                        errorType = 'warning';
                        break;
                    case 404:
                        errorMessage = 'Server error: The requested resource was not found. Please refresh the page and try again.';
                        break;
                    case 429:
                        errorMessage = qanw_ajax.strings.too_many_requests;
                        errorType = 'warning';
                        break;
                    case 500:
                        errorMessage = 'Server error: An internal server error occurred. Please try again later or contact support.';
                        break;
                    case 503:
                        errorMessage = 'Service unavailable: The server is temporarily unavailable. Please try again later.';
                        break;
                    default:
                        if (xhr.status >= 500) {
                            errorMessage = 'Server error: The server encountered an error. Please try again later.';
                        } else if (xhr.status >= 400) {
                            errorMessage = 'Client error: There was a problem with your request. Please check your input and try again.';
                        } else {
                            errorMessage = qanw_ajax.strings.error + ' (Status: ' + xhr.status + ')';
                        }
                        break;
                }

                // Log detailed error information for debugging
                if (console && console.error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }

                showMessage(errorMessage, errorType);
            },
            complete: function () {
                $('#qanw-add-note').prop('disabled', false).text('Add Note');
            }
        });
    }

    /**
     * Load all notes
     */
    function loadNotes() {
        $.ajax({
            url: qanw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qanw_get_notes',
                nonce: qanw_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    displayNotes(response.data);

                    // Update nonce if provided
                    if (response.data.new_nonce) {
                        qanw_ajax.nonce = response.data.new_nonce;
                    }
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function (xhr, status, error) {
                // Handle specific HTTP error codes with detailed messages
                var errorMessage = '';
                var errorType = 'error';

                switch (xhr.status) {
                    case 0:
                        errorMessage = 'Network error: Unable to connect to the server. Please check your internet connection and try again.';
                        break;
                    case 400:
                        errorMessage = 'Bad request: The server could not understand your request. Please refresh the page and try again.';
                        break;
                    case 401:
                        errorMessage = qanw_ajax.strings.authentication_required;
                        errorType = 'warning';
                        break;
                    case 403:
                        errorMessage = qanw_ajax.strings.security_check_failed;
                        errorType = 'warning';
                        break;
                    case 404:
                        errorMessage = 'Server error: The requested resource was not found. Please refresh the page and try again.';
                        break;
                    case 429:
                        errorMessage = qanw_ajax.strings.too_many_requests;
                        errorType = 'warning';
                        break;
                    case 500:
                        errorMessage = 'Server error: An internal server error occurred. Please try again later or contact support.';
                        break;
                    case 503:
                        errorMessage = 'Service unavailable: The server is temporarily unavailable. Please try again later.';
                        break;
                    default:
                        if (xhr.status >= 500) {
                            errorMessage = 'Server error: The server encountered an error. Please try again later.';
                        } else if (xhr.status >= 400) {
                            errorMessage = 'Client error: There was a problem with your request. Please refresh the page and try again.';
                        } else {
                            errorMessage = qanw_ajax.strings.error + ' (Status: ' + xhr.status + ')';
                        }
                        break;
                }

                // Log detailed error information for debugging
                if (console && console.error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }

                showMessage(errorMessage, errorType);
            }
        });
    }

    /**
     * Display notes in the container
     */
    function displayNotes(notes) {
        var container = $('#qanw-notes-list');
        container.empty();

        if (notes.length === 0) {
            container.html('<p class="qanw-no-notes">No notes yet. Add your first note above!</p>');
            return;
        }

        // Sort notes by creation date (newest first)
        notes.sort(function (a, b) {
            return new Date(b.created) - new Date(a.created);
        });

        notes.forEach(function (note) {
            var noteHtml = createNoteHtml(note);
            container.append(noteHtml);
        });

        // Bind delete events
        $('.qanw-delete-note').on('click', function () {
            var noteId = $(this).data('note-id');
            deleteNote(noteId);
        });
    }

    /**
     * Create HTML for a single note
     */
    function createNoteHtml(note) {
        var date = new Date(note.created);
        var formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        var senderInfo = '';
        if (note.is_sent_note && note.sender_name) {
            senderInfo = '<div class="qanw-note-sender">From: ' + note.sender_name + '</div>';
        }

        // Sanitize the note text to prevent XSS attacks
        var sanitizedText = sanitizeHtml(note.text);

        return '<div class="qanw-note qanw-note-' + note.color + '" data-note-id="' + note.id + '">' +
            '<div class="qanw-note-content">' +
            senderInfo +
            '<div class="qanw-note-text">' + sanitizedText + '</div>' +
            '<div class="qanw-note-meta">' +
            '<span class="qanw-note-date">' + formattedDate + '</span>' +
            '<button type="button" class="qanw-delete-note" data-note-id="' + note.id + '" title="Delete note">' +
            '<span class="dashicons dashicons-trash"></span>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>';
    }

    /**
     * Delete a note
     */
    function deleteNote(noteId) {
        if (!confirm(qanw_ajax.strings.confirm_delete)) {
            return;
        }

        $.ajax({
            url: qanw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qanw_delete_note',
                note_id: noteId,
                nonce: qanw_ajax.nonce
            },
            beforeSend: function () {
                $('[data-note-id="' + noteId + '"]').addClass('qanw-deleting');
            },
            success: function (response) {
                if (response.success) {
                    $('[data-note-id="' + noteId + '"]').fadeOut(300, function () {
                        $(this).remove();
                        if ($('.qanw-note').length === 0) {
                            $('#qanw-notes-list').html('<p class="qanw-no-notes">No notes yet. Add your first note above!</p>');
                        }
                    });
                    showMessage(response.data.message || response.data, 'success');

                    // Update nonce if provided
                    if (response.data.new_nonce) {
                        qanw_ajax.nonce = response.data.new_nonce;
                    }
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function (xhr, status, error) {
                // Handle specific HTTP error codes
                if (xhr.status === 401) {
                    showMessage(qanw_ajax.strings.authentication_required, 'error');
                } else if (xhr.status === 403) {
                    showMessage(qanw_ajax.strings.security_check_failed, 'error');
                } else if (xhr.status === 429) {
                    showMessage(qanw_ajax.strings.too_many_requests, 'error');
                } else {
                    showMessage(qanw_ajax.strings.error, 'error');
                }
            },
            complete: function () {
                $('[data-note-id="' + noteId + '"]').removeClass('qanw-deleting');
            }
        });
    }

    /**
     * Show message to user
     */
    function showMessage(message, type) {
        // Remove existing messages
        $('.qanw-message').remove();

        // Sanitize the message to prevent XSS attacks (extra security layer)
        var sanitizedMessage = sanitizeHtml(message);

        var messageClass = 'qanw-message qanw-message-' + type;
        var messageHtml = '<div class="' + messageClass + '">' + sanitizedMessage + '</div>';

        $('#qanw-container').prepend(messageHtml);

        // Auto-hide after 3 seconds
        setTimeout(function () {
            $('.qanw-message').fadeOut(300, function () {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Apply formatting to selected text using modern Selection API
     */
    function applyFormat(format) {
        var editor = $('#qanw-editor')[0];
        var selection = window.getSelection();

        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);

        switch (format) {
            case 'bold':
                applyInlineFormat('strong', 'b');
                break;
            case 'italic':
                applyInlineFormat('em', 'i');
                break;
            case 'underline':
                applyInlineFormat('u');
                break;
            case 'ul':
                insertList('ul');
                break;
            case 'ol':
                insertList('ol');
                break;
            case 'link':
                insertLink();
                break;
            case 'code':
                var codeElement = document.createElement('code');
                if (selection.toString()) {
                    range.surroundContents(codeElement);
                } else {
                    codeElement.textContent = 'code';
                    range.insertNode(codeElement);
                }
                break;
        }

        editor.focus();
    }

    /**
     * Apply inline formatting (bold, italic, underline)
     */
    function applyInlineFormat(primaryTag, fallbackTag) {
        var selection = window.getSelection();
        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);
        var selectedText = selection.toString();

        if (selectedText) {
            // Check if text is already formatted
            var parentElement = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
                ? range.commonAncestorContainer.parentNode
                : range.commonAncestorContainer;

            // If already formatted, remove formatting
            if (parentElement.tagName &&
                (parentElement.tagName.toLowerCase() === primaryTag.toLowerCase() ||
                    (fallbackTag && parentElement.tagName.toLowerCase() === fallbackTag.toLowerCase()))) {
                // Unwrap the element
                var unwrappedContent = document.createRange();
                unwrappedContent.selectNodeContents(parentElement);
                var fragment = unwrappedContent.extractContents();
                parentElement.parentNode.insertBefore(fragment, parentElement);
                parentElement.parentNode.removeChild(parentElement);
                return;
            }

            // Apply new formatting
            var formatElement = document.createElement(primaryTag);
            range.surroundContents(formatElement);
        } else {
            // Insert formatting element at cursor
            var formatElement = document.createElement(primaryTag);
            formatElement.textContent = primaryTag === 'strong' ? qanw_ajax.strings.bold_text :
                primaryTag === 'em' ? qanw_ajax.strings.italic_text :
                    qanw_ajax.strings.underline_text;
            range.insertNode(formatElement);
            range.setStartAfter(formatElement);
            range.setEndAfter(formatElement);
        }
        selection.removeAllRanges();
        selection.addRange(range);
    }

    /**
     * Insert list (ordered or unordered)
     */
    function insertList(listType) {
        var selection = window.getSelection();
        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);
        var selectedText = selection.toString();

        if (selectedText) {
            // Split text into lines
            var lines = selectedText.split('\n');
            var listElement = document.createElement(listType);

            lines.forEach(function (line) {
                if (line.trim()) {
                    var listItem = document.createElement('li');
                    listItem.textContent = line.trim();
                    listElement.appendChild(listItem);
                }
            });

            // Replace selected text with list
            range.deleteContents();
            range.insertNode(listElement);
        } else {
            // Insert empty list at cursor
            var listElement = document.createElement(listType);
            var listItem = document.createElement('li');
            listItem.textContent = qanw_ajax.strings.list_item;
            listElement.appendChild(listItem);
            range.insertNode(listElement);
            range.setStart(listItem, 0);
            range.setEnd(listItem, 0);
        }
        selection.removeAllRanges();
        selection.addRange(range);
    }

    /**
     * Insert link
     */
    function insertLink() {
        var selection = window.getSelection();
        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);
        var selectedText = selection.toString();

        if (selectedText) {
            var url = prompt(qanw_ajax.strings.enter_url);
            if (url && url.trim()) {
                // Validate URL
                if (!isValidUrl(url)) {
                    url = 'https://' + url;
                }

                var linkElement = document.createElement('a');
                linkElement.href = url;
                linkElement.textContent = selectedText;
                linkElement.target = '_blank';
                linkElement.rel = 'noopener noreferrer';

                range.deleteContents();
                range.insertNode(linkElement);
            }
        } else {
            // Insert link at cursor
            var url = prompt(qanw_ajax.strings.enter_url);
            if (url && url.trim()) {
                if (!isValidUrl(url)) {
                    url = 'https://' + url;
                }

                var linkElement = document.createElement('a');
                linkElement.href = url;
                linkElement.textContent = qanw_ajax.strings.link_text;
                linkElement.target = '_blank';
                linkElement.rel = 'noopener noreferrer';

                range.insertNode(linkElement);
                range.setStartAfter(linkElement);
                range.setEndAfter(linkElement);
            }
        }
        selection.removeAllRanges();
        selection.addRange(range);
    }

    /**
     * Validate URL format
     */
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    /**
     * Undo/Redo functionality
     */
    var undoStack = [];
    var redoStack = [];
    var maxUndoSteps = 50;

    function saveState() {
        var editor = $('#qanw-editor')[0];
        var currentState = editor.innerHTML;

        // Don't save if it's the same as the last state
        if (undoStack.length === 0 || undoStack[undoStack.length - 1] !== currentState) {
            undoStack.push(currentState);

            // Limit undo stack size
            if (undoStack.length > maxUndoSteps) {
                undoStack.shift();
            }

            // Clear redo stack when new action is performed
            redoStack = [];
        }
    }

    function undoAction() {
        if (undoStack.length > 0) {
            var editor = $('#qanw-editor')[0];
            var currentState = editor.innerHTML;

            // Save current state to redo stack
            redoStack.push(currentState);

            // Restore previous state
            var previousState = undoStack.pop();
            editor.innerHTML = previousState;

            // Maintain cursor position
            editor.focus();
        }
    }

    function redoAction() {
        if (redoStack.length > 0) {
            var editor = $('#qanw-editor')[0];
            var currentState = editor.innerHTML;

            // Save current state to undo stack
            undoStack.push(currentState);

            // Restore next state
            var nextState = redoStack.pop();
            editor.innerHTML = nextState;

            // Maintain cursor position
            editor.focus();
        }
    }

    // Save state on input changes
    $('#qanw-editor').on('input', function () {
        // Debounce the save to avoid too many saves
        clearTimeout(window.saveStateTimeout);
        window.saveStateTimeout = setTimeout(saveState, 300);
    });

    /**
     * Sanitize HTML to allow safe formatting
     */
    function sanitizeHtml(html) {
        // Allow safe HTML tags only
        var allowedTags = {
            'b': true, 'strong': true, 'i': true, 'em': true, 'u': true,
            'ul': true, 'ol': true, 'li': true, 'a': true, 'code': true,
            'br': true, 'p': true, 'div': true, 'span': true
        };

        // Create a temporary div to parse HTML
        var temp = document.createElement('div');
        temp.innerHTML = html;

        // Remove any script tags and other unsafe elements
        var unsafeElements = temp.querySelectorAll('script, style, iframe, object, embed, form, input, button, select, textarea, meta, link, head, body, html, canvas, svg, audio, video, source, track, map, area, base, bdo, command, datalist, details, dialog, fieldset, legend, menu, menuitem, meter, optgroup, option, output, progress, rp, rt, ruby, summary, time, wbr');
        unsafeElements.forEach(function (el) {
            el.remove();
        });

        // Only allow specific safe attributes
        var allowedAttributes = {
            'a': ['href', 'target', 'rel'],
            'span': ['class'],
            'div': ['class'],
            'p': ['class'],
            'code': ['class']
        };

        var elements = temp.querySelectorAll('*');
        elements.forEach(function (el) {
            var tagName = el.tagName.toLowerCase();
            var allowedAttrs = allowedAttributes[tagName] || [];

            // Remove all attributes except allowed ones
            var attrs = el.attributes;
            for (var i = attrs.length - 1; i >= 0; i--) {
                var attr = attrs[i];
                if (allowedAttrs.indexOf(attr.name) === -1) {
                    el.removeAttribute(attr.name);
                }
            }

            // Additional security: remove any elements with javascript: in href
            if (tagName === 'a' && el.href && el.href.toLowerCase().indexOf('javascript:') !== -1) {
                el.removeAttribute('href');
            }

            // Remove any onclick, onload, onerror, etc. attributes
            var eventAttributes = ['onclick', 'onload', 'onerror', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'onchange', 'onsubmit', 'onreset', 'onselect', 'onunload'];
            eventAttributes.forEach(function (eventAttr) {
                if (el.hasAttribute(eventAttr)) {
                    el.removeAttribute(eventAttr);
                }
            });

            // Remove any data-* attributes that might contain malicious content
            var dataAttributes = el.querySelectorAll('[data-*]');
            dataAttributes.forEach(function (dataEl) {
                var dataAttrs = dataEl.attributes;
                for (var j = dataAttrs.length - 1; j >= 0; j--) {
                    var dataAttr = dataAttrs[j];
                    if (dataAttr.name.startsWith('data-')) {
                        dataEl.removeAttribute(dataAttr.name);
                    }
                }
            });
        });

        return temp.innerHTML;
    }

    /**
     * Open send note modal
     */
    function openSendModal() {
        var noteText = $('#qanw-editor').html().trim();

        if (!noteText || noteText === '<br>' || noteText === '') {
            alert(qanw_ajax.strings.please_enter_note_before_sending);
            return;
        }

        // Load admin users
        loadAdminUsers();

        // Show modal
        $('#qanw-send-modal').fadeIn(300);
    }

    /**
     * Close send note modal
     */
    function closeSendModal() {
        $('#qanw-send-modal').fadeOut(300);
        $('#qanw-admin-users-list').empty();
    }

    /**
     * Load admin users for sending
     */
    function loadAdminUsers() {
        $.ajax({
            url: qanw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qanw_get_admin_users',
                nonce: qanw_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    displayAdminUsers(response.data);

                    // Update nonce if provided
                    if (response.data.new_nonce) {
                        qanw_ajax.nonce = response.data.new_nonce;
                    }
                } else {
                    showMessage(response.data || 'Failed to load admin users.', 'error');
                }
            },
            error: function (xhr, status, error) {
                // Handle specific HTTP error codes
                if (xhr.status === 401) {
                    showMessage(qanw_ajax.strings.authentication_required, 'error');
                } else if (xhr.status === 403) {
                    showMessage(qanw_ajax.strings.security_check_failed, 'error');
                } else if (xhr.status === 429) {
                    showMessage(qanw_ajax.strings.too_many_requests, 'error');
                } else {
                    showMessage('Failed to load admin users.', 'error');
                }
            }
        });
    }

    /**
     * Display admin users in modal
     */
    function displayAdminUsers(users) {
        var container = $('#qanw-admin-users-list');
        container.empty();

        if (users.length === 0) {
            container.html('<p>No other admin users found.</p>');
            return;
        }

        var html = '<div class="qanw-admin-users">';
        users.forEach(function (user) {
            // Sanitize user data to prevent XSS attacks
            var sanitizedName = sanitizeHtml(user.name);
            var sanitizedEmail = sanitizeHtml(user.email);

            html += '<label class="qanw-user-checkbox">' +
                '<input type="checkbox" name="recipient_ids[]" value="' + user.id + '">' +
                '<span class="qanw-user-name">' + sanitizedName + '</span>' +
                '<span class="qanw-user-email">(' + sanitizedEmail + ')</span>' +
                '</label>';
        });
        html += '</div>';

        container.html(html);
    }

    /**
     * Send note to selected admins
     */
    function sendNote() {
        var noteText = $('#qanw-editor').html().trim();
        var noteColor = $('input[name="note_color"]:checked').val();
        var selectedUsers = $('input[name="recipient_ids[]"]:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedUsers.length === 0) {
            showMessage(qanw_ajax.strings.please_select_admin_user, 'warning');
            return;
        }

        if (!noteText || noteText.trim() === '' || noteText === '<br>') {
            showMessage(qanw_ajax.strings.please_enter_note_before_sending, 'warning');
            return;
        }

        // Sanitize the HTML before sending
        noteText = sanitizeHtml(noteText);

        $.ajax({
            url: qanw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'qanw_send_note',
                note_text: noteText,
                note_color: noteColor,
                recipient_ids: selectedUsers,
                nonce: qanw_ajax.nonce
            },
            beforeSend: function () {
                $('#qanw-confirm-send').prop('disabled', true).text('Sending...');
            },
            success: function (response) {
                if (response.success) {
                    $('#qanw-editor').html('').addClass('qanw-placeholder');
                    closeSendModal();
                    loadNotes();
                    showMessage(response.data.message, 'success');

                    // Update nonce if provided
                    if (response.data.new_nonce) {
                        qanw_ajax.nonce = response.data.new_nonce;
                    }
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function (xhr, status, error) {
                // Handle specific HTTP error codes
                if (xhr.status === 401) {
                    showMessage(qanw_ajax.strings.authentication_required, 'error');
                } else if (xhr.status === 403) {
                    showMessage(qanw_ajax.strings.security_check_failed, 'error');
                } else if (xhr.status === 429) {
                    showMessage(qanw_ajax.strings.too_many_requests, 'error');
                } else {
                    showMessage(qanw_ajax.strings.error, 'error');
                }
            },
            complete: function () {
                $('#qanw-confirm-send').prop('disabled', false).text('Send Note');
            }
        });
    }

    /**
     * Enhanced error handling utility function
     * Provides comprehensive error analysis and user-friendly messages
     */
    function handleAjaxError(xhr, status, error, context) {
        var errorMessage = '';
        var errorType = 'error';
        var suggestions = [];

        // Analyze the error and provide specific guidance
        switch (xhr.status) {
            case 0:
                errorMessage = 'Network connection failed';
                suggestions = [
                    'Check your internet connection',
                    'Verify the server is accessible',
                    'Try refreshing the page'
                ];
                break;
            case 400:
                errorMessage = 'Invalid request format';
                suggestions = [
                    'Check your input data',
                    'Ensure all required fields are filled',
                    'Try refreshing the page'
                ];
                break;
            case 401:
                errorMessage = 'Authentication required';
                errorType = 'warning';
                suggestions = [
                    'You may need to log in again',
                    'Check if your session has expired',
                    'Contact your administrator if the problem persists'
                ];
                break;
            case 403:
                errorMessage = 'Access denied';
                errorType = 'warning';
                suggestions = [
                    'You may not have permission for this action',
                    'Contact your administrator for access',
                    'Try refreshing the page'
                ];
                break;
            case 404:
                errorMessage = 'Resource not found';
                suggestions = [
                    'The requested resource may have been moved',
                    'Try refreshing the page',
                    'Contact support if the problem persists'
                ];
                break;
            case 429:
                errorMessage = 'Too many requests';
                errorType = 'warning';
                suggestions = [
                    'You have exceeded the rate limit',
                    'Wait a moment before trying again',
                    'Reduce the frequency of your requests'
                ];
                break;
            case 500:
                errorMessage = 'Internal server error';
                suggestions = [
                    'The server encountered an error',
                    'Try again in a few moments',
                    'Contact support if the problem persists'
                ];
                break;
            case 503:
                errorMessage = 'Service temporarily unavailable';
                suggestions = [
                    'The server is temporarily down',
                    'Try again in a few minutes',
                    'Contact support if the problem persists'
                ];
                break;
            default:
                if (xhr.status >= 500) {
                    errorMessage = 'Server error';
                    suggestions = [
                        'The server encountered an error',
                        'Try again later',
                        'Contact support if the problem persists'
                    ];
                } else if (xhr.status >= 400) {
                    errorMessage = 'Client error';
                    suggestions = [
                        'There was a problem with your request',
                        'Check your input and try again',
                        'Contact support if the problem persists'
                    ];
                } else {
                    errorMessage = 'Unknown error occurred';
                    suggestions = [
                        'An unexpected error occurred',
                        'Try refreshing the page',
                        'Contact support if the problem persists'
                    ];
                }
                break;
        }

        // Add context information if provided
        if (context) {
            errorMessage = context + ': ' + errorMessage;
        }

        // Log detailed error information for debugging
        if (console && console.error) {
            console.error('Enhanced Error Details:', {
                context: context || 'Unknown',
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error,
                status: status,
                url: xhr.responseURL || 'Unknown'
            });
        }

        // Show the error message with suggestions
        var fullMessage = errorMessage + '\n\nSuggestions:\n• ' + suggestions.join('\n• ');
        showMessage(fullMessage, errorType);

        return {
            message: errorMessage,
            type: errorType,
            suggestions: suggestions,
            status: xhr.status
        };
    }

}); 