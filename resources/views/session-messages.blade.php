{{--
  Modern session flash messages.
  Drop-in replacement for the old Bootstrap-based version.
  Usage: @include('session-messages')
--}}
@if(session('status') || session('success'))
<div class="alert-modern alert-success mb-4" data-auto-close="5000">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
    <span>{{ session('status') ?? session('success') }}</span>
    <button onclick="this.closest('.alert-modern').remove()" class="ms-auto p-0.5 rounded hover:opacity-70 transition" aria-label="Dismiss">
        <i class="bi bi-x"></i>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert-modern alert-error mb-4" data-auto-close="7000">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
    <span>{{ session('error') }}</span>
    <button onclick="this.closest('.alert-modern').remove()" class="ms-auto p-0.5 rounded hover:opacity-70 transition" aria-label="Dismiss">
        <i class="bi bi-x"></i>
    </button>
</div>
@endif

@if(session('warning'))
<div class="alert-modern alert-warning mb-4" data-auto-close="6000">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
    <span>{{ session('warning') }}</span>
    <button onclick="this.closest('.alert-modern').remove()" class="ms-auto p-0.5 rounded hover:opacity-70 transition" aria-label="Dismiss">
        <i class="bi bi-x"></i>
    </button>
</div>
@endif

@if(session('info'))
<div class="alert-modern alert-info mb-4" data-auto-close="5000">
    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
    <span>{{ session('info') }}</span>
    <button onclick="this.closest('.alert-modern').remove()" class="ms-auto p-0.5 rounded hover:opacity-70 transition" aria-label="Dismiss">
        <i class="bi bi-x"></i>
    </button>
</div>
@endif

@if(isset($errors) && $errors->any())
<div class="alert-modern alert-error mb-4">
    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
    <div class="flex-1 min-w-0">
        @if($errors->count() === 1)
            {{ $errors->first() }}
        @else
            <p class="font-medium mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif
