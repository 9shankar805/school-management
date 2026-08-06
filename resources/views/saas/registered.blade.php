<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Received — NepalSchoolHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body style="background:var(--bg-surface);font-family:'Inter',sans-serif">
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="card-modern p-10">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
                 style="background:#dcfce7">
                <i class="bi bi-check-circle-fill text-4xl text-emerald-500"></i>
            </div>
            <h1 class="font-bold text-2xl mb-2" style="color:var(--text-heading)">You're registered!</h1>
            <p class="text-sm mb-6" style="color:var(--text-muted)">
                Your school registration has been received. For the <strong>free plan</strong>, your account is
                active immediately. For paid plans, our team will review and activate within 24 hours.
            </p>
            <div class="alert-modern alert-info mb-6 text-left">
                <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                <p class="text-xs">Check your admin email inbox for login instructions. You can log in now if you're on the free plan.</p>
            </div>
            <a href="{{ route('login') }}" class="btn-primary w-full justify-center py-2.5">
                <i class="bi bi-box-arrow-in-right"></i> Go to Login
            </a>
            <a href="{{ route('saas.landing') }}" class="block mt-3 text-xs hover:underline" style="color:var(--text-muted)">
                ← Back to homepage
            </a>
        </div>
    </div>
</div>
</body>
</html>
