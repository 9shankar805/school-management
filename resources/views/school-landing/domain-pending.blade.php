<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Domain setup in progress — {{ $school->name }}</title>
    @php
        $primary = $school->primary_color_resolved ?? '#1e4080';
        $logoUrl = $school->logo ? asset('storage/' . $school->logo) : null;
    @endphp
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;
            min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:linear-gradient(135deg,{{ $primary }}12,{{ $primary }}06);color:#0f172a;padding:24px}
        .card{max-width:560px;width:100%;text-align:center;background:#fff;border-radius:20px;
            box-shadow:0 20px 60px -20px rgba(15,23,42,.25);padding:40px 32px;border-top:6px solid {{ $primary }}}
        .logo{width:64px;height:64px;border-radius:16px;object-fit:cover;margin:0 auto 18px;display:block;background:#f1f5f9}
        h1{font-size:22px;margin-bottom:10px;color:#0f172a}
        p{font-size:15px;line-height:1.6;color:#475569;margin-bottom:8px}
        .pill{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;
            background:{{ $primary }}14;color:{{ $primary }};padding:8px 16px;border-radius:999px;margin:16px 0}
        .dot{width:8px;height:8px;border-radius:50%;background:{{ $primary }};animation:pulse 1.4s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}
        .brand{font-weight:700;color:{{ $primary }}}
        .footer{margin-top:22px;font-size:12px;color:#94a3b8}
    </style>
</head>
<body>
    <div class="card">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $school->name }}" class="logo">
        @endif
        <div class="pill"><span class="dot"></span> Domain setup in progress</div>
        <h1>{{ $school->name }}</h1>
        <p>This domain (<strong>{{ $school->custom_domain }}</strong>) is connected to <span class="brand">{{ $school->name }}</span> but is still being verified.</p>
        <p>The school is finishing DNS verification. Once the domain owner completes the DNS records and clicks <em>Verify</em>, this website will be published here automatically.</p>
        <div class="footer">Powered by Nepal School Hub · NSH LMS</div>
    </div>
</body>
</html>
