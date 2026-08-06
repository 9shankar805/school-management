@extends('layouts.app')
@section('title', 'Recovery Codes')
@section('content')
<div class="container" style="max-width:480px">
    <div class="row justify-content-center pt-5">
        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold text-center py-3">
                    <i class="bi bi-key fs-4 text-warning"></i>
                    <div>Save Your Recovery Codes</div>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning">
                        <strong>Important:</strong> Store these codes somewhere safe. Each code can only be used once.
                        If you lose access to your authenticator app, you can use these codes to sign in.
                    </div>

                    @if(session('recovery_codes'))
                    <div class="bg-light rounded p-3 font-monospace mb-3" id="recoveryCodes">
                        @foreach(session('recovery_codes') as $code)
                        <div class="py-1 border-bottom">{{ $code }}</div>
                        @endforeach
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyAll()">
                            <i class="bi bi-clipboard"></i> Copy All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                    @else
                    <p class="text-muted">Recovery codes are only shown once at setup. If you lost them, disable and re-enable 2FA to generate new codes.</p>
                    @endif

                    <a href="{{ route('home') }}" class="btn btn-primary w-100">I've saved my codes — Continue</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function copyAll() {
    const text = [...document.querySelectorAll('#recoveryCodes div')].map(d => d.textContent.trim()).join('\n');
    navigator.clipboard.writeText(text).then(() => alert('Codes copied to clipboard!'));
}
</script>
@endpush
@endsection
