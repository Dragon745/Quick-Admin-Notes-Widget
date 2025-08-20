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
    });

    /**
     * Add a new note
     */
    function addNote() {
        var noteText = $('#qanw-editor').html().trim();
        var noteColor = $('#qanw-note-color').val();

        if (!noteText || noteText === '<br>' || noteText === '') {
            alert('Please enter a note.');
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
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function () {
                showMessage(qanw_ajax.strings.error, 'error');
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
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function () {
                showMessage(qanw_ajax.strings.error, 'error');
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

        return '<div class="qanw-note qanw-note-' + note.color + '" data-note-id="' + note.id + '">' +
            '<div class="qanw-note-content">' +
            senderInfo +
            '<div class="qanw-note-text">' + note.text + '</div>' +
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
                    showMessage(response.data, 'success');
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function () {
                showMessage(qanw_ajax.strings.error, 'error');
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

        var messageClass = 'qanw-message qanw-message-' + type;
        var messageHtml = '<div class="' + messageClass + '">' + message + '</div>';

        $('#qanw-container').prepend(messageHtml);

        // Auto-hide after 3 seconds
        setTimeout(function () {
            $('.qanw-message').fadeOut(300, function () {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Apply formatting to selected text
     */
    function applyFormat(format) {
        var editor = $('#qanw-editor')[0];
        var selection = window.getSelection();

        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);

        switch (format) {
            case 'bold':
                document.execCommand('bold', false, null);
                break;
            case 'italic':
                document.execCommand('italic', false, null);
                break;
            case 'underline':
                document.execCommand('underline', false, null);
                break;
            case 'ul':
                document.execCommand('insertUnorderedList', false, null);
                break;
            case 'ol':
                document.execCommand('insertOrderedList', false, null);
                break;
            case 'link':
                var url = prompt('Enter URL:');
                if (url) {
                    document.execCommand('createLink', false, url);
                }
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
     * Sanitize HTML to allow safe formatting
     */
    function sanitizeHtml(html) {
        // Allow safe HTML tags
        var allowedTags = {
            'b': true, 'strong': true, 'i': true, 'em': true, 'u': true,
            'ul': true, 'ol': true, 'li': true, 'a': true, 'code': true,
            'br': true, 'p': true, 'div': true, 'span': true
        };

        // Create a temporary div to parse HTML
        var temp = document.createElement('div');
        temp.innerHTML = html;

        // Remove any script tags and other unsafe elements
        var scripts = temp.querySelectorAll('script, style, iframe, object, embed');
        scripts.forEach(function (el) {
            el.remove();
        });

        // Only allow specific attributes
        var allowedAttributes = ['href', 'target', 'class', 'style'];
        var elements = temp.querySelectorAll('*');
        elements.forEach(function (el) {
            var attrs = el.attributes;
            for (var i = attrs.length - 1; i >= 0; i--) {
                var attr = attrs[i];
                if (allowedAttributes.indexOf(attr.name) === -1) {
                    el.removeAttribute(attr.name);
                }
            }
        });

        return temp.innerHTML;
    }

    /**
     * Open send note modal
     */
    function openSendModal() {
        var noteText = $('#qanw-editor').html().trim();

        if (!noteText || noteText === '<br>' || noteText === '') {
            alert('Please enter a note before sending.');
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
                } else {
                    showMessage(response.data || 'Failed to load admin users.', 'error');
                }
            },
            error: function () {
                showMessage('Failed to load admin users.', 'error');
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
            html += '<label class="qanw-user-checkbox">' +
                '<input type="checkbox" name="recipient_ids[]" value="' + user.id + '">' +
                '<span class="qanw-user-name">' + user.name + '</span>' +
                '<span class="qanw-user-email">(' + user.email + ')</span>' +
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
        var noteColor = $('#qanw-note-color').val();
        var selectedUsers = $('input[name="recipient_ids[]"]:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedUsers.length === 0) {
            alert('Please select at least one admin user.');
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
                } else {
                    showMessage(response.data || qanw_ajax.strings.error, 'error');
                }
            },
            error: function () {
                showMessage(qanw_ajax.strings.error, 'error');
            },
            complete: function () {
                $('#qanw-confirm-send').prop('disabled', false).text('Send Note');
            }
        });
    }



}); 