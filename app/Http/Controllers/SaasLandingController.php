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
        ContactMessage::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'school_name' => $validated['school_name'] ?? null,
            'subject'     => $validated['subject'],
            'message'     => $validated['message'],
            'status'      => 'unread',
            'ip_address'  => $request->ip(),
        ]);

        // Send notification email to admin
        $adminEmail = config('mail.from.address', 'support@nepalschoolhub.com');

        try {
            Mail::send([], [], function ($mail) use ($validated, $adminEmail) {
                $body  = "<h2 style='font-family:sans-serif;color:#1e293b;'>New Contact Form Submission</h2>";
                $body .= "<table style='font-family:sans-serif;font-size:14px;color:#374151;border-collapse:collapse;' cellpadding='8'>";
                $body .= "<tr><td style='font-weight:600;color:#6366f1;'>Name:</td><td>" . e($validated['name']) . "</td></tr>";
                $body .= "<tr><td style='font-weight:600;color:#6366f1;'>Email:</td><td>" . e($validated['email']) . "</td></tr>";
                if (!empty($validated['school_name'])) {
                    $body .= "<tr><td style='font-weight:600;color:#6366f1;'>School:</td><td>" . e($validated['school_name']) . "</td></tr>";
                }
                $body .= "<tr><td style='font-weight:600;color:#6366f1;'>Subject:</td><td>" . e($validated['subject']) . "</td></tr>";
                $body .= "<tr><td style='font-weight:600;color:#6366f1;vertical-align:top;'>Message:</td><td>" . nl2br(e($validated['message'])) . "</td></tr>";
                $body .= "</table>";

                $mail->to($adminEmail)
                     ->replyTo($validated['email'], $validated['name'])
                     ->subject('[NepalSchoolHub] Contact: ' . $validated['subject'])
                     ->html($body);
            });
        } catch (\Throwable $e) {
            // Don't fail the user experience if mail is misconfigured — log and continue
            Log::warning('Contact form mail failed: ' . $e->getMessage());
        }

        return redirect()->route('saas.landing', ['#contact'])
                         ->with('contact_success', 'Thank you, ' . $validated['name'] . '! Your message has been received. We\'ll get back to you within 24 hours.');
    }
}
