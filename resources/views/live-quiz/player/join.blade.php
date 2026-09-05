<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Join Live Quiz Arena</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            background: #080b14;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: drift 8s ease-in-out infinite alternate;
        }
        .orb-1 { width: 350px; height: 350px; background: rgba(99,102,241,0.25); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(245,158,11,0.18); bottom: -80px; right: -60px; animation-delay: -4s; }
        .orb-3 { width: 200px; height: 200px; background: rgba(139,92,246,0.2); top: 40%; left: 60%; animation-delay: -2s; }
        @keyframes drift { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(30px,20px) scale(1.1); } }

        /* Card */
        .join-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(15, 20, 35, 0.85);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 28px;
            padding: 36px 28px 32px;
            backdrop-filter: blur(20px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.08);
        }

        /* Logo ring */
        .logo-ring {
            width: 76px; height: 76px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2));
            border: 2px solid rgba(139,92,246,0.5);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 0 24px rgba(139,92,246,0.3);
        }

        /* Labels */
        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 8px;
        }

        /* PIN input */
        .pin-input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 14px 20px;
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: 12px;
            text-align: center;
            color: #38bdf8;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            caret-color: #38bdf8;
        }
        .pin-input::placeholder { color: rgba(56,189,248,0.25); letter-spacing: 8px; }
        .pin-input:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56,189,248,0.15), 0 0 20px rgba(56,189,248,0.1);
        }
        .pin-input.valid { border-color: #10b981; color: #34d399; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }

        /* Text inputs */
        .text-input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #ffffff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Outfit', sans-serif;
        }
        .text-input::placeholder { color: rgba(255,255,255,0.25); font-weight: 400; }
        .text-input:focus {
            border-color: rgba(139,92,246,0.6);
            box-shadow: 0 0 0 3px rgba(139,92,246,0.12);
        }

        /* Color picker */
        .color-swatches { display: flex; gap: 10px; justify-content: center; }
        .swatch {
            width: 38px; height: 38px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: transform 0.2s, border-color 0.15s, box-shadow 0.2s;
            position: relative;
        }
        .swatch:hover { transform: scale(1.15); }
        .swatch.active {
            border-color: #ffffff;
            transform: scale(1.2);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }
        .swatch.active::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 900;
            color: #fff;
        }

        /* Join button */
        .join-btn {
            width: 100%;
            padding: 15px;
            border-radius: 16px;
            border: none;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #1a1a2e;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            box-shadow: 0 8px 24px rgba(245,158,11,0.4);
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.2s;
        }
        .join-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(245,158,11,0.5); }
        .join-btn:active:not(:disabled) { transform: translateY(0); }
        .join-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Error */
        .error-box {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.85rem;
            color: #fca5a5;
            display: none;
        }

        .divider { height: 1px; background: rgba(255,255,255,0.08); margin: 20px 0; }
        .field-group { margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="bg-scene">
        <div class="bg-orb orb-1"></div>
        <div class="bg-orb orb-2"></div>
        <div class="bg-orb orb-3"></div>
    </div>

    <div class="join-card">
        {{-- Logo --}}
        <div class="logo-ring">
            <i class="bi bi-trophy-fill" style="font-size:2rem;color:#fbbf24;"></i>
        </div>

        <div class="text-center mb-6">
            <h2 style="font-size:1.6rem;font-weight:900;color:#fff;line-height:1.2;">Quiz Arena</h2>
            <p style="font-size:0.82rem;color:rgba(255,255,255,0.4);margin-top:4px;">Enter your PIN and team details to compete live</p>
        </div>

        <div id="errorBox" class="error-box mb-4"></div>

        <form id="joinForm" onsubmit="handleJoin(event)" autocomplete="off">

            {{-- PIN --}}
            <div class="field-group">
                <label class="field-label" for="pinInput">Game PIN</label>
                <input type="text" id="pinInput" class="pin-input"
                       maxlength="6" inputmode="numeric" placeholder="• • • • • •"
                       value="{{ $presetPin }}" required autocomplete="off"
                       oninput="this.classList.toggle('valid', this.value.length === 6)">
            </div>

            <div class="divider"></div>

            {{-- Team Name --}}
            <div class="field-group">
                <label class="field-label" for="teamNameInput">Team / Group Name</label>
                <input type="text" id="teamNameInput" class="text-input"
                       placeholder="e.g. Red Phoenix, Group 1" maxlength="30" required>
            </div>

            {{-- Captain Name --}}
            <div class="field-group">
                <label class="field-label" for="captainNameInput">Captain Name</label>
                <input type="text" id="captainNameInput" class="text-input"
                       placeholder="e.g. Rahul / Sara" maxlength="30" required>
            </div>

            {{-- Team Color --}}
            <div class="field-group">
                <label class="field-label" style="text-align:center;display:block;margin-bottom:12px;">Team Banner Color</label>
                <div class="color-swatches" id="colorSwatches">
                    <div class="swatch active" style="background:#ef4444;" data-color="#ef4444" onclick="selectColor(this)"></div>
                    <div class="swatch" style="background:#3b82f6;" data-color="#3b82f6" onclick="selectColor(this)"></div>
                    <div class="swatch" style="background:#10b981;" data-color="#10b981" onclick="selectColor(this)"></div>
                    <div class="swatch" style="background:#f59e0b;" data-color="#f59e0b" onclick="selectColor(this)"></div>
                    <div class="swatch" style="background:#8b5cf6;" data-color="#8b5cf6" onclick="selectColor(this)"></div>
                    <div class="swatch" style="background:#ec4899;" data-color="#ec4899" onclick="selectColor(this)"></div>
                    <div class="swatch" style="background:#06b6d4;" data-color="#06b6d4" onclick="selectColor(this)"></div>
                </div>
            </div>

            <div style="margin-top:24px;">
                <button type="submit" id="joinBtn" class="join-btn">
                    <i class="bi bi-box-arrow-in-right" style="margin-right:8px;"></i>Join Competition
                </button>
            </div>
        </form>

        <div style="text-align:center;margin-top:18px;font-size:0.78rem;color:rgba(255,255,255,0.25);">
            <i class="bi bi-shield-lock-fill" style="margin-right:4px;color:rgba(99,102,241,0.5);"></i>
            Screen locks automatically during live rounds
        </div>
    </div>

    <script>
        let selectedColor = '#ef4444';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function selectColor(el) {
            document.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
            el.classList.add('active');
            selectedColor = el.dataset.color;
        }

        async function handleJoin(e) {
            e.preventDefault();
            const btn     = document.getElementById('joinBtn');
            const errBox  = document.getElementById('errorBox');
            const pin     = document.getElementById('pinInput').value.trim();
            const team    = document.getElementById('teamNameInput').value.trim();
            const captain = document.getElementById('captainNameInput').value.trim();

            errBox.style.display = 'none';

            if (pin.length !== 6) {
                showError('Please enter a valid 6-digit Game PIN.');
                return;
            }
            if (!team) { showError('Please enter a team name.'); return; }
            if (!captain) { showError('Please enter the captain\'s name.'); return; }

            btn.disabled = true;
            btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2.5px solid rgba(0,0,0,0.3);border-top-color:#1a1a2e;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:8px;"></span>Joining…';

            try {
                const res  = await fetch('/quiz/player/api/join', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ pin_code: pin, team_name: team, captain_name: captain, team_color: selectedColor })
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    localStorage.setItem('quiz_captain_token', data.captain_token);
                    localStorage.setItem('quiz_pin', pin);
                    window.location.href = data.redirect;
                } else {
                    showError(data.error || 'Failed to join quiz. Check the PIN and try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-box-arrow-in-right" style="margin-right:8px;"></i>Join Competition';
                }
            } catch (err) {
                showError('Network error — please check your connection and retry.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-in-right" style="margin-right:8px;"></i>Join Competition';
            }
        }

        function showError(msg) {
            const b = document.getElementById('errorBox');
            b.textContent = msg;
            b.style.display = 'block';
        }
    </script>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</body>
</html>
