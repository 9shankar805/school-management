<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Services\PushNotificationService;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function __construct(
        private SmsService              $sms,
        private WhatsAppService         $whatsapp,
        private PushNotificationService $push,
    ) {
        $this->middleware('auth');
    }

    /**
     * The main communication hub dashboard
     */
    public function index()
    {
        $this->authorize('view notices');
        return view('communication.index');
    }

    /**
     * UI for Bulk Announcement System
     */
    public function bulk()
    {
        $this->authorize('create notices');
        return view('communication.bulk');
    }

    /**
     * Process and send a bulk announcement via selected channels.
     *
     * POST /communication/bulk/send
     */
    public function sendBulk(Request $request)
    {
        $this->authorize('create notices');

        $data = $request->validate([
            'subject'     => 'required|string|max:200',
            'message'     => 'required|string|max:1000',
            'audience'    => 'required|array|min:1',
            'audience.*'  => 'in:students,parents,teachers,staff,all',
            'channels'    => 'required|array|min:1',
            'channels.*'  => 'in:in_app,email,sms,whatsapp,push',
        ]);

        // Resolve target users
        $roles = collect($data['audience'])->flatMap(fn($a) => match ($a) {
            'students' => ['student'],
            'parents'  => ['parent'],
            'teachers' => ['teacher', 'class-teacher'],
            'staff'    => ['admin', 'accountant', 'librarian', 'hr-manager', 'receptionist',
                           'transport-manager', 'hostel-manager', 'attendance-officer', 'admission-officer'],
            'all'      => ['student', 'parent', 'teacher', 'class-teacher', 'admin'],
            default    => [],
        })->unique()->values()->all();

        $users = User::role($roles)->get();

        $results = [
            'in_app'    => 0,
            'email'     => 0,
            'sms'       => 0,
            'whatsapp'  => 0,
            'push'      => 0,
        ];

        $channels = $data['channels'];

        foreach ($users as $user) {
            // In-app notification
            if (in_array('in_app', $channels, true)) {
                $user->notify(new GeneralNotification(
                    title:   $data['subject'],
                    message: $data['message'],
                    type:    'announcement',
                    link:    null,
                ));
                $results['in_app']++;
            }

            // Email (uses queue)
            if (in_array('email', $channels, true) && $user->email) {
                // GeneralNotification supports mail channel
                $results['email']++;
            }

            // SMS
            if (in_array('sms', $channels, true) && $user->phone) {
                $body = "{$data['subject']}: {$data['message']}";
                if ($this->sms->send($user->phone, $body)) {
                    $results['sms']++;
                }
            }

            // WhatsApp
            if (in_array('whatsapp', $channels, true) && $user->phone) {
                $body = "*{$data['subject']}*\n{$data['message']}";
                $this->whatsapp->setSchool(auth()->user()->school)->setModule('announcement');
                if ($this->whatsapp->sendText($user->phone, $body)) {
                    $results['whatsapp']++;
                }
            }
        }

        // Push broadcast (by role segments)
        if (in_array('push', $channels, true)) {
            foreach ($data['audience'] as $segment) {
                $this->push->broadcast($segment, $data['subject'], $data['message']);
            }
            $results['push'] = $users->count();
        }

        return back()->with('status',
            "Announcement sent — In-app: {$results['in_app']}, SMS: {$results['sms']}, WhatsApp: {$results['whatsapp']}, Push: {$results['push']}."
        );
    }

    /**
     * UI for Individual WhatsApp message
     *
     * GET /communication/individual
     */
    public function individual(Request $request)
    {
        $this->authorize('create notices');

        // Load the selected user when returning after a successful send
        $selectedUser = null;
        if ($request->filled('user_id')) {
            $selectedUser = User::find((int) $request->user_id);
        }

        return view('communication.individual', compact('selectedUser'));
    }

    /**
     * AJAX — search users by name or phone for the individual-send autocomplete.
     *
     * GET /communication/individual/search?q=…
     */
    public function searchUsers(Request $request)
    {
        $this->authorize('create notices');

        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::where(function ($query) use ($q) {
                $query->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$q}%"])
                      ->orWhere('phone', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%");
            })
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('first_name')
            ->limit(15)
            ->get(['id', 'first_name', 'last_name', 'phone', 'role'])
            ->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->full_name,
                'phone' => $u->phone,
                'role'  => $u->primary_role ?? ($u->role ?? 'user'),
            ]);

        return response()->json($users);
    }

    /**
     * Send a WhatsApp message to one specific person.
     *
     * POST /communication/individual/send
     */
    public function sendIndividual(Request $request)
    {
        $this->authorize('create notices');

        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|min:5|max:1000',
        ]);

        $recipient = User::findOrFail($data['user_id']);

        if (! $recipient->phone) {
            return back()
                ->withInput()
                ->with('error', "No phone number on record for {$recipient->full_name}.");
        }

        $school   = auth()->user()->school;
        $sent     = $this->whatsapp
                         ->setSchool($school)
                         ->setModule('individual')
                         ->sendText($recipient->phone, $data['message']);

        if ($sent) {
            return redirect()
                ->route('communication.individual', ['user_id' => $recipient->id])
                ->with('status', "WhatsApp message sent to {$recipient->full_name} ({$recipient->phone}).");
        }

        return back()
            ->withInput()
            ->with('error', "Failed to send to {$recipient->full_name}. Check Zavu credentials and phone format.");
    }

    /**
     * UI for live Chat / Discussion Forums
     */
    public function chat()
    {
        return view('communication.chat');
    }

    /**
     * UI for configuring Gateway API keys (Twilio, WhatsApp, Mail, Push)
     */
    public function settings()
    {
        $this->authorize('view settings');
        return view('communication.settings');
    }

    /**
     * WhatsApp webhook verification (GET from Meta)
     */
    public function whatsappWebhookVerify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $result = $this->whatsapp->verifyWebhook($mode, $token, $challenge);
        if ($result !== false) {
            return response($result, 200);
        }
        return response('Forbidden', 403);
    }

    /**
     * WhatsApp incoming message webhook (POST from Meta)
     */
    public function whatsappWebhookReceive(Request $request)
    {
        $payload = $request->all();
        $parsed  = $this->whatsapp->parseWebhook($payload);

        if ($parsed) {
            // Store the incoming message and notify relevant staff
            try {
                \Illuminate\Support\Facades\Log::info('WhatsApp incoming message', $parsed);

                $from    = $parsed['from']    ?? null;
                $body    = $parsed['message'] ?? $parsed['text'] ?? $parsed['body'] ?? '';
                $msgId   = $parsed['message_id'] ?? $parsed['id'] ?? null;

                if ($from && $body) {
                    // Record the inbound message in whats_app_messages as 'received'
                    \App\Models\WhatsAppMessage::create([
                        'school_id'       => null, // may be resolved by from-number lookup
                        'recipient_phone' => $from,
                        'module'          => 'inbound',
                        'status'          => 'received',
                        'error_message'   => null,
                    ]);

                    // Try to find the user (parent/student) by phone number
                    $user = \App\Models\User::where('phone', 'like', '%' . substr($from, -9))
                        ->first();

                    // Notify school admins via database notification
                    if ($user) {
                        $admins = \App\Models\User::role(['admin', 'principal', 'receptionist'])
                            ->whereNotNull('school_id')
                            ->when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
                            ->take(5)
                            ->get();

                        foreach ($admins as $admin) {
                            $admin->notify(new \App\Notifications\GeneralNotification(
                                title:     'WhatsApp Message Received',
                                message:   'From: ' . ($user->full_name ?? $from) . ' — ' . \Str::limit($body, 80),
                                actionUrl: null,
                            ));
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('WhatsApp webhook handler error: ' . $e->getMessage());
            }
        }

        return response('OK', 200);
    }
}
