<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Championship — {{ $session->quiz->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%; font-family: 'Outfit', sans-serif;
            background: radial-gradient(ellipse at 30% 10%, #1a1040 0%, #0c0c1a 60%, #020206 100%);
            color: #ffffff; overflow-x: hidden;
        }
        body { display: flex; flex-direction: column; }

        /* ── Top bar ── */
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 24px;
            background: rgba(0,0,0,0.4);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            backdrop-filter: blur(16px);
            flex-shrink: 0; gap: 12px;
        }
        .pin-tag {
            padding: 5px 14px; border-radius: 999px;
            background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.3);
            color: #fbbf24; font-size: 13px; font-weight: 800; letter-spacing: 1px;
        }
        .progress-tag {
            padding: 5px 14px; border-radius: 999px;
            background: rgba(99,102,241,0.18); border: 1px solid rgba(99,102,241,0.3);
            color: #a5b4fc; font-size: 13px; font-weight: 700;
        }
        .topbar-btn {
            padding: 7px 16px; border-radius: 10px;
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.7); font-family: 'Outfit', sans-serif;
            font-size: 13px; font-weight: 600; cursor: pointer; transition: background .2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .topbar-btn:hover { background: rgba(255,255,255,0.13); color: #fff; }
        .action-btn {
            padding: 9px 22px; border-radius: 12px; border: none;
            font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800;
            cursor: pointer; transition: all .2s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ── Timer circle ── */
        .timer-circle {
            width: 100px; height: 100px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; font-weight: 900;
            background: rgba(0,0,0,0.5);
            border: 6px solid #10b981; color: #10b981;
            box-shadow: 0 0 30px rgba(16,185,129,0.35);
            transition: border-color .3s, color .3s, box-shadow .3s;
            flex-shrink: 0;
        }
        .timer-circle.warn  { border-color:#f59e0b!important; color:#f59e0b!important; box-shadow:0 0 30px rgba(245,158,11,.5)!important; }
        .timer-circle.danger{ border-color:#ef4444!important; color:#ef4444!important; box-shadow:0 0 40px rgba(239,68,68,.8)!important; animation:pulse-timer .5s infinite alternate; }
        @keyframes pulse-timer { 0%{transform:scale(1);} 100%{transform:scale(1.08);} }

        /* ── Question box ── */
        .q-box {
            flex: 1;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 24px; padding: 28px 32px;
            backdrop-filter: blur(12px);
        }
        .q-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .q-type-label { font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #fbbf24; }
        .q-pts-label  { font-size: 13px; font-weight: 700; color: rgba(255,255,255,.45); }
        .q-text { font-size: 2rem; font-weight: 800; line-height: 1.35; color: #fff; }
        @media (max-width: 900px) { .q-text { font-size: 1.4rem; } }

        /* ── Option cards ── */
        .opts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 700px) { .opts-grid { grid-template-columns: 1fr; } }

        .opt-card {
            border-radius: 20px; padding: 20px 24px;
            display: flex; align-items: center; gap: 16px;
            font-size: 1.35rem; font-weight: 700; color: #fff;
            box-shadow: 0 8px 22px rgba(0,0,0,0.3);
            transition: transform .25s, opacity .3s, filter .3s, box-shadow .3s;
            position: relative; overflow: hidden;
        }
        .opt-card::before { content:''; position:absolute; inset:0; background:rgba(255,255,255,0); transition:background .2s; }
        .opt-a { background: linear-gradient(135deg,#dc2626,#991b1b); }
        .opt-b { background: linear-gradient(135deg,#2563eb,#1d4ed8); }
        .opt-c { background: linear-gradient(135deg,#d97706,#b45309); }
        .opt-d { background: linear-gradient(135deg,#059669,#047857); }

        .opt-icon {
            width: 46px; height: 46px; border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 1.1rem; flex-shrink: 0;
        }
        .opt-card.dimmed { opacity: 0.28; filter: grayscale(80%); }
        .opt-card.reveal-correct {
            border: 3px solid #22c55e;
            box-shadow: 0 0 40px rgba(34,197,94,.8);
            transform: scale(1.02);
        }
        .opt-count { margin-left: auto; flex-shrink: 0; font-size: 0.85rem; font-weight: 700; opacity: .85; display: none; }

        /* ── Turn badge ── */
        .turn-hero {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 24px; border-radius: 16px;
            background: rgba(234,179,8,.15); border: 1.5px solid rgba(234,179,8,.4);
            margin-bottom: 16px;
        }
        .turn-hero-text { font-size: 1.3rem; font-weight: 800; color: #fff; }

        /* ── Direct judge panel ── */
        .judge-panel {
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px; padding: 20px 24px;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
        }
        .judge-btn {
            padding: 12px 24px; border-radius: 12px; border: none;
            font-family: 'Outfit', sans-serif; font-size: .9rem; font-weight: 800;
            cursor: pointer; transition: transform .15s, box-shadow .15s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .judge-btn:hover { transform: translateY(-2px); }
        .judge-btn:active { transform: scale(0.96); }
        .judge-correct { background: linear-gradient(135deg,#10b981,#059669); color: #fff; box-shadow: 0 6px 18px rgba(16,185,129,.4); }
        .judge-pass    { background: linear-gradient(135deg,#f59e0b,#d97706); color: #1a1a2e; box-shadow: 0 6px 18px rgba(245,158,11,.4); }
        .judge-wrong   { background: linear-gradient(135deg,#ef4444,#dc2626); color: #fff; box-shadow: 0 6px 18px rgba(239,68,68,.4); }

        /* ── Explanation ── */
        .explain-box {
            background: rgba(99,102,241,.12); border: 1.5px solid rgba(99,102,241,.3);
            border-radius: 16px; padding: 14px 18px;
            display: flex; align-items: flex-start; gap: 10px;
            font-size: .9rem; color: rgba(255,255,255,.8);
        }

        /* ── Bottom chips bar ── */
        .chips-bar {
            padding: 12px 24px;
            background: rgba(0,0,0,.35); border-top: 1px solid rgba(255,255,255,.07);
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            flex-shrink: 0;
        }
        .team-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 999px;
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
            font-size: 12px; font-weight: 600; transition: all .2s;
        }
        .team-chip.answered   { background: rgba(16,185,129,.2); border-color: #10b981; color: #6ee7b7; }
        .team-chip.active-turn{ background: rgba(234,179,8,.2); border-color: #eab308; color: #fef08a; box-shadow: 0 0 12px rgba(234,179,8,.4); }
        .team-chip.warned     { border-color: #ef4444 !important; }

        /* ── Leaderboard ── */
        .lb-stage { padding: 32px 24px; }
        .podium-card {
            background: rgba(255,255,255,.07); border-radius: 22px; padding: 24px 20px;
            text-align: center; border: 1.5px solid rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
            transition: transform .3s;
        }
        .rank1 { background: linear-gradient(180deg,rgba(234,179,8,.2),rgba(255,255,255,.04)); border-color: #eab308; transform: scale(1.07); }
        .rank2 { border-color: #94a3b8; }
        .rank3 { border-color: #d97706; }
        .rank-emoji { font-size: 2.8rem; display: block; margin-bottom: 8px; }

        .lb-table { width: 100%; border-collapse: collapse; }
        .lb-table td { padding: 10px 12px; }
        .lb-table tr { border-bottom: 1px solid rgba(255,255,255,.06); }
        .lb-table tr:last-child { border-bottom: none; }

        .hidden { display: none !important; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <div class="pin-tag">PIN: {{ $session->pin_code }}</div>
            <div style="font-weight:800;font-size:1rem;color:#fff;" class="d-none d-md-block">{{ $session->quiz->title }}</div>
            <div class="progress-tag" id="progressTag">Q{{ $session->current_question_index }} / {{ $totalQuestions }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <button class="topbar-btn" onclick="toggleSound()" id="sndBtn"><i class="bi bi-volume-up-fill" id="sndIcon"></i></button>
            <button class="action-btn" id="actionBtn" onclick="doAction()"
                    style="background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;box-shadow:0 6px 18px rgba(239,68,68,.35);">
                <i class="bi bi-lock-fill"></i> Lock & Reveal
            </button>
            <a href="{{ route('live-quiz.host.lobby', $session->id) }}" class="topbar-btn">
                <i class="bi bi-door-closed"></i>
            </a>
        </div>
    </div>

    <!-- GAMEPLAY STAGE -->
    <div style="flex:1;display:flex;flex-direction:column;padding:20px 24px;gap:16px;overflow:auto;" id="gameStage">

        <!-- Turn hero (direct questions) -->
        <div class="turn-hero hidden" id="turnHero">
            <i class="bi bi-crown-fill" style="color:#fbbf24;font-size:1.6rem;flex-shrink:0;"></i>
            <div class="turn-hero-text" id="turnHeroText">DIRECT QUESTION FOR: GROUP 1</div>
            <div style="margin-left:auto;flex-shrink:0;" id="turnTimeBadge">
                <span style="padding:4px 12px;border-radius:999px;background:rgba(234,179,8,.2);color:#fef08a;font-size:12px;font-weight:700;">Main Round</span>
            </div>
        </div>

        <!-- Question row: timer + text -->
        <div style="display:flex;align-items:flex-start;gap:16px;">
            <div class="timer-circle" id="timerEl">—</div>
            <div class="q-box">
                <div class="q-meta">
                    <span class="q-type-label" id="qTypeLabel"><i class="bi bi-lightning-charge-fill me-1"></i> MCQ</span>
                    <span class="q-pts-label" id="qPtsLabel">100 pts</span>
                </div>
                <div class="q-text" id="qText">{{ $session->currentQuestion->question_text ?? 'Loading…' }}</div>
            </div>
        </div>

        <!-- MCQ options -->
        <div class="opts-grid" id="optsGrid">
            <div class="opt-card opt-a" id="cardA"><div class="opt-icon">A</div><div style="flex:1;" id="textA">Option A</div><div class="opt-count" id="countA"></div></div>
            <div class="opt-card opt-b" id="cardB"><div class="opt-icon">B</div><div style="flex:1;" id="textB">Option B</div><div class="opt-count" id="countB"></div></div>
            <div class="opt-card opt-c" id="cardC"><div class="opt-icon">C</div><div style="flex:1;" id="textC">Option C</div><div class="opt-count" id="countC"></div></div>
            <div class="opt-card opt-d" id="cardD"><div class="opt-icon">D</div><div style="flex:1;" id="textD">Option D</div><div class="opt-count" id="countD"></div></div>
        </div>

        <!-- Direct judge panel -->
        <div class="judge-panel hidden" id="judgePanel">
            <div>
                <div style="font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:6px;">Answer Reference</div>
                <div style="font-size:1.1rem;font-weight:800;color:#fff;" id="directAnsText">Hidden until judged</div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <button class="judge-btn judge-correct" onclick="judge(true)"><i class="bi bi-check-circle-fill"></i> Correct (<span id="judgePts">+100</span>)</button>
                <button class="judge-btn judge-pass"    onclick="doPass()"><i class="bi bi-arrow-repeat"></i> Pass (+50 pts)</button>
                <button class="judge-btn judge-wrong"   onclick="judge(false)"><i class="bi bi-x-circle-fill"></i> Wrong (+0)</button>
            </div>
        </div>

        <!-- Explanation -->
        <div class="explain-box hidden" id="explainBox">
            <i class="bi bi-lightbulb-fill" style="color:#fbbf24;font-size:1.1rem;flex-shrink:0;margin-top:2px;"></i>
            <div><strong style="color:#fff;">Explanation: </strong><span id="explainText"></span></div>
        </div>
    </div>

    <!-- LEADERBOARD STAGE -->
    <div class="lb-stage hidden" id="lbStage">
        <div style="text-align:center;margin-bottom:28px;">
            <h2 style="font-size:2rem;font-weight:900;color:#fbbf24;margin-bottom:4px;"><i class="bi bi-trophy-fill me-2"></i>Live Scoreboard</h2>
            <p style="color:rgba(255,255,255,.4);font-size:.9rem;" id="lbSubtitle">Championship Rankings</p>
        </div>

        <!-- Podium (top 3) -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:700px;margin:0 auto 24px;align-items:end;" id="podiumGrid"></div>

        <!-- Rest table -->
        <div style="max-width:600px;margin:0 auto;background:rgba(255,255,255,.06);border-radius:18px;overflow:hidden;" id="restWrap">
            <table class="lb-table" id="restTable">
                <tbody id="restBody"></tbody>
            </table>
        </div>
    </div>

    <!-- Bottom chips bar -->
    <div class="chips-bar">
        <span style="font-size:12px;font-weight:700;color:rgba(255,255,255,.35);margin-right:4px;"><i class="bi bi-people-fill me-1"></i>Teams:</span>
        <div id="chipsWrap" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
        <div style="margin-left:auto;font-size:12px;color:rgba(255,255,255,.3);" id="subCount">0/0 answered</div>
    </div>

    <script>
        const SID   = {{ $session->id }};
        const TOTAL = {{ $totalQuestions }};
        const CSRF  = document.querySelector('meta[name="csrf-token"]').content;

        let soundOn = true, curStatus = '{{ $session->status }}', curIdx = {{ $session->current_question_index }};
        let lastSec = null, confettiDone = false;

        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function tone(freq, type='sine', dur=0.1, vol=0.12) {
            if (!soundOn) return;
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const o = audioCtx.createOscillator(), g = audioCtx.createGain();
                o.type = type; o.frequency.setValueAtTime(freq, audioCtx.currentTime);
                g.gain.setValueAtTime(vol, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + dur);
                o.connect(g); g.connect(audioCtx.destination);
                o.start(); o.stop(audioCtx.currentTime + dur);
            } catch(e) {}
        }
        function tickNorm()   { tone(700, 'triangle', .06, .08); }
        function tickWarn()   { tone(1100, 'square', .08, .12); }
        function fanfare()    { [523,659,784,1047].forEach((f,i) => setTimeout(() => tone(f,'triangle',.3,.18), i*110)); }
        function passBell()   { tone(988,'triangle',.25,.22); setTimeout(() => tone(1319,'triangle',.35,.22),120); }
        function toggleSound(){ soundOn = !soundOn; document.getElementById('sndIcon').className = soundOn ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill'; }

        function esc(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        // ── State poller ──
        async function poll() {
            try {
                const res  = await fetch(`/quiz/host/api/state/${SID}`);
                if (!res.ok) return;
                render(await res.json());
            } catch(e) {}
        }

        function render(d) {
            curStatus = d.status; curIdx = d.current_question_index;
            document.getElementById('progressTag').textContent = `Q${d.current_question_index} / ${d.total_questions}`;
            document.getElementById('subCount').textContent    = `${d.submitted_count}/${d.teams_count} answered`;

            const gameEl = document.getElementById('gameStage');
            const lbEl   = document.getElementById('lbStage');
            const actBtn = document.getElementById('actionBtn');

            // ── Timer ──
            const timerEl = document.getElementById('timerEl');
            const rem     = d.seconds_remaining;
            if (d.status === 'question_active') {
                timerEl.textContent = rem;
                timerEl.className = 'timer-circle' + (rem <= 5 ? ' danger' : rem <= 10 ? ' warn' : '');
                if (lastSec !== rem) { rem <= 5 ? tickWarn() : tickNorm(); lastSec = rem; }
            } else {
                timerEl.textContent = '🔒';
                timerEl.className = 'timer-circle';
            }

            // ── Screens ──
            if (d.status === 'leaderboard' || d.status === 'completed') {
                gameEl.classList.add('hidden'); lbEl.classList.remove('hidden');
                renderLeaderboard(d.teams, d.status === 'completed');
                if (d.status === 'completed') {
                    actBtn.innerHTML = '<i class="bi bi-award-fill"></i> Championship Ended';
                    actBtn.disabled = true;
                    actBtn.style.background = 'rgba(255,255,255,.1)';
                    actBtn.style.boxShadow = 'none';
                } else {
                    actBtn.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i> Next Question';
                    actBtn.style.background = 'linear-gradient(135deg,#6366f1,#4f46e5)';
                    actBtn.style.boxShadow = '0 6px 18px rgba(99,102,241,.35)';
                    actBtn.disabled = false;
                }
            } else {
                gameEl.classList.remove('hidden'); lbEl.classList.add('hidden');

                if (d.current_question) {
                    const q       = d.current_question;
                    const isDirect = q.question_type === 'direct';

                    document.getElementById('qText').textContent = q.question_text;
                    document.getElementById('qPtsLabel').textContent = `${q.points} pts`;

                    // Turn hero visibility
                    document.getElementById('turnHero').classList.toggle('hidden', !isDirect);
                    document.getElementById('optsGrid').classList.toggle('hidden', isDirect);
                    document.getElementById('judgePanel').classList.toggle('hidden', !isDirect);

                    if (isDirect) {
                        document.getElementById('qTypeLabel').innerHTML = '<i class="bi bi-person-video3 me-1"></i> Direct Q';
                        const tt = d.active_turn_team;
                        document.getElementById('turnHeroText').textContent = d.is_passed
                            ? `🔁 PASSED TO: ${tt?.team_name?.toUpperCase() || '?'}`
                            : `👑 DIRECT QUESTION FOR: ${tt?.team_name?.toUpperCase() || '?'}`;

                        const tb = document.getElementById('turnTimeBadge');
                        tb.innerHTML = d.is_passed
                            ? `<span style="padding:4px 12px;border-radius:999px;background:rgba(6,182,212,.2);color:#67e8f9;font-size:12px;font-weight:700;">Passed Bonus (${q.pass_time_seconds||15}s)</span>`
                            : `<span style="padding:4px 12px;border-radius:999px;background:rgba(234,179,8,.2);color:#fef08a;font-size:12px;font-weight:700;">Main Round (${q.time_limit_seconds||60}s)</span>`;

                        document.getElementById('judgePts').textContent = `+${d.is_passed ? (q.pass_points||50) : (q.points||100)}`;

                        const isRevealed = d.status === 'question_result' || d.status === 'question_locked';
                        document.getElementById('directAnsText').textContent = isRevealed
                            ? (q.direct_answer || q.correct_option || 'Correct Answer')
                            : '•••••••••••• (Hidden until judged)';

                    } else {
                        document.getElementById('qTypeLabel').innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> MCQ';
                        const opts = q.options || [];
                        const map  = { A: 'textA', B: 'textB', C: 'textC', D: 'textD' };
                        opts.forEach(o => { const el = document.getElementById(map[o.key?.toUpperCase()]); if (el) el.textContent = o.text; });
                    }

                    // Reveal mode
                    const isReveal = d.status === 'question_result' || d.status === 'question_locked';
                    if (isReveal && !isDirect) {
                        const correct = (q.correct_option || '').toUpperCase();
                        ['A','B','C','D'].forEach(k => {
                            const card  = document.getElementById(`card${k}`);
                            const count = document.getElementById(`count${k}`);
                            const n     = d.option_breakdown?.[k] || 0;
                            count.textContent = `${n} team${n !== 1 ? 's' : ''}`;
                            count.style.display = 'block';
                            card.classList.toggle('reveal-correct', k === correct);
                            card.classList.toggle('dimmed', k !== correct);
                        });
                    } else if (!isDirect) {
                        ['A','B','C','D'].forEach(k => {
                            document.getElementById(`card${k}`).classList.remove('reveal-correct','dimmed');
                            document.getElementById(`count${k}`).style.display = 'none';
                        });
                    }

                    // Explanation
                    const explainBox = document.getElementById('explainBox');
                    if (isReveal && q.explanation) {
                        document.getElementById('explainText').textContent = q.explanation;
                        explainBox.classList.remove('hidden');
                    } else {
                        explainBox.classList.add('hidden');
                    }

                    // Action button
                    if (d.status === 'question_active') {
                        actBtn.innerHTML = '<i class="bi bi-lock-fill"></i> Lock & Reveal';
                        actBtn.style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
                        actBtn.style.boxShadow  = '0 6px 18px rgba(239,68,68,.35)';
                        actBtn.disabled = false;
                    } else if (isReveal) {
                        actBtn.innerHTML = '<i class="bi bi-trophy-fill"></i> View Leaderboard';
                        actBtn.style.background = 'linear-gradient(135deg,#f59e0b,#d97706)';
                        actBtn.style.boxShadow  = '0 6px 18px rgba(245,158,11,.35)';
                        actBtn.disabled = false;
                    }
                }
            }

            // ── Team chips ──
            let chipsHtml = '';
            d.teams.forEach(t => {
                const isActive  = d.active_turn_team?.id === t.id;
                const hasWarn   = t.tab_switch_count > 0;
                chipsHtml += `
                <div class="team-chip ${t.has_answered ? 'answered' : ''} ${isActive ? 'active-turn' : ''} ${hasWarn ? 'warned' : ''}">
                    <span style="width:8px;height:8px;border-radius:50%;background:${t.team_color};flex-shrink:0;"></span>
                    ${esc(t.team_name)}
                    ${isActive  ? '<i class="bi bi-mic-fill" style="color:#fbbf24;"></i>' : (t.has_answered ? '<i class="bi bi-check2-circle" style="color:#10b981;"></i>' : '<i class="bi bi-clock" style="color:rgba(255,255,255,.3);"></i>')}
                    ${hasWarn   ? `<span style="background:#ef4444;color:#fff;padding:1px 6px;border-radius:999px;font-size:10px;">⚠${t.tab_switch_count}</span>` : ''}
                </div>`;
            });
            document.getElementById('chipsWrap').innerHTML = chipsHtml;
        }

        // ── Leaderboard ──
        function renderLeaderboard(teams, isFinal) {
            document.getElementById('lbSubtitle').textContent = isFinal ? '🏆 Final Championship Rankings' : 'After This Round';
            if (isFinal && !confettiDone) {
                fanfare(); boom(); confettiDone = true;
            }

            const medals  = ['🥇','🥈','🥉'];
            const rankCls = ['rank1','rank2','rank3'];
            // Podium order: 2nd, 1st, 3rd (visual height)
            const top3    = teams.slice(0, 3);
            const order   = [1, 0, 2];
            let podHtml   = '';
            order.forEach(i => {
                const t = top3[i];
                if (!t) { podHtml += '<div></div>'; return; }
                podHtml += `
                <div class="podium-card ${rankCls[i]}">
                    <span class="rank-emoji">${medals[i]}</span>
                    <h3 style="font-weight:900;color:#fff;font-size:1.1rem;margin-bottom:4px;">${esc(t.team_name)}</h3>
                    <p style="font-size:.78rem;color:rgba(255,255,255,.4);margin-bottom:10px;">${esc(t.captain_name)}</p>
                    <div style="font-size:1.8rem;font-weight:900;color:#fbbf24;">${t.total_score}<span style="font-size:.85rem;color:rgba(255,255,255,.4);font-weight:600;"> pts</span></div>
                </div>`;
            });
            document.getElementById('podiumGrid').innerHTML = podHtml;

            const rest = teams.slice(3);
            let restHtml = '';
            rest.forEach((t, i) => {
                restHtml += `
                <tr>
                    <td style="color:rgba(255,255,255,.4);font-weight:700;width:36px;">#${i+4}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:${t.team_color};flex-shrink:0;"></span>
                            <span style="font-weight:700;color:#fff;">${esc(t.team_name)}</span>
                            <span style="font-size:.75rem;color:rgba(255,255,255,.35);">(${esc(t.captain_name)})</span>
                        </div>
                    </td>
                    <td style="text-align:right;font-weight:900;color:#fbbf24;font-size:1.05rem;">${t.total_score} pts</td>
                </tr>`;
            });
            document.getElementById('restBody').innerHTML = restHtml;
            document.getElementById('restWrap').style.display = rest.length ? 'block' : 'none';
        }

        // ── Host actions ──
        async function doAction() {
            const btn = document.getElementById('actionBtn');
            btn.disabled = true;
            try {
                if (curStatus === 'question_active') {
                    await fetch(`/quiz/host/api/reveal/${SID}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF} });
                    fanfare();
                } else if (curStatus === 'question_result' || curStatus === 'question_locked') {
                    await fetch(`/quiz/host/api/leaderboard/${SID}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF} });
                } else if (curStatus === 'leaderboard') {
                    if (curIdx >= TOTAL) {
                        await fetch(`/quiz/host/api/finish/${SID}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF} });
                    } else {
                        await fetch(`/quiz/host/api/next/${SID}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF} });
                    }
                }
            } catch(e) {}
            btn.disabled = false;
            poll();
        }

        async function judge(correct) {
            await fetch(`/quiz/host/api/judge/${SID}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify({is_correct: correct}) });
            if (correct) fanfare();
            poll();
        }

        async function doPass() {
            await fetch(`/quiz/host/api/pass/${SID}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF} });
            passBell(); poll();
        }

        function boom() {
            try {
                confetti({ particleCount: 160, spread: 110, origin: { y: 0.6 } });
                setTimeout(() => { confetti({ particleCount: 80, angle: 60, spread: 70, origin: { x: 0 } }); }, 350);
                setTimeout(() => { confetti({ particleCount: 80, angle: 120, spread: 70, origin: { x: 1 } }); }, 500);
            } catch(e) {}
        }

        poll();
        setInterval(poll, 1200);
    </script>
</body>
</html>
