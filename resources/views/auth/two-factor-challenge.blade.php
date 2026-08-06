@extends('layouts.app')
@section('title', 'Two-Factor Authentication')
@section('content')
<div class="container" style="max-width:400px">
    <div class="row justify-content-center pt-5">
        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold text-center py-3">
                    <i class="bi bi-shield-lock-fill fs-4 text-primary"></i>
                    <div>Two-Factor Authentication</div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                    <div class="alert alert-danger small">{{ $errors->first() }}</div>
                    @endif

                    <p class="text-muted small text-center mb-4">
                        Enter the 6-digit code from your authenticator app, or use one of your recovery codes.
                    </p>

                    <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Verification Code</label>
                            <input type="text" name="code"
                                   class="form-control text-center font-monospace fs-4"
                                   maxlength="10"
                                   placeholder="000000"
                                   autocomplete="one-time-code"
                                   autofocus required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="small text-muted">Sign out</a>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
