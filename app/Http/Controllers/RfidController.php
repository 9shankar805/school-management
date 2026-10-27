<?php

namespace App\Http\Controllers;

use App\Jobs\SendAbsentParentNotification;
use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\RfidCard;
use App\Models\RfidScan;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Traits\SchoolSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * RFID / Biometric attendance controller.
 *
 * Web routes (admin panel):
 *   /rfid/devices      — manage registered scanners
 *   /rfid/cards        — assign RFID cards to users
 *   /rfid/scans        — scan log viewer
 *
 * API endpoint (called by hardware devices):
 *   POST /api/rfid/scan  — receives a scan event and marks attendance
 */
class RfidController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware('auth')->except(['incomingScan']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Device management
    // ─────────────────────────────────────────────────────────────────────────

    public function devices()
    {
        $this->authorize('manage settings');
        $devices = BiometricDevice::latest()->paginate(20);
        return view('rfid.devices', compact('devices'));
    }

    public function storeDevice(Request $request)
    {
        $this->authorize('manage settings');
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'serial_number' => 'required|string|max:100|unique:biometric_devices,serial_number',
            'location'      => 'nullable|string|max:100',
            'type'          => 'required|in:rfid,fingerprint,face,iris,barcode',
            'ip_address'    => 'nullable|ip',
        ]);
        $data['api_key'] = bin2hex(random_bytes(32)); // 64-char hex key
        BiometricDevice::create($data);
        return back()->with('status', 'Device registered. API key generated.');
    }

    public function toggleDevice(int $id)
    {
        $this->authorize('manage settings');
        $device = BiometricDevice::findOrFail($id);
        $device->update(['is_active' => ! $device->is_active]);
        return back()->with('status', 'Device status toggled.');
    }

    public function destroyDevice(int $id)
    {
        $this->authorize('manage settings');
        BiometricDevice::findOrFail($id)->delete();
        return back()->with('status', 'Device removed.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RFID Card management
    // ─────────────────────────────────────────────────────────────────────────

    public function cards(Request $request)
    {
        $this->authorize('manage settings');
        $cards = RfidCard::with('user')->latest()->paginate(30);
        $users = User::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'role']);
        return view('rfid.cards', compact('cards', 'users'));
    }

    public function storeCard(Request $request)
    {
        $this->authorize('manage settings');
        $data = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'card_uid'  => 'required|string|max:64|unique:rfid_cards,card_uid',
            'card_type' => 'required|in:rfid,nfc,barcode',
            'expires_at'=> 'nullable|date|after:today',
            'notes'     => 'nullable|string|max:255',
        ]);
        RfidCard::create(array_merge($data, ['is_active' => true]));
        return back()->with('status', 'Card assigned to user.');
    }

    public function revokeCard(int $id)
    {
        $this->authorize('manage settings');
        RfidCard::findOrFail($id)->update(['is_active' => false]);
        return back()->with('status', 'Card revoked.');
    }

    public function destroyCard(int $id)
    {
        $this->authorize('manage settings');
        RfidCard::findOrFail($id)->delete();
        return back()->with('status', 'Card deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scan log viewer
    // ─────────────────────────────────────────────────────────────────────────

    public function scans(Request $request)
    {
        $this->authorize('view attendances');
        $scans = RfidScan::with(['device', 'user'])
            ->when($request->date, fn($q) => $q->whereDate('scanned_at', $request->date))
            ->when($request->device_id, fn($q) => $q->where('device_id', $request->device_id))
            ->latest('scanned_at')
            ->paginate(50)
            ->withQueryString();

        $devices = BiometricDevice::orderBy('name')->get();
        return view('rfid.scans', compact('scans', 'devices'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Incoming scan from hardware device (API endpoint)
    // POST /api/rfid/scan
    // Headers: X-Device-Key: {api_key}
    // Body:    { card_uid, direction, scanned_at?, biometric_template_id? }
    // ─────────────────────────────────────────────────────────────────────────

    public function incomingScan(Request $request): JsonResponse
    {
        // 1. Authenticate device
        $apiKey = $request->header('X-Device-Key') ?? $request->input('api_key');
        $device = BiometricDevice::findByApiKey((string) $apiKey);

        if (! $device) {
            return response()->json(['error' => 'Invalid or inactive device key.'], 401);
        }

        // 2. Validate payload
        $data = $request->validate([
            'card_uid'               => 'nullable|string|max:64',
            'biometric_template_id'  => 'nullable|string|max:100',
            'direction'              => 'nullable|in:in,out,unknown',
            'scanned_at'             => 'nullable|date',
        ]);

        if (empty($data['card_uid']) && empty($data['biometric_template_id'])) {
            return response()->json(['error' => 'card_uid or biometric_template_id is required.'], 422);
        }

        $scannedAt = isset($data['scanned_at'])
            ? Carbon::parse($data['scanned_at'])
            : Carbon::now();

        // 3. Resolve user from RFID card
        $userId = null;
        if (! empty($data['card_uid'])) {
            $card = RfidCard::where('card_uid', $data['card_uid'])
                ->where('is_active', true)
                ->first();

            if (! $card) {
                $this->logScan($device->id, null, $data, $scannedAt, false);
                return response()->json(['error' => 'Card not registered or inactive.'], 404);
            }

            if ($card->is_expired) {
                $this->logScan($device->id, null, $data, $scannedAt, false);
                return response()->json(['error' => 'Card has expired.'], 403);
            }

            $userId = $card->user_id;
        }

        if (! $userId) {
            // Biometric template lookup — placeholder for fingerprint/face engines
            // The biometric device SDK calls a 1:N match; the template ID comes back
            // from that match. Store raw for manual reconciliation.
            $this->logScan($device->id, null, $data, $scannedAt, false);
            return response()->json(['status' => 'biometric_logged', 'message' => 'Scan logged; biometric matching pending.'], 202);
        }

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // 4. Mark attendance (only for "in" direction or unknown on school-day entry)
        $attendanceId = null;
        $marked       = false;

        if (in_array($data['direction'] ?? 'unknown', ['in', 'unknown'], true)) {
            $sessionId = $this->resolveSessionId();
            $marked    = $this->markAttendance($user, $scannedAt, $sessionId, $attendanceId);
        }

        // 5. Log the scan
        $scan = $this->logScan($device->id, $userId, $data, $scannedAt, $marked, $attendanceId);

        // 6. Notify parents in real-time on Smart Gate Arrival / Departure (Veda Model)
        $this->notifyParentsGateActivity($user, $data['direction'] ?? 'unknown', $scannedAt, $device);

        // 7. Update device last_seen
        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'status'         => 'ok',
            'user_id'        => $userId,
            'user_name'      => $user->fullName,
            'attendance'     => $marked ? 'marked_present' : 'skipped',
            'scan_id'        => $scan->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** Mark student/staff present via RFID scan */
    private function markAttendance(User $user, Carbon $scannedAt, ?int $sessionId, ?int &$attendanceId): bool
    {
        try {
            // Prevent duplicate attendance for the same student on the same day
            $existing = Attendance::where('student_id', $user->id)
                ->where('session_id', $sessionId)
                ->whereDate('date', $scannedAt->toDateString())
                ->first();

            if ($existing) {
                $attendanceId = $existing->id;
                return false; // already marked
            }

            $attendance = Attendance::create([
                'student_id'  => $user->id,
                'session_id'  => $sessionId,
                'date'        => $scannedAt->toDateString(),
                'status'      => 'on',
                'type'        => 'rfid',
                'late_minutes'=> $this->calcLateMinutes($scannedAt),
            ]);

            $attendanceId = $attendance->id;
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RFID markAttendance failed', [
                'user_id' => $user->id, 'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function logScan(
        int $deviceId,
        ?int $userId,
        array $data,
        Carbon $scannedAt,
        bool $marked,
        ?int $attendanceId = null
    ): RfidScan {
        return RfidScan::create([
            'device_id'              => $deviceId,
            'user_id'                => $userId,
            'card_uid'               => $data['card_uid'] ?? null,
            'biometric_template_id'  => $data['biometric_template_id'] ?? null,
            'scanned_at'             => $scannedAt,
            'direction'              => $data['direction'] ?? 'unknown',
            'attendance_marked'      => $marked,
            'attendance_id'          => $attendanceId,
            'raw_payload'            => json_encode($data),
        ]);
    }

    /** Calculate late minutes against default school start time (08:00) */
    private function calcLateMinutes(Carbon $scannedAt): int
    {
        $schoolStart = Carbon::parse($scannedAt->format('Y-m-d') . ' 08:00:00');
        return max(0, (int) $scannedAt->diffInMinutes($schoolStart, false) * -1);
    }

    private function resolveSessionId(): ?int
    {
        try {
            return $this->getSchoolCurrentSession();
        } catch (\Throwable) {
            return \App\Models\SchoolSession::latest('id')->value('id');
        }
    }

    /**
     * Notify student parents of arrival/departure through Smart Gate / RFID.
     */
    private function notifyParentsGateActivity(User $user, string $direction, Carbon $scannedAt, BiometricDevice $device): void
    {
        try {
            // Only trigger for students
            if (! $user->hasRole('student')) {
                return;
            }

            $parents = $user->parents;
            if ($parents->isEmpty()) {
                return;
            }

            $timeStr  = $scannedAt->format('h:i A');
            $gateName = $device->name ?: ($device->location ?: 'Main Gate');

            if (in_array($direction, ['in', 'unknown'], true)) {
                $title   = "Campus Arrival: {$user->fullName}";
                $message = "Dear Parent, {$user->fullName} arrived safely at the school campus at {$timeStr} (Gate: {$gateName}).";
            } else {
                $title   = "Campus Departure: {$user->fullName}";
                $message = "Dear Parent, {$user->fullName} has departed from the school campus at {$timeStr} (Gate: {$gateName}).";
            }

            foreach ($parents as $parent) {
                $parent->notify(new GeneralNotification(
                    title: $title,
                    message: $message,
                    type: 'attendance',
                    actionUrl: '/parent/' . $user->id . '/attendance',
                    actionText: 'View Attendance Record',
                    sendEmail: false
                ));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('RFID notifyParentsGateActivity failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
