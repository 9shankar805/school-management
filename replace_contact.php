<?php
$filePath = '/var/www/school-management/resources/views/saas/landing.blade.php';
$content = file_get_contents($filePath);

// Replace "Contact" with "Contact Us" in navbar
$content = preg_replace('/<a href="#contact">Contact<\/a>/', '<a href="#contact">Contact Us</a>', $content);

$contactSection = <<<HTML
{{-- ══ CONTACT ═════════════════════════════════════════════════════════ --}}
<section class="section" id="contact" style="background:#f8fafc; padding: 4rem 2rem;">
    <div class="section-inner">
        <style>
            .contact-wrapper {
                display: flex;
                flex-direction: column;
                background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0.4) 100%);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-radius: 24px;
                border: 1px solid rgba(255,255,255,0.5);
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
                overflow: hidden;
            }
            @media (min-width: 768px) {
                .contact-wrapper {
                    flex-direction: row;
                }
            }
            .contact-info {
                flex: 1;
                padding: 3rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .contact-title {
                font-size: 2.5rem;
                font-weight: 800;
                margin-bottom: 1rem;
                line-height: 1.2;
                margin-top: 0;
            }
            .contact-subtitle {
                font-size: 1.1rem;
                opacity: 0.9;
                margin-bottom: 3rem;
                line-height: 1.6;
            }
            .contact-details {
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }
            .contact-item {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }
            .contact-icon-wrapper {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 50px;
                height: 50px;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                backdrop-filter: blur(10px);
                transition: transform 0.3s ease, background 0.3s ease;
                flex-shrink: 0;
            }
            .contact-item:hover .contact-icon-wrapper {
                transform: scale(1.1);
                background: rgba(255,255,255,0.3);
            }
            .contact-item-title {
                font-size: 1.2rem;
                font-weight: 600;
                margin: 0 0 0.25rem 0;
            }
            .contact-item-text {
                margin: 0;
                opacity: 0.8;
                font-size: 1rem;
            }
            .contact-form-container {
                flex: 1.2;
                padding: 3rem;
                background: white;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .form-title {
                font-size: 2rem;
                font-weight: 700;
                color: #2d3748;
                margin-bottom: 2rem;
                margin-top: 0;
            }
            .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
            }
            .form-label {
                display: block;
                font-size: 0.95rem;
                font-weight: 600;
                color: #4a5568;
                margin-bottom: 0.5rem;
            }
            .form-input {
                width: 100%;
                padding: 0.8rem 1.2rem;
                font-size: 1rem;
                font-family: inherit;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                background: #f7fafc;
                transition: all 0.3s ease;
                outline: none;
                box-sizing: border-box;
                color: #2d3748;
            }
            .form-input:focus {
                border-color: #667eea;
                background: white;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            }
            .submit-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-size: 1.1rem;
                font-weight: 600;
                padding: 1rem 2rem;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                width: 100%;
                margin-top: 1rem;
                box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
            }
            .submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 25px rgba(118, 75, 162, 0.3);
            }
            .submit-btn:active {
                transform: translateY(0);
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
                            <p class="contact-item-text"><a href="tel:+9779744911550" style="color:white; text-decoration:none;">+977 9744911550</a></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                @if(session('contact_success'))
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;">
                    <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.1rem;"></i>
                    <p style="font-size:.875rem;color:#166534;font-weight:500;margin:0;">{{ session('contact_success') }}</p>
                </div>
                @endif
    
                @if(\$errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
                    <p style="font-size:.8rem;color:#dc2626;font-weight:600;margin-bottom:.4rem;">Please fix the following:</p>
                    @foreach(\$errors->all() as \$error)
                    <p style="font-size:.8rem;color:#dc2626;margin:0;">• {{ \$error }}</p>
                    @endforeach
                </div>
                @endif

                <form class="contact-form" action="{{ route('saas.contact') }}" method="POST">
                    @csrf
                    <h3 class="form-title">Send us a Message</h3>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
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
HTML;

$startMarker = '{{-- ══ CONTACT ═════════════════════════════════════════════════════════ --}}';
$endMarker = '</section>';

$startPos = strpos($content, $startMarker);
if ($startPos !== false) {
    // Find the next </section> after the start marker
    $endPos = strpos($content, $endMarker, $startPos);
    if ($endPos !== false) {
        $endPos += strlen($endMarker);
        $before = substr($content, 0, $startPos);
        $after = substr($content, $endPos);
        
        $newContent = $before . $contactSection . $after;
        file_put_contents($filePath, $newContent);
        echo "Contact section replaced successfully.\n";
    } else {
        echo "Could not find end marker.\n";
    }
} else {
    echo "Could not find start marker.\n";
}
