<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Lobby — {{ $session->quiz->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(ellipse at 20% 10%, #1e1448 0%, #0d0d1a 55%, #050508 100%);
            color: #ffffff;
            overflow-x: hidden;
        }
        body { display: flex; flex-direction: column; }

        /* Ambient particles */
        .star { position: fixed; border-radius: 50%; background: #fff; animation: twinkle 3s infinite alternate; pointer-events: none; }
        @keyframes twinkle { 0%{opacity:0.1;} 100%{opacity:0.6;} }

        /* Top bar */
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 28px;
            background: rgba(255,255,255,0.04);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            flex-shrink: 0;
        }
        .live-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 5px 14px; border-radius: 999px;
            background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.35);
            color: #fca5a5; font-size: 12px; font-weight: 700; letter-spacing: 1px;
        }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; animation: pulse-dot 1.2s infinite; }
        @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:0.5;transform:scale(1.4);} }

        .btn-ghost {
            padding: 8px 18px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.75);
            font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: background 0.2s, color 0.2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.12); color: #fff; }

        /* Main stage */
        .stage {
            flex: 1; display: grid;
            grid-template-columns: 1fr 380px;
            grid-template-rows: 1fr;
            gap: 0;
            overflow: hidden;
        }
        @media (max-width: 900px) {
            .stage { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
        }

        /* Left: PIN + QR hero */
        .pin-stage {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 32px;
            border-right: 1px solid rgba(255,255,255,0.06);
        }
        .join-url-badge {
            font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.5);
            letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px;
        }
        .join-url-badge span { color: #38bdf8; }

        .pin-block {
            background: rgba(255,255,255,0.05);
            border: 1.5px solid rgba(56,189,248,0.25);
            border-radius: 28px;
            padding: 28px 48px;
            text-align: center;
            box-shadow: 0 0 60px rgba(56,189,248,0.08), inset 0 1px 0 rgba(255,255,255,0.08);
            margin-bottom: 28px;
        }
        .pin-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; color: rgba(255,255,255,0.35); text-transform: uppercase; margin-bottom: 8px; }
        .pin-digits {
            font-size: 5rem; font-weight: 900; letter-spacing: 16px;
            color: #38bdf8;
            text-shadow: 0 0 30px rgba(56,189,248,0.5);
            line-height: 1;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 14px; border-radius: 20px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.5);
            display: inline-block;
        }

        .instruction-row {
            display: flex; flex-direction: column; gap: 8px; margin-top: 20px;
            max-width: 320px; text-align: left;
        }
        .instruction-row span {
            font-size: 13px; color: rgba(255,255,255,0.45);
            display: flex; align-items: center; gap: 8px;
        }

        /* Right: Teams panel */
        .teams-panel {
            display: flex; flex-direction: column;
            padding: 24px;
            overflow: hidden;
        }
        .teams-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }
        .teams-count-badge {
            font-size: 13px; font-weight: 700;
            padding: 4px 12px; border-radius: 999px;
            background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6);
        }

        .teams-scroll { flex: 1; overflow-y: auto; padding-right: 4px; }
        .teams-scroll::-webkit-scrollbar { width: 4px; }
        .teams-scroll::-webkit-scrollbar-track { background: transparent; }
        .teams-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .team-entry {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            margin-bottom: 8px;
            animation: slideIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        @keyframes slideIn { from{opacity:0;transform:translateX(20px) scale(0.95);} to{opacity:1;transform:none;} }

        .team-avatar {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 900; color: #fff;
            flex-shrink: 0;
        }
        .team-name { font-size: 0.9rem; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .captain-name { font-size: 0.75rem; color: rgba(255,255,255,0.4); }

        .empty-state {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; color: rgba(255,255,255,0.3);
            padding: 40px 20px; gap: 10px;
        }

        /* Start button area */
        .start-area {
            padding: 20px 24px;
            border-top: 1px solid rgba(255,255,255,0.07);
            background: rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        .start-btn {
            width: 100%; padding: 16px;
            border-radius: 16px; border: none;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem; font-weight: 800;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .start-btn:disabled {
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.25);
            cursor: not-allowed;
            box-shadow: none;
        }
        .start-btn:not(:disabled) {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1a1a2e;
            box-shadow: 0 8px 24px rgba(245,158,11,0.4);
        }
        .start-btn:not(:disabled):hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(245,158,11,0.5); }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Ambient stars -->
    <script>
        for (let i = 0; i < 60; i++) {
            const s = document.createElement('div');
            s.className = 'star';
            const size = Math.random() * 2.5 + 0.5;
            s.style.cssText = `width:${size}px;height:${size}px;top:${Math.random()*100}vh;left:${Math.random()*100}vw;opacity:${Math.random()*0.4+0.05};animation-delay:${Math.random()*3}s;animation-duration:${2+Math.random()*3}s;`;
            document.body.appendChild(s);
        }
    </script>

    <!-- Top Bar -->
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="live-badge"><div class="live-dot"></div>LOBBY LIVE</div>
            <div>
                <div style="font-weight:800;font-size:1rem;color:#fff;">{{ $session->quiz->title }}</div>
                <div style="font-size:12px;color:rgba(255,255,255,0.35);">Hosted by {{ $session->host->first_name ?? 'Teacher' }}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <button onclick="toggleSound()" class="btn-ghost" id="soundBtn">
                <i class="bi bi-volume-up-fill" id="soundIcon"></i> Sound
            </button>
            <a href="{{ route('live-quiz.show', $session->live_quiz_id) }}" class="btn-ghost">
                <i class="bi bi-x-lg"></i> Exit
            </a>
        </div>
    </div>

    <!-- Main Stage -->
    <div class="stage">

        <!-- Left: PIN + QR -->
        <div class="pin-stage">
            <div class="join-url-badge">
                Join at: <span>{{ url('/quiz/join') }}</span>
            </div>

            <div class="pin-block">
                <div class="pin-label">Game PIN</div>
                <div class="pin-digits">{{ $session->pin_code }}</div>
            </div>

            <div class="qr-wrapper">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($joinUrl) }}&bgcolor=ffffff&color=0d0d1a"
                     alt="QR Code" width="160" height="160" style="display:block;border-radius:8px;">
            </div>

            <div class="instruction-row">
                <span><i class="bi bi-qr-code-scan" style="color:#38bdf8;font-size:1rem;"></i> Scan with phone camera to join instantly</span>
                <span><i class="bi bi-people-fill" style="color:#fbbf24;font-size:1rem;"></i> Each captain enters team name + picks color</span>
                <span><i class="bi bi-broadcast" style="color:#10b981;font-size:1rem;"></i> Start game once all groups are connected</span>
            </div>
        </div>

        <!-- Right: Teams panel + Start button -->
        <div style="display:flex;flex-direction:column;overflow:hidden;">
            <div class="teams-panel" style="flex:1;overflow:hidden;">
                <div class="teams-header">
                    <div style="font-size:0.95rem;font-weight:700;color:#fff;">
                        <i class="bi bi-shield-check" style="color:#10b981;margin-right:6px;"></i>
                        Connected Groups
                    </div>
                    <div class="teams-count-badge">
                        <span id="teamCount">0</span>/{{ $session->max_teams }}
                    </div>
                </div>

                <div class="teams-scroll" id="teamsScroll">
                    <div class="empty-state" id="emptyState">
                        <i class="bi bi-hourglass-split" style="font-size:2.5rem;color:rgba(245,158,11,0.4);"></i>
                        <div style="font-size:0.9rem;font-weight:600;">Waiting for teams to join…</div>
                        <div style="font-size:0.78rem;">Students scan QR or enter PIN at<br>{{ url('/quiz/join') }}</div>
                    </div>
                </div>
            </div>

            <div class="start-area">
                <button id="startBtn" class="start-btn" onclick="startGame()" disabled>
                    <i class="bi bi-play-circle-fill" style="font-size:1.1rem;"></i>
                    <span id="startBtnLabel">Waiting for players (<span id="btnCount">0</span>)</span>
                </button>
            </div>
        </div>

    </div>

    <script>
        const sessionId = {{ $session->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let soundEnabled = true;
        let knownIds = new Set();

        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playJoin() {
            if (!soundEnabled) return;
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(440, audioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.18);
                gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
                osc.connect(gain); gain.connect(audioCtx.destination);
                osc.start(); osc.stop(audioCtx.currentTime + 0.4);
            } catch(e) {}
        }

        function toggleSound() {
            soundEnabled = !soundEnabled;
            document.getElementById('soundIcon').className = soundEnabled ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill';
            document.getElementById('soundBtn').innerHTML = (soundEnabled ? '<i class="bi bi-volume-up-fill"></i>' : '<i class="bi bi-volume-mute-fill"></i>') + ' Sound';
        }

        function esc(s) {
            return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        async function poll() {
            try {
                const res  = await fetch(`/quiz/host/api/state/${sessionId}`);
                if (!res.ok) return;
                const data = await res.json();
                if (data.status !== 'lobby') { window.location.href = `/quiz/host/game/${sessionId}`; return; }
                renderTeams(data.teams);
            } catch(e) {}
        }

        function renderTeams(teams) {
            const scroll   = document.getElementById('teamsScroll');
            const empty    = document.getElementById('emptyState');
            const count    = document.getElementById('teamCount');
            const btnCount = document.getElementById('btnCount');
            const startBtn = document.getElementById('startBtn');
            const startLbl = document.getElementById('startBtnLabel');

            count.textContent   = teams.length;
            btnCount.textContent = teams.length;

            // New joins
            teams.forEach(t => {
                if (!knownIds.has(t.id)) { playJoin(); knownIds.add(t.id); }
            });

            if (teams.length === 0) {
                empty.style.display = 'flex';
                startBtn.disabled = true;
                startLbl.innerHTML = `Waiting for players (<span id="btnCount">0</span>)`;
                return;
            }

            empty.style.display = 'none';
            startBtn.disabled = false;
            startLbl.innerHTML = `<i class="bi bi-play-circle-fill" style="font-size:1.1rem;"></i>&nbsp; Start Competition (${teams.length} team${teams.length > 1 ? 's' : ''})`;

            // Rebuild team list
            let html = '';
            teams.forEach((t, i) => {
                html += `
                <div class="team-entry" style="border-left: 3px solid ${t.team_color};">
                    <div class="team-avatar" style="background:${t.team_color};">
                        ${i + 1}
                    </div>
                    <div style="min-width:0;">
                        <div class="team-name">${esc(t.team_name)}</div>
                        <div class="captain-name"><i class="bi bi-person-fill me-1"></i>${esc(t.captain_name)}</div>
                    </div>
                    <div style="margin-left:auto;flex-shrink:0;">
                        <i class="bi bi-wifi" style="color:#10b981;font-size:0.85rem;"></i>
                    </div>
                </div>`;
            });
            scroll.innerHTML = html;
        }

        async function startGame() {
            const btn = document.getElementById('startBtn');
            btn.disabled = true;
            btn.innerHTML = '<span style="width:18px;height:18px;border:2.5px solid rgba(0,0,0,0.2);border-top-color:#1a1a2e;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;"></span>&nbsp; Starting…';

            try {
                const res  = await fetch(`/quiz/host/api/start/${sessionId}`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = data.redirect || `/quiz/host/game/${sessionId}`;
                } else {
                    alert(data.error || 'Failed to start');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-play-circle-fill"></i>&nbsp; Start Competition';
                }
            } catch(e) {
                alert('Network error');
                btn.disabled = false;
            }
        }

        poll();
        setInterval(poll, 1500);
    </script>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
</body>
</html>
