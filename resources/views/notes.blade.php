<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0d14">
    <title>Secure Notes & Secrets Vault</title>
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
            --warning: #f59e0b;
            --purple: #8b5cf6;
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

        .tab-btn.active.private-tab { color: var(--accent); }
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

        /* Note Cards & Secret Cards */
        .notes-list { display: flex; flex-direction: column; gap: 0.85rem; }

        .note-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.1rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .note-card:hover {
            background: var(--surface-hover);
            border-color: #2e354d;
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .note-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .note-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 600;
            color: var(--fg);
        }

        .badge-tag {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: rgba(59, 130, 246, 0.15);
            color: var(--accent);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .badge-tag.secret-tag {
            background: rgba(139, 92, 246, 0.15);
            color: var(--purple);
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .note-date { font-size: 0.72rem; color: var(--muted); }

        .note-body {
            margin: 0;
            font-size: 0.9rem;
            color: #c5cbd8;
            line-height: 1.5;
            white-space: pre-wrap;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Secret Box Component */
        .secret-box {
            background: #0b0d14;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 0.6rem 0.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.88rem;
        }

        .secret-val {
            color: #34d399;
            word-break: break-all;
            user-select: all;
        }

        .secret-actions {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .action-icon-btn {
            appearance: none;
            border: none;
            background: #171a26;
            color: var(--muted);
            padding: 0.45rem;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .action-icon-btn:active { transform: scale(0.92); color: var(--fg); background: #252a3d; }
        .action-icon-btn svg { width: 1rem; height: 1rem; }

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
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(8px);
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
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-title { margin: 0; font-size: 1.1rem; font-weight: 700; }

        /* Type Selector Pills */
        .type-selector {
            display: flex;
            gap: 0.5rem;
            background: #0d0f18;
            padding: 0.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
        }

        .type-pill {
            flex: 1;
            appearance: none;
            border: none;
            background: transparent;
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.55rem;
            border-radius: 0.55rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
        }

        .type-pill.active {
            background: var(--surface-hover);
            color: var(--fg);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
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

        .input-control:focus { border-color: var(--accent); }
        textarea.input-control { min-height: 5rem; resize: vertical; }

        .modal-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }

        .btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary { background: #1c2030; color: var(--muted); }
        .btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }

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

        /* Toast notification */
        .toast-popup {
            position: fixed;
            top: 4rem;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background: #10b981;
            color: #000;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.6rem 1.2rem;
            border-radius: 999px;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            z-index: 50;
            opacity: 0;
            pointer-events: none;
            transition: all 0.25s ease;
        }

        .toast-popup.active {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .view-content-full {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #d1d5db;
            white-space: pre-wrap;
            background: #0d0f18;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            max-height: 15rem;
            overflow-y: auto;
        }
    </style>
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
                            <button class="delete-btn" onclick="event.stopPropagation(); deleteNote('{{ $note['id'] }}', false)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
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
                                <button class="delete-btn" onclick="event.stopPropagation(); deleteNote('{{ $note['id'] }}', true)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
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

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                <span id="view-date" class="note-date">Aug 27, 2026</span>
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()" style="flex: initial; padding: 0.6rem 1.4rem;">Close</button>
            </div>
        </div>
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

        function selectFormType(type) {
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
        }

        // Fingerprint Authentication Trigger
        scanFingerprintBtn.addEventListener('click', async () => {
            scanFingerprintBtn.disabled = true;
            scanFingerprintBtn.querySelector('span').textContent = 'Scanning...';

            try {
                const response = await fetch(@json(route('biometrics.authenticate')), {
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
            modalHeading.textContent = isPrivate ? 'Add Secret / Note 🔒' : 'Add Public Secret / Note 🌐';
            notePrivateFlag.value = isPrivate ? '1' : '0';
            noteTitleInput.value = '';
            secretValueInput.value = '';
            noteContentInput.value = '';
            selectFormType('secret');
            noteModal.classList.add('active');
        });

        function closeModal() { noteModal.classList.remove('active'); }

        noteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const isPrivate = notePrivateFlag.value === '1';
            const type = entryTypeInput.value;

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
                        type: type,
                        category: categorySelect.value,
                        secret_value: secretValueInput.value,
                        is_private: isPrivate,
                    }),
                });

                const result = await response.json();
                if (result.success) {
                    closeModal();
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

        // View Item Details Modal
        function openViewModal(id) {
            const card = document.querySelector(`.note-card[data-id="${id}"]`);
            if (!card) return;

            const title = card.querySelector('.note-title').textContent;
            const badge = card.querySelector('.badge-tag').textContent;
            const date = card.querySelector('.note-date').textContent;
            const bodyEl = card.querySelector('.note-body');
            const content = bodyEl ? bodyEl.textContent : '';

            const secretEl = card.querySelector(`#secret-val-${id}`);
            const isSecret = secretEl !== null;

            viewTitle.textContent = title;
            viewBadge.textContent = badge;
            viewBadge.className = isSecret ? 'badge-tag secret-tag' : 'badge-tag';
            viewDate.textContent = date;

            if (isSecret) {
                currentViewRealSecret = secretEl.dataset.real || '';
                viewSecretVal.textContent = '••••••••••••••••';
                viewSecretWrap.style.display = 'block';
            } else {
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
        }

        function closeViewModal() { viewModal.classList.remove('active'); }

        function toggleViewSecretVisibility() {
            const isMasked = viewSecretVal.textContent === '••••••••••••••••';
            viewSecretVal.textContent = isMasked ? currentViewRealSecret : '••••••••••••••••';
        }

        async function copyViewSecret() {
            if (!currentViewRealSecret) return;
            try {
                await navigator.clipboard.writeText(currentViewRealSecret);
                showToast(`Copied Secret to Clipboard!`);
            } catch (err) {
                alert('Failed to copy to clipboard');
            }
        }

        function toggleSecretVisibility(id) {
            const el = document.getElementById(`secret-val-${id}`);
            if (!el) return;
            const isMasked = el.textContent === '••••••••••••••••';
            el.textContent = isMasked ? el.dataset.real : '••••••••••••••••';
        }

        async function copySecret(id, title) {
            const el = document.getElementById(`secret-val-${id}`);
            if (!el || !el.dataset.real) return;
            try {
                await navigator.clipboard.writeText(el.dataset.real);
                showToast(`Copied "${title}" to Clipboard!`);
            } catch (err) {
                alert('Failed to copy to clipboard');
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
            card.onclick = () => openViewModal(note.id);

            const isSecret = note.type === 'secret';
            const tagText = isSecret ? (note.category || 'SECRET') : 'NOTE';
            const tagClass = isSecret ? 'badge-tag secret-tag' : 'badge-tag';

            let secretHtml = '';
            if (isSecret) {
                secretHtml = `
                    <div class="secret-box" onclick="event.stopPropagation();">
                        <span class="secret-val" id="secret-val-${note.id}" data-real="${escapeHtml(note.secret_value || '')}">••••••••••••••••</span>
                        <div class="secret-actions">
                            <button class="action-icon-btn" onclick="toggleSecretVisibility('${note.id}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                            <button class="action-icon-btn" onclick="copySecret('${note.id}', '${escapeHtml(note.title)}')">
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
                    <button class="delete-btn" onclick="event.stopPropagation(); deleteNote('${note.id}', ${note.is_private})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
                ${secretHtml}
                ${bodyHtml}
                <span class="note-date">${escapeHtml(note.created_at)}</span>
            `;
            list.prepend(card);
        }

        async function deleteNote(id, isPrivate) {
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
        }

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
    </script>
</body>
</html>
