document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const authMeta = document.querySelector('meta[name="app-auth"]');
    const authRouteMeta = document.querySelector('meta[name="auth-route"]');
    const lockRouteMeta = document.querySelector('meta[name="lock-route"]');
    const storeRouteMeta = document.querySelector('meta[name="store-route"]');

    let activeTab = 'public';
    let isAuthenticated = authMeta ? authMeta.content === '1' : false;

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
    const entryTypeInput = document.getElementById('entry-type-input');
    const notePrivateFlag = document.getElementById('note-private-flag');

    const noteTitleInput = document.getElementById('note-title-input');
    const secretValueInput = document.getElementById('secret-value-input');
    const categorySelect = document.getElementById('category-select');
    const noteContentInput = document.getElementById('note-content-input');

    const pillSecretBtn = document.getElementById('pill-secret-btn');
    const pillNoteBtn = document.getElementById('pill-note-btn');
    const groupSecretFields = document.getElementById('group-secret-fields');
    const labelTitle = document.getElementById('label-title');
    const toast = document.getElementById('toast');

    // View Modal Elements
    const viewModal = document.getElementById('view-modal');
    const viewBadge = document.getElementById('view-badge');
    const viewTitle = document.getElementById('view-title');
    const viewSecretWrap = document.getElementById('view-secret-wrap');
    const viewSecretVal = document.getElementById('view-secret-val');
    const viewContentWrap = document.getElementById('view-content-wrap');
    const viewContentBody = document.getElementById('view-content-body');
    const viewDate = document.getElementById('view-date');
    let currentViewRealSecret = '';
    let currentViewTitle = '';

    // QR Modal Elements
    const qrModal = document.getElementById('qr-modal');
    const qrCanvasContainer = document.getElementById('qr-canvas-container');
    const qrValPreview = document.getElementById('qr-val-preview');
    let currentQrText = '';

    // Tab Switching
    if (tabPublicBtn && tabPrivateBtn) {
        tabPublicBtn.addEventListener('click', () => switchTab('public'));
        tabPrivateBtn.addEventListener('click', () => switchTab('private'));
    }

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

    window.selectFormType = function(type) {
        entryTypeInput.value = type;
        if (type === 'secret') {
            pillSecretBtn.classList.add('active');
            pillNoteBtn.classList.remove('active');
            groupSecretFields.style.display = 'flex';
            labelTitle.textContent = 'Key Name / Identifier';
            noteTitleInput.placeholder = 'e.g. STRIPE_SECRET_KEY';
        } else {
            pillNoteBtn.classList.add('active');
            pillSecretBtn.classList.remove('active');
            groupSecretFields.style.display = 'none';
            labelTitle.textContent = 'Note Title';
            noteTitleInput.placeholder = 'e.g. Meeting Standup Notes';
        }
    };

    // Biometric Authentication Trigger
    if (scanFingerprintBtn) {
        scanFingerprintBtn.addEventListener('click', async () => {
            scanFingerprintBtn.disabled = true;
            scanFingerprintBtn.querySelector('span').textContent = 'Scanning...';

            try {
                const response = await fetch(authRouteMeta.content, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
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
    }

    // Lock Vault
    window.lockVault = async function() {
        await fetch(lockRouteMeta.content, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        isAuthenticated = false;
        privateLockedView.style.display = 'flex';
        privateUnlockedView.style.display = 'none';
    };

    // Modal Form Handling
    if (addNoteFab) {
        addNoteFab.addEventListener('click', () => {
            if (activeTab === 'private' && !isAuthenticated) {
                alert('Please unlock your Private Vault with your fingerprint first!');
                return;
            }

            const isPrivate = activeTab === 'private';
            modalHeading.textContent = isPrivate ? 'Add Secret / Note 🔒' : 'Add Public Secret / Note 🌐';
            notePrivateFlag.value = isPrivate ? '1' : '0';
            noteTitleInput.value = '';
            secretValueInput.value = '';
            noteContentInput.value = '';
            window.selectFormType('secret');
            noteModal.classList.add('active');
        });
    }

    window.closeModal = function() { noteModal.classList.remove('active'); };

    if (noteForm) {
        noteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isPrivate = notePrivateFlag.value === '1';
            const type = entryTypeInput.value;

            try {
                const response = await fetch(storeRouteMeta.content, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: noteTitleInput.value,
                        content: noteContentInput.value,
                        type: type,
                        category: categorySelect.value,
                        secret_value: secretValueInput.value,
                        is_private: isPrivate,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    window.closeModal();
                    const containerId = isPrivate ? 'private-notes-list' : 'public-notes-list';
                    appendNoteToDom(containerId, result.note);
                    showToast('✓ Item Saved Successfully');
                } else {
                    alert(result.message || 'Failed to save item.');
                }
            } catch (err) {
                alert('Error saving item: ' + err.message);
            }
        });
    }

    // View Item Details Modal
    window.openViewModal = function(id) {
        const card = document.querySelector(`.note-card[data-id="${id}"]`);
        if (!card) return;

        const title = card.querySelector('.note-title').textContent;
        const badge = card.querySelector('.badge-tag').textContent;
        const date = card.querySelector('.note-date').textContent;
        const bodyEl = card.querySelector('.note-body');
        const content = bodyEl ? bodyEl.textContent : '';

        const secretEl = card.querySelector(`#secret-val-${id}`);
        const isSecret = secretEl !== null;

        currentViewTitle = title;
        viewTitle.textContent = title;
        viewBadge.textContent = badge;
        viewBadge.className = isSecret ? 'badge-tag secret-tag' : 'badge-tag';
        viewDate.textContent = date;

        if (isSecret) {
            currentViewRealSecret = secretEl.dataset.real || '';
            viewSecretVal.textContent = '••••••••••••••••';
            viewSecretWrap.style.display = 'block';
        } else {
            currentViewRealSecret = '';
            viewSecretWrap.style.display = 'none';
        }

        if (content.trim()) {
            viewContentBody.textContent = content;
            viewContentWrap.style.display = 'block';
        } else {
            viewContentWrap.style.display = isSecret ? 'none' : 'block';
            viewContentBody.textContent = 'No description provided.';
        }

        viewModal.classList.add('active');
    };

    window.closeViewModal = function() { viewModal.classList.remove('active'); };

    // QR Code Generation Modal
    window.generateQrModal = function(id) {
        const card = document.querySelector(`.note-card[data-id="${id}"]`);
        if (!card) return;

        const title = card.querySelector('.note-title').textContent;
        const secretEl = card.querySelector(`#secret-val-${id}`);
        const bodyEl = card.querySelector('.note-body');

        let textToEncode = '';
        if (secretEl && secretEl.dataset.real) {
            textToEncode = secretEl.dataset.real;
        } else if (bodyEl) {
            textToEncode = bodyEl.textContent;
        } else {
            textToEncode = title;
        }

        currentQrText = textToEncode;
        document.getElementById('qr-modal-title').textContent = `Transfer "${title}"`;
        qrValPreview.textContent = textToEncode.length > 40 ? textToEncode.substring(0, 40) + '...' : textToEncode;

        qrCanvasContainer.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(qrCanvasContainer, {
                text: textToEncode,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } else {
            qrCanvasContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(textToEncode)}" alt="QR Code">`;
        }

        qrModal.classList.add('active');
    };

    window.closeQrModal = function() { qrModal.classList.remove('active'); };

    window.triggerNativeShareFromQr = function() {
        if (navigator.share && currentQrText) {
            navigator.share({
                title: 'Secure Secret',
                text: currentQrText
            }).catch(err => console.log('Share canceled'));
        } else {
            copyTextToClipboard(currentQrText);
        }
    };

    window.shareNativeCurrentView = function() {
        const textToShare = currentViewRealSecret || viewContentBody.textContent;
        if (navigator.share && textToShare) {
            navigator.share({
                title: currentViewTitle,
                text: textToShare
            }).catch(err => console.log('Share canceled'));
        } else {
            copyTextToClipboard(textToShare);
        }
    };

    window.toggleViewSecretVisibility = function() {
        const isMasked = viewSecretVal.textContent === '••••••••••••••••';
        viewSecretVal.textContent = isMasked ? currentViewRealSecret : '••••••••••••••••';
    };

    window.copyViewSecret = async function() {
        if (!currentViewRealSecret) return;
        copyTextToClipboard(currentViewRealSecret);
    };

    window.toggleSecretVisibility = function(id) {
        const el = document.getElementById(`secret-val-${id}`);
        if (!el) return;
        const isMasked = el.textContent === '••••••••••••••••';
        el.textContent = isMasked ? el.dataset.real : '••••••••••••••••';
    };

    window.copySecret = async function(id, title) {
        const el = document.getElementById(`secret-val-${id}`);
        if (!el || !el.dataset.real) return;
        copyTextToClipboard(el.dataset.real, `Copied "${title}" to Clipboard!`);
    };

    async function copyTextToClipboard(text, customMsg = 'Copied to Clipboard!') {
        try {
            await navigator.clipboard.writeText(text);
            showToast(customMsg);
        } catch (err) {
            alert('Failed to copy');
        }
    }

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('active');
        setTimeout(() => toast.classList.remove('active'), 2500);
    }

    function appendNoteToDom(containerId, note) {
        const list = document.getElementById(containerId);
        const card = document.createElement('article');
        card.className = 'note-card';
        card.dataset.id = note.id;
        card.dataset.private = note.is_private ? '1' : '0';
        card.onclick = () => window.openViewModal(note.id);

        const isSecret = note.type === 'secret';
        const tagText = isSecret ? (note.category || 'SECRET') : 'NOTE';
        const tagClass = isSecret ? 'badge-tag secret-tag' : 'badge-tag';

        let secretHtml = '';
        if (isSecret) {
            secretHtml = `
                <div class="secret-box" onclick="event.stopPropagation();">
                    <span class="secret-val" id="secret-val-${note.id}" data-real="${escapeHtml(note.secret_value || '')}">••••••••••••••••</span>
                    <div class="secret-actions">
                        <button class="action-icon-btn" title="Toggle Secret" onclick="toggleSecretVisibility('${note.id}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button class="action-icon-btn" title="Copy" onclick="copySecret('${note.id}', '${escapeHtml(note.title)}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                </div>
            `;
        }

        const bodyHtml = note.content ? `<p class="note-body">${escapeHtml(note.content)}</p>` : '';

        card.innerHTML = `
            <div class="note-header">
                <div class="note-title-wrap">
                    <h3 class="note-title">${escapeHtml(note.title)}</h3>
                    <span class="${tagClass}">${escapeHtml(tagText)}</span>
                </div>
                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    <button class="action-icon-btn" title="Generate QR Code for Laptop" onclick="event.stopPropagation(); generateQrModal('${note.id}')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </button>
                    <button class="delete-btn" onclick="event.stopPropagation(); deleteNote('${note.id}', ${note.is_private})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
            ${secretHtml}
            ${bodyHtml}
            <span class="note-date">${escapeHtml(note.created_at)}</span>
        `;
        list.prepend(card);
    }

    window.deleteNote = async function(id, isPrivate) {
        if (!confirm('Are you sure you want to delete this item?')) return;
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
            alert('Error deleting item');
        }
    };

    function renderNotesList(containerId, notes, isPrivate) {
        const list = document.getElementById(containerId);
        list.innerHTML = '';
        if (notes.length === 0) {
            list.innerHTML = `<p style="color: var(--muted); text-align: center; margin-top: 2rem;">Vault is empty. Tap + to add a secret key or note!</p>`;
            return;
        }
        notes.forEach(note => appendNoteToDom(containerId, note));
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }
});
