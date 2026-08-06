@extends('layouts.app')
@section('title', 'Two-Factor Authentication Setup')
@section('content')
<div class="container" style="max-width:480px">
    <div class="row justify-content-center pt-5">
        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold text-center py-3">
                    <i class="bi bi-shield-lock fs-4 text-primary"></i>
                    <div>Set Up Two-Factor Authentication</div>
                </div>
                <div class="card-body p-4">
                    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

                    @if(!$tfa->isConfirmed())
                    <p class="text-muted small mb-3">
                        Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.)
                        then enter the 6-digit code to confirm.
                    </p>

                    <div class="text-center mb-3">
                        <img src="{{ $qrUrl }}" alt="TOTP QR Code" class="rounded border p-2" width="200" height="200">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Or enter this key manually:</label>
                        <div class="input-group">
                            <input type="text" id="secretKey" class="form-control form-control-sm font-monospace"
                                   value="{{ $plainSecret }}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                    onclick="navigator.clipboard.writeText('{{ $plainSecret }}');this.textContent='Copied!'">
                                Copy
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('two-factor.confirm') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Verification Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-center font-monospace fs-5 letter-spacing-2"
                                   maxlength="6" pattern="\d{6}" placeholder="000000" autocomplete="one-time-code" autofocus required>
                            @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Enable 2FA</button>
                    </form>

                    @else
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-shield-check"></i> Two-factor authentication is <strong>active</strong>.
                    </div>
                    <p class="text-muted small">Enabled on {{ $tfa->confirmed_at->format('d M Y') }}.</p>

                    <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Confirm your password to disable 2FA</label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Are you sure? Disabling 2FA reduces your account security.')">
                            Disable 2FA
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
