<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-auth" content="{{ $authenticated ? '1' : '0' }}">
    <meta name="auth-route" content="{{ route('biometrics.authenticate') }}">
    <meta name="lock-route" content="{{ route('biometrics.lock') }}">
    <meta name="store-route" content="{{ route('notes.store') }}">
    <meta name="theme-color" content="#0b0d14">
    <title>Secure Notes & Secrets Vault</title>

    <!-- Clean CSS Architecture -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- External QRCode Lib & Modular Application Logic -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <div id="toast" class="toast-popup">Copied to Clipboard!</div>

    <header>
        <div class="brand">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span>Secure Notes & Secrets</span>
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
                    <article class="note-card" data-id="{{ $note['id'] }}" data-private="0" onclick="openViewModal('{{ $note['id'] }}')">
                        <div class="note-header">
                            <div class="note-title-wrap">
                                <h3 class="note-title">{{ $note['title'] }}</h3>
                                @if(!empty($note['type']) && $note['type'] === 'secret')
                                    <span class="badge-tag secret-tag">{{ $note['category'] ?? 'SECRET' }}</span>
                                @else
                                    <span class="badge-tag">NOTE</span>
                                @endif
                            </div>
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <button class="action-icon-btn" title="Generate QR Code for Laptop" onclick="event.stopPropagation(); generateQrModal('{{ $note['id'] }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                </button>
                                <button class="delete-btn" onclick="event.stopPropagation(); deleteNote('{{ $note['id'] }}', false)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>

                        @if(!empty($note['type']) && $note['type'] === 'secret')
                            <div class="secret-box" onclick="event.stopPropagation();">
                                <span class="secret-val" id="secret-val-{{ $note['id'] }}" data-real="{{ $note['secret_value'] }}">••••••••••••••••</span>
                                <div class="secret-actions">
                                    <button class="action-icon-btn" title="Toggle Secret" onclick="toggleSecretVisibility('{{ $note['id'] }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    <button class="action-icon-btn" title="Copy" onclick="copySecret('{{ $note['id'] }}', '{{ $note['title'] }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if(!empty($note['content']))
                            <p class="note-body">{{ $note['content'] }}</p>
                        @endif
                        <span class="note-date">{{ $note['created_at'] }}</span>
                    </article>
                @empty
                    <p style="color: var(--muted); text-align: center; margin-top: 2rem;">No public items yet. Tap + to add one!</p>
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
                    <p class="lock-status">Scan your fingerprint to unlock your encrypted API Keys, Passwords & Private Notes.</p>
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
                    <span>✓ Vault Unlocked</span>
                    <button class="lock-again-btn" onclick="lockVault()">Lock Vault</button>
                </div>

                <div class="notes-list" id="private-notes-list">
                    @forelse ($privateNotes as $note)
                        <article class="note-card" data-id="{{ $note['id'] }}" data-private="1" onclick="openViewModal('{{ $note['id'] }}')">
                            <div class="note-header">
                                <div class="note-title-wrap">
                                    <h3 class="note-title">{{ $note['title'] }}</h3>
                                    @if(!empty($note['type']) && $note['type'] === 'secret')
                                        <span class="badge-tag secret-tag">{{ $note['category'] ?? 'SECRET' }}</span>
                                    @else
                                        <span class="badge-tag">NOTE</span>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 0.4rem; align-items: center;">
                                    <button class="action-icon-btn" title="Generate QR Code for Laptop" onclick="event.stopPropagation(); generateQrModal('{{ $note['id'] }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                    </button>
                                    <button class="delete-btn" onclick="event.stopPropagation(); deleteNote('{{ $note['id'] }}', true)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </div>

                            @if(!empty($note['type']) && $note['type'] === 'secret')
                                <div class="secret-box" onclick="event.stopPropagation();">
                                    <span class="secret-val" id="secret-val-{{ $note['id'] }}" data-real="{{ $note['secret_value'] }}">••••••••••••••••</span>
                                    <div class="secret-actions">
                                        <button class="action-icon-btn" onclick="toggleSecretVisibility('{{ $note['id'] }}')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </button>
                                        <button class="action-icon-btn" onclick="copySecret('{{ $note['id'] }}', '{{ $note['title'] }}')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($note['content']))
                                <p class="note-body">{{ $note['content'] }}</p>
                            @endif
                            <span class="note-date">{{ $note['created_at'] }}</span>
                        </article>
                    @empty
                        <p style="color: var(--muted); text-align: center; margin-top: 2rem;">Vault is empty. Tap + to add a secret key or note!</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    <!-- Floating Add Button -->
    <button id="add-note-fab" class="fab" type="button" aria-label="Add Item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
    </button>

    <!-- Create Item Modal -->
    <div id="note-modal" class="modal-overlay">
        <form id="note-form" class="modal-card">
            <h3 class="modal-title" id="modal-heading">Add New Item</h3>

            <!-- Item Type Selector Pill -->
            <div class="type-selector">
                <button type="button" id="pill-secret-btn" class="type-pill active" onclick="selectFormType('secret')">🔑 Secret / API Key</button>
                <button type="button" id="pill-note-btn" class="type-pill" onclick="selectFormType('note')">📝 Text Note</button>
            </div>

            <input type="hidden" id="entry-type-input" value="secret">
            <input type="hidden" id="note-private-flag" value="0">

            <div class="input-group">
                <label for="note-title-input" id="label-title">Key Name / Identifier</label>
                <input id="note-title-input" class="input-control" type="text" placeholder="e.g. STRIPE_SECRET_KEY" required>
            </div>

            <!-- Secret Key Inputs -->
            <div id="group-secret-fields" class="input-group">
                <label for="secret-value-input">Secret Value / Password</label>
                <input id="secret-value-input" class="input-control" type="password" placeholder="Enter password or secret key...">

                <label for="category-select" style="margin-top: 0.5rem;">Category Tag</label>
                <select id="category-select" class="input-control">
                    <option value="API KEY">🔑 API Key</option>
                    <option value="DATABASE">🗄️ Database Password</option>
                    <option value="SSH KEY">🔒 SSH Key</option>
                    <option value="OAUTH">🛡️ OAuth Token</option>
                    <option value="ENV VAR">⚙️ Environment Variable</option>
                </select>
            </div>

            <div class="input-group">
                <label for="note-content-input">Description / Notes (Optional)</label>
                <textarea id="note-content-input" class="input-control" placeholder="Optional details or instructions..."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>

    <!-- View / Inspect Item Modal -->
    <div id="view-modal" class="modal-overlay">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span id="view-badge" class="badge-tag">NOTE</span>
                    <h3 id="view-title" class="modal-title" style="margin-top: 0.4rem; font-size: 1.2rem;">Item Title</h3>
                </div>
                <button class="delete-btn" onclick="closeViewModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div id="view-secret-wrap" style="display: none;">
                <label style="font-size: 0.78rem; color: var(--muted); text-transform: uppercase;">Secret Key Value</label>
                <div class="secret-box" style="margin-top: 0.4rem;">
                    <span class="secret-val" id="view-secret-val">••••••••••••••••</span>
                    <div class="secret-actions">
                        <button class="action-icon-btn" onclick="toggleViewSecretVisibility()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button class="action-icon-btn" onclick="copyViewSecret()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div id="view-content-wrap">
                <label style="font-size: 0.78rem; color: var(--muted); text-transform: uppercase;">Details & Notes</label>
                <div id="view-content-body" class="view-content-full" style="margin-top: 0.4rem;">No description provided.</div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; gap: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="shareNativeCurrentView()" style="flex: 1; padding: 0.6rem; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1.1rem; height:1.1rem;"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                    <span>Share via Mobile</span>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()" style="flex: initial; padding: 0.6rem 1.4rem;">Close</button>
            </div>
        </div>
    </div>

    <!-- QR Code Laptop Transfer Modal -->
    <div id="qr-modal" class="modal-overlay">
        <div class="modal-card" style="text-align: center; align-items: center;">
            <h3 id="qr-modal-title" class="modal-title">Laptop Transfer (QR Code)</h3>
            <p style="font-size: 0.85rem; color: var(--muted); margin: 0;">Scan this QR code with your Laptop Camera or Phone Scanner to transfer instantly.</p>

            <div id="qr-canvas-container"></div>

            <p id="qr-val-preview" style="font-size: 0.8rem; color: #34d399; font-family: monospace; word-break: break-all; max-width: 100%; margin: 0;"></p>

            <div style="display: flex; gap: 0.75rem; width: 100%; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeQrModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="triggerNativeShareFromQr()">Share via App</button>
            </div>
        </div>
    </div>
</body>
</html>
