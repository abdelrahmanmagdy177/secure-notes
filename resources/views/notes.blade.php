<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0d14">
    <title>Secure Notes</title>
    <style>
        :root {
            --bg: #0b0d14;
            --surface: #141722;
            --surface-hover: #1c2030;
            --border: #23283a;
            --fg: #f3f5fc;
            --muted: #79839a;
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.35);
            --danger: #ef4444;
            --success: #10b981;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            margin: 0;
            min-height: 100dvh;
            background: var(--bg);
            color: var(--fg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            flex-direction: column;
            padding-bottom: max(2rem, env(safe-area-inset-bottom));
        }

        /* Top Header */
        header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(11, 13, 20, 0.85);
            backdrop-filter: blur(12px);
            padding: max(1.2rem, env(safe-area-inset-top)) 1.2rem 0.8rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .brand svg { width: 1.5rem; height: 1.5rem; color: var(--accent); }

        /* Nav Tabs */
        .tabs-nav {
            display: flex;
            background: #10121b;
            border-radius: 999px;
            padding: 0.25rem;
            border: 1px solid var(--border);
            margin: 1rem 1.2rem 0.5rem;
        }

        .tab-btn {
            flex: 1;
            appearance: none;
            border: none;
            background: transparent;
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 600;
            padding: 0.6rem 0.8rem;
            border-radius: 999px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: var(--surface-hover);
            color: var(--fg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .tab-btn.active.private-tab {
            color: var(--accent);
        }

        .tab-btn svg { width: 1.1rem; height: 1.1rem; }

        /* Main Container */
        main {
            flex: 1;
            padding: 0.8rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .tab-content { display: none; flex-direction: column; gap: 1rem; }
        .tab-content.active { display: flex; }

        /* Locked Vault View */
        .locked-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 2.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.2rem;
            text-align: center;
            box-shadow: 0 12px 30px rgba(0,0,0,0.5);
            margin-top: 1rem;
        }

        .lock-icon-wrapper {
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            display: grid;
            place-items: center;
            color: var(--accent);
            box-shadow: 0 0 30px var(--accent-glow);
        }

        .lock-icon-wrapper svg { width: 2.5rem; height: 2.5rem; }

        .unlock-btn {
            appearance: none;
            border: none;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.85rem 1.8rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            box-shadow: 0 6px 20px var(--accent-glow);
            transition: transform 0.15s ease;
        }

        .unlock-btn:active { transform: scale(0.96); }

        .lock-status {
            font-size: 0.82rem;
            color: var(--muted);
            max-width: 16rem;
            line-height: 1.4;
        }

        /* Note Cards */
        .notes-list { display: flex; flex-direction: column; gap: 0.85rem; }

        .note-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.1rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: background 0.2s ease;
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .note-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--fg);
        }

        .note-date {
            font-size: 0.72rem;
            color: var(--muted);
        }

        .note-body {
            margin: 0;
            font-size: 0.9rem;
            color: #c5cbd8;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .delete-btn {
            appearance: none;
            border: none;
            background: transparent;
            color: var(--muted);
            padding: 0.2rem;
            cursor: pointer;
            border-radius: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .delete-btn:hover, .delete-btn:active { color: var(--danger); }
        .delete-btn svg { width: 1.1rem; height: 1.1rem; }

        /* Floating Action Button (Add Note) */
        .fab {
            position: fixed;
            bottom: max(1.5rem, env(safe-area-inset-bottom));
            right: 1.5rem;
            width: 3.6rem;
            height: 3.6rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            border: none;
            display: grid;
            place-items: center;
            box-shadow: 0 8px 25px var(--accent-glow);
            cursor: pointer;
            z-index: 20;
            transition: transform 0.2s ease;
        }

        .fab:active { transform: scale(0.92); }
        .fab svg { width: 1.6rem; height: 1.6rem; }

        /* Modal Sheet */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
            z-index: 30;
            display: none;
            align-items: flex-end;
            justify-content: center;
        }

        .modal-overlay.active { display: flex; }

        .modal-card {
            background: var(--surface);
            border-top: 1px solid var(--border);
            border-radius: 1.5rem 1.5rem 0 0;
            padding: 1.5rem;
            width: 100%;
            max-width: 32rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.6);
            animation: slideUp 0.25s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            text-align: left;
        }

        .input-group label {
            font-size: 0.78rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-control {
            appearance: none;
            border: 1px solid var(--border);
            background: #0d0f18;
            color: var(--fg);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
        }

        .input-control:focus {
            border-color: var(--accent);
        }

        textarea.input-control {
            min-height: 6rem;
            resize: vertical;
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary {
            background: #1c2030;
            color: var(--muted);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }

        .unlocked-banner {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 0.75rem;
            padding: 0.65rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--success);
            font-weight: 600;
        }

        .lock-again-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="brand">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Secure Notes</span>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="tabs-nav">
        <button id="tab-public-btn" class="tab-btn active" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
            </svg>
            <span>Public Notes</span>
        </button>

        <button id="tab-private-btn" class="tab-btn private-tab" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 12c0-3 2.5-5.5 5.5-5.5S23 9 23 12"></path>
                <path d="M12 12c0 3-2.5 5.5-5.5 5.5S1 15 1 12"></path>
                <path d="M12 7c-2.8 0-5 2.2-5 5 0 2.8 2.2 5 5 5"></path>
            </svg>
            <span>Private Vault</span>
        </button>
    </nav>

    <main>
        <!-- PUBLIC NOTES TAB CONTENT -->
        <section id="tab-public" class="tab-content active">
            <div class="notes-list" id="public-notes-list">
                @forelse ($publicNotes as $note)
                    <article class="note-card" data-id="{{ $note['id'] }}" data-private="0">
                        <div class="note-header">
                            <h3 class="note-title">{{ $note['title'] }}</h3>
                            <button class="delete-btn" onclick="deleteNote('{{ $note['id'] }}', false)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                        <p class="note-body">{{ $note['content'] }}</p>
                        <span class="note-date">{{ $note['created_at'] }}</span>
                    </article>
                @empty
                    <p style="color: var(--muted); text-align: center; margin-top: 2rem;">No public notes yet. Tap + to create one!</p>
                @endforelse
            </div>
        </section>

        <!-- PRIVATE VAULT TAB CONTENT -->
        <section id="tab-private" class="tab-content">
            <div id="private-locked-view" class="locked-card" style="{{ $authenticated ? 'display: none;' : '' }}">
                <div class="lock-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div>
                    <h2 style="margin: 0 0 0.3rem; font-size: 1.25rem;">Private Vault Locked</h2>
                    <p class="lock-status">Scan your fingerprint to unlock and view your encrypted private notes.</p>
                </div>
                <button id="scan-fingerprint-btn" class="unlock-btn" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1.4rem; height:1.4rem;">
                        <path d="M12 12c0-3 2.5-5.5 5.5-5.5S23 9 23 12"/>
                        <path d="M12 12c0 3-2.5 5.5-5.5 5.5S1 15 1 12"/>
                        <path d="M12 7c-2.8 0-5 2.2-5 5 0 2.8 2.2 5 5 5"/>
                    </svg>
                    <span>Unlock with Fingerprint</span>
                </button>
            </div>

            <div id="private-unlocked-view" style="{{ $authenticated ? '' : 'display: none;' }}">
                <div class="unlocked-banner" style="margin-bottom: 1rem;">
                    <span>✓ Private Vault Unlocked</span>
                    <button class="lock-again-btn" onclick="lockVault()">Lock Vault</button>
                </div>

                <div class="notes-list" id="private-notes-list">
                    @forelse ($privateNotes as $note)
                        <article class="note-card" data-id="{{ $note['id'] }}" data-private="1">
                            <div class="note-header">
                                <h3 class="note-title">{{ $note['title'] }}</h3>
                                <button class="delete-btn" onclick="deleteNote('{{ $note['id'] }}', true)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                            <p class="note-body">{{ $note['content'] }}</p>
                            <span class="note-date">{{ $note['created_at'] }}</span>
                        </article>
                    @empty
                        <p style="color: var(--muted); text-align: center; margin-top: 2rem;">Vault is empty. Tap + to add a private note!</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    <!-- Floating Add Button -->
    <button id="add-note-fab" class="fab" type="button" aria-label="Add Note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
    </button>

    <!-- Create Note Modal -->
    <div id="note-modal" class="modal-overlay">
        <form id="note-form" class="modal-card">
            <h3 class="modal-title" id="modal-heading">New Public Note</h3>

            <div class="input-group">
                <label for="note-title-input">Title</label>
                <input id="note-title-input" class="input-control" type="text" placeholder="Enter note title..." required>
            </div>

            <div class="input-group">
                <label for="note-content-input">Content</label>
                <textarea id="note-content-input" class="input-control" placeholder="Write your note here..." required></textarea>
            </div>

            <input type="hidden" id="note-private-flag" value="0">

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Note</button>
            </div>
        </form>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let activeTab = 'public';
        let isAuthenticated = @json($authenticated);

        const tabPublicBtn = document.getElementById('tab-public-btn');
        const tabPrivateBtn = document.getElementById('tab-private-btn');
        const tabPublic = document.getElementById('tab-public');
        const tabPrivate = document.getElementById('tab-private');

        const scanFingerprintBtn = document.getElementById('scan-fingerprint-btn');
        const privateLockedView = document.getElementById('private-locked-view');
        const privateUnlockedView = document.getElementById('private-unlocked-view');

        const addNoteFab = document.getElementById('add-note-fab');
        const noteModal = document.getElementById('note-modal');
        const noteForm = document.getElementById('note-form');
        const modalHeading = document.getElementById('modal-heading');
        const noteTitleInput = document.getElementById('note-title-input');
        const noteContentInput = document.getElementById('note-content-input');
        const notePrivateFlag = document.getElementById('note-private-flag');

        // Tab Switching
        tabPublicBtn.addEventListener('click', () => switchTab('public'));
        tabPrivateBtn.addEventListener('click', () => switchTab('private'));

        function switchTab(tab) {
            activeTab = tab;
            if (tab === 'public') {
                tabPublicBtn.classList.add('active');
                tabPrivateBtn.classList.remove('active');
                tabPublic.classList.add('active');
                tabPrivate.classList.remove('active');
            } else {
                tabPrivateBtn.classList.add('active');
                tabPublicBtn.classList.remove('active');
                tabPrivate.classList.add('active');
                tabPublic.classList.remove('active');
            }
        }

        // Fingerprint Authentication Trigger
        scanFingerprintBtn.addEventListener('click', async () => {
            scanFingerprintBtn.disabled = true;
            scanFingerprintBtn.querySelector('span').textContent = 'Scanning...';

            try {
                const response = await fetch(@json(route('biometrics.authenticate')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();

                if (result.success) {
                    isAuthenticated = true;
                    privateLockedView.style.display = 'none';
                    privateUnlockedView.style.display = 'block';
                    renderNotesList('private-notes-list', result.privateNotes, true);
                } else {
                    alert(result.message || 'Fingerprint authentication failed.');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                scanFingerprintBtn.disabled = false;
                scanFingerprintBtn.querySelector('span').textContent = 'Unlock with Fingerprint';
            }
        });

        // Lock Vault
        async function lockVault() {
            await fetch(@json(route('biometrics.lock')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            isAuthenticated = false;
            privateLockedView.style.display = 'flex';
            privateUnlockedView.style.display = 'none';
        }

        // Modal Form Handling
        addNoteFab.addEventListener('click', () => {
            if (activeTab === 'private' && !isAuthenticated) {
                alert('Please unlock your Private Vault with your fingerprint first!');
                return;
            }

            const isPrivate = activeTab === 'private';
            modalHeading.textContent = isPrivate ? 'New Private Note 🔒' : 'New Public Note 🌐';
            notePrivateFlag.value = isPrivate ? '1' : '0';
            noteTitleInput.value = '';
            noteContentInput.value = '';
            noteModal.classList.add('active');
        });

        function closeModal() {
            noteModal.classList.remove('active');
        }

        noteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isPrivate = notePrivateFlag.value === '1';

            try {
                const response = await fetch(@json(route('notes.store')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: noteTitleInput.value,
                        content: noteContentInput.value,
                        is_private: isPrivate,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    closeModal();
                    const containerId = isPrivate ? 'private-notes-list' : 'public-notes-list';
                    appendNoteToDom(containerId, result.note);
                } else {
                    alert(result.message || 'Failed to save note.');
                }
            } catch (err) {
                alert('Error saving note: ' + err.message);
            }
        });

        // Helper functions
        function appendNoteToDom(containerId, note) {
            const list = document.getElementById(containerId);
            const card = document.createElement('article');
            card.className = 'note-card';
            card.dataset.id = note.id;
            card.dataset.private = note.is_private ? '1' : '0';
            card.innerHTML = `
                <div class="note-header">
                    <h3 class="note-title">${escapeHtml(note.title)}</h3>
                    <button class="delete-btn" onclick="deleteNote('${note.id}', ${note.is_private})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
                <p class="note-body">${escapeHtml(note.content)}</p>
                <span class="note-date">${escapeHtml(note.created_at)}</span>
            `;
            list.prepend(card);
        }

        async function deleteNote(id, isPrivate) {
            if (!confirm('Are you sure you want to delete this note?')) return;
            try {
                const response = await fetch(`/notes/${id}?is_private=${isPrivate ? 1 : 0}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                const res = await response.json();
                if (res.success) {
                    const card = document.querySelector(`.note-card[data-id="${id}"]`);
                    if (card) card.remove();
                }
            } catch (err) {
                alert('Error deleting note');
            }
        }

        function renderNotesList(containerId, notes, isPrivate) {
            const list = document.getElementById(containerId);
            list.innerHTML = '';
            if (notes.length === 0) {
                list.innerHTML = `<p style="color: var(--muted); text-align: center; margin-top: 2rem;">Vault is empty. Tap + to add a private note!</p>`;
                return;
            }
            notes.forEach(note => appendNoteToDom(containerId, note));
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
        }
    </script>
</body>
</html>
