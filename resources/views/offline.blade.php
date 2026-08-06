<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — NepalSchoolHub</title>
    <link rel="icon" type="image/png" href="/favicon_io/favicon-32x32.png">
    <style>
        :root {
            --bg:      #0f172a;
            --surface: #1e293b;
            --border:  #334155;
            --accent:  #6366f1;
            --text:    #f1f5f9;
            --muted:   #94a3b8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,.4);
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: .75rem;
        }
        p {
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: var(--accent);
            color: #fff;
            padding: .75rem 2rem;
            border-radius: .5rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            transition: opacity .15s;
            text-decoration: none;
        }
        .btn:hover { opacity: .85; }
        .status {
            margin-top: 1.5rem;
            font-size: .85rem;
            color: var(--muted);
        }
        #online-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            margin-right: .4rem;
            vertical-align: middle;
        }
        .online #online-indicator { background: #22c55e; }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon">📡</span>
        <h1>You're Offline</h1>
        <p>
            NepalSchoolHub can't reach the server right now.<br>
            Check your internet connection and try again.
        </p>
        <button class="btn" onclick="window.location.reload()">Try Again</button>
        <div class="status" id="status-bar">
            <span id="online-indicator"></span>
            <span id="status-text">Checking connection…</span>
        </div>
    </div>

    <script>
        function updateStatus() {
            const bar  = document.querySelector('.card');
            const dot  = document.getElementById('online-indicator');
            const txt  = document.getElementById('status-text');

            if (navigator.onLine) {
                bar.classList.add('online');
                dot.style.background = '#22c55e';
                txt.textContent = 'Connection restored — click Try Again';
            } else {
                bar.classList.remove('online');
                dot.style.background = '#ef4444';
                txt.textContent = 'No internet connection detected';
            }
        }

        window.addEventListener('online',  updateStatus);
        window.addEventListener('offline', updateStatus);
        updateStatus();
    </script>
</body>
</html>
