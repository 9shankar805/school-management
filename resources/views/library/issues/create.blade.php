@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-box-arrow-up-right"></i> Issue Book</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.issues.index') }}">Issues</a></li>
                            <li class="breadcrumb-item active">Issue Book</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Issue Details</div>
                                <div class="card-body">
                                    <form action="{{ route('library.issues.store') }}" method="POST" id="issueForm">
                                        @csrf

                                        {{-- QR / Barcode scanner strip --}}
                                        <div class="card bg-light mb-3 p-3">
                                            <div class="small fw-semibold mb-2"><i class="bi bi-qr-code-scan"></i> Quick Lookup (Scan barcode / member card)</div>
                                            <div class="row g-2">
                                                <div class="col">
                                                    <input type="text" id="barcodeInput" class="form-control form-control-sm" placeholder="Scan book barcode or ISBN…">
                                                </div>
                                                <div class="col">
                                                    <input type="text" id="cardInput" class="form-control form-control-sm" placeholder="Scan member card number…">
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" class="btn btn-secondary btn-sm" id="scanBtn">Lookup</button>
                                                </div>
                                            </div>
                                            <div id="scanResult" class="mt-2 small text-success"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Book <span class="text-danger">*</span></label>
                                            <select name="book_id" id="bookSelect" class="form-select" required>
                                                <option value="">— Select a book —</option>
                                                @foreach($books as $book)
                                                    <option value="{{ $book->id }}"
                                                            data-available="{{ $book->available_qty }}"
                                                            @selected(old('book_id', $selectedBook?->id) == $book->id)>
                                                        {{ $book->title }}
                                                        ({{ $book->author }})
                                                        — Available: {{ $book->available_qty }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="bookAvailNote" class="form-text"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Member <span class="text-danger">*</span></label>
                                            <select name="member_id" id="memberSelect" class="form-select" required>
                                                <option value="">— Select a member —</option>
                                                @foreach($members as $member)
                                                    <option value="{{ $member->id }}"
                                                            data-quota="{{ $member->remaining_quota }}"
                                                            @selected(old('member_id', $selectedMember?->id) == $member->id)>
                                                        {{ $member->user->first_name }} {{ $member->user->last_name }}
                                                        [{{ $member->card_number }}]
                                                        — Quota: {{ $member->remaining_quota }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="memberQuotaNote" class="form-text"></div>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                                                <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Fine Per Day ($)</label>
                                                <input type="number" name="fine_per_day" class="form-control" value="{{ old('fine_per_day', '1.00') }}" min="0" step="0.01">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Notes</label>
                                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Issue Book</button>
                                            <a href="{{ route('library.issues.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold"><i class="bi bi-info-circle"></i> Issue Rules</div>
                                <div class="card-body small text-muted">
                                    <ul class="mb-0 ps-3">
                                        <li>Only active members can borrow books.</li>
                                        <li>Member must have remaining quota.</li>
                                        <li>Book must have available stock.</li>
                                        <li>Same book cannot be issued twice to same member.</li>
                                        <li>Due date is calculated from issue date + member's loan period.</li>
                                        <li>Fine applies per day after due date.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Book availability note
document.getElementById('bookSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const avail = parseInt(opt.dataset.available || 0);
    const note = document.getElementById('bookAvailNote');
    note.textContent = this.value ? 'Available copies: ' + avail : '';
    note.className = 'form-text ' + (avail <= 0 ? 'text-danger' : 'text-success');
});

// Member quota note
document.getElementById('memberSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const quota = parseInt(opt.dataset.quota || 0);
    const note = document.getElementById('memberQuotaNote');
    note.textContent = this.value ? 'Remaining quota: ' + quota : '';
    note.className = 'form-text ' + (quota <= 0 ? 'text-danger' : 'text-success');
});

// QR scan lookup
document.getElementById('scanBtn').addEventListener('click', function() {
    const barcode = document.getElementById('barcodeInput').value.trim();
    const card    = document.getElementById('cardInput').value.trim();
    if (!barcode && !card) return;

    fetch('{{ route("library.issues.scan") }}?barcode=' + encodeURIComponent(barcode) + '&card=' + encodeURIComponent(card))
        .then(r => r.json())
        .then(data => {
            const result = document.getElementById('scanResult');
            let msg = '';
            if (data.book) {
                document.getElementById('bookSelect').value = data.book.id;
                msg += '✓ Book: ' + data.book.title + ' (Avail: ' + data.book.available_qty + ')  ';
            }
            if (data.member) {
                document.getElementById('memberSelect').value = data.member.id;
                msg += '✓ Member: ' + data.member.name + ' (Quota: ' + data.member.quota + ')';
            }
            if (!data.book && !data.member) {
                msg = 'No match found.';
                result.className = 'mt-2 small text-danger';
            } else {
                result.className = 'mt-2 small text-success';
            }
            result.textContent = msg;
        })
        .catch(() => {
            document.getElementById('scanResult').textContent = 'Lookup failed.';
        });
});

// Trigger initial notes if pre-selected
document.getElementById('bookSelect').dispatchEvent(new Event('change'));
document.getElementById('memberSelect').dispatchEvent(new Event('change'));
</script>
@endpush
