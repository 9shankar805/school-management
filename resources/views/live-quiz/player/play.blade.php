<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Live Quiz — PIN {{ $pin }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; -webkit-user-select: none; user-select: none; }
        html, body {
            height: 100%; font-family: 'Outfit', sans-serif;
            background: #080b14; color: #ffffff;
            overflow-x: hidden;
        }
        body {
            display: flex; flex-direction: column;
            min-height: 100vh; padding: 12px;
            gap: 10px;
        }

        /* ── Header ── */
        .hdr {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 10px 16px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .hdr-left { display: flex; align-items: center; gap: 10px; }
        .team-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
        .team-name-lbl { font-size: 0.9rem; font-weight: 800; color: #fff; }
        .captain-lbl { font-size: 0.72rem; color: rgba(255,255,255,0.4); }
        .score-lbl { font-size: 1.1rem; font-weight: 900; color: #fbbf24; }
        .rank-pill { font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.5); margin-top: 2px; }

        /* ── Anti-cheat bar ── */
        .security-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 4px; font-size: 0.72rem;
        }
        .warn-badge { background: #ef4444; color: #fff; padding: 2px 8px; border-radius: 999px; font-weight: 700; display: none; }

        /* ── Timer ring ── */
        .timer-ring {
            width: 80px; height: 80px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.4rem; font-weight: 900;
            border: 5px solid #10b981; color: #10b981;
            box-shadow: 0 0 20px rgba(16,185,129,0.35);
            transition: border-color .2s, color .2s, box-shadow .2s;
            margin: 0 auto;
        }
        .timer-ring.warn  { border-color: #f59e0b !important; color: #f59e0b !important; box-shadow: 0 0 20px rgba(245,158,11,0.4) !important; }
        .timer-ring.danger{ border-color: #ef4444 !important; color: #ef4444 !important; box-shadow: 0 0 28px rgba(239,68,68,0.7) !important; animation: pulse-danger .45s infinite alternate; }
        @keyframes pulse-danger { 0%{transform:scale(1);} 100%{transform:scale(1.07);} }

        /* ── MCQ pads ── */
        .pads { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .pad {
            height: 118px; border-radius: 20px; border: none;
            font-size: 2rem; font-weight: 900; color: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            transition: transform .12s ease, filter .2s;
            cursor: pointer; position: relative; overflow: hidden;
        }
        .pad::after { content:''; position:absolute; inset:0; background:rgba(255,255,255,0); transition:background .15s; }
        .pad:active::after { background: rgba(255,255,255,0.15); }
        .pad:active { transform: scale(0.91); filter: brightness(1.2); }
        .pad.locked { opacity: 0.5; pointer-events: none; }
        .pad.selected { border: 4px solid #ffffff; box-shadow: 0 0 30px rgba(255,255,255,0.7); transform: scale(0.95); }
        .pad-label { font-size: 0.78rem; font-weight: 700; opacity: 0.75; margin-top: 4px; letter-spacing: 1px; }
        .pad-a { background: linear-gradient(135deg,#ef4444,#b91c1c); }
        .pad-b { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }
        .pad-c { background: linear-gradient(135deg,#f59e0b,#b45309); }
        .pad-d { background: linear-gradient(135deg,#10b981,#047857); }

        /* ── Buzzer ── */
        .buzzer {
            width: 210px; height: 210px; border-radius: 50%;
            background: radial-gradient(circle at 38% 32%, #ef4444 0%, #7f1d1d 100%);
            border: 8px solid rgba(255,255,255,0.2);
            box-shadow: 0 16px 40px rgba(239,68,68,0.55), inset 0 -8px 12px rgba(0,0,0,0.35);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-size: 2.2rem; font-weight: 900; color: #fff;
            margin: 16px auto;
            cursor: pointer; transition: transform .12s;
        }
        .buzzer:active { transform: scale(0.9); }

        /* ── Status boxes ── */
        .status-box {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px; padding: 24px 20px;
            text-align: center; backdrop-filter: blur(8px);
        }

        /* ── Turn boxes ── */
        .my-turn-box {
            background: linear-gradient(135deg,rgba(234,179,8,.2),rgba(255,255,255,.04));
            border: 2px solid #eab308; border-radius: 22px; padding: 24px 18px; text-align: center;
            animation: glow-turn 1.6s infinite alternate;
        }
        @keyframes glow-turn { 0%{box-shadow:0 0 12px rgba(234,179,8,.25);} 100%{box-shadow:0 0 28px rgba(234,179,8,.6);} }
        .waiting-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 22px; padding: 24px 18px; text-align: center;
        }

        /* ── Result ── */
        .result-correct { border: 2px solid #10b981; background: rgba(16,185,129,0.1); }
        .result-wrong   { border: 2px solid #ef4444; background: rgba(239,68,68,0.1);  }

        /* ── Screen overlay (anti-cheat) ── */
        #overlay {
            position: fixed; inset: 0;
            background: rgba(8,11,20,0.96); backdrop-filter: blur(20px);
            z-index: 99999; display: none;
            flex-direction: column; align-items: center; justify-content: center;
            padding: 28px; text-align: center;
        }

        /* ── Toast ── */
        #toast {
            position: fixed; top: 14px; left: 50%; transform: translateX(-50%);
            background: #ef4444; color: #fff;
            padding: 10px 22px; border-radius: 999px; font-weight: 700; font-size: .85rem;
            z-index: 9999; display: none; box-shadow: 0 8px 20px rgba(239,68,68,0.5);
        }

        .hidden { display: none !important; }
        .q-num { font-size: .72rem; font-weight: 700; letter-spacing: 1px; color: rgba(255,255,255,.4); margin-top: 6px; text-transform: uppercase; }
    </style>
</head>
<body oncontextmenu="return false;">

    <!-- Anti-cheat overlay -->
    <div id="overlay">
        <div style="font-size:3.5rem;margin-bottom:12px;">⚠️</div>
        <h3 style="color:#ef4444;font-weight:900;margin-bottom:8px;">WARNING!</h3>
        <p style="color:rgba(255,255,255,.75);font-size:.95rem;margin-bottom:6px;">You left the quiz screen.</p>
        <p style="color:rgba(255,255,255,.4);font-size:.8rem;margin-bottom:24px;">This has been <strong style="color:#fbbf24;">logged and reported</strong> to the teacher.</p>
        <button onclick="dismissOverlay()"
                style="padding:12px 32px;border-radius:999px;background:#fbbf24;color:#1a1a2e;font-weight:800;font-size:.95rem;border:none;cursor:pointer;font-family:'Outfit',sans-serif;">
            <i class="bi bi-shield-lock-fill"></i>&nbsp; Return to Quiz
        </button>
    </div>

    <div id="toast"></div>

    <!-- Header -->
    <div class="hdr">
        <div class="hdr-left">
            <div class="team-dot" id="teamDot" style="background:#ef4444;"></div>
            <div>
                <div class="team-name-lbl" id="teamNameLbl">Connecting…</div>
                <div class="captain-lbl" id="captainLbl">Captain</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div class="score-lbl" id="scoreLbl">0 <span style="font-size:.75rem;color:rgba(255,255,255,.4);font-weight:600;">pts</span></div>
            <div class="rank-pill" id="rankLbl">Rank #1</div>
        </div>
    </div>

    <!-- Security bar -->
    <div class="security-bar">
        <span style="color:rgba(255,255,255,.3);"><i class="bi bi-shield-check" style="color:#10b981;margin-right:4px;"></i>Anti-Cheat Active</span>
        <span class="warn-badge" id="warnBadge">0 Warnings</span>
    </div>

    <!-- Main controller area -->
    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;" id="ctrl">

        <!-- 1. Lobby -->
        <div id="scrLobby" class="status-box">
            <div style="width:52px;height:52px;border-radius:50%;border:5px solid rgba(255,255,255,.15);border-top-color:#fbbf24;animation:spin .9s linear infinite;margin:0 auto 16px;"></div>
            <h4 style="font-weight:800;color:#fff;margin-bottom:6px;">You're in!</h4>
            <p style="font-size:.82rem;color:rgba(255,255,255,.4);margin-bottom:16px;">Waiting for teacher to launch Question 1…</p>
            <div style="font-family:monospace;font-size:.85rem;background:rgba(0,0,0,.3);color:#fbbf24;padding:8px 18px;border-radius:10px;display:inline-block;">PIN: {{ $pin }}</div>
        </div>

        <!-- 2. MCQ / Buzzer active -->
        <div id="scrActive" class="hidden">
            <div style="text-align:center;margin-bottom:14px;">
                <div class="timer-ring" id="timerRing">30</div>
                <div class="q-num" id="qNumLbl">Question #1</div>
            </div>

            <div class="pads" id="mcqPads">
                <button class="pad pad-a" onclick="tapOption('A')"><span>A</span><span class="pad-label">Option A</span></button>
                <button class="pad pad-b" onclick="tapOption('B')"><span>B</span><span class="pad-label">Option B</span></button>
                <button class="pad pad-c" onclick="tapOption('C')"><span>C</span><span class="pad-label">Option C</span></button>
                <button class="pad pad-d" onclick="tapOption('D')"><span>D</span><span class="pad-label">Option D</span></button>
            </div>

            <div class="hidden" id="buzzerWrap">
                <button class="buzzer" onclick="tapBuzzer()">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <div style="font-size:1rem;margin-top:4px;letter-spacing:2px;">BUZZ!</div>
                </button>
            </div>
        </div>

        <!-- 3. Direct / turn screen -->
        <div id="scrDirect" class="hidden">
            <div style="text-align:center;margin-bottom:14px;">
                <div class="timer-ring" id="directTimer">60</div>
                <div class="q-num" id="directQNum">Question #1 — Direct Round</div>
            </div>
            <div id="myTurnBox" class="my-turn-box hidden">
                <div style="font-size:2.8rem;margin-bottom:6px;">👑</div>
                <h3 style="font-weight:900;color:#fbbf24;margin-bottom:6px;" id="turnTitle">YOUR TEAM'S TURN!</h3>
                <p style="font-size:.82rem;color:rgba(255,255,255,.6);margin-bottom:16px;" id="turnSub">Discuss and answer verbally to the teacher!</p>
                <button onclick="tapPass()"
                        style="padding:12px 28px;border-radius:999px;background:#eab308;color:#1a1a2e;font-weight:800;font-size:.9rem;border:none;cursor:pointer;font-family:'Outfit',sans-serif;">
                    <i class="bi bi-arrow-repeat"></i>&nbsp; Pass to Next Team
                </button>
            </div>
            <div id="waitBox" class="waiting-box hidden">
                <div style="font-size:2.5rem;margin-bottom:8px;">👀</div>
                <h4 style="font-weight:800;color:#fff;margin-bottom:4px;" id="waitTitle">Another team's turn</h4>
                <p style="font-size:.8rem;color:rgba(255,255,255,.4);">If they pass or fail, your team may get a bonus!</p>
            </div>
        </div>

        <!-- 4. Answer locked -->
        <div id="scrLocked" class="status-box hidden">
            <div style="font-size:3rem;margin-bottom:8px;">🔒</div>
            <h4 style="font-weight:800;color:#fff;margin-bottom:12px;">Answer Locked!</h4>
            <div style="display:inline-block;padding:10px 28px;border-radius:999px;background:#fbbf24;color:#1a1a2e;font-size:1.3rem;font-weight:900;" id="lockedLbl">Option A</div>
            <p style="font-size:.78rem;color:rgba(255,255,255,.35);margin-top:12px;">Waiting for host to reveal answer…</p>
        </div>

        <!-- 5. Result -->
        <div id="scrResult" class="status-box hidden">
            <div style="font-size:3.5rem;margin-bottom:8px;" id="resultIcon">🎉</div>
            <h3 style="font-weight:900;margin-bottom:8px;" id="resultTitle">CORRECT!</h3>
            <div style="font-size:1.4rem;font-weight:900;color:#10b981;margin-bottom:6px;" id="pointsLbl">+100 pts</div>
            <p style="font-size:.78rem;color:rgba(255,255,255,.35);" id="resultMsg">Check the big screen for full results!</p>
        </div>

        <!-- 6. Final -->
        <div id="scrFinal" class="status-box hidden">
            <div style="font-size:3.5rem;margin-bottom:8px;">🏆</div>
            <h3 style="font-weight:900;color:#fbbf24;margin-bottom:4px;">Championship Complete!</h3>
            <p style="font-size:.82rem;color:rgba(255,255,255,.4);margin-bottom:12px;">Final Score</p>
            <div style="font-size:2.8rem;font-weight:900;color:#fff;margin-bottom:8px;" id="finalScore">0 pts</div>
            <div style="display:inline-block;padding:6px 20px;border-radius:999px;background:#fbbf24;color:#1a1a2e;font-weight:800;" id="finalRank">Rank #1</div>
        </div>

    </div>

    <!-- Footer -->
    <div style="text-align:center;font-size:.72rem;color:rgba(255,255,255,.2);padding-bottom:4px;">
        PIN: {{ $pin }} &nbsp;•&nbsp; Captain Mode
    </div>

    <script>
        const PIN   = "{{ $pin }}";
        const TOKEN = "{{ $token }}" || localStorage.getItem('quiz_captain_token');
        const CSRF  = document.querySelector('meta[name="csrf-token"]').content;

        let warnings = 0, lastVibSec = null, wasTurn = false, wakeLock = null;

        // Screen management
        const screens = ['scrLobby','scrActive','scrDirect','scrLocked','scrResult','scrFinal'];
        function show(id) { screens.forEach(s => { const el = document.getElementById(s); if (el) el.classList.toggle('hidden', s !== id); }); }

        // Wake lock
        async function keepAwake() {
            try { if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen'); } catch(e) {}
        }
        keepAwake();
        document.addEventListener('visibilitychange', () => { if (!document.hidden) keepAwake(); });

        // Fullscreen on first tap
        document.body.addEventListener('click', () => {
            if (!document.fullscreenElement) document.documentElement.requestFullscreen?.().catch(()=>{});
        }, { once: true });

        // Navigation lock
        history.pushState(null, null, location.href);
        window.addEventListener('popstate', () => { history.pushState(null, null, location.href); toast('⚠️ Back button disabled during quiz!'); vibrate([100,50,100]); });
        window.addEventListener('beforeunload', e => { e.preventDefault(); e.returnValue = 'Quiz is active!'; });

        // Anti-cheat
        let tabActive = true;
        document.addEventListener('visibilitychange', () => { if (document.hidden) cheat('tab_hidden'); });
        window.addEventListener('blur', () => cheat('window_blur'));

        function cheat(type) {
            if (!tabActive) return;
            tabActive = false; warnings++;
            document.getElementById('overlay').style.display = 'flex';
            const b = document.getElementById('warnBadge');
            b.textContent = `${warnings} Warning${warnings > 1 ? 's' : ''}`;
            b.style.display = 'inline-block';
            vibrate([200,100,200,100,300]);
            fetch('/quiz/player/api/security-event', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ captain_token: TOKEN, pin_code: PIN, event_type: type })
            }).catch(() => {});
        }
        function dismissOverlay() { document.getElementById('overlay').style.display = 'none'; setTimeout(() => tabActive = true, 800); }

        window.addEventListener('keydown', e => {
            if (e.key === 'PrintScreen' || (e.ctrlKey && ['p','s','u'].includes(e.key)) || e.key === 'F12') {
                e.preventDefault(); toast('⚠️ Action blocked!'); cheat('screenshot'); return false;
            }
        });

        // Helpers
        function toast(msg, dur = 3000) {
            const t = document.getElementById('toast');
            t.textContent = msg; t.style.display = 'block';
            clearTimeout(toast._t);
            toast._t = setTimeout(() => t.style.display = 'none', dur);
        }
        function vibrate(p = 60) { try { navigator.vibrate?.(p); } catch(e) {} }

        // Gameplay actions
        async function tapOption(opt) {
            vibrate([70,40,70]);
            document.querySelectorAll('.pad').forEach(p => p.classList.add('locked'));
            show('scrLocked');
            document.getElementById('lockedLbl').textContent = `Option ${opt}`;
            await fetch('/quiz/player/api/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ captain_token: TOKEN, pin_code: PIN, option: opt })
            }).catch(() => {});
        }

        async function tapBuzzer() {
            vibrate([100,50,150]);
            const res  = await fetch('/quiz/player/api/buzz', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ captain_token: TOKEN, pin_code: PIN })
            }).catch(() => {});
            if (res) { const d = await res.json(); toast(d.message || 'Buzzed!'); }
        }

        async function tapPass() {
            vibrate([80,50,80]);
            await fetch('/quiz/player/api/pass', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ captain_token: TOKEN, pin_code: PIN })
            }).catch(() => {});
            toast('Turn passed!');
        }

        // Timer helper
        function setTimer(id, sec) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = sec;
            el.className = 'timer-ring' + (sec <= 5 ? ' danger' : sec <= 10 ? ' warn' : '');
            if (sec <= 5 && lastVibSec !== sec && sec > 0) { vibrate(40); lastVibSec = sec; }
        }

        // State poller
        async function poll() {
            try {
                const res  = await fetch(`/quiz/player/api/state/${PIN}?token=${TOKEN}`);
                if (!res.ok) return;
                const d = await res.json();
                render(d);
            } catch(e) {}
        }

        function render(d) {
            // Header
            if (d.my_team) {
                document.getElementById('teamDot').style.background = d.my_team.team_color;
                document.getElementById('teamNameLbl').textContent  = d.my_team.team_name;
                document.getElementById('captainLbl').textContent   = `Captain: ${d.my_team.captain_name}`;
                document.getElementById('scoreLbl').innerHTML       = `${d.my_team.total_score} <span style="font-size:.75rem;color:rgba(255,255,255,.4);font-weight:600;">pts</span>`;
                document.getElementById('rankLbl').textContent      = `Rank #${d.my_team.current_rank}`;
            }

            const status = d.session_status;

            if (status === 'lobby') {
                show('scrLobby');

            } else if (status === 'question_active') {
                const isDirect = d.current_question?.question_type === 'direct';

                if (isDirect) {
                    show('scrDirect');
                    setTimer('directTimer', d.seconds_remaining);
                    document.getElementById('directQNum').textContent = `Question #${d.current_question_index} — Direct Round`;

                    const myTurn = d.is_my_turn;
                    document.getElementById('myTurnBox').classList.toggle('hidden', !myTurn);
                    document.getElementById('waitBox').classList.toggle('hidden', myTurn);

                    if (myTurn) {
                        document.getElementById('turnTitle').textContent = d.is_passed ? '⚡ PASSED TO YOUR TEAM!' : "👑 YOUR TEAM'S TURN!";
                        document.getElementById('turnSub').textContent   = d.is_passed ? 'Bonus round — answer verbally!' : 'Discuss and answer verbally to the teacher!';
                        if (!wasTurn) { vibrate([150,80,150]); wasTurn = true; }
                    } else {
                        wasTurn = false;
                        const act = d.active_turn_team?.team_name || 'Another team';
                        document.getElementById('waitTitle').textContent = `Question with: ${act}`;
                    }

                } else {
                    if (d.submitted_answer) {
                        show('scrLocked');
                        document.getElementById('lockedLbl').textContent = `Option ${d.submitted_answer}`;
                    } else {
                        show('scrActive');
                        setTimer('timerRing', d.seconds_remaining);
                        document.getElementById('qNumLbl').textContent = `Question #${d.current_question_index}`;

                        const isBuzzer = d.current_question?.question_type === 'buzzer';
                        document.getElementById('mcqPads').classList.toggle('hidden', isBuzzer);
                        document.getElementById('buzzerWrap').classList.toggle('hidden', !isBuzzer);
                    }
                }

            } else if (status === 'question_locked') {
                show('scrLocked');
                document.getElementById('lockedLbl').textContent = d.submitted_answer ? `Option ${d.submitted_answer}` : 'No Answer';

            } else if (status === 'question_result' || status === 'leaderboard') {
                show('scrResult');
                const o = d.answer_outcome;
                if (o?.is_correct) {
                    document.getElementById('resultIcon').textContent  = '🎉';
                    document.getElementById('resultTitle').textContent = 'CORRECT!';
                    document.getElementById('resultTitle').style.color = '#10b981';
                    document.getElementById('pointsLbl').textContent   = `+${o.points_awarded} pts`;
                    document.getElementById('pointsLbl').style.color   = '#10b981';
                } else {
                    document.getElementById('resultIcon').textContent  = '❌';
                    document.getElementById('resultTitle').textContent = 'INCORRECT';
                    document.getElementById('resultTitle').style.color = '#ef4444';
                    document.getElementById('pointsLbl').textContent   = '+0 pts';
                    document.getElementById('pointsLbl').style.color   = '#ef4444';
                }

            } else if (status === 'completed') {
                show('scrFinal');
                if (d.my_team) {
                    document.getElementById('finalScore').textContent = `${d.my_team.total_score} pts`;
                    document.getElementById('finalRank').textContent  = `Final Rank #${d.my_team.current_rank}`;
                }
            }
        }

        poll();
        setInterval(poll, 1000);
    </script>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
</body>
</html>
