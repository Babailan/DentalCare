(function () {
    var root = document.getElementById('chat-app');
    if (!root) {
        return;
    }

    var apiUrl = root.getAttribute('data-api');
    var role = root.getAttribute('data-role');
    var patientId = root.getAttribute('data-patient-id');
    var isAdmin = role === 'admin';

    var messagesEl = document.getElementById('chat-messages');
    var form = document.getElementById('chat-form');
    var input = document.getElementById('chat-input');
    var statusEl = document.getElementById('chat-status');
    var emptyEl = document.getElementById('chat-empty');

    var lastId = 0;
    var renderedIds = {};
    var sending = false;

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    function appendMessage(msg) {
        if (renderedIds[msg.id]) {
            return;
        }
        renderedIds[msg.id] = true;

        if (emptyEl) {
            emptyEl.style.display = 'none';
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'chat-message chat-message-' + msg.sender;
        wrapper.setAttribute('data-id', String(msg.id));

        var meta = document.createElement('div');
        meta.className = 'chat-message-meta';
        meta.textContent = (msg.sender === 'admin' ? 'Admin' : 'Patient') + ' · ' + msg.created_at;

        var body = document.createElement('div');
        body.className = 'chat-message-body';
        body.textContent = msg.message;

        wrapper.appendChild(meta);
        wrapper.appendChild(body);
        messagesEl.appendChild(wrapper);
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function loadMessages() {
        var params = new URLSearchParams();
        params.set('action', 'fetch');
        params.set('after', String(lastId));
        if (isAdmin) {
            params.set('patient_id', patientId);
        }

        return fetch(apiUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        throw new Error(data.error || 'Could not load messages.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                var messages = data.messages || [];
                messages.forEach(function (msg) {
                    appendMessage(msg);
                    if (Number(msg.id) > lastId) {
                        lastId = Number(msg.id);
                    }
                });
                if (messages.length > 0) {
                    scrollToBottom();
                }
                setStatus('');
            })
            .catch(function (err) {
                setStatus(err.message || 'Could not load messages.');
            });
    }

    function sendMessage(text) {
        var body = new URLSearchParams();
        body.set('action', 'send');
        body.set('message', text);
        if (isAdmin) {
            body.set('patient_id', patientId);
        }

        return fetch(apiUrl, {
            method: 'POST',
            body: body,
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) {
                    throw new Error(data.error || 'Could not send the message.');
                }
                return data;
            });
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var text = input.value.trim();
        if (!text || sending) {
            return;
        }

        sending = true;
        input.value = '';
        setStatus('Sending...');

        sendMessage(text)
            .then(function () {
                input.focus();
                return loadMessages();
            })
            .then(function () {
                setStatus('');
            })
            .catch(function (err) {
                input.value = text;
                setStatus(err.message || 'Could not send the message.');
            })
            .finally(function () {
                sending = false;
            });
    });

    // Enter sends the message; Shift+Enter inserts a new line.
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

    loadMessages().then(function () {
        scrollToBottom();
        input.focus();
        setInterval(loadMessages, 3000);
    });
})();
