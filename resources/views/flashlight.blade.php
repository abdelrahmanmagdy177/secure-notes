<!DOCTYPE html>
<html lang="en" data-state="{{ $state ? 'on' : 'off' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b0d12">
    <title>Flashlight & Biometrics</title>
    <style>
        :root {
            --bg: #0b0d12;
            --fg: #f4f6fb;
            --muted: #7c8496;
            --glow: #ffd76a;
            --accent: #3b82f6;
            --success: #10b981;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            margin: 0;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            padding: max(2rem, env(safe-area-inset-top)) 1.5rem max(2rem, env(safe-area-inset-bottom));
            background: var(--bg);
            color: var(--fg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            text-align: center;
            transition: background .25s ease;
            user-select: none;
        }

        html[data-state="on"] body { background: #16181f; }

        h1 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--muted);
        }

        #torch {
            appearance: none;
            border: 0;
            width: 14rem;
            height: 14rem;
            border-radius: 50%;
            background: #171a22;
            color: var(--muted);
            display: grid;
            place-items: center;
            cursor: pointer;
            box-shadow: inset 0 0 0 1px #262a35, 0 18px 40px rgba(0, 0, 0, .55);
            transition: transform .12s ease, box-shadow .3s ease, background .3s ease, color .3s ease;
        }

        #torch:active { transform: scale(.96); }
        #torch[disabled] { opacity: .55; cursor: progress; }

        html[data-state="on"] #torch {
            background: radial-gradient(circle at 50% 40%, #fff3cf 0%, var(--glow) 55%, #f0a92e 100%);
            color: #3b2a05;
            box-shadow: 0 0 0 1px rgba(255, 215, 106, .5),
                        0 0 60px 12px rgba(255, 215, 106, .45),
                        0 0 160px 40px rgba(255, 215, 106, .22);
        }

        #torch svg { width: 5rem; height: 5rem; }

        .readout { display: grid; gap: .55rem; }

        #state {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: .04em;
        }

        #hint {
            margin: 0;
            font-size: .85rem;
            line-height: 1.5;
            color: var(--muted);
            max-width: 22rem;
        }

        /* Biometric Card Styling */
        .bio-card {
            background: #13161f;
            border: 1px solid #232733;
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.85rem;
            width: 100%;
            max-width: 20rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        .bio-btn {
            appearance: none;
            border: none;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.75rem 1.4rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
            transition: all 0.2s ease;
        }

        .bio-btn:active {
            transform: scale(0.96);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .bio-btn svg { width: 1.3rem; height: 1.3rem; }

        .bio-status {
            font-size: 0.8rem;
            color: var(--muted);
            margin: 0;
        }

        .bio-status.verified {
            color: var(--success);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: .3rem .7rem;
            border-radius: 999px;
            font-size: .7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: #1d212b;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <h1>Flashlight</h1>

    <button id="torch" type="button" aria-pressed="{{ $state ? 'true' : 'false' }}" aria-label="Toggle flashlight">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M7 2h10v3.2a2 2 0 0 1-.5 1.32L15 8.2V22H9V8.2L7.5 6.52A2 2 0 0 1 7 5.2V2Z"/>
            <path d="M7 5h10"/>
            <path d="M12 12v3"/>
        </svg>
    </button>

    <div class="readout">
        <p id="state">{{ $state ? 'ON' : 'OFF' }}</p>
        <p id="hint">Tap the torch to toggle your device's flash LED.</p>
        @unless ($native)
            <span class="badge">Browser preview &middot; torch simulated</span>
        @endunless
    </div>

    <!-- Biometric Fingerprint Access Card -->
    <div class="bio-card">
        <button id="bio-btn" class="bio-btn" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 12c0-3 2.5-5.5 5.5-5.5S23 9 23 12"/>
                <path d="M12 12c0 3-2.5 5.5-5.5 5.5S1 15 1 12"/>
                <path d="M12 7c-2.8 0-5 2.2-5 5 0 2.8 2.2 5 5 5"/>
                <path d="M12 2C6.5 2 2 6.5 2 12c0 5.5 4.5 10 10 10s10-4.5 10-10c0-5.5-4.5-10-10-10z"/>
            </svg>
            <span>Scan Fingerprint</span>
        </button>
        <p id="bio-status" class="bio-status {{ $authenticated ? 'verified' : '' }}">
            {{ $authenticated ? '✓ Fingerprint Verified' : 'Fingerprint Lock Enabled' }}
        </p>
    </div>

    <script>
        const html = document.documentElement;
        const torch = document.getElementById('torch');
        const stateLabel = document.getElementById('state');
        const hint = document.getElementById('hint');
        const bioBtn = document.getElementById('bio-btn');
        const bioStatus = document.getElementById('bio-status');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        torch.addEventListener('click', async () => {
            torch.disabled = true;

            try {
                const response = await fetch(@json(route('flashlight.toggle')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();

                html.dataset.state = result.state ? 'on' : 'off';
                stateLabel.textContent = result.state ? 'ON' : 'OFF';
                torch.setAttribute('aria-pressed', String(result.state));
                hint.textContent = result.simulated
                    ? 'No torch behind the bridge \u2014 showing a simulated state.'
                    : "Tap the torch to toggle your device's flash LED.";
            } catch (error) {
                hint.textContent = 'Could not reach the app: ' + error.message;
            } finally {
                torch.disabled = false;
            }
        });

        // Fingerprint Biometric Authentication
        bioBtn.addEventListener('click', async () => {
            bioBtn.disabled = true;
            bioStatus.textContent = 'Scanning fingerprint...';
            bioStatus.classList.remove('verified');

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
                    bioStatus.textContent = result.simulated
                        ? '✓ Fingerprint Verified (Simulated)'
                        : '✓ Fingerprint Verified';
                    bioStatus.classList.add('verified');
                } else {
                    bioStatus.textContent = '❌ Fingerprint authentication failed';
                }
            } catch (error) {
                bioStatus.textContent = 'Error: ' + error.message;
            } finally {
                bioBtn.disabled = false;
            }
        });
    </script>
</body>
</html>

