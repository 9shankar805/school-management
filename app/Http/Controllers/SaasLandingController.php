<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SaasLandingController extends Controller
{
    public function index(Request $request)
    {
        $host = strtolower($request->getHost());
        // Automatically check if request is for a school subdomain (e.g. genesisglobalschool.nshlms.com)
        if (preg_match('/^([a-z0-9\-]+)\.(nshlms\.com|nepalschoolhub\.com|techprocod\.com\.np|localhost)$/i', $host, $matches)) {
            $subdomain = strtolower($matches[1]);
            if (!in_array($subdomain, ['www', 'app', 'admin', 'super', 'api', 'mail', 'blog', 'support'])) {
                return app(SchoolLandingController::class)->showBySubdomain($subdomain);
            }
        }

        $allPlans = School::planLimits();
        $plans = [
            'free'   => $allPlans['free'],
            'yearly' => $allPlans['yearly'] ?? ['students' => 9999, 'teachers' => 999, 'staff' => 500, 'ai' => true, 'price_npr' => 15000],
        ];
        $schoolCount = School::where('status', 'active')->count();
        return view('saas.landing', compact('plans', 'schoolCount'));
    }

    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|max:150',
            'school_name' => 'nullable|string|max:150',
            'subject'     => 'required|string|max:100',
            'message'     => 'required|string|min:10|max:2000',
        ]);

        // Save to database so super admin can view in inbox
        $contactMessage = ContactMessage::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'school_name' => $validated['school_name'] ?? null,
            'subject'     => $validated['subject'],
            'message'     => $validated['message'],
            'status'      => 'unread',
            'ip_address'  => $request->ip(),
        ]);

        // Destination for incoming contact messages
        $adminEmail = config('mail.admin_address') 
            ?: env('ADMIN_EMAIL', env('MAIL_USERNAME', 'nepalschoolhub@gmail.com'));
        $fromAddress = config('mail.from.address', env('MAIL_USERNAME', 'nepalschoolhub@gmail.com'));
        $fromName    = config('mail.from.name', 'Nepal School Hub');

        $schoolNameDisplay = !empty($validated['school_name']) ? e($validated['school_name']) : '<span style="color:#94a3b8;font-style:italic;">Not provided</span>';
        $submissionTime = now()->format('F j, Y - g:i A') . ' (NPT)';
        $ip = e($request->ip() ?? 'Unknown');
        $safeName = e($validated['name']);
        $safeEmail = e($validated['email']);
        $safeSubject = e($validated['subject']);
        $safeMessage = nl2br(e($validated['message']));
        $replyMailto = 'mailto:' . urlencode($validated['email']) . '?subject=' . urlencode('Re: [NepalSchoolHub] ' . $validated['subject']);

        // 1. Send detailed inquiry notification to Admin Gmail
        try {
            $adminEmailHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>New Contact Message - NepalSchoolHub</title>
            </head>
            <body style='margin:0;padding:0;background-color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Helvetica,Arial,sans-serif;color:#334155;'>
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color:#f8fafc;padding:32px 16px;'>
                    <tr>
                        <td align='center'>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width:620px;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.06);overflow:hidden;border:1px solid #e2e8f0;'>
                                <!-- Header -->
                                <tr>
                                    <td style='background:linear-gradient(135deg,#4f46e5 0%,#3730a3 100%);padding:28px 32px;text-align:left;'>
                                        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                            <tr>
                                                <td>
                                                    <span style='background:rgba(255,255,255,0.2);color:#ffffff;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;padding:4px 10px;border-radius:20px;display:inline-block;margin-bottom:8px;'>Contact Us Inquiry</span>
                                                    <h1 style='margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.02em;line-height:1.2;'>New Message from {$safeName}</h1>
                                                    <p style='margin:6px 0 0;color:#c7d2fe;font-size:13px;'>Received via nshlms.com landing page</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Details Table -->
                                <tr>
                                    <td style='padding:28px 32px;'>
                                        <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom:24px;'>
                                            <tr>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;width:120px;font-weight:600;font-size:13px;color:#64748b;'>Full Name:</td>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-weight:700;font-size:14px;color:#0f172a;'>{$safeName}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-weight:600;font-size:13px;color:#64748b;'>Email Address:</td>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#4f46e5;font-weight:600;'><a href='mailto:{$safeEmail}' style='color:#4f46e5;text-decoration:none;'>{$safeEmail}</a></td>
                                            </tr>
                                            <tr>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-weight:600;font-size:13px;color:#64748b;'>School Name:</td>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#0f172a;'>{$schoolNameDisplay}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-weight:600;font-size:13px;color:#64748b;'>Subject:</td>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#0f172a;font-weight:700;'><span style='background:#eef2ff;color:#4338ca;padding:3px 8px;border-radius:6px;'>{$safeSubject}</span></td>
                                            </tr>
                                            <tr>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-weight:600;font-size:13px;color:#64748b;'>Submitted At:</td>
                                                <td style='padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;color:#64748b;'>{$submissionTime}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding:8px 0;font-weight:600;font-size:13px;color:#64748b;'>Sender IP:</td>
                                                <td style='padding:8px 0;font-size:13px;color:#94a3b8;font-family:monospace;'>{$ip}</td>
                                            </tr>
                                        </table>

                                        <!-- Message Body Box -->
                                        <div style='margin-bottom:28px;'>
                                            <div style='font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;'>Message Content:</div>
                                            <div style='background-color:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #4f46e5;border-radius:8px;padding:16px 20px;font-size:14px;line-height:1.65;color:#1e293b;'>
                                                {$safeMessage}
                                            </div>
                                        </div>

                                        <!-- Direct Reply CTA -->
                                        <div style='text-align:center;padding:12px 0 6px;'>
                                            <a href='{$replyMailto}' style='background:linear-gradient(135deg,#4f46e5,#4338ca);color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 28px;border-radius:8px;display:inline-block;box-shadow:0 4px 12px rgba(79,70,229,0.3);'>
                                                &larr; Reply to {$safeName}
                                            </a>
                                            <p style='font-size:12px;color:#94a3b8;margin-top:10px;'>Tip: You can also hit 'Reply' directly on this email in your Gmail.</p>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td style='background-color:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;text-align:center;'>
                                        <p style='margin:0 0 4px;font-size:12px;font-weight:700;color:#475569;'>NepalSchoolHub Portal Management</p>
                                        <p style='margin:0;font-size:11px;color:#94a3b8;'>Kathmandu, Nepal &bull; <a href='https://nshlms.com' style='color:#6366f1;text-decoration:none;'>nshlms.com</a> &bull; Support: +977 9744911550</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ";

            Mail::html($adminEmailHtml, function ($mail) use ($validated, $adminEmail, $fromAddress, $fromName) {
                $mail->to($adminEmail)
                     ->from($fromAddress, $fromName)
                     ->replyTo($validated['email'], $validated['name'])
                     ->subject('[NepalSchoolHub Contact] ' . $validated['subject'] . ' - ' . $validated['name']);
            });
        } catch (\Throwable $e) {
            Log::error('Admin contact notification email failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // 2. Send professional auto-confirmation email to the inquirer
        try {
            $userEmailHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>We Received Your Message - Nepal School Hub</title>
            </head>
            <body style='margin:0;padding:0;background-color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Helvetica,Arial,sans-serif;color:#334155;'>
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color:#f8fafc;padding:32px 16px;'>
                    <tr>
                        <td align='center'>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width:600px;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.06);overflow:hidden;border:1px solid #e2e8f0;'>
                                <!-- Header -->
                                <tr>
                                    <td style='background:linear-gradient(135deg,#4f46e5 0%,#3730a3 100%);padding:30px 32px;text-align:center;'>
                                        <h1 style='margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.02em;'>Nepal School Hub</h1>
                                        <p style='margin:6px 0 0;color:#c7d2fe;font-size:13px;'>Nepal's Complete Cloud School ERP Platform</p>
                                    </td>
                                </tr>

                                <!-- Content -->
                                <tr>
                                    <td style='padding:32px;'>
                                        <h2 style='margin:0 0 12px;color:#0f172a;font-size:18px;font-weight:700;'>Thank you for reaching out, {$safeName}!</h2>
                                        <p style='margin:0 0 18px;color:#475569;font-size:14px;line-height:1.6;'>
                                            We have received your message regarding <strong>\"{$safeSubject}\"</strong>. Our support team is reviewing your inquiry and will get back to you within <strong>24 hours</strong>.
                                        </p>

                                        <div style='background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:24px;'>
                                            <div style='font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:6px;'>Your Message Summary:</div>
                                            <div style='font-size:13px;color:#334155;line-height:1.5;'>{$safeMessage}</div>
                                        </div>

                                        <div style='border-top:1px solid #f1f5f9;padding-top:18px;'>
                                            <p style='font-size:13px;color:#64748b;margin:0 0 8px;font-weight:600;'>Need urgent assistance?</p>
                                            <p style='font-size:13px;color:#475569;margin:0 0 4px;'>
                                                &bull; <strong>WhatsApp / Phone:</strong> <a href='https://wa.me/9779744911550' style='color:#4f46e5;text-decoration:none;'>+977 9744911550</a>
                                            </p>
                                            <p style='font-size:13px;color:#475569;margin:0;'>
                                                &bull; <strong>Direct Email:</strong> <a href='mailto:support@nshlms.com' style='color:#4f46e5;text-decoration:none;'>support@nshlms.com</a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td style='background-color:#f8fafc;padding:18px 32px;border-top:1px solid #e2e8f0;text-align:center;'>
                                        <p style='margin:0;font-size:11px;color:#94a3b8;'>&copy; " . date('Y') . " Nepal School Hub &bull; Kathmandu, Nepal &bull; <a href='https://nshlms.com' style='color:#6366f1;text-decoration:none;'>nshlms.com</a></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ";

            Mail::html($userEmailHtml, function ($mail) use ($validated, $fromAddress, $fromName) {
                $mail->to($validated['email'], $validated['name'])
                     ->from($fromAddress, $fromName)
                     ->replyTo($fromAddress, $fromName)
                     ->subject('Thank you for contacting Nepal School Hub');
            });
        } catch (\Throwable $e) {
            Log::warning('User confirmation email failed: ' . $e->getMessage());
        }

        return redirect()->route('saas.landing', ['#contact'])
                         ->with('contact_success', 'Thank you, ' . $validated['name'] . '! Your message has been received. We\'ll get back to you within 24 hours.');
    }
}
