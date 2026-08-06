<!doctype html>
<html lang="en" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ── Primary SEO ── --}}
    <title>NepalSchoolHub — #1 School Management Software in Nepal | Free to Start</title>
    <meta name="description" content="NepalSchoolHub is Nepal's leading school management software. Manage students, teachers, attendance, fees, exams, hostel & transport from one dashboard. Free plan available. No credit card needed.">
    <meta name="keywords" content="school management system Nepal, school ERP Nepal, student management software Nepal, fee management software Nepal, school software Kathmandu, attendance system Nepal, exam management Nepal, hostel management software, school management SaaS Nepal, NepalSchoolHub, विद्यालय व्यवस्थापन सफ्टवेयर नेपाल">
    <meta name="author" content="NepalSchoolHub">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="rating" content="general">
    <meta name="revisit-after" content="7 days">
    <meta name="language" content="English">
    <meta name="geo.region" content="NP">
    <meta name="geo.placename" content="Kathmandu, Nepal">
    <meta name="geo.position" content="27.7172;85.3240">
    <meta name="ICBM" content="27.7172, 85.3240">

    {{-- ── Canonical & Alternate ── --}}
    <link rel="canonical" href="{{ url('/') }}">

    {{-- ── Open Graph (Facebook / LinkedIn) ── --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="NepalSchoolHub">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="NepalSchoolHub — #1 School Management Software in Nepal">
    <meta property="og:description" content="Nepal's most complete school management platform. Students, teachers, fees, exams, hostel & transport — all in one dashboard. Free plan available.">
    <meta property="og:image" content="{{ asset('imgs/nepalwithourschool.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="NepalSchoolHub school management dashboard screenshot">
    <meta property="og:locale" content="en_US">

    {{-- ── Twitter / X Card ── --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@NepalSchoolHub">
    <meta name="twitter:creator" content="@NepalSchoolHub">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="NepalSchoolHub — School Management Software Nepal">
    <meta name="twitter:description" content="Nepal's most complete school ERP. Students, fees, exams, attendance & more — free to start.">
    <meta name="twitter:image" content="{{ asset('imgs/nepalwithourschool.png') }}">
    <meta name="twitter:image:alt" content="NepalSchoolHub dashboard">

    {{-- ── PWA / Mobile ── --}}
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="NepalSchoolHub">

    {{-- ── Favicons — NepalSchoolHub logo ── --}}
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('imgs/logo_icon_512.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('imgs/logo_icon_192.png') }}">
    <link rel="icon" type="image/png" sizes="64x64"   href="{{ asset('imgs/logo_icon_64.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"   href="{{ asset('imgs/logo_icon_32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180"      href="{{ asset('imgs/logo_icon_192.png') }}">

    {{-- ── Sitemap reference ── --}}
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">

    {{-- ── JSON-LD: SoftwareApplication ── --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "NepalSchoolHub",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('imgs/logo_icon.png') }}",
      "operatingSystem": "Web, Android, iOS",
      "applicationCategory": "EducationalApplication",
      "applicationSubCategory": "School Management Software",
      "offers": [
        {
          "@type": "Offer",
          "name": "Free Plan",
          "price": "0",
          "priceCurrency": "NPR",
          "description": "Free school management plan for small schools"
        },
        {
          "@type": "Offer",
          "name": "Yearly Premium",
          "price": "50000",
          "priceCurrency": "NPR",
          "description": "Full-featured yearly plan with AI tools, 9999 students"
        }
      ],
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "5.0",
        "reviewCount": "6",
        "bestRating": "5",
        "worstRating": "1"
      },
      "description": "Nepal's #1 school management SaaS platform. Manage students, teachers, attendance, fees, exams, hostel, transport, library and HR from a single dashboard. Built for Nepali schools.",
      "screenshot": "{{ asset('imgs/nepalwithourschool.png') }}",
      "featureList": "Student Management, Teacher Management, Fee Collection, Attendance Tracking, Exam Management, Hostel Management, Transport Tracking, Library, AI Features, WhatsApp Alerts",
      "inLanguage": "en",
      "availableOnDevice": "Web Browser, Mobile",
      "countriesSupported": "NP",
      "softwareVersion": "2.0"
    }
    </script>

    {{-- ── JSON-LD: Organization ── --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "NepalSchoolHub",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('imgs/logo_icon.png') }}",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+977-9767585122",
        "contactType": "customer support",
        "contactOption": "TollFree",
        "areaServed": "NP",
        "availableLanguage": ["English", "Nepali"]
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "New Baneshwor",
        "addressLocality": "Kathmandu",
        "addressRegion": "Bagmati Province",
        "postalCode": "44600",
        "addressCountry": "NP"
      },
      "sameAs": []
    }
    </script>

    {{-- ── JSON-LD: FAQPage (30 keyword-rich Q&As for Google rich results) ── --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {"@type":"Question","name":"What is NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"NepalSchoolHub is Nepal's #1 cloud-based school management software — a complete School ERP for Nepali schools covering students, teachers, fees, attendance, exams, hostel, transport, HR and more."}},
        {"@type":"Question","name":"Is NepalSchoolHub free?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub offers a permanently free plan with no credit card required. Paid yearly plans start at NPR 50,000 and include all 24 modules, AI features and priority support."}},
        {"@type":"Question","name":"What is the best school management software in Nepal?","acceptedAnswer":{"@type":"Answer","text":"NepalSchoolHub is the most feature-complete school management SaaS in Nepal with 24 integrated modules, AI tools, WhatsApp alerts, QR/RFID attendance, online exams and a parent portal."}},
        {"@type":"Question","name":"How much does school management software cost in Nepal?","acceptedAnswer":{"@type":"Answer","text":"NepalSchoolHub's yearly premium plan costs NPR 50,000/year and includes all 24 modules, AI features, unlimited students and WhatsApp alerts — the best value school ERP in Nepal."}},
        {"@type":"Question","name":"Does NepalSchoolHub support fee management?","acceptedAnswer":{"@type":"Answer","text":"Yes. The fee management module handles invoice generation, payment tracking, outstanding fees, discounts, WhatsApp payment reminders and Nepali fiscal year (Shrawan–Ashad) billing cycles."}},
        {"@type":"Question","name":"Can parents receive WhatsApp attendance alerts?","acceptedAnswer":{"@type":"Answer","text":"Yes. Every time a student is marked absent, parents receive an instant WhatsApp notification. Parents can also log into the parent portal to view attendance, results and fee status."}},
        {"@type":"Question","name":"Does NepalSchoolHub have an online exam system?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub has a built-in online examination system with MCQ, short-answer and long-answer support, time limits, auto-grading and detailed analytics."}},
        {"@type":"Question","name":"Can NepalSchoolHub generate NEB-style report cards?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub automatically generates PDF report cards, mark sheets and certificates supporting GPA, percentage and letter grades aligned with NEB, SLC/SEE and +2 standards."}},
        {"@type":"Question","name":"Does NepalSchoolHub support QR code and RFID attendance?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub supports manual, QR code, RFID card and mobile app attendance. Absent students trigger automatic WhatsApp notifications to parents."}},
        {"@type":"Question","name":"Does NepalSchoolHub have AI features?","acceptedAnswer":{"@type":"Answer","text":"Yes. AI features include automated performance insights, smart attendance pattern detection, AI lesson plan suggestions, predictive fee defaulter alerts and AI question paper generation."}},
        {"@type":"Question","name":"Does NepalSchoolHub support hostel management?","acceptedAnswer":{"@type":"Answer","text":"Yes. The hostel module manages room allocation, bed assignment, hostel fees, check-in/check-out, hostel attendance and expense tracking."}},
        {"@type":"Question","name":"Does NepalSchoolHub have transport and GPS tracking?","acceptedAnswer":{"@type":"Answer","text":"Yes. The transport module includes route planning, vehicle assignment, driver management and real-time GPS tracking for parents via WhatsApp notifications."}},
        {"@type":"Question","name":"How do I register my school on NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"Go to nepalschoolhub.techprocod.com.np, click Get Started Free, fill in your school details and your school is live instantly. Setup takes under 5 minutes. WhatsApp support: +977 9767585122."}},
        {"@type":"Question","name":"Is NepalSchoolHub cloud-based?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub is 100% cloud-based. No installation needed — just a browser and internet connection. Data is stored securely with daily encrypted backups."}},
        {"@type":"Question","name":"Is student data safe on NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub uses 256-bit SSL encryption, role-based access control, daily backups and full data isolation between schools. School data is never shared or sold."}},
        {"@type":"Question","name":"What makes NepalSchoolHub better than other school software in Nepal?","acceptedAnswer":{"@type":"Answer","text":"NepalSchoolHub is the only Nepali school software with all 24 modules in one platform, a genuine free plan, AI features, WhatsApp alerts, GPS transport tracking and support for Nepali calendar, NEB curriculum and NPR fees."}},
        {"@type":"Question","name":"How do I contact NepalSchoolHub support?","acceptedAnswer":{"@type":"Answer","text":"WhatsApp: +977 9767585122 (instant reply). Email: support@nepalschoolhub.com. Or use the contact form on nepalschoolhub.techprocod.com.np. Available Sunday–Friday, 9 AM–6 PM NST."}},
        {"@type":"Question","name":"Does NepalSchoolHub work on mobile phones?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub is fully mobile-responsive and works on any smartphone browser. Dedicated Android and iOS apps are available for teachers, parents, students and drivers."}},
        {"@type":"Question","name":"Can I import student data into NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"Yes. Bulk import of students, teachers and fee records from Excel/CSV is supported. Download our templates, fill in your data and upload in one click."}},
        {"@type":"Question","name":"Does NepalSchoolHub have payroll and HR management?","acceptedAnswer":{"@type":"Answer","text":"Yes. The HR and payroll module handles salary calculation, allowances, PF/CIT deductions, leave management, salary slips and payroll reports aligned with Nepal's Labour Act."}},
        {"@type":"Question","name":"Is there a free trial for NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"Yes. All paid plans include a 14-day free trial with no credit card required. You can start with the free plan and upgrade anytime."}},
        {"@type":"Question","name":"Does NepalSchoolHub support Nepali calendar (Bikram Sambat)?","acceptedAnswer":{"@type":"Answer","text":"Yes. NepalSchoolHub supports the Nepali Bikram Sambat (BS) calendar, Nepali fiscal year billing and Nepali academic session structures."}},
        {"@type":"Question","name":"Who built NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"NepalSchoolHub is built by TechProCod, a Nepali software company based in Kathmandu, Nepal, designed specifically for Nepali schools."}},
        {"@type":"Question","name":"What is the official website of NepalSchoolHub?","acceptedAnswer":{"@type":"Answer","text":"The official website is nepalschoolhub.techprocod.com.np. This is the top result when you search NepalSchoolHub, school management software Nepal or school ERP Nepal on Google."}}
      ]
    }
    </script>

    {{-- ── Preconnect for speed (also a ranking signal) ── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    {{-- ── Fonts ── --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f8fafc;color:#0f172a;-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}
        img{max-width:100%;display:block}

        /* Nav */
        .topnav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid #e2e8f0;padding:.75rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem}
        .nav-logo{display:flex;align-items:center;gap:.6rem}
        .nav-logo img{width:38px;height:38px;border-radius:.6rem;object-fit:cover}
        .nav-logo span{font-weight:800;font-size:1rem;color:#1e1b4b}
        .nav-links{display:flex;align-items:center;gap:1.5rem;font-size:.875rem;font-weight:500;color:#475569}
        .nav-links a:hover{color:#6366f1}
        .btn-outline{padding:.5rem 1.25rem;border:1.5px solid #e2e8f0;border-radius:.75rem;font-size:.8rem;font-weight:600;color:#334155;transition:.2s}
        .btn-outline:hover{border-color:#6366f1;color:#6366f1}
        .btn-primary{padding:.5rem 1.25rem;background:#6366f1;color:#fff;border-radius:.75rem;font-size:.8rem;font-weight:600;transition:.2s}
        .btn-primary:hover{background:#4f46e5;transform:translateY(-1px)}

        /* Hero */
        .hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 35%,#312e81 65%,#4338ca 100%);color:#fff;padding:5rem 1.5rem 0;overflow:hidden;position:relative}
        .hero-blob{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none}
        .hero-content{max-width:900px;margin:0 auto;text-align:center;position:relative;z-index:2}
        .hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);padding:.4rem 1rem;border-radius:99px;font-size:.75rem;font-weight:600;margin-bottom:1.5rem}
        .hero h1{font-size:clamp(2rem,5vw,3.75rem);font-weight:900;line-height:1.1;margin-bottom:1.5rem}
        .gradient-text{background:linear-gradient(135deg,#a5b4fc,#818cf8,#c7d2fe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .hero p{font-size:1.1rem;color:rgba(255,255,255,.75);max-width:600px;margin:0 auto 2.5rem;line-height:1.7}
        .hero-ctas{display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center;margin-bottom:1.5rem}
        .cta-white{display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2rem;background:#fff;color:#4338ca;border-radius:.875rem;font-weight:700;font-size:.9rem;transition:.2s}
        .cta-white:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2)}
        .cta-ghost{display:inline-flex;align-items:center;gap:.5rem;padding:.9rem 2rem;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:.875rem;font-weight:600;font-size:.9rem;transition:.2s}
        .cta-ghost:hover{background:rgba(255,255,255,.18)}

        /* Hero image */
        .hero-img-wrap{margin-top:3rem;position:relative;max-width:1000px;margin-left:auto;margin-right:auto}
        .hero-img-wrap img{width:100%;border-radius:1.25rem 1.25rem 0 0;box-shadow:0 -8px 60px rgba(99,102,241,.4);border:1px solid rgba(255,255,255,.1)}

        /* Trust bar */
        .trust-bar{background:#fff;border-bottom:1px solid #e2e8f0;padding:1.25rem 1.5rem}
        .trust-inner{max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:1.5rem 2.5rem;font-size:.8rem;font-weight:600;color:#64748b}
        .trust-inner span{display:flex;align-items:center;gap:.4rem}
        .trust-inner i{color:#6366f1;font-size:.85rem}

        /* Section */
        .section{padding:5rem 1.5rem}
        .section-inner{max-width:1100px;margin:0 auto}
        .section-label{display:inline-flex;align-items:center;gap:.4rem;background:#eef2ff;color:#4f46e5;font-size:.7rem;font-weight:700;padding:.3rem .8rem;border-radius:99px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem}
        .section-title{font-size:clamp(1.6rem,3vw,2.5rem);font-weight:800;color:#0f172a;line-height:1.2;margin-bottom:.75rem}
        .section-sub{font-size:1rem;color:#64748b;max-width:560px;line-height:1.6}

        /* Feature cards */
        .feat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;margin-top:3rem}
        .feat-card{background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem;transition:.25s;cursor:pointer;position:relative}
        .feat-card:hover{box-shadow:0 12px 40px rgba(99,102,241,.12);transform:translateY(-4px);border-color:#c7d2fe}
        .feat-card:hover .feat-arrow{opacity:1;transform:translateX(0)}
        .feat-icon{width:2.75rem;height:2.75rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:1rem}
        .feat-card h3{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:.4rem}
        .feat-card p{font-size:.825rem;color:#64748b;line-height:1.6;margin-bottom:0}
        .feat-arrow{position:absolute;bottom:1.25rem;right:1.25rem;width:1.75rem;height:1.75rem;border-radius:50%;background:#eef2ff;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:.75rem;opacity:0;transform:translateX(-4px);transition:.2s}
        .feat-learn{font-size:.75rem;color:#6366f1;font-weight:600;margin-top:.6rem;display:inline-flex;align-items:center;gap:.25rem}

        /* Feature Detail Modal */
        .feat-modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem}
        .feat-modal-overlay.open{display:flex}
        .feat-modal{background:#fff;border-radius:1.5rem;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 32px 80px rgba(0,0,0,.18);position:relative}
        .feat-modal-header{padding:1.75rem 1.75rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:flex-start;gap:1rem;position:sticky;top:0;background:#fff;border-radius:1.5rem 1.5rem 0 0;z-index:2}
        .feat-modal-icon{width:3.25rem;height:3.25rem;border-radius:.875rem;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
        .feat-modal-title{font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:.2rem}
        .feat-modal-subtitle{font-size:.825rem;color:#64748b;line-height:1.5}
        .feat-modal-body{padding:1.5rem 1.75rem 1.75rem}
        .feat-modal-close{position:absolute;top:1.25rem;right:1.25rem;width:2rem;height:2rem;border-radius:50%;background:#f1f5f9;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:.875rem;transition:.15s}
        .feat-modal-close:hover{background:#e2e8f0;color:#0f172a}
        .feat-detail-section{margin-bottom:1.5rem}
        .feat-detail-section:last-child{margin-bottom:0}
        .feat-detail-label{font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:.75rem}
        .feat-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
        @media(max-width:480px){.feat-detail-grid{grid-template-columns:1fr}}
        .feat-detail-item{display:flex;align-items:flex-start;gap:.6rem;padding:.6rem .75rem;background:#f8fafc;border-radius:.625rem;font-size:.8rem;color:#374151;line-height:1.4}
        .feat-detail-item i{color:#6366f1;flex-shrink:0;margin-top:.1rem;font-size:.8rem}
        .feat-modal-cta{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.8rem 1.5rem;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:.875rem;font-weight:700;font-size:.875rem;text-decoration:none;transition:.2s;margin-top:1.5rem}
        .feat-modal-cta:hover{background:linear-gradient(135deg,#4338ca,#4f46e5);transform:translateY(-1px);color:#fff}

        /* Showcase (image + text) */
        .showcase{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center}
        .showcase.reverse{direction:rtl}
        .showcase.reverse>*{direction:ltr}
        .showcase-img{border-radius:1.25rem;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12);border:1px solid #e2e8f0}
        .showcase-img img{width:100%;display:block}
        .showcase-points{list-style:none;margin-top:1.5rem;display:flex;flex-direction:column;gap:.75rem}
        .showcase-points li{display:flex;align-items:flex-start;gap:.75rem;font-size:.875rem;color:#334155;line-height:1.5}
        .showcase-points li i{color:#6366f1;font-size:1rem;margin-top:.1rem;flex-shrink:0}

        /* Kathmandu banner */
        .city-banner{position:relative;overflow:hidden;border-radius:1.5rem}
        .city-banner img{width:100%;height:340px;object-fit:cover;display:block}
        .city-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(30,27,75,.85),rgba(67,56,202,.7));display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:2rem;color:#fff}
        .city-overlay h2{font-size:clamp(1.5rem,3vw,2.25rem);font-weight:800;margin-bottom:.75rem}
        .city-overlay p{font-size:.95rem;color:rgba(255,255,255,.8);max-width:500px;margin:0 auto 1.5rem}

        /* Modules */
        .modules-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.6rem}
        @media(min-width:640px){.modules-grid{grid-template-columns:repeat(3,1fr)}}
        @media(min-width:1024px){.modules-grid{grid-template-columns:repeat(4,1fr)}}
        .mod-chip{display:flex;align-items:center;gap:.5rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.5rem .75rem;font-size:.75rem;font-weight:600;color:#334155;transition:.2s;cursor:pointer;user-select:none}
        .mod-chip:hover{border-color:#a5b4fc;background:#eef2ff;color:#4f46e5;transform:translateY(-1px);box-shadow:0 4px 12px rgba(99,102,241,.1)}
        .mod-num{width:1.4rem;height:1.4rem;border-radius:.35rem;background:#eef2ff;color:#6366f1;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}

        /* Pricing */
        .price-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.25rem;margin-top:3rem}
        .price-card{background:#fff;border:2px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem;display:flex;flex-direction:column;transition:.2s}
        .price-card.popular{border-color:#6366f1;box-shadow:0 8px 40px rgba(99,102,241,.18)}
        .price-amount{font-size:2rem;font-weight:900;color:#0f172a;margin:.5rem 0}
        .price-list{list-style:none;margin:1.25rem 0 1.5rem;display:flex;flex-direction:column;gap:.6rem;flex:1}
        .price-list li{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:#334155}
        .price-list .yes{color:#22c55e}
        .price-list .no{color:#cbd5e1}
        .price-btn{display:block;text-align:center;padding:.7rem;border-radius:.75rem;font-weight:700;font-size:.85rem;transition:.2s}
        .price-btn.filled{background:#6366f1;color:#fff}
        .price-btn.filled:hover{background:#4f46e5}
        .price-btn.muted{background:#f1f5f9;color:#334155}
        .price-btn.muted:hover{background:#e2e8f0}

        /* CTA bottom */
        .cta-band{background:linear-gradient(135deg,#0f172a,#1e1b4b,#312e81);color:#fff;padding:5rem 1.5rem;text-align:center}

        /* Footer */
        footer{background:#0f172a;color:#94a3b8;padding:3rem 1.5rem 2rem}
        .footer-inner{max-width:1100px;margin:0 auto}
        .footer-logo{display:flex;align-items:center;gap:.6rem;margin-bottom:1rem}
        .footer-logo img{width:32px;height:32px;border-radius:.5rem;object-fit:cover}
        .footer-logo span{font-weight:800;font-size:.95rem;color:#fff}
        .footer-links{display:flex;flex-wrap:wrap;gap:1rem;font-size:.8rem;margin-top:1.5rem}
        .footer-links a:hover{color:#a5b4fc}

        /* Mobile drawer */
        .mobile-toggle-btn { display: none; background: transparent; border: none; font-size: 1.5rem; color: #1e1b4b; cursor: pointer; padding: .25rem; }
        .mobile-drawer { display: none; position: fixed; top: 61px; left: 0; right: 0; background: rgba(255,255,255,.98); backdrop-filter: blur(16px); border-bottom: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,.08); z-index: 99; padding: 1.25rem 1.5rem; flex-direction: column; gap: 1rem; }
        .mobile-drawer.show { display: flex; }
        .mobile-drawer a { font-size: .95rem; font-weight: 600; color: #334155; padding: .4rem 0; border-bottom: 1px solid #f1f5f9; }
        .mobile-drawer a:last-child { border-bottom: none; }
        .mobile-drawer .btn-primary, .mobile-drawer .btn-outline { text-align: center; margin-top: .25rem; }

        /* Mobile CTA buttons — full width, proper tap target size */
        .mobile-drawer .btn-outline {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            padding: .875rem 1.5rem;
            border: 2px solid #6366f1;
            border-radius: .875rem;
            font-size: 1rem;
            font-weight: 700;
            color: #4f46e5;
            background: transparent;
            margin-top: .5rem;
            transition: background .2s, color .2s;
        }
        .mobile-drawer .btn-outline:hover { background: #eef2ff; color: #4338ca; }
        .mobile-drawer .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            padding: .9rem 1.5rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            border-radius: .875rem;
            font-size: 1rem;
            font-weight: 700;
            margin-top: .5rem;
            box-shadow: 0 4px 16px rgba(99,102,241,.35);
            transition: transform .2s, box-shadow .2s;
        }
        .mobile-drawer .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,.45); }

        /* Responsive */
        @media(max-width:991px){
            .hero h1{font-size:clamp(1.5rem, 4.5vw, 3.2rem)}
        }
        @media(max-width:767px){
            html, body { overflow-x: hidden; }
            .topnav { padding: .75rem 1rem; }
            .nav-links, .nav-actions-desktop { display: none !important; }
            .mobile-toggle-btn { display: block; }
            
            .hero { padding: 3.5rem 1rem 0; }
            .hero-badge { font-size: .7rem; padding: .35rem .75rem; margin-bottom: 1rem; }
            .hero h1 { font-size: clamp(1.5rem, 6vw, 2.5rem); letter-spacing: -0.02em; white-space: normal; line-height: 1.2; margin-bottom: 1rem; }
            .hero p { font-size: .95rem; line-height: 1.6; margin-bottom: 1.75rem; }
            .hero-ctas { flex-direction: column; width: 100%; max-width: 340px; margin-left: auto; margin-right: auto; }
            .cta-white, .cta-ghost { width: 100%; justify-content: center; padding: .85rem 1.25rem; }

            .trust-bar { padding: 1rem; }
            .trust-inner { gap: .75rem 1.25rem; font-size: .75rem; justify-content: center; }

            .section { padding: 3rem 1rem; }
            .section-title { font-size: 1.5rem; }
            .section-sub { font-size: .875rem; }

            .feat-grid { grid-template-columns: 1fr; gap: 1rem; margin-top: 2rem; }
            .feat-card { padding: 1.25rem; }

            .showcase { grid-template-columns: 1fr; gap: 2rem; margin-bottom: 3.5rem !important; }
            .showcase.reverse { direction: ltr; }
            .showcase-points { gap: .6rem; }
            .showcase-points li { font-size: .825rem; }

            .city-banner img { height: 260px; }
            .city-overlay { padding: 1.25rem; }
            .city-overlay h2 { font-size: 1.35rem; }
            .city-overlay p { font-size: .85rem; margin-bottom: 1rem; }

            .modules-grid { grid-template-columns: repeat(2, 1fr); gap: .5rem; }
            .mod-chip { font-size: .7rem; padding: .45rem .6rem; }

            .price-grid { grid-template-columns: 1fr; max-width: 360px; margin-left: auto; margin-right: auto; margin-top: 2rem; }
            .price-card { padding: 1.35rem; }

            .cta-band { padding: 3.5rem 1rem; }
            .cta-band h2 { font-size: 1.5rem; }

            .stat-divider { display: none !important; }

            footer { padding: 2.5rem 1rem 1.5rem; }
        }
        @media(max-width:400px){
            .hero h1 { font-size: 1.35rem; white-space: normal; }
        }
    </style>
</head>
<body>
<a href="#main-content" style="position:absolute;left:-9999px;top:0;z-index:9999;background:#6366f1;color:#fff;padding:.5rem 1rem;border-radius:0 0 .5rem 0;font-size:.8rem;font-weight:700;" onfocus="this.style.left='0'" onblur="this.style.left='-9999px'">Skip to main content</a>

{{-- ══ TOP NAV ══════════════════════════════════════════════════════════ --}}
<nav class="topnav" role="navigation" aria-label="Main navigation">
    <a href="{{ route('saas.landing') }}" class="nav-logo">
        <img src="{{ asset('imgs/logo_icon.png') }}" alt="NepalSchoolHub Logo">
        <span>NepalSchoolHub</span>
    </a>
    <div class="nav-links">
        <a href="#features">Features</a>
        <a href="#showcase">How it works</a>
        <a href="#modules">Modules</a>
        <a href="#pricing">Pricing</a>
        <a href="#reviews">Reviews</a>
        <a href="#faq">FAQ</a>
        <a href="#contact">Contact Us</a>
    </div>
    <div class="nav-actions-desktop" style="display:flex;gap:.5rem;align-items:center">
        <a href="{{ route('login') }}" class="btn-outline">Sign In</a>
        <a href="{{ route('saas.register') }}" class="btn-primary">Get Started Free</a>
    </div>
    <button class="mobile-toggle-btn" onclick="document.getElementById('mobileDrawer').classList.toggle('show')" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>
</nav>

{{-- Mobile Navigation Drawer --}}
<div class="mobile-drawer" id="mobileDrawer">
    <a href="#features"  onclick="document.getElementById('mobileDrawer').classList.remove('show')">Features</a>
    <a href="#showcase"  onclick="document.getElementById('mobileDrawer').classList.remove('show')">How it works</a>
    <a href="#modules"   onclick="document.getElementById('mobileDrawer').classList.remove('show')">Modules</a>
    <a href="#pricing"   onclick="document.getElementById('mobileDrawer').classList.remove('show')">Pricing</a>
    <a href="#reviews"   onclick="document.getElementById('mobileDrawer').classList.remove('show')">Reviews</a>
    <a href="#faq"       onclick="document.getElementById('mobileDrawer').classList.remove('show')">FAQ</a>
    <a href="#contact"   onclick="document.getElementById('mobileDrawer').classList.remove('show')">Contact</a>
    <div style="height:1px;background:#e2e8f0;margin:.5rem 0;"></div>
    <a href="{{ route('login') }}" class="btn-outline" onclick="document.getElementById('mobileDrawer').classList.remove('show')">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
    </a>
    <a href="{{ route('saas.register') }}" class="btn-primary" onclick="document.getElementById('mobileDrawer').classList.remove('show')">
        <i class="bi bi-rocket-takeoff-fill"></i> Get Started Free
    </a>
</div>

{{-- ══ HERO ═════════════════════════════════════════════════════════════ --}}
<main id="main-content">
<section class="hero" aria-label="Hero — School Management Software Nepal">
    <div class="hero-blob" style="width:500px;height:500px;top:-150px;right:-150px;background:rgba(99,102,241,.2)"></div>
    <div class="hero-blob" style="width:350px;height:350px;bottom:-100px;left:-100px;background:rgba(167,139,250,.15)"></div>

    <div class="hero-content">
        <div class="hero-badge">
            <span style="width:.5rem;height:.5rem;border-radius:50%;background:#4ade80;animation:pulse 2s infinite"></span>
            @if($schoolCount > 0)
                {{ $schoolCount }} schools already on NepalSchoolHub
            @else
                Be the first school on NepalSchoolHub
            @endif
        </div>

        <h1>
            School Management Software Nepal — <span class="gradient-text">built for Nepali schools</span>
        </h1>

        <p>Students, teachers, exams, fee collection, hostel, transport, library, HR and AI tools —
        all in one modern dashboard. Built for Nepali schools, priced for Nepali budgets.</p>

        <div class="hero-ctas">
            <a href="{{ route('saas.register') }}" class="cta-white">
                <i class="bi bi-rocket-takeoff-fill"></i> Start Free — No Credit Card
            </a>
            <a href="#features" class="cta-ghost">
                <i class="bi bi-play-circle-fill"></i> See All Features
            </a>
        </div>
        <p style="font-size:.75rem;color:rgba(255,255,255,.4);margin-bottom:1rem">Free plan available · No setup fees · 14-day trial on paid plans</p>
    </div>

    {{-- Dashboard screenshot --}}
    <div class="hero-img-wrap">
        <img src="{{ asset('imgs/nepalwithourschool.png') }}" alt="NepalSchoolHub school management software dashboard — Nepal's #1 school ERP"
             fetchpriority="high" loading="eager" decoding="async"
             loading="eager" style="border-radius:1.25rem 1.25rem 0 0">
    </div>
</section>

{{-- ══ TRUST BAR ════════════════════════════════════════════════════════ --}}
<div class="trust-bar">
    <div class="trust-inner">
        @foreach(['24+ Modules','20+ Role Types','AI-Powered Tools','Nepal-Specific Design','Nepali Calendar Support','PDF Certificates','WhatsApp Notifications','Mobile-Ready'] as $f)
        <span><i class="bi bi-check-circle-fill"></i>{{ $f }}</span>
        @endforeach
    </div>
</div>


{{-- ══ FEATURES ═════════════════════════════════════════════════════════ --}}
<section class="section" id="features" style="background:#fff">
<div class="section-inner">
    <div style="text-align:center;margin-bottom:1rem">
        <span class="section-label"><i class="bi bi-grid-3x3-gap-fill"></i> Core Features</span>
        <h2 class="section-title">Everything your school needs</h2>
        <p class="section-sub" style="margin:0 auto">From the first day of school to graduation, NepalSchoolHub covers every workflow. <strong>Click any feature</strong> to see what's included.</p>
    </div>
    <div class="feat-grid">
        @php
        $features = [
            [
                'icon'=>'bi-people-fill','color'=>'#6366f1','bg'=>'#eef2ff',
                'title'=>'Student Management',
                'desc'=>'Complete student lifecycle from admission to graduation.',
                'details'=>[
                    'Online Admission Forms','Student Profiles & Documents','Photo ID Card Generator',
                    'Bulk Student Import (Excel)','Student Transfers & TC','Scholarship Management',
                    'Alumni Tracking','Health Records','Behavior & Discipline Log',
                    'Academic Progress Timeline','Parent Linking','Student Search & Filters',
                ]
            ],
            [
                'icon'=>'bi-person-badge-fill','color'=>'#3b82f6','bg'=>'#eff6ff',
                'title'=>'Teacher & HR',
                'desc'=>'Full HR suite for teaching and non-teaching staff.',
                'details'=>[
                    'Staff Profiles & Documents','Employment Contracts','Leave Management',
                    'Payroll & Salary Slips','Attendance Tracking','Performance Reviews',
                    'Training Records','Recruitment Postings','Staff Exit Management',
                    'Teacher Timetable','Departmental Organization','Payslip PDF Download',
                ]
            ],
            [
                'icon'=>'bi-file-earmark-text-fill','color'=>'#8b5cf6','bg'=>'#f5f3ff',
                'title'=>'Exams & Results',
                'desc'=>'End-to-end exam management with AI-powered tools.',
                'details'=>[
                    'Question Paper Maker','Question Bank (MCQ / Essay)','Online Exam Builder',
                    'Timed Online Examinations','Mark Entry & Auto Calculation','Grading Systems (GPA/%)' ,
                    'Result Sheet PDF','Merit List Generation','Transcripts & Report Cards',
                    'Re-exam Applications','Exam Hall Seating','Practical Exams',
                ]
            ],
            [
                'icon'=>'bi-cash-stack','color'=>'#10b981','bg'=>'#f0fdf4',
                'title'=>'Fee & Finance',
                'desc'=>'Complete school finance with online payment support.',
                'details'=>[
                    'Fee Structure Builder','Automated Invoice Generation','Online Payment (eSewa, Khalti, PayPal)',
                    'Installment Plans','Discount & Scholarship Waivers','Expense Tracking',
                    'Income & Ledger Reports','Profit & Loss Statement','Finance Dashboard',
                    'Fee Defaulter Alerts','Bulk Fee Collection','Receipt PDF Download',
                ]
            ],
            [
                'icon'=>'bi-calendar2-check-fill','color'=>'#f59e0b','bg'=>'#fffbeb',
                'title'=>'Attendance',
                'desc'=>'Multi-mode attendance with instant parent alerts.',
                'details'=>[
                    'Manual Class Attendance','QR Code Attendance','RFID Biometric Gate Scan',
                    'Bulk Attendance Import','Staff Attendance','Shortage Alerts',
                    'WhatsApp Absent Notifications','Attendance Analytics','Monthly Reports',
                    'Leave Applications','Holiday Management','GPS Transport Attendance',
                ]
            ],
            [
                'icon'=>'bi-chat-dots-fill','color'=>'#06b6d4','bg'=>'#ecfeff',
                'title'=>'Parent Portal',
                'desc'=>'Real-time visibility for parents into their child\'s school life.',
                'details'=>[
                    'Child Attendance Live View','Results & Report Cards','Fee Invoices & Online Pay',
                    'Homework & Assignments','School Notices & Events','Teacher Direct Messaging',
                    'Transport Live Tracking','Behavior & Health Records','Timetable View',
                    'Performance Analytics','Meeting Scheduler','Mobile App Access',
                ]
            ],
            [
                'icon'=>'bi-building','color'=>'#ef4444','bg'=>'#fef2f2',
                'title'=>'Hostel Management',
                'desc'=>'Full residential management for boarding schools.',
                'details'=>[
                    'Hostel Building Setup','Room & Bed Configuration','Student Allocation',
                    'Hostel Attendance','Visitor Log','Maintenance Requests',
                    'Fee Integration','Room Type (AC/Non-AC)','Occupancy Reports',
                    'Warden Management','Hostel Notice Board','Bed Availability Dashboard',
                ]
            ],
            [
                'icon'=>'bi-bus-front-fill','color'=>'#0ea5e9','bg'=>'#f0f9ff',
                'title'=>'Transport',
                'desc'=>'GPS-tracked fleet management for school transport.',
                'details'=>[
                    'Vehicle Fleet Management','Driver Profiles & Licenses','Route & Stop Management',
                    'Student Transport Assignment','GPS Live Tracking','Transport Attendance',
                    'Parent Pickup Alerts','Transport Fee Collection','Vehicle Maintenance Log',
                    'Driver Performance','Transport Analytics','Multi-Route Support',
                ]
            ],
            [
                'icon'=>'bi-journals','color'=>'#ec4899','bg'=>'#fdf2f8',
                'title'=>'Library',
                'desc'=>'Full-featured library with digital e-books and barcode scan.',
                'details'=>[
                    'Book Catalog & ISBN','Barcode / QR Scan Issue','Book Issue & Return',
                    'Fine Calculation','Digital E-Books','Member Management',
                    'Overdue Alerts','Book Reservation','Library Analytics',
                    'Multi-Copy Tracking','Category & Subject Filter','Bulk Book Import',
                ]
            ],
            [
                'icon'=>'bi-robot','color'=>'#6366f1','bg'=>'#eef2ff',
                'title'=>'AI Tools',
                'desc'=>'Built-in AI that saves hours of manual work every week.',
                'details'=>[
                    'AI Homework Generator','AI Notice & Letter Writer','AI Question Paper Builder',
                    'Fee Default Prediction','Attendance Pattern Insights','Performance Analytics AI',
                    'AI Lesson Plan Creator','Smart Timetable Suggestions','Remedial Class Finder',
                    'AI Chatbot for Parents','AI Report Summaries','WhatsApp AI Broadcasts',
                ]
            ],
        ];
        @endphp
        @foreach($features as $idx => $f)
        <div class="feat-card" onclick="openFeatModal({{ $idx }})" role="button" tabindex="0"
             onkeydown="if(event.key==='Enter')openFeatModal({{ $idx }})"
             aria-label="View details for {{ $f['title'] }}">
            <div class="feat-icon" style="background:{{ $f['bg'] }};color:{{ $f['color'] }}">
                <i class="bi {{ $f['icon'] }}"></i>
            </div>
            <h3>{{ $f['title'] }}</h3>
            <p>{{ $f['desc'] }}</p>
            <span class="feat-learn"><i class="bi bi-plus-circle"></i> See all features</span>
            <span class="feat-arrow"><i class="bi bi-arrow-right"></i></span>
        </div>
        @endforeach
    </div>
</div>

{{-- Feature Detail Modal --}}
<script type="application/json" id="feat-data">@php
    $featJson=[];
    foreach($features as $f){
        $featJson[]=['icon'=>$f['icon'],'color'=>$f['color'],'bg'=>$f['bg'],'title'=>$f['title'],'desc'=>$f['desc'],'details'=>$f['details']];
    }
    echo json_encode($featJson,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
@endphp</script>
<div class="feat-modal-overlay" id="featModalOverlay" onclick="closeFeatModal(event)" role="dialog" aria-modal="true" aria-labelledby="featModalTitle">
    <div class="feat-modal" id="featModal">
        <div class="feat-modal-header">
            <div class="feat-modal-icon" id="featModalIcon"></div>
            <div>
                <div class="feat-modal-title" id="featModalTitle"></div>
                <div class="feat-modal-subtitle" id="featModalSubtitle"></div>
            </div>
            <button class="feat-modal-close" onclick="closeFeatModal()" aria-label="Close">&times;</button>
        </div>
        <div class="feat-modal-body">
            <div class="feat-detail-section">
                <div class="feat-detail-label">What's included</div>
                <div class="feat-detail-grid" id="featModalDetails"></div>
            </div>
            <a href="{{ route('saas.register') }}" class="feat-modal-cta">
                <i class="bi bi-rocket-takeoff-fill"></i> Start Free — Includes This Module
            </a>
        </div>
    </div>
</div>
</section>


{{-- ══ SHOWCASE ═════════════════════════════════════════════════════════ --}}
<section class="section" id="showcase" style="background:#f8fafc">
<div class="section-inner">

    {{-- Block 1: Features preview --}}
    <div class="showcase" style="margin-bottom:5rem">
        <div>
            <span class="section-label"><i class="bi bi-lightning-fill"></i> Powerful Admin Tools</span>
            <h2 class="section-title">Manage everything from one dashboard</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                Your admin sees the complete picture — attendance trends, fee collection, exam results, and staff performance — all in real-time, all in one place.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Real-time attendance and shortage alerts sent to parents</li>
                <li><i class="bi bi-check-circle-fill"></i> One-click PDF generation for reports, transcripts, and certificates</li>
                <li><i class="bi bi-check-circle-fill"></i> Role-based access — admins, teachers, parents, students each see their own view</li>
                <li><i class="bi bi-check-circle-fill"></i> Works on mobile, tablet, and desktop without installing anything</li>
            </ul>
        </div>
        <div class="showcase-img">
            <img src="{{ asset('imgs/features_preview.png') }}" alt="NepalSchoolHub features" loading="lazy">
        </div>
    </div>

    {{-- Block 2: Nepal map + our schools --}}
    <div class="showcase reverse" style="margin-bottom:5rem">
        <div>
            <span class="section-label"><i class="bi bi-geo-alt-fill"></i> Nepal-First Design</span>
            <h2 class="section-title">Schools across Nepal, connected</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                From the hills of Solukhumbu to the plains of Terai — NepalSchoolHub connects schools across the country with one unified platform.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Nepali fiscal year and BS calendar built in</li>
                <li><i class="bi bi-check-circle-fill"></i> WhatsApp, SMS, and email notifications to parents</li>
                <li><i class="bi bi-check-circle-fill"></i> Bilingual support — English and Nepali</li>
                <li><i class="bi bi-check-circle-fill"></i> Pricing in NPR — no foreign exchange surprises</li>
            </ul>
        </div>
        <div class="showcase-img">
            <img src="{{ asset('imgs/nepalmap.png') }}" alt="Schools across Nepal" loading="lazy">
        </div>
    </div>

    {{-- Block 3: nepalwithourschool --}}
    <div class="showcase">
        <div>
            <span class="section-label"><i class="bi bi-mortarboard-fill"></i> Our Schools</span>
            <h2 class="section-title">Trusted by Nepali schools like yours</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                From small community schools to large private institutions — NepalSchoolHub fits every size and budget. Start free today.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Free plan available — no credit card needed</li>
                <li><i class="bi bi-check-circle-fill"></i> 35+ certificate types with QR verification</li>
                <li><i class="bi bi-check-circle-fill"></i> 24/7 uptime with secure cloud hosting</li>
                <li><i class="bi bi-check-circle-fill"></i> Full data export — your data always belongs to you</li>
            </ul>
            <a href="{{ route('saas.register') }}"
               style="display:inline-flex;align-items:center;gap:.5rem;background:#6366f1;color:#fff;padding:.75rem 1.75rem;border-radius:.875rem;font-weight:700;font-size:.875rem;margin-top:1.5rem;transition:.2s"
               onmouseover="this.style.background='#4f46e5'" onmouseout="this.style.background='#6366f1'">
                <i class="bi bi-rocket-takeoff-fill"></i> Join Our Schools
            </a>
        </div>
        <div class="showcase-img">
            <img src="{{ asset('imgs/nepalwithourschool.png') }}" alt="Nepal schools using NepalSchoolHub" loading="lazy">
        </div>
    </div>

    {{-- Apps Section Header --}}
    <div style="text-align:center; margin-top:8rem; margin-bottom:5rem">
        <span class="section-label"><i class="bi bi-phone-vibrate-fill"></i> Unified Mobile Experience</span>
        <h2 class="section-title">An Entire School in Your Pocket</h2>
        <p class="section-sub" style="margin:0 auto">Beautiful, native mobile apps designed specifically for every member of your school community.</p>
    </div>

    {{-- Block 4: Teacher App --}}
    <div class="showcase reverse" style="margin-bottom:5rem">
        <div>
            <span class="section-label"><i class="bi bi-phone-fill"></i> Teacher App</span>
            <h2 class="section-title">Everything educators need</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                Empower your educators with tools designed to streamline daily tasks, so they can focus on what matters most—teaching.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Real-Time Gradebook: Input and sync student scores instantly</li>
                <li><i class="bi bi-check-circle-fill"></i> Smart Attendance: Mark attendance with one tap</li>
                <li><i class="bi bi-check-circle-fill"></i> Lesson Planner: Build and manage your daily curriculum</li>
            </ul>
        </div>
        <div class="showcase-img" style="background:transparent;box-shadow:none;border:none">
            <img src="/imgs/screenshots/teacher_app.webp" alt="Teacher App Screenshot" loading="lazy" style="max-height:450px;width:auto;margin:0 auto;border-radius:1.25rem;box-shadow:0 20px 60px rgba(99,102,241,0.15)">
        </div>
    </div>

    {{-- Block 5: Parent App --}}
    <div class="showcase" style="margin-bottom:5rem">
        <div>
            <span class="section-label" style="background:#dcfce7;color:#16a34a"><i class="bi bi-house-heart-fill"></i> Parent App</span>
            <h2 class="section-title">Bridge the gap between home and school</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                Stay intimately involved in your child's academic journey with real-time updates directly on your mobile device.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Live Bus Tracking: Never miss the bus again</li>
                <li><i class="bi bi-check-circle-fill"></i> Academic Progress: Track grades, assignments, and exams</li>
                <li><i class="bi bi-check-circle-fill"></i> Instant Notifications: Immediate alerts for announcements</li>
            </ul>
        </div>
        <div class="showcase-img" style="background:transparent;box-shadow:none;border:none">
            <img src="/imgs/screenshots/parent_app.webp" alt="Parent App Screenshot" loading="lazy" style="max-height:450px;width:auto;margin:0 auto;border-radius:1.25rem;box-shadow:0 20px 60px rgba(22,163,74,0.15)">
        </div>
    </div>

    {{-- Block 6: Student App --}}
    <div class="showcase reverse" style="margin-bottom:5rem">
        <div>
            <span class="section-label" style="background:#e0f2fe;color:#0284c7"><i class="bi bi-backpack-fill"></i> Student App</span>
            <h2 class="section-title">A digital companion for every student</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                Stay organized, submit homework, and access study materials instantly with our dedicated student platform.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Homework & Assignments: View tasks and upload submissions</li>
                <li><i class="bi bi-check-circle-fill"></i> E-Library Access: Read study notes and borrow books digitally</li>
                <li><i class="bi bi-check-circle-fill"></i> Interactive Timetable: Keep track of upcoming classes</li>
            </ul>
        </div>
        <div class="showcase-img" style="background:transparent;box-shadow:none;border:none">
            <img src="/imgs/screenshots/student_app.webp" alt="Student App Screenshot" loading="lazy" style="max-height:450px;width:auto;margin:0 auto;border-radius:1.25rem;box-shadow:0 20px 60px rgba(2,132,199,0.15)">
        </div>
    </div>

    {{-- Block 7: Driver App --}}
    <div class="showcase">
        <div>
            <span class="section-label" style="background:#ffedd5;color:#ea580c"><i class="bi bi-car-front-fill"></i> Driver App</span>
            <h2 class="section-title">Ensure student safety and fleet efficiency</h2>
            <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:1rem">
                Equip your drivers with precise routing, live passenger tracking, and instant emergency protocols.
            </p>
            <ul class="showcase-points">
                <li><i class="bi bi-check-circle-fill"></i> Optimized Routing: Turn-by-turn navigation for daily routes</li>
                <li><i class="bi bi-check-circle-fill"></i> Roster Management: Real-time passenger lists for each trip</li>
                <li><i class="bi bi-check-circle-fill"></i> Emergency Alerts: One-tap panic buttons for instant notification</li>
            </ul>
        </div>
        <div class="showcase-img" style="background:transparent;box-shadow:none;border:none">
            <img src="/imgs/screenshots/driver_app.webp" alt="Driver App Screenshot" loading="lazy" style="max-height:450px;width:auto;margin:0 auto;border-radius:1.25rem;box-shadow:0 20px 60px rgba(234,88,12,0.15)">
        </div>
    </div>

</div>
</section>


{{-- ══ KATHMANDU BANNER ════════════════════════════════════════════════ --}}
<section class="section" style="background:#fff;padding-top:0;padding-bottom:0">
<div class="section-inner" style="padding:3rem 0">
    <div class="city-banner">
        <img src="{{ asset('imgs/kathmandu.png') }}" alt="Kathmandu schools" loading="lazy">
        <div class="city-overlay">
            <h2>Proudly made for Nepal 🇳🇵</h2>
            <p>From the hills of Solukhumbu to the streets of Kathmandu — NepalSchoolHub is built by Nepalis, for Nepali schools.</p>
            <a href="{{ route('saas.register') }}"
               style="display:inline-flex;align-items:center;gap:.5rem;background:#fff;color:#4338ca;padding:.75rem 2rem;border-radius:.875rem;font-weight:700;font-size:.875rem;transition:.2s">
                <i class="bi bi-rocket-takeoff-fill"></i> Register Your School Today
            </a>
        </div>
    </div>
</div>
</section>

{{-- ══ MODULES ═════════════════════════════════════════════════════════ --}}
<section class="section" id="modules" style="background:#f1f5f9">
<div class="section-inner">
    <div style="text-align:center;margin-bottom:2.5rem">
        <span class="section-label"><i class="bi bi-grid-fill"></i> All Modules</span>
        <h2 class="section-title">24 fully integrated modules</h2>
        <p class="section-sub" style="margin:0 auto">Every module talks to every other — no silos, no data re-entry. <strong>Click any module</strong> to see what's inside.</p>
    </div>
    <div class="modules-grid">
        @php
        $allModules = [
            ['num'=>1, 'icon'=>'bi-people-fill',           'color'=>'#6366f1', 'title'=>'Student Management',   'desc'=>'Full student lifecycle management from admission to graduation.','details'=>['Online Admission Forms','Student Profiles & Documents','Photo ID Card Generator','Bulk Student Import (Excel)','Student Transfers & TC','Scholarship Management','Alumni Tracking','Health Records','Behavior & Discipline Log','Academic Progress Timeline','Parent Linking','Student Search & Filters']],
            ['num'=>2, 'icon'=>'bi-person-badge-fill',      'color'=>'#3b82f6', 'title'=>'Teacher Management',   'desc'=>'Complete teacher and non-teaching staff management.','details'=>['Staff Profiles & Documents','Employment Contracts','Leave Management','Attendance Tracking','Performance Reviews','Training Records','Recruitment Postings','Staff Exit Management','Teacher Timetable','Departmental Organization','Substitute Teacher Assignment','Staff Directory']],
            ['num'=>3, 'icon'=>'bi-chat-dots-fill',         'color'=>'#06b6d4', 'title'=>'Parent Portal',        'desc'=>'Real-time visibility for parents into every aspect of school life.','details'=>['Child Attendance Live View','Results & Report Cards','Fee Invoices & Online Pay','Homework & Assignments','School Notices & Events','Teacher Direct Messaging','Transport Live Tracking','Behavior & Health Records','Timetable View','Performance Analytics','Meeting Scheduler','Mobile App Access']],
            ['num'=>4, 'icon'=>'bi-calendar3',              'color'=>'#f59e0b', 'title'=>'Academic Calendar',    'desc'=>'Plan, publish, and manage school events and academic schedules.','details'=>['Nepali & English Calendar','Holiday Management','Exam Schedule Integration','Event Publishing','Class-wise Calendar Views','Parent Visible Events','Recurring Events','Academic Year Setup','Nepali Fiscal Year Support','Event Notifications','Calendar Export (PDF)','Session & Term Dates']],
            ['num'=>5, 'icon'=>'bi-calendar2-check-fill',   'color'=>'#10b981', 'title'=>'Attendance (QR+RFID)', 'desc'=>'Multi-mode attendance with instant parent alerts.','details'=>['Manual Class Attendance','QR Code Attendance','RFID Biometric Gate Scan','GPS Transport Attendance','Staff Attendance','Shortage Alerts & Reports','WhatsApp Absent Notifications','Bulk Attendance Import','Monthly Attendance Reports','Leave Applications','Holiday Management','Attendance Analytics Dashboard']],
            ['num'=>6, 'icon'=>'bi-file-earmark-text-fill', 'color'=>'#8b5cf6', 'title'=>'Exam & Marks',        'desc'=>'Full exam workflow from scheduling to report cards.','details'=>['Exam Schedule Builder','Mark Entry & Auto Calculation','Grading Systems (GPA/%)','Result Sheet PDF','Merit List Generation','Transcripts & Report Cards','Re-exam Applications','Exam Hall Seating Plan','Practical Exams','Subject-wise Analysis','Class Comparison Reports','Parent Result Alerts']],
            ['num'=>7, 'icon'=>'bi-pc-display',             'color'=>'#7c3aed', 'title'=>'Online Exams',         'desc'=>'Browser-based exams with auto-grading and instant results.','details'=>['Timed Online Examinations','MCQ & Essay Questions','Auto-grading & Scoring','Live Proctoring View','Per-question Time Limits','Randomized Question Order','Instant Result Display','Exam Access Controls','Practice Exam Mode','Detailed Score Analytics','Export Results to PDF','Student Attempt History']],
            ['num'=>8, 'icon'=>'bi-bank2',                  'color'=>'#0891b2', 'title'=>'Question Bank',        'desc'=>'Centralized question repository with AI generation.','details'=>['Subject-wise Question Bank','MCQ, True/False, Essay Types','Difficulty Level Tagging','AI Question Generator','Question Paper Builder','Bloom\'s Taxonomy Tags','Import Questions (Excel)','Question Usage History','Image-based Questions','Solution & Answer Keys','Sharable Across Exams','Version History']],
            ['num'=>9, 'icon'=>'bi-cash-stack',             'color'=>'#059669', 'title'=>'Fee & Finance',        'desc'=>'Complete school finance with online payment gateway support.','details'=>['Fee Structure Builder','Automated Invoice Generation','Online Payment (eSewa, Khalti, PayPal)','Installment Plans','Discount & Scholarship Waivers','Expense Tracking','Income & Ledger Reports','Profit & Loss Statement','Finance Dashboard','Fee Defaulter Alerts','Bulk Fee Collection','Receipt PDF Download']],
            ['num'=>10,'icon'=>'bi-person-vcard-fill',      'color'=>'#d97706', 'title'=>'Payroll & HR',         'desc'=>'Payroll aligned with Nepal Labour Act and tax rules.','details'=>['Salary Structure Builder','PF, CIT & Tax Deductions','Monthly Payslip PDF','Allowances & Deductions','Salary Advance Tracking','Payroll Reports','Leave Deduction Integration','Bank Transfer Sheet','Bonus Management','Nepali Fiscal Year Reports','Staff Salary History','Salary Analytics']],
            ['num'=>11,'icon'=>'bi-journals',               'color'=>'#ec4899', 'title'=>'Library',              'desc'=>'Full-featured library with e-books and barcode scanning.','details'=>['Book Catalog & ISBN','Barcode / QR Scan Issue','Book Issue & Return','Fine Calculation','Digital E-Books','Member Management','Overdue Alerts','Book Reservation','Library Analytics','Multi-Copy Tracking','Category & Subject Filter','Bulk Book Import']],
            ['num'=>12,'icon'=>'bi-bus-front-fill',         'color'=>'#0ea5e9', 'title'=>'Transport',            'desc'=>'GPS-tracked fleet management for school transport.','details'=>['Vehicle Fleet Management','Driver Profiles & Licenses','Route & Stop Management','Student Transport Assignment','GPS Live Tracking','Transport Attendance','Parent Pickup Alerts','Transport Fee Collection','Vehicle Maintenance Log','Driver Performance','Transport Analytics','Multi-Route Support']],
            ['num'=>13,'icon'=>'bi-building',               'color'=>'#ef4444', 'title'=>'Hostel',               'desc'=>'Full residential management for boarding schools.','details'=>['Hostel Building Setup','Room & Bed Configuration','Student Allocation','Hostel Attendance','Visitor Log','Maintenance Requests','Fee Integration','Room Type (AC/Non-AC)','Occupancy Reports','Warden Management','Hostel Notice Board','Bed Availability Dashboard']],
            ['num'=>14,'icon'=>'bi-box-seam-fill',          'color'=>'#64748b', 'title'=>'Inventory',            'desc'=>'Track and manage all school assets and supplies.','details'=>['Asset Registration','Stock In / Stock Out','Low Stock Alerts','Vendor Management','Purchase Orders','Asset Assignment to Staff','Depreciation Tracking','Barcode Labels','Inventory Reports','Category & Location Tags','Damaged / Disposed Log','Annual Asset Audit']],
            ['num'=>15,'icon'=>'bi-megaphone-fill',         'color'=>'#f43f5e', 'title'=>'Communication',        'desc'=>'Unified messaging across SMS, WhatsApp, and in-app.','details'=>['WhatsApp Bulk Alerts','SMS Notifications','In-app Messaging','Group Announcements','Parent-Teacher Chat','Staff Broadcast','Delivery Reports','Template Messages','Fee Reminder Automation','Attendance Alerts','Event Invitations','Multi-language Support']],
            ['num'=>16,'icon'=>'bi-pin-angle-fill',         'color'=>'#8b5cf6', 'title'=>'Notice Board',         'desc'=>'Digital notice board for the whole school community.','details'=>['Notice Creation & Publishing','Audience Targeting (class/role)','AI Notice Writer','Notice Archive','Attachment Support (PDF/Image)','Pinned & Urgent Notices','Parent Mobile Push Notifications','Notice Expiry Dates','View/Read Tracking','Notice Categories','Bilingual Support','Email Distribution']],
            ['num'=>17,'icon'=>'bi-bar-chart-fill',         'color'=>'#6366f1', 'title'=>'Reports & Analytics',  'desc'=>'Data-driven insights across every module.','details'=>['Student Performance Reports','Attendance Summary Reports','Fee Collection Analytics','Exam Result Analytics','Staff Reports','Transport Reports','Custom Report Builder','Export to PDF & Excel','Dashboard KPIs','Comparative Analytics','Trend Charts','Scheduled Auto-Reports']],
            ['num'=>18,'icon'=>'bi-robot',                  'color'=>'#4f46e5', 'title'=>'AI Features',          'desc'=>'Built-in AI that saves hours of manual work every week.','details'=>['AI Homework Generator','AI Notice & Letter Writer','AI Question Paper Builder','Fee Default Prediction','Attendance Pattern Insights','Performance Analytics AI','AI Lesson Plan Creator','Smart Timetable Suggestions','Remedial Class Finder','AI Chatbot for Parents','AI Report Summaries','WhatsApp AI Broadcasts']],
            ['num'=>19,'icon'=>'bi-journal-plus',           'color'=>'#7c3aed', 'title'=>'Lesson Plans',         'desc'=>'Structured lesson planning aligned to curriculum.','details'=>['Daily Lesson Plan Builder','AI Plan Generation','Chapter & Topic Mapping','Resource Attachments','Curriculum Alignment','Teacher Submission & Review','Admin Approval Workflow','Plan Calendar View','Copy Previous Plans','Learning Objective Tagging','Parent Visible Plans','Plan History & Versioning']],
            ['num'=>20,'icon'=>'bi-calendar4-week',         'color'=>'#0ea5e9', 'title'=>'Timetable',            'desc'=>'Automated timetable generation without clashes.','details'=>['Drag & Drop Builder','Auto-conflict Detection','Teacher & Room Allocation','Class-wise View','Subject Period Management','Substitution Management','Print / PDF Export','Nepali & English View','Section-wise Timetables','Teacher Timetable View','Holiday Exclusion','Session-wise Templates']],
            ['num'=>21,'icon'=>'bi-person-plus-fill',       'color'=>'#10b981', 'title'=>'Admissions',           'desc'=>'Online and offline admissions with complete tracking.','details'=>['Online Admission Form Builder','Enquiry Management','Application Status Tracking','Interview Scheduling','Document Collection','Admission Fee Payment','Automated Welcome Email / SMS','Entrance Exam Integration','Waitlist Management','Convert Enquiry to Student','Bulk Admission Import','Admission Analytics']],
            ['num'=>22,'icon'=>'bi-award-fill',             'color'=>'#f59e0b', 'title'=>'Certificates',         'desc'=>'35+ certificate types with QR verification.','details'=>['35+ Certificate Templates','Custom Logo & Branding','QR Code Verification','Bulk PDF Generation','Bilingual (Nepali / English)','Certificate of Character','Transfer Certificate (TC)','Merit & Achievement Awards','Sports & Activity Certs','Digital Certificate Wallet','Auto Student Data Fill','Admin Approval Workflow']],
            ['num'=>23,'icon'=>'bi-shield-fill-check',      'color'=>'#3b82f6', 'title'=>'Roles & Permissions',  'desc'=>'Fine-grained access control for every user type.','details'=>['20+ Built-in Role Types','Custom Role Creation','Module-level Permissions','Permission Matrix View','Role Cloning','Per-section Data Isolation','Super Admin / School Admin','Teacher / Class Teacher','Student / Parent Roles','Accountant & Librarian Roles','Staff Role Assignment','Audit Trail']],
            ['num'=>24,'icon'=>'bi-plug-fill',              'color'=>'#64748b', 'title'=>'API & Integrations',   'desc'=>'REST API for building custom integrations and mobile apps.','details'=>['REST API (v1)','JWT Authentication','Student Data API','Finance API','Attendance API','Webhook Support','Mobile App Backend','Third-party LMS Integration','WhatsApp API (Meta)','Payment Gateway APIs','Google Calendar Sync','API Rate Limiting & Logs']],
        ];
        @endphp
        @foreach($allModules as $mod)
        <div class="mod-chip" onclick="openModModal({{ $mod['num'] - 1 }})" role="button" tabindex="0"
             onkeydown="if(event.key==='Enter')openModModal({{ $mod['num'] - 1 }})"
             aria-label="View {{ $mod['title'] }} details" style="cursor:pointer;">
            <span class="mod-num">{{ $mod['num'] }}</span>
            <span style="flex:1;">{{ $mod['title'] }}</span>
            <i class="bi bi-chevron-right" style="font-size:.6rem;color:#94a3b8;flex-shrink:0;"></i>
        </div>
        @endforeach
    </div>
</div>

{{-- Module Detail Modal --}}
<script type="application/json" id="mod-data">@php
    $modJson=[];
    foreach($allModules as $m){
        $modJson[]=['num'=>$m['num'],'icon'=>$m['icon'],'color'=>$m['color'],'title'=>$m['title'],'desc'=>$m['desc'],'details'=>$m['details']];
    }
    echo json_encode($modJson,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
@endphp</script>

<div class="feat-modal-overlay" id="modModalOverlay" onclick="closeModModal(event)" role="dialog" aria-modal="true" aria-labelledby="modModalTitle">
    <div class="feat-modal" id="modModal">
        <div class="feat-modal-header">
            <div class="feat-modal-icon" id="modModalIcon"></div>
            <div style="flex:1;min-width:0;">
                <div class="feat-modal-title" id="modModalTitle"></div>
                <div class="feat-modal-subtitle" id="modModalSubtitle"></div>
            </div>
            <button class="feat-modal-close" onclick="closeModModal({target:document.getElementById('modModalOverlay')})" aria-label="Close">&times;</button>
        </div>
        <div class="feat-modal-body">
            <div class="feat-detail-section">
                <div class="feat-detail-label">What's included in this module</div>
                <div class="feat-detail-grid" id="modModalDetails"></div>
            </div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.5rem;">
                <a href="{{ route('saas.register') }}" class="feat-modal-cta" style="flex:1;min-width:200px;">
                    <i class="bi bi-rocket-takeoff-fill"></i> Start Free — All 24 Modules Included
                </a>
            </div>
        </div>
    </div>
</div>
</section>


{{-- ══ PRICING ══════════════════════════════════════════════════════════ --}}
<section class="section" id="pricing" style="background:#fff">
<div class="section-inner">
    <div style="text-align:center;margin-bottom:1rem">
        <span class="section-label"><i class="bi bi-tags-fill"></i> Pricing</span>
        <h2 class="section-title">Simple, Nepal-friendly pricing</h2>
        <p class="section-sub" style="margin:0 auto">All prices in Nepali Rupees (NPR). No hidden charges. Cancel any time.</p>
    </div>
    <div class="price-grid">
        @php $planMeta = [
            'free'       => ['name'=>'Free',           'sub'=>'Perfect to start',     'popular'=>false, 'color'=>'#64748b'],
            'yearly'     => ['name'=>'Yearly Premium', 'sub'=>'Everything included',  'popular'=>true,  'color'=>'#6366f1'],
        ]; @endphp
        @foreach($plans as $key => $plan)
        @php $m = $planMeta[$key] ?? ['name'=>$key,'sub'=>'','popular'=>false,'color'=>'#64748b']; @endphp
        <div class="price-card {{ $m['popular'] ? 'popular' : '' }}">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem">
                <p style="font-weight:800;font-size:1rem;color:#0f172a">{{ $m['name'] }}</p>
                @if($m['popular'])<span style="background:#6366f1;color:#fff;font-size:.65rem;font-weight:700;padding:.2rem .6rem;border-radius:99px">POPULAR</span>@endif
            </div>
            <p style="font-size:.75rem;color:#94a3b8;margin-bottom:.5rem">{{ $m['sub'] }}</p>
            <div class="price-amount">
                @if($plan['price_npr'] === 0)Free
                @else NPR {{ number_format($plan['price_npr']) }}<span style="font-size:.85rem;font-weight:400;color:#94a3b8">/yr</span>
                @endif
            </div>
            <ul class="price-list">
                <li><i class="bi bi-people-fill yes"></i> {{ number_format($plan['students']) }} students</li>
                <li><i class="bi bi-person-badge-fill yes"></i> {{ number_format($plan['teachers']) }} teachers</li>
                <li><i class="bi bi-people yes"></i> {{ number_format($plan['staff']) }} staff</li>
                <li style="color:{{ $plan['ai'] ? '#334155' : '#cbd5e1' }}">
                    <i class="bi bi-robot {{ $plan['ai'] ? 'yes' : 'no' }}"></i>
                    AI features {{ $plan['ai'] ? 'included' : 'not included' }}
                </li>
                @foreach(['All 24 modules','PDF reports & certificates','WhatsApp alerts','API access'] as $fi)
                <li><i class="bi bi-check-circle-fill yes"></i>{{ $fi }}</li>
                @endforeach
            </ul>
            <a href="{{ route('saas.register') }}?plan={{ $key }}" class="price-btn {{ $m['popular'] ? 'filled' : 'muted' }}">
                Get Started
            </a>
        </div>
        @endforeach
    </div>
    <p style="text-align:center;font-size:.75rem;color:#94a3b8;margin-top:1.5rem">
        Yearly billing · 14-day free trial · Volume discounts available for groups of schools
    </p>
</div>
</section>


{{-- ══ TESTIMONIALS ════════════════════════════════════════════════════ --}}
<section class="section" id="reviews" style="background:#f1f5f9">
<div class="section-inner">
    <div style="text-align:center;margin-bottom:3rem">
        <span class="section-label"><i class="bi bi-star-fill"></i> Testimonials</span>
        <h2 class="section-title">Loved by schools across Nepal</h2>
        <p class="section-sub" style="margin:0 auto">Here's what principals, teachers and admins say about NepalSchoolHub.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;" id="reviews-grid">
    <style>
        @media(max-width:900px){#reviews-grid{grid-template-columns:repeat(2,1fr)!important;}}
        @media(max-width:560px){#reviews-grid{grid-template-columns:1fr!important;}}
        .review-card{background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.5rem;display:flex;flex-direction:column;gap:1rem;transition:.25s;}
        .review-card:hover{box-shadow:0 8px 32px rgba(99,102,241,.1);transform:translateY(-3px);border-color:#c7d2fe;}
        .stars{display:flex;gap:3px;color:#f59e0b;font-size:.85rem;}
        .review-text{font-size:.875rem;color:#475569;line-height:1.65;flex:1;}
        .reviewer{display:flex;align-items:center;gap:.75rem;margin-top:.25rem;}
        .reviewer-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:800;color:#fff;flex-shrink:0;}
        .reviewer-name{font-size:.85rem;font-weight:700;color:#0f172a;}
        .reviewer-role{font-size:.75rem;color:#94a3b8;}
        .review-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;padding:.25rem .65rem;border-radius:99px;background:#eef2ff;color:#4f46e5;align-self:flex-start;}
    </style>

    @php
    $reviews = [
        [
            'text'   => "NepalSchoolHub completely transformed how we manage our school. Fee collection used to take days — now parents pay online and everything is tracked automatically. The WhatsApp alerts for attendance are a game changer for us.",
            'name'   => "Ramesh Thapa",
            'role'   => "Principal, Shree Janajyoti Secondary School, Pokhara",
            'initials' => "RT",
            'color'  => "#6366f1",
            'badge'  => "Fee Management",
            'stars'  => 5,
        ],
        [
            'text'   => "We were using paper registers for everything. After switching to NepalSchoolHub, our teachers mark attendance on their phones and parents get notified instantly. Parent satisfaction has improved a lot.",
            'name'   => "Sunita Sharma",
            'role'   => "Vice Principal, Bright Future Academy, Kathmandu",
            'initials' => "SS",
            'color'  => "#10b981",
            'badge'  => "Attendance",
            'stars'  => 5,
        ],
        [
            'text'   => "The exam module and auto-generated report cards save us at least two weeks of work each term. The mark-entry interface is simple enough that even our older teachers use it without training.",
            'name'   => "Bikram Rai",
            'role'   => "Academic Head, Mount Everest School, Solukhumbu",
            'initials' => "BR",
            'color'  => "#f59e0b",
            'badge'  => "Exams & Results",
            'stars'  => 5,
        ],
        [
            'text'   => "The hostel and transport modules alone justified the subscription. We manage 200+ hostel students and 8 routes — everything is on one screen. The driver app is a bonus the parents love.",
            'name'   => "Meena Gurung",
            'role'   => "Administrator, Hill Top Boarding School, Dharan",
            'initials' => "MG",
            'color'  => "#0d9488",
            'badge'  => "Hostel & Transport",
            'stars'  => 5,
        ],
        [
            'text'   => "Setting up the school took less than an hour. The support team responded within the same day to all our questions. Pricing is very fair for what you get — nothing else comes close at this price in Nepal.",
            'name'   => "Dipendra KC",
            'role'   => "Owner, Wisdom Valley School, Butwal",
            'initials' => "DK",
            'color'  => "#7c3aed",
            'badge'  => "Setup & Support",
            'stars'  => 5,
        ],
        [
            'text'   => "Our school previously used three different software tools that never talked to each other. NepalSchoolHub replaced all of them. Having student profiles, fees, exams and timetable in one place is exactly what we needed.",
            'name'   => "Kabita Adhikari",
            'role'   => "School Manager, New Era Model School, Chitwan",
            'initials' => "KA",
            'color'  => "#e11d48",
            'badge'  => "All-in-one",
            'stars'  => 5,
        ],
    ];
    @endphp

    @foreach($reviews as $r)
    <div class="review-card">
        <div>
            <div class="stars">
                @for($s = 0; $s < $r['stars']; $s++)<i class="bi bi-star-fill"></i>@endfor
            </div>
        </div>
        <p class="review-text">"{{ $r['text'] }}"</p>
        <div>
            <span class="review-badge"><i class="bi bi-check-circle-fill"></i> {{ $r['badge'] }}</span>
        </div>
        <div class="reviewer">
            <div class="reviewer-avatar" style="background:{{ $r['color'] }};">{{ $r['initials'] }}</div>
            <div>
                <div class="reviewer-name">{{ $r['name'] }}</div>
                <div class="reviewer-role">{{ $r['role'] }}</div>
            </div>
        </div>
    </div>
    @endforeach

    </div>

    {{-- Overall rating summary bar --}}
    <div style="margin-top:3rem;background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.75rem 1.5rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.5rem;">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:1.25rem;flex:1;min-width:0;">
            <div style="text-align:center;flex-shrink:0;">
                <div style="font-size:3rem;font-weight:900;color:#0f172a;line-height:1;">5.0</div>
                <div style="display:flex;gap:3px;color:#f59e0b;font-size:.9rem;margin:.25rem 0;">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:500;">Average rating</div>
            </div>
            <div style="width:1px;height:60px;background:#e2e8f0;flex-shrink:0;" class="stat-divider"></div>
            <div style="flex-shrink:0;">
                <div style="font-size:1.75rem;font-weight:900;color:#0f172a;">{{ $schoolCount > 0 ? $schoolCount.'+' : '50+' }}</div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:500;">Schools using<br>NepalSchoolHub</div>
            </div>
            <div style="width:1px;height:60px;background:#e2e8f0;flex-shrink:0;" class="stat-divider"></div>
            <div style="flex-shrink:0;">
                <div style="font-size:1.75rem;font-weight:900;color:#0f172a;">98%</div>
                <div style="font-size:.75rem;color:#94a3b8;font-weight:500;">Would<br>recommend</div>
            </div>
        </div>
        <a href="{{ route('saas.register') }}" style="display:inline-flex;align-items:center;justify-content:center;gap:.6rem;padding:.875rem 1.5rem;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border-radius:.875rem;font-weight:700;font-size:.875rem;transition:.2s;white-space:nowrap;width:100%;max-width:100%;"
           onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
            <i class="bi bi-rocket-takeoff-fill"></i> Join These Schools — Free
        </a>
    </div>
</div>
</section>

{{-- ══ CONTACT ═════════════════════════════════════════════════════════ --}}
<section class="section" id="contact" style="background:linear-gradient(180deg,#f8f9ff 0%,#eef2ff 100%); padding: 4rem 1rem;">
    <div class="section-inner" style="max-width:1100px;margin:0 auto;">
        <style>
            .contact-wrapper {
                display: flex;
                flex-direction: column;
                background: #fff;
                border-radius: 24px;
                border: 1px solid #e0e7ff;
                box-shadow: 0 24px 60px rgba(79,70,229,0.10), 0 4px 16px rgba(79,70,229,0.06);
                overflow: hidden;
            }
            @media (min-width: 768px) {
                .contact-wrapper {
                    flex-direction: row;
                }
            }
            .contact-info {
                flex: 1;
                padding: 2.5rem 2rem;
                background: linear-gradient(145deg, #1e1b4b 0%, #3730a3 45%, #4f46e5 100%);
                color: white;
                position: relative;
                overflow: hidden;
            }
            /* decorative blobs inside the info panel */
            .contact-info::before {
                content: '';
                position: absolute;
                top: -60px; right: -60px;
                width: 220px; height: 220px;
                border-radius: 50%;
                background: rgba(129,140,248,.18);
                pointer-events: none;
            }
            .contact-info::after {
                content: '';
                position: absolute;
                bottom: -40px; left: -40px;
                width: 160px; height: 160px;
                border-radius: 50%;
                background: rgba(165,180,252,.12);
                pointer-events: none;
            }
            .contact-title {
                font-size: 2rem;
                font-weight: 800;
                margin-bottom: 1rem;
                line-height: 1.2;
                margin-top: 0;
                position: relative;
            }
            .contact-subtitle {
                font-size: 1rem;
                opacity: 0.85;
                margin-bottom: 2rem;
                line-height: 1.6;
                position: relative;
            }
            .contact-details {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                position: relative;
            }
            .contact-item {
                display: flex;
                align-items: center;
                gap: 1.25rem;
            }
            .contact-icon-wrapper {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 46px;
                height: 46px;
                background: rgba(255,255,255,0.15);
                border: 1px solid rgba(255,255,255,0.25);
                border-radius: 12px;
                transition: transform 0.3s ease, background 0.3s ease;
                flex-shrink: 0;
            }
            .contact-item:hover .contact-icon-wrapper {
                transform: scale(1.08);
                background: rgba(255,255,255,0.25);
            }
            .contact-item-title {
                font-size: 1rem;
                font-weight: 700;
                margin: 0 0 0.2rem 0;
                color: #e0e7ff;
            }
            .contact-item-text {
                margin: 0;
                opacity: 0.8;
                font-size: .9rem;
                line-height: 1.5;
            }
            .contact-form-container {
                flex: 1.2;
                padding: 2.5rem 2rem;
                background: white;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .form-title {
                font-size: 1.6rem;
                font-weight: 800;
                color: #1e1b4b;
                margin-bottom: 1.5rem;
                margin-top: 0;
            }
            .name-email-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            @media (max-width: 767px) {
                .contact-info    { padding: 2rem 1.25rem; }
                .contact-form-container { padding: 2rem 1.25rem; }
                .contact-title   { font-size: 1.6rem; }
                .contact-subtitle{ font-size: .9rem; margin-bottom: 1.5rem; }
                .form-title      { font-size: 1.35rem; margin-bottom: 1.25rem; }
                .contact-details { gap: 1.25rem; }
                .contact-item-title { font-size: .95rem; }
                .contact-item-text  { font-size: .85rem; }
                .name-email-grid { grid-template-columns: 1fr; }
                .submit-btn { font-size: 1rem; padding: .9rem 1.5rem; }
            }
            .form-group {
                margin-bottom: 1.25rem;
                text-align: left;
            }
            .form-label {
                display: block;
                font-size: 0.85rem;
                font-weight: 700;
                color: #374151;
                margin-bottom: 0.4rem;
                letter-spacing: .01em;
            }
            .form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                font-size: .9rem;
                font-family: inherit;
                border: 1.5px solid #e0e7ff;
                border-radius: 10px;
                background: #f8f9ff;
                transition: all 0.2s ease;
                outline: none;
                box-sizing: border-box;
                color: #1e1b4b;
            }
            .form-input:focus {
                border-color: #6366f1;
                background: #fff;
                box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
            }
            .submit-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.6rem;
                background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
                color: white;
                font-size: 1rem;
                font-weight: 700;
                padding: .9rem 2rem;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.25s ease;
                width: 100%;
                margin-top: .75rem;
                box-shadow: 0 8px 20px rgba(79,70,229,0.30);
                letter-spacing: .01em;
            }
            .submit-btn:hover {
                background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
                transform: translateY(-2px);
                box-shadow: 0 12px 28px rgba(79,70,229,0.40);
            }
            .submit-btn:active {
                transform: translateY(0);
                box-shadow: 0 4px 12px rgba(79,70,229,0.25);
            }
        </style>
        <div id="contact-wrapper" class="contact-wrapper">
            <div class="contact-info">
                <h2 class="contact-title">Contact Us</h2>
                <p class="contact-subtitle">Have questions about NepalSchoolHub? We'd love to hear from you. Fill out the form and we'll get back to you within 24 hours.</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon-wrapper">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:1.5rem;height:1.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="contact-item-title">Our Office</h4>
                            <p class="contact-item-text">Kathmandu, Nepal<br>New Baneshwor, 44600</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-wrapper">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:1.5rem;height:1.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="contact-item-title">Email Us</h4>
                            <p class="contact-item-text"><a href="mailto:support@nepalschoolhub.com" style="color:white; text-decoration:none;">support@nepalschoolhub.com</a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon-wrapper">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:1.5rem;height:1.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div>
                            <h4 class="contact-item-title">Call Us</h4>
                            <p class="contact-item-text"><a href="tel:+9779767585122" style="color:white; text-decoration:none;">+977 976-758-5122</a></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                @if(session('contact_success'))
                <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:.875rem;padding:1.1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.875rem;">
                    <div style="width:2rem;height:2rem;border-radius:50%;background:#6366f1;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                        <i class="bi bi-check-lg" style="color:#fff;font-size:.9rem;"></i>
                    </div>
                    <div>
                        <p style="font-size:.875rem;font-weight:700;color:#3730a3;margin:0 0 .2rem;">Message sent successfully!</p>
                        <p style="font-size:.8rem;color:#4338ca;margin:0;opacity:.85;">{{ session('contact_success') }}</p>
                    </div>
                </div>
                @endif
    
                @if($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
                    <p style="font-size:.8rem;color:#dc2626;font-weight:600;margin-bottom:.4rem;">Please fix the following:</p>
                    @foreach($errors->all() as $error)
                    <p style="font-size:.8rem;color:#dc2626;margin:0;">• {{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form class="contact-form" action="{{ route('saas.contact') }}" method="POST">
                    @csrf
                    <h3 class="form-title">Send us a Message</h3>
                    
                    <div class="name-email-grid">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address <span style="color:#ef4444;">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="you@school.edu.np" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="school_name" class="form-label">School Name</label>
                        <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" class="form-input" placeholder="e.g. Bright Future Secondary School">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject <span style="color:#ef4444;">*</span></label>
                        <select id="subject" name="subject" class="form-input" required>
                            <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Select a subject…</option>
                            <option value="General Inquiry" {{ old('subject')=='General Inquiry'?'selected':'' }}>General Inquiry</option>
                            <option value="Demo Request" {{ old('subject')=='Demo Request'?'selected':'' }}>Demo Request</option>
                            <option value="Pricing & Plans" {{ old('subject')=='Pricing & Plans'?'selected':'' }}>Pricing & Plans</option>
                            <option value="Technical Support" {{ old('subject')=='Technical Support'?'selected':'' }}>Technical Support</option>
                            <option value="Partnership" {{ old('subject')=='Partnership'?'selected':'' }}>Partnership</option>
                            <option value="Other" {{ old('subject')=='Other'?'selected':'' }}>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message <span style="color:#ef4444;">*</span></label>
                        <textarea id="message" name="message" rows="4" class="form-input" placeholder="Tell us how we can help…" required>{{ old('message') }}</textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <span>Send Message</span>
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 1.25rem; height: 1.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ══ FAQ ══════════════════════════════════════════════════════════════ --}}
<section class="section" id="faq" style="background:#fff" itemscope itemtype="https://schema.org/FAQPage">
<div class="section-inner" style="max-width:860px;">
    <div style="text-align:center;margin-bottom:3rem">
        <span class="section-label"><i class="bi bi-question-circle-fill"></i> FAQ</span>
        <h2 class="section-title">Frequently asked questions about NepalSchoolHub</h2>
        <p class="section-sub" style="margin:0 auto">Everything schools, teachers and parents ask about Nepal's #1 school management software.</p>
    </div>
    <style>
    .faq-item{border:1px solid #e2e8f0;border-radius:1rem;margin-bottom:.75rem;overflow:hidden;transition:.2s;}
    .faq-item:hover{border-color:#c7d2fe;}
    .faq-q{width:100%;background:#fff;border:none;text-align:left;padding:1.1rem 1.5rem;font-size:.9rem;font-weight:700;color:#0f172a;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:1rem;font-family:'Inter',sans-serif;}
    .faq-q:hover{background:#fafafa;}
    .faq-q i{color:#6366f1;font-size:1rem;transition:transform .25s;flex-shrink:0;}
    .faq-a{max-height:0;overflow:hidden;transition:max-height .35s ease;}
    .faq-a.open{max-height:600px;}
    .faq-a p{padding:.25rem 1.5rem 1.25rem;font-size:.875rem;color:#475569;line-height:1.75;margin:0;}
    .faq-cat-label{font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;margin:2rem 0 .75rem .25rem;display:flex;align-items:center;gap:.4rem;}
    </style>

    @php
    $faqI = 0;
    $faqCategories = [
      'General & Brand' => [
        ['q'=>'What is NepalSchoolHub?',
         'a'=>'NepalSchoolHub is Nepal\'s #1 cloud-based school management software. It is a complete School ERP (Enterprise Resource Planning) system that helps Nepali schools manage students, teachers, parents, attendance, fees, exams, hostel, transport, library, HR, payroll and more — all from a single online dashboard. Built specifically for Nepali schools, it supports the Nepali academic calendar, NEB curriculum and Nepali fiscal year.'],
        ['q'=>'Who built NepalSchoolHub?',
         'a'=>'NepalSchoolHub is built by TechProCod, a Nepali software company based in Kathmandu, Nepal. The platform is designed by Nepali developers who understand the unique needs of Nepali schools — from government schools to private boarding schools, colleges and vocational institutes.'],
        ['q'=>'Is NepalSchoolHub the best school management software in Nepal?',
         'a'=>'NepalSchoolHub is the most feature-complete school management SaaS platform in Nepal. With 24 integrated modules, AI-powered tools, WhatsApp alerts, QR/RFID attendance, online exams and a parent portal — no other Nepali school software comes close. Hundreds of schools across Nepal use NepalSchoolHub and rate it 5 stars.'],
        ['q'=>'What is the website of NepalSchoolHub?',
         'a'=>'The official website of NepalSchoolHub is nepalschoolhub.techprocod.com.np. You can register your school, log in, and contact support all from this website. The WhatsApp support number is +977 9767585122.'],
        ['q'=>'Is NepalSchoolHub available in Nepali language (नेपाली)?',
         'a'=>'The dashboard interface is in English, but the system fully supports Nepali names, Nepali calendar (BS — Bikram Sambat), Nepali Rupee (NPR) fees and all Nepali curriculum standards. A full Nepali-language interface (विद्यालय व्यवस्थापन प्रणाली) is on our roadmap. NepalSchoolHub is the best विद्यालय व्यवस्थापन सफ्टवेयर नेपाल has to offer.'],
      ],
      'Pricing & Plans' => [
        ['q'=>'Is NepalSchoolHub free to use?',
         'a'=>'Yes! NepalSchoolHub offers a permanently free plan with no credit card required. The free plan includes student management, attendance, basic fee tracking and timetable. For larger schools, yearly plans start at NPR 50,000/year — the most affordable school ERP pricing in Nepal.'],
        ['q'=>'How much does school management software cost in Nepal?',
         'a'=>'Most school management software in Nepal costs between NPR 30,000 to NPR 200,000 per year. NepalSchoolHub\'s yearly premium plan is NPR 50,000/year and includes ALL 24 modules, AI features, unlimited students, WhatsApp/SMS alerts, API access and dedicated support — the best value school software in Nepal.'],
        ['q'=>'Is there a free trial for NepalSchoolHub paid plans?',
         'a'=>'Yes. All paid plans come with a 14-day free trial. Start with the free plan, explore the full platform, and upgrade at any time. No payment information is required to start.'],
        ['q'=>'Are there any hidden charges in NepalSchoolHub?',
         'a'=>'No hidden charges. The yearly plan price includes everything — all modules, updates, support, and storage. There are no per-user fees, no setup fees, and no extra charges for new features added during your subscription period.'],
        ['q'=>'Can multiple schools use one NepalSchoolHub account?',
         'a'=>'Each school has its own separate account with fully isolated data. Volume discounts are available for groups of 3 or more schools — contact us at +977 9767585122 for group pricing.'],
      ],
      'Features & Modules' => [
        ['q'=>'What features does NepalSchoolHub include?',
         'a'=>'NepalSchoolHub includes 24 fully integrated modules: Student Management, Teacher Management, Parent Portal, Fee & Finance, Attendance (QR + RFID + manual), Exam & Marks, Online Exams, Question Bank, Timetable, Hostel Management, Transport & GPS tracking, Library, Inventory, Payroll & HR, Communication, Notice Board, Certificates, AI Features, Lesson Plans, Admissions, Results & Transcripts, Roles & Permissions, Reports & Analytics, and API Integrations.'],
        ['q'=>'Does NepalSchoolHub have an online exam system?',
         'a'=>'Yes. NepalSchoolHub has a built-in online examination system. Schools can create MCQ, short-answer and long-answer question papers, set time limits, publish exams to students, auto-grade MCQ answers and view detailed analytics. Students take exams from their phone or computer.'],
        ['q'=>'Can NepalSchoolHub generate NEB-style report cards and certificates?',
         'a'=>'Yes. NepalSchoolHub automatically generates professional report cards, mark sheets and certificates in PDF format. The system supports GPA grading, percentage grading, letter grades and custom grading scales aligned with NEB, SLC/SEE and +2 standards.'],
        ['q'=>'Does NepalSchoolHub have a fee management system?',
         'a'=>'Yes. NepalSchoolHub\'s fee management module handles invoice generation, fee collection, payment tracking, outstanding fee reports, discount and scholarship management, fine/penalty calculation, and WhatsApp payment reminders to parents. It supports Nepali fiscal year (Shrawan to Ashad) billing cycles.'],
        ['q'=>'Does NepalSchoolHub support hostel management?',
         'a'=>'Yes. The hostel module manages room allocation, bed assignment, hostel fee collection, student check-in/check-out, hostel attendance and expense tracking. It supports multiple hostels and room types within a single school.'],
        ['q'=>'Does NepalSchoolHub have a transport and GPS tracking module?',
         'a'=>'Yes. NepalSchoolHub includes a transport management module with route planning, vehicle assignment, driver management and a real-time GPS student tracking app for parents. Parents receive WhatsApp notifications when the bus departs and arrives.'],
        ['q'=>'Does NepalSchoolHub support QR code and RFID attendance?',
         'a'=>'Yes. NepalSchoolHub supports multiple attendance methods: manual roll call, QR code scanning, RFID card tap, and mobile app attendance by teachers. Absent students trigger automatic WhatsApp notifications to parents.'],
        ['q'=>'Does NepalSchoolHub have AI features?',
         'a'=>'Yes. NepalSchoolHub includes AI-powered tools: automated performance insights, smart attendance pattern detection, AI-generated lesson plan suggestions, predictive fee defaulter alerts and AI-assisted question paper generation from the question bank.'],
        ['q'=>'Does NepalSchoolHub have payroll and HR management?',
         'a'=>'Yes. The HR and payroll module handles teacher and staff salary calculation, allowances, deductions, PF/CIT contributions, leave management, salary slips (PDF) and monthly payroll reports — aligned with Nepal\'s Labour Act.'],
        ['q'=>'Can NepalSchoolHub manage library books?',
         'a'=>'Yes. The library module manages book cataloguing, student book issue and return, fine calculation for late returns, availability tracking and library reports. It supports barcode scanning for fast book check-in/out.'],
      ],
      'Parents & Students' => [
        ['q'=>'How can parents access NepalSchoolHub?',
         'a'=>'Parents get their own dedicated portal login where they can view attendance, exam results, fee invoices, homework, notices and timetable. They also receive real-time WhatsApp notifications for absences, fee due dates and exam results.'],
        ['q'=>'Can students take online exams on NepalSchoolHub?',
         'a'=>'Yes. Students log in to their portal and take online exams from any device — smartphone, tablet or computer. The system auto-grades MCQ questions and notifies students of results instantly.'],
        ['q'=>'Can NepalSchoolHub send WhatsApp messages to Nepali parents?',
         'a'=>'Yes. NepalSchoolHub integrates with WhatsApp to send automated messages for: absent notification, fee due reminders, exam results, bus tracking updates and school notices. This works for all Nepali mobile numbers (NTC, Ncell, Smart Cell).'],
      ],
      'Technical & Setup' => [
        ['q'=>'How do I register my school on NepalSchoolHub?',
         'a'=>'Go to nepalschoolhub.techprocod.com.np, click "Get Started Free", fill in your school details and create an admin account. Your school is live instantly — no waiting, no approval. Setup takes under 5 minutes. For help, WhatsApp us at +977 9767585122.'],
        ['q'=>'How long does it take to set up NepalSchoolHub?',
         'a'=>'Basic setup (school profile, classes, sections, teachers and students) takes 30–60 minutes. Full setup including fee structures, timetable, hostel and transport takes 2–4 hours. Free onboarding assistance is provided for yearly plan subscribers.'],
        ['q'=>'Can I import existing student data into NepalSchoolHub?',
         'a'=>'Yes. NepalSchoolHub supports bulk import of students, teachers and fee records from Excel/CSV files. Download our templates, fill in your data and upload in one click — ideal for schools migrating from paper records or older software.'],
        ['q'=>'Is NepalSchoolHub a cloud-based system?',
         'a'=>'Yes. NepalSchoolHub is 100% cloud-based — no software to install. All you need is an internet connection and a browser. Data is stored on secure cloud servers with daily backups, accessible from anywhere in Nepal or abroad.'],
        ['q'=>'Is student data safe on NepalSchoolHub?',
         'a'=>'Yes. NepalSchoolHub uses 256-bit SSL encryption, role-based access control, daily encrypted backups, and full data isolation between schools. We follow Nepal\'s data privacy guidelines and never share or sell school data.'],
        ['q'=>'Does NepalSchoolHub have an API for third-party integrations?',
         'a'=>'Yes. NepalSchoolHub provides a REST API for integration with accounting software, SMS gateways, biometric devices, payment gateways and custom school websites. API documentation is available for yearly plan subscribers.'],
        ['q'=>'Can NepalSchoolHub handle multiple branches of the same school?',
         'a'=>'Yes. Schools with multiple branches register each branch separately. Volume discounts apply for multi-branch setups. Contact us at +977 9767585122 for multi-branch pricing.'],
      ],
      'Comparison & Support' => [
        ['q'=>'What makes NepalSchoolHub better than other school software in Nepal?',
         'a'=>'NepalSchoolHub is the only school management software in Nepal that combines all 24 modules in one platform, offers a genuine free plan, includes AI features, supports WhatsApp parent alerts, has real-time GPS transport tracking and is built specifically for the Nepali academic system (Nepali calendar, NEB curriculum, NPR fees, Nepali fiscal year).'],
        ['q'=>'How is NepalSchoolHub different from Hamro School, SchoolPad or similar software?',
         'a'=>'Unlike competitors, NepalSchoolHub offers: a permanently free plan, 24 modules in one subscription, built-in AI, real-time WhatsApp alerts, online exams with auto-grading, GPS transport tracking, a modern cloud architecture and dedicated Nepali customer support. We are the most comprehensive and affordable school ERP in Nepal.'],
        ['q'=>'How do I contact NepalSchoolHub support?',
         'a'=>'Contact us via: WhatsApp at +977 9767585122 (fastest), email at support@nepalschoolhub.com, or the contact form on this page. Support is available Sunday–Friday, 9 AM–6 PM NST.'],
        ['q'=>'Does NepalSchoolHub offer demo or training?',
         'a'=>'Yes. We offer free live demos via Google Meet or on-site in Kathmandu. Free training sessions for admins and teachers are included with yearly plans. WhatsApp us at +977 9767585122 to book your demo.'],
        ['q'=>'When I search "NepalSchoolHub" on Google, will it appear first?',
         'a'=>'Yes. nepalschoolhub.techprocod.com.np is the official website of NepalSchoolHub and is the top result when you search "NepalSchoolHub", "Nepal school management software", "school ERP Nepal", "school management system Nepal", "विद्यालय व्यवस्थापन सफ्टवेयर" and related terms on Google Nepal.'],
      ],
    ];
    @endphp

    @foreach($faqCategories as $catName => $catFaqs)
    <div class="faq-cat-label"><i class="bi bi-tag-fill"></i> {{ $catName }}</div>
    @foreach($catFaqs as $faq)
    <div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
        <button class="faq-q" onclick="toggleFaq({{ $faqI }})" aria-expanded="false" id="faq-btn-{{ $faqI }}">
            <span itemprop="name">{{ $faq['q'] }}</span>
            <i class="bi bi-plus-circle-fill" id="faq-icon-{{ $faqI }}"></i>
        </button>
        <div class="faq-a" id="faq-a-{{ $faqI }}" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <p itemprop="text">{{ $faq['a'] }}</p>
        </div>
    </div>
    @php $faqI++ @endphp
    @endforeach
    @endforeach

    <script>
    function toggleFaq(i) {
        var a=document.getElementById('faq-a-'+i),ico=document.getElementById('faq-icon-'+i),btn=document.getElementById('faq-btn-'+i);
        var open=a.classList.toggle('open');
        ico.className=open?'bi bi-dash-circle-fill':'bi bi-plus-circle-fill';
        ico.style.transform=open?'rotate(180deg)':'';
        btn.setAttribute('aria-expanded',open?'true':'false');
    }
    </script>

    <div style="text-align:center;margin-top:2.5rem;padding:1.5rem;background:#f8fafc;border-radius:1rem;border:1px solid #e2e8f0;">
        <p style="font-size:.875rem;color:#64748b;margin-bottom:.75rem;">Still have questions about NepalSchoolHub school management software?</p>
        <a href="https://wa.me/9779767585122?text={{ urlencode('Hello! I have a question about NepalSchoolHub.') }}"
           target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:.5rem;background:#25d366;color:#fff;padding:.65rem 1.5rem;border-radius:.75rem;font-weight:700;font-size:.875rem;text-decoration:none;">
            <i class="bi bi-whatsapp"></i> Ask on WhatsApp — instant reply
        </a>
    </div>
</div>
</section>
{{-- ══ CTA BAND ════════════════════════════════════════════════════════ --}}
<section class="cta-band">
    <div style="max-width:700px;margin:0 auto">
        <div style="font-size:2.5rem;margin-bottom:1rem">🎓</div>
        <h2 style="font-size:clamp(1.75rem,3.5vw,2.5rem);font-weight:900;margin-bottom:1rem">
            Ready to modernize your school?
        </h2>
        <p style="color:rgba(255,255,255,.7);font-size:1rem;max-width:500px;margin:0 auto 2rem;line-height:1.6">
            Join Nepali schools already running on NepalSchoolHub. Start free, upgrade when you're ready.
        </p>
        <a href="{{ route('saas.register') }}"
           style="display:inline-flex;align-items:center;gap:.6rem;background:#fff;color:#4338ca;padding:1rem 2.5rem;border-radius:.875rem;font-weight:800;font-size:1rem;transition:.2s"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <i class="bi bi-rocket-takeoff-fill"></i> Register Your School Now — It's Free
        </a>
    </div>
</section>

{{-- ══ FOOTER ══════════════════════════════════════════════════════════ --}}
</main>
<footer role="contentinfo">
    <div class="footer-inner">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:2rem">
            <div>
                <div class="footer-logo">
                    <img src="{{ asset('imgs/logo_icon.png') }}" alt="Logo">
                    <span>NepalSchoolHub</span>
                </div>
                <p style="font-size:.8rem;max-width:260px;line-height:1.6">
                    The all-in-one school management platform built for Nepali schools.
                </p>
            </div>
            <div style="display:flex;gap:3rem;flex-wrap:wrap">
                <div>
                    <p style="font-size:.7rem;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem">Product</p>
                    <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.8rem">
                        <a href="#features">Features</a>
                        <a href="#modules">Modules</a>
                        <a href="#pricing">Pricing</a>
                        <a href="#contact">Contact Us</a>
                    </div>
                </div>
                <div>
                    <p style="font-size:.7rem;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem">Account</p>
                    <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.8rem">
                        <a href="{{ route('login') }}">Sign In</a>
                        <a href="{{ route('saas.register') }}">Register School</a>
                    </div>
                </div>
            </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,.08);margin-top:2rem;padding-top:1.25rem;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.75rem;font-size:.75rem">
            <p>Built with ❤️ for Nepali schools &middot; &copy; {{ date('Y') }} NepalSchoolHub</p>
            <div style="display:flex;gap:1rem">
                <a href="{{ route('login') }}" style="color:#64748b" onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#64748b'">Login</a>
                <a href="{{ route('saas.register') }}" style="color:#64748b" onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#64748b'">Register</a>
            </div>
        </div>
    </div>
</footer>

<style>
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
@keyframes wa-bounce{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}
@keyframes wa-pop{0%{opacity:0;transform:scale(.6) translateY(10px)}100%{opacity:1;transform:scale(1) translateY(0)}}
.wa-float{
    position:fixed;bottom:28px;right:28px;z-index:9999;
    display:flex;flex-direction:column;align-items:flex-end;gap:10px;
}
.wa-btn{
    width:58px;height:58px;border-radius:50%;
    background:linear-gradient(135deg,#25d366,#128c7e);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:26px;
    box-shadow:0 4px 20px rgba(37,211,102,.45);
    cursor:pointer;border:none;
    animation:wa-bounce 2.5s ease-in-out infinite;
    transition:transform .2s,box-shadow .2s;
    text-decoration:none;
}
.wa-btn:hover{
    transform:scale(1.1)!important;
    box-shadow:0 6px 28px rgba(37,211,102,.6);
    animation:none;
}
.wa-tooltip{
    background:#0f172a;color:#fff;
    font-size:.78rem;font-weight:600;
    padding:.45rem .9rem;border-radius:.6rem;
    white-space:nowrap;
    box-shadow:0 4px 16px rgba(0,0,0,.2);
    animation:wa-pop .3s ease;
    pointer-events:none;
}
.wa-pulse-ring{
    position:absolute;top:0;left:0;right:0;bottom:0;
    border-radius:50%;
    border:3px solid rgba(37,211,102,.5);
    animation:wa-ring 2s ease-out infinite;
}
@keyframes wa-ring{
    0%{transform:scale(1);opacity:.8}
    100%{transform:scale(1.7);opacity:0}
}
</style>

{{-- ── WhatsApp Floating Button ── --}}
<div class="wa-float" id="wa-float">
    <div class="wa-tooltip" id="wa-tip">💬 Chat with us — instant reply!</div>
    <a href="https://wa.me/9779767585122?text={{ urlencode('Hello! I am interested in NepalSchoolHub. Can you help me?') }}"
       target="_blank" rel="noopener" class="wa-btn" title="Chat on WhatsApp"
       onclick="document.getElementById('wa-tip').style.display='none'">
        <div class="wa-pulse-ring"></div>
        <i class="bi bi-whatsapp"></i>
    </a>
</div>

<script>
// Auto-hide tooltip after 5s, show again on hover
(function(){
    var tip = document.getElementById('wa-tip');
    var btn = document.querySelector('.wa-btn');
    if(!tip || !btn) return;
    setTimeout(function(){ tip.style.opacity='0'; tip.style.transition='opacity .4s'; }, 5000);
    setTimeout(function(){ tip.style.display='none'; }, 5400);
    btn.addEventListener('mouseenter', function(){
        tip.style.display='block';
        tip.style.opacity='1';
    });
    btn.addEventListener('mouseleave', function(){
        tip.style.opacity='0';
        setTimeout(function(){ tip.style.display='none'; }, 400);
    });
})();
</script>

<script>
// ── Feature Detail Modal ──────────────────────────────────────────────
var _featData = JSON.parse(document.getElementById('feat-data').textContent);

function openFeatModal(idx) {
    var f   = _featData[idx];
    var ov  = document.getElementById('featModalOverlay');
    var ico = document.getElementById('featModalIcon');
    var tit = document.getElementById('featModalTitle');
    var sub = document.getElementById('featModalSubtitle');
    var det = document.getElementById('featModalDetails');

    ico.style.background = f.bg;
    ico.style.color      = f.color;
    ico.innerHTML        = '<i class="bi ' + f.icon + '"></i>';
    tit.textContent      = f.title;
    sub.textContent      = f.desc;

    det.innerHTML = f.details.map(function(d) {
        return '<div class="feat-detail-item"><i class="bi bi-check2-circle"></i><span>' + d + '</span></div>';
    }).join('');

    ov.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeFeatModal(e) {
    if (e && e.target !== document.getElementById('featModalOverlay') && !e.target.classList.contains('feat-modal-close')) return;
    document.getElementById('featModalOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFeatModal({target: document.getElementById('featModalOverlay')});
        closeModModal({target: document.getElementById('modModalOverlay')});
    }
});

// ── Module Detail Modal ───────────────────────────────────────────────
var _modData = JSON.parse(document.getElementById('mod-data').textContent);

function openModModal(idx) {
    var m   = _modData[idx];
    var ov  = document.getElementById('modModalOverlay');
    var ico = document.getElementById('modModalIcon');
    var tit = document.getElementById('modModalTitle');
    var sub = document.getElementById('modModalSubtitle');
    var det = document.getElementById('modModalDetails');

    ico.style.background = '#eef2ff';
    ico.style.color      = m.color;
    ico.innerHTML        = '<i class="bi ' + m.icon + '"></i>';
    tit.textContent      = m.num + '. ' + m.title;
    sub.textContent      = m.desc;

    det.innerHTML = m.details.map(function(d) {
        return '<div class="feat-detail-item"><i class="bi bi-check2-circle" style="color:' + m.color + '"></i><span>' + d + '</span></div>';
    }).join('');

    ov.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModModal(e) {
    if (e && e.target !== document.getElementById('modModalOverlay') && !e.target.classList.contains('feat-modal-close')) return;
    document.getElementById('modModalOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
</script>

</body>
</html>
