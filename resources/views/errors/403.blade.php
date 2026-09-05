<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access Forbidden | NepalSchoolHub</title>
    
    {{-- Favicons --}}
    <link rel="shortcut icon" href="/imgs/logo_icon_32.webp">
    <link rel="icon" type="image/png" sizes="32x32" href="/imgs/logo_icon_32.webp">
    <link rel="icon" type="image/png" sizes="192x192" href="/imgs/logo_icon_192.webp">
    <link rel="apple-touch-icon" href="/imgs/logo_icon_192.webp">

    {{-- Google Fonts & Bootstrap Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-blue: #3b52e3;
            --primary-blue-hover: #2a3fd0;
            --heading-color: #1e293b;
            --body-color: #64748b;
            --bg-color: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--heading-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }

        .error-wrapper {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Outer Card Container */
        .error-card {
            position: relative;
            width: 100%;
            background-color: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.05), 0 0 1px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .error-card-inner {
            position: relative;
            width: 100%;
            overflow: hidden;
            background-color: #ffffff;
        }

        /* Background Illustration Image Element */
        .main-bg-img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            pointer-events: none;
            user-select: none;
        }

        /* ── Overlay Components ── */

        /* Logo Header (Top Center) */
        .overlay-header {
            position: absolute;
            top: 4.5%;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            z-index: 10;
        }

        .logo-img {
            height: 44px;
            width: auto;
            object-fit: contain;
        }

        .logo-text-wrapper {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .logo-brand {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .logo-brand span {
            color: var(--primary-blue);
        }

        .logo-tagline {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--body-color);
            letter-spacing: 0.01em;
            margin-top: 1px;
        }

        /* Left Main Section (403 + Heading + Message + 3 Circles + 2 Buttons) */
        .overlay-left-section {
            position: absolute;
            top: 16%;
            left: 7%;
            width: 37%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 10;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 900;
            color: var(--primary-blue);
            line-height: 1;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--heading-color);
            margin-top: 0.35rem;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .error-description {
            font-size: 0.93rem;
            font-weight: 500;
            color: var(--body-color);
            margin-top: 0.75rem;
            line-height: 1.55;
        }

        /* 3 Circle Icon Items */
        .info-circles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            width: 100%;
            margin-top: 1.75rem;
        }

        .info-circle-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .circle-badge {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: #eef2ff;
            border: 1.5px solid #c7d2fe;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 6px rgba(59, 82, 227, 0.08);
            transition: transform 0.2s ease;
        }

        .circle-badge:hover {
            transform: scale(1.08);
        }

        .circle-text {
            font-size: 0.74rem;
            font-weight: 500;
            color: var(--body-color);
            line-height: 1.35;
            margin-top: 0.6rem;
        }

        /* Action Buttons */
        .action-buttons-group {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            margin-top: 1.75rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            flex: 1;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            white-space: nowrap;
        }

        .btn-homepage {
            background-color: var(--primary-blue);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(59, 82, 227, 0.35);
        }

        .btn-homepage:hover {
            background-color: var(--primary-blue-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(59, 82, 227, 0.45);
        }

        .btn-support {
            background-color: #ffffff;
            color: var(--primary-blue);
            border: 1.5px solid #a5b4fc;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .btn-support:hover {
            background-color: #f8fafc;
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 82, 227, 0.15);
        }

        /* Bottom Banner Text Overlay */
        .overlay-banner-text {
            position: absolute;
            top: 79.5%;
            left: 27.5%;
            width: 44%;
            text-align: left;
            z-index: 10;
        }

        .banner-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--heading-color);
            line-height: 1.3;
            letter-spacing: -0.01em;
        }

        .banner-subtitle-1 {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--body-color);
            margin-top: 3px;
        }

        .banner-subtitle-2 {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--body-color);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .heart-icon {
            color: #3b82f6;
            display: inline-block;
        }

        /* Contact Support Modal */
        .contact-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease;
            padding: 1rem;
        }

        .contact-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .contact-modal-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.75rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.92) translateY(10px);
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .contact-modal-overlay.active .contact-modal-card {
            transform: scale(1) translateY(0);
        }

        .contact-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.4rem;
        }

        .contact-modal-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .contact-modal-close {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .contact-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .contact-modal-sub {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 1.25rem;
        }

        .contact-options-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .contact-option-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .contact-option-item:hover {
            border-color: var(--primary-blue);
            background: #f8fafc;
            transform: translateX(4px);
        }

        .option-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .whatsapp-bg { background: #dcfce7; color: #16a34a; }
        .email-bg { background: #dbeafe; color: #2563eb; }
        .call-bg { background: #e0e7ff; color: #4f46e5; }

        .option-details {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .option-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #1e293b;
        }

        .option-desc {
            font-size: 0.78rem;
            color: #64748b;
        }

        .option-arrow {
            font-size: 1rem;
            color: #94a3b8;
        }

        /* Footer */
        .page-footer {
            margin-top: 1.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #94a3b8;
            text-align: center;
        }

        /* ── Responsive Styling ── */
        @media (max-width: 1120px) {
            .error-code { font-size: 4.2rem; }
            .error-title { font-size: 1.8rem; }
            .error-description { font-size: 0.85rem; }
            .logo-brand { font-size: 1.4rem; }
            .logo-tagline { font-size: 0.72rem; }
            .circle-badge { width: 38px; height: 38px; font-size: 1rem; }
            .circle-text { font-size: 0.68rem; }
            .btn-action { height: 38px; font-size: 0.78rem; gap: 6px; }
            .banner-title { font-size: 1rem; }
            .banner-subtitle-1, .banner-subtitle-2 { font-size: 0.78rem; }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .main-bg-img {
                display: none;
            }
            .error-card-inner {
                background-color: #ffffff;
                padding: 2rem 1.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .overlay-header {
                position: relative;
                top: auto;
                left: auto;
                transform: none;
                margin-bottom: 1.5rem;
            }
            .overlay-left-section {
                position: relative;
                top: auto;
                left: auto;
                width: 100%;
                margin-bottom: 1.5rem;
            }
            .action-buttons-group {
                flex-direction: column;
            }
            .btn-action {
                width: 100%;
            }
            .overlay-banner-text {
                position: relative;
                top: auto;
                left: auto;
                width: 100%;
                text-align: center;
                background-color: #eff6ff;
                padding: 1.25rem;
                border-radius: 14px;
            }
            .banner-subtitle-2 {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="error-wrapper">
        <div class="error-card">
            <div class="error-card-inner">
                
                {{-- Background Image Element with Cache-Busting --}}
                <img src="{{ asset('imgs/500.webp') }}?v={{ file_exists(public_path('imgs/500.webp')) ? filemtime(public_path('imgs/500.webp')) : time() }}" alt="Access Forbidden Illustration" class="main-bg-img">

                {{-- Logo Header --}}
                <a href="/" class="overlay-header" title="NepalSchoolHub Home">
                    <img src="/imgs/logo_icon.webp" alt="NepalSchoolHub Logo" class="logo-img" onerror="this.onerror=null; this.src='{{ asset('imgs/logo_icon.webp') }}';">
                    <div class="logo-text-wrapper">
                        <div class="logo-brand">Nepal<span>School</span>Hub</div>
                        <div class="logo-tagline">Smart School, Better Future</div>
                    </div>
                </a>

                {{-- Left Section (403 + Title + Message + 3 Circle Badges + 2 Action Buttons) --}}
                <div class="overlay-left-section">
                    <div class="error-code">403</div>
                    <h1 class="error-title">Access Forbidden</h1>
                    <p class="error-description">
                        You do not have permission to access this page.<br>
                        Please check your account permissions or log in.
                    </p>

                    {{-- 3 Circle Icons Grid --}}
                    <div class="info-circles-grid">
                        <div class="info-circle-item">
                            <div class="circle-badge">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <p class="circle-text">Permissions<br>restricted.</p>
                        </div>
                        <div class="info-circle-item">
                            <div class="circle-badge">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <p class="circle-text">Verify your<br>account role.</p>
                        </div>
                        <div class="info-circle-item" onclick="openContactModal()" style="cursor:pointer;">
                            <div class="circle-badge">
                                <i class="bi bi-headset"></i>
                            </div>
                            <p class="circle-text">If this is an error,<br>contact our<br>support.</p>
                        </div>
                    </div>

                    {{-- 2 Action Buttons --}}
                    <div class="action-buttons-group">
                        <a href="/" class="btn-action btn-homepage">
                            <i class="bi bi-house-door-fill"></i>
                            <span>Go to Homepage</span>
                        </a>
                        <button type="button" class="btn-action btn-support" onclick="openContactModal()">
                            <i class="bi bi-headset"></i>
                            <span>Contact Support</span>
                        </button>
                    </div>
                </div>

                {{-- Bottom Banner Box Text --}}
                <div class="overlay-banner-text">
                    <h2 class="banner-title">Role & Security Protected</h2>
                    <p class="banner-subtitle-1">Role-based permissions ensure school data security.</p>
                    <p class="banner-subtitle-2">
                        <span>Let's build a better future together!</span>
                        <span class="heart-icon">💙</span>
                    </p>
                </div>

            </div>
        </div>

        {{-- Page Footer --}}
        <footer class="page-footer">
            &copy; {{ date('Y') }} NepalSchoolHub. All rights reserved.
        </footer>
    </div>

    {{-- Contact Support Modal --}}
    <div id="contactModal" class="contact-modal-overlay" onclick="closeContactModal(event)">
        <div class="contact-modal-card" onclick="event.stopPropagation()">
            <div class="contact-modal-header">
                <div class="contact-modal-title">
                    <i class="bi bi-headset" style="color:var(--primary-blue)"></i>
                    <span>Contact Support</span>
                </div>
                <button type="button" class="contact-modal-close" onclick="closeContactModal()">&times;</button>
            </div>
            <p class="contact-modal-sub">Choose how you would like to connect with our support team:</p>

            <div class="contact-options-list">
                <!-- WhatsApp Chat -->
                <a href="https://wa.me/9779744911550?text=Hi%2C%20I%20need%20support%20with%20NepalSchoolHub" target="_blank" class="contact-option-item">
                    <div class="option-icon-box whatsapp-bg">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div class="option-details">
                        <span class="option-title">WhatsApp Chat</span>
                        <span class="option-desc">+977 9744911550 (Instant reply)</span>
                    </div>
                    <i class="bi bi-chevron-right option-arrow"></i>
                </a>

                <!-- Email Support -->
                <a href="mailto:support@nshlms.com" class="contact-option-item">
                    <div class="option-icon-box email-bg">
                        <i class="bi bi-envelope-at-fill"></i>
                    </div>
                    <div class="option-details">
                        <span class="option-title">Email Support</span>
                        <span class="option-desc">support@nshlms.com</span>
                    </div>
                    <i class="bi bi-chevron-right option-arrow"></i>
                </a>

                <!-- Phone Call -->
                <a href="tel:+9779744911550" class="contact-option-item">
                    <div class="option-icon-box call-bg">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="option-details">
                        <span class="option-title">Phone Call</span>
                        <span class="option-desc">+977 9744911550 (Sun–Fri, 9AM–6PM)</span>
                    </div>
                    <i class="bi bi-chevron-right option-arrow"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        function openContactModal() {
            document.getElementById('contactModal').classList.add('active');
        }
        function closeContactModal(e) {
            if (!e || e.target === document.getElementById('contactModal') || e.target.classList.contains('contact-modal-close')) {
                document.getElementById('contactModal').classList.remove('active');
            }
        }
    </script>

</body>
</html>
