<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TC Verification — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; }
        .verify-card { max-width: 640px; margin: 60px auto; }
        .verified-badge { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px; }
    </style>
</head>
<body>
<div class="verify-card">
    <div class="bg-white rounded-4 shadow-sm border p-5 text-center">

        @if($transfer)
            <div class="verified-badge" style="background:#d1fae5;">
                <i class="bi bi-patch-check-fill" style="color:#059669;"></i>
            </div>
            <h3 class="fw-bold mb-1" style="color:#065f46;">Authentic Certificate</h3>
            <p class="text-muted small mb-4">This Transfer Certificate is genuine and was issued by {{ $transfer->fromSchool?->name ?? 'the issuing school' }}.</p>

            <div class="text-start rounded-3 border p-4 mb-4">
                <table class="table table-sm small mb-0">
                    <tr><td class="text-muted fw-semibold" style="width:45%;">TC Number</td><td class="fw-bold">{{ $transfer->tc_number }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Student Name</td><td>{{ $transfer->student?->first_name }} {{ $transfer->student?->last_name }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Issuing School</td><td>{{ $transfer->source_school_name }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Transfer Date</td><td>{{ $transfer->transfer_date?->format('d F Y') }}</td></tr>
                    <tr><td class="text-muted fw-semibold">TC Issued On</td><td>{{ $transfer->tc_issued_at?->format('d F Y H:i') }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Destination</td><td>{{ $transfer->destination_school_name }}</td></tr>
                    <tr><td class="text-muted fw-semibold">Verification Token</td><td><code class="small">{{ $transfer->tc_token }}</code></td></tr>
                </table>
            </div>

            <div class="alert border small text-start mb-0" style="background:#f0fdf4;border-color:#bbf7d0!important;">
                <i class="bi bi-info-circle-fill me-2" style="color:#059669;"></i>
                This certificate was digitally verified against the official records of
                <strong>{{ config('app.name') }}</strong>. If you have concerns about its authenticity, contact
                <a href="mailto:{{ $transfer->fromSchool?->email ?? '' }}">{{ $transfer->fromSchool?->email ?? 'the issuing school' }}</a>.
            </div>

        @else
            <div class="verified-badge" style="background:#fee2e2;">
                <i class="bi bi-x-octagon-fill" style="color:#dc2626;"></i>
            </div>
            <h3 class="fw-bold mb-1 text-danger">Certificate Not Found</h3>
            <p class="text-muted small mb-4">
                The token <code>{{ $token }}</code> did not match any Transfer Certificate in our records.
                It may have been revoked, is invalid, or belongs to a different system.
            </p>
            <div class="alert alert-warning border small text-start">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                If you believe this is an error, contact the school that issued this certificate.
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ url('/') }}" class="btn btn-light border fw-semibold rounded-3">
                <i class="bi bi-house me-1"></i> Back to Home
            </a>
        </div>
    </div>
    <p class="text-center small text-muted mt-3">
        Verified by <strong>{{ config('app.name') }}</strong> &bull; {{ now()->format('d F Y H:i') }}
    </p>
</div>
</body>
</html>
