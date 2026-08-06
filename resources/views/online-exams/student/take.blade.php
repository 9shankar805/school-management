@extends('layouts.app')
@section('content')
<div class="flex-1 overflow-auto">

    {{-- Sticky exam header --}}
    <div class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm px-6 py-3 flex items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-base font-bold text-slate-800 truncate">{{ $exam->title }}</h1>
            <p class="text-xs text-slate-400">
                {{ $questions->count() }} questions
                @if($exam->is_practice)
                &middot; <span class="text-emerald-600 font-medium">Practice — no marks recorded</span>
                @else
                &middot; {{ $exam->total_marks }} marks &middot; Pass: {{ $exam->pass_marks }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-4 flex-shrink-0">
            {{-- Progress indicator --}}
            <div class="hidden sm:flex items-center gap-1" id="progress-dots">
                @foreach($questions as $i => $q)
                <div id="dot-{{ $q->id }}"
                     class="w-2.5 h-2.5 rounded-full border-2 transition
                     {{ ($savedAnswers[$q->id] ?? '') !== '' ? 'bg-indigo-500 border-indigo-500' : 'border-slate-300 bg-white' }}"
                     title="Q{{ $i+1 }}"></div>
                @endforeach
            </div>
            {{-- Timer --}}
            <div class="flex flex-col items-center">
                <div id="timer"
                     class="text-xl font-mono font-bold text-slate-800 tabular-nums transition">
                    {{ floor($remaining/60) }}:{{ str_pad($remaining%60,2,'0',STR_PAD_LEFT) }}
                </div>
                <span class="text-[10px] text-slate-400 uppercase tracking-wide">Time Left</span>
            </div>
        </div>
    </div>

    {{-- Instructions banner --}}
    @if($exam->instructions)
    <div class="mx-6 mt-4 bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 text-sm text-indigo-800 flex gap-2 items-start">
        <i class="bi bi-info-circle-fill mt-0.5 flex-shrink-0"></i>
        <p>{{ $exam->instructions }}</p>
    </div>
    @endif

    {{-- Questions --}}
    <form method="POST" action="{{ route('online-exams.student.submit', $attempt->id) }}" id="examForm"
          class="px-6 py-6 max-w-3xl mx-auto space-y-6">
        @csrf

        @foreach($questions as $i => $question)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" id="qcard-{{ $question->id }}">
            {{-- Question header --}}
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $i + 1 }}
                    </span>
                    <span class="text-xs font-medium text-slate-500 capitalize">
                        {{ str_replace('_', ' ', $question->question_type) }}
                    </span>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded-lg">
                    {{ $question->marks }} {{ Str::plural('mark', $question->marks) }}
                </span>
            </div>

            <div class="px-5 py-4">
                {{-- Question text --}}
                <p class="text-slate-800 font-medium mb-4 leading-relaxed">{{ $question->question_text }}</p>

                {{-- MCQ --}}
                @if($question->question_type === 'mcq' && $question->options)
                <div class="space-y-2">
                    @foreach($question->options as $idx => $option)
                    <label for="q{{ $question->id }}_opt{{ $idx }}"
                           class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 cursor-pointer
                                  hover:border-indigo-400 hover:bg-indigo-50 transition has-[:checked]:border-indigo-500
                                  has-[:checked]:bg-indigo-50 has-[:checked]:ring-2 has-[:checked]:ring-indigo-200">
                        <input type="radio"
                               name="answers[{{ $question->id }}]"
                               value="{{ $idx }}"
                               id="q{{ $question->id }}_opt{{ $idx }}"
                               class="accent-indigo-600 w-4 h-4 flex-shrink-0"
                               @checked(($savedAnswers[$question->id] ?? null) == $idx)
                               onchange="autoSave({{ $question->id }}, this.value); markAnswered({{ $question->id }})">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ chr(65 + $loop->index) }}
                        </span>
                        <span class="text-sm text-slate-700">{{ $option }}</span>
                    </label>
                    @endforeach
                </div>

                {{-- True / False --}}
                @elseif($question->question_type === 'true_false')
                <div class="grid grid-cols-2 gap-3">
                    @foreach(['true' => 'True', 'false' => 'False'] as $val => $label)
                    <label for="q{{ $question->id }}_{{ $val }}"
                           class="flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer
                                  hover:border-indigo-400 hover:bg-indigo-50 transition has-[:checked]:border-indigo-500
                                  has-[:checked]:bg-indigo-50 has-[:checked]:ring-2 has-[:checked]:ring-indigo-200 text-sm font-medium text-slate-700">
                        <input type="radio"
                               name="answers[{{ $question->id }}]"
                               value="{{ $val }}"
                               id="q{{ $question->id }}_{{ $val }}"
                               class="accent-indigo-600 w-4 h-4"
                               @checked(($savedAnswers[$question->id] ?? '') === $val)
                               onchange="autoSave({{ $question->id }}, this.value); markAnswered({{ $question->id }})">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                {{-- Short Answer --}}
                @elseif($question->question_type === 'short_answer')
                <input type="text"
                       name="answers[{{ $question->id }}]"
                       value="{{ $savedAnswers[$question->id] ?? '' }}"
                       placeholder="Type your answer…"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       oninput="autoSave({{ $question->id }}, this.value); markAnswered({{ $question->id }})">

                {{-- Essay --}}
                @else
                <textarea name="answers[{{ $question->id }}]"
                          rows="5"
                          placeholder="Write your detailed answer here…"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-y"
                          oninput="autoSave({{ $question->id }}, this.value); markAnswered({{ $question->id }})">{{ $savedAnswers[$question->id] ?? '' }}</textarea>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Submit bar --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-wrap items-center justify-between gap-4">
            <div class="text-sm text-slate-500">
                <span id="answered-count">0</span> of {{ $questions->count() }} answered
            </div>
            <button type="button" onclick="confirmSubmit()"
                    class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                <i class="bi bi-send-fill me-1"></i>
                {{ $exam->is_practice ? 'Finish Quiz' : 'Submit Exam' }}
            </button>
        </div>

    </form>
</div>

{{-- Confirm modal --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-2">
            {{ $exam->is_practice ? 'Finish Practice Quiz?' : 'Submit Exam?' }}
        </h2>
        <p class="text-sm text-slate-500 mb-5" id="confirmMsg">
            You have answered <span id="modal-answered">0</span> of {{ $questions->count() }} questions.
        </p>
        <div class="flex gap-3">
            <button onclick="document.getElementById('examForm').submit()"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">
                {{ $exam->is_practice ? 'Finish' : 'Submit' }}
            </button>
            <button onclick="document.getElementById('confirmModal').classList.add('hidden')"
                    class="flex-1 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm hover:bg-slate-50 transition">
                Continue
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Timer ──────────────────────────────────────────────────────────────────
let remaining = {{ $remaining }};
const timerEl = document.getElementById('timer');

(function tick() {
    if (remaining <= 0) {
        timerEl.textContent = '0:00';
        document.getElementById('examForm').submit();
        return;
    }
    const m = Math.floor(remaining / 60);
    const s = (remaining % 60).toString().padStart(2, '0');
    timerEl.textContent = `${m}:${s}`;
    // Turn red in last 5 minutes
    timerEl.classList.toggle('text-rose-600', remaining <= 300);
    timerEl.classList.toggle('text-slate-800', remaining > 300);
    remaining--;
    setTimeout(tick, 1000);
})();

// ── Progress tracking ──────────────────────────────────────────────────────
const answeredSet = new Set(
    @json(collect($savedAnswers)->filter(fn($v) => $v !== '' && $v !== null)->keys()->toArray())
);

function markAnswered(qId) {
    answeredSet.add(String(qId));
    const dot = document.getElementById('dot-' + qId);
    if (dot) {
        dot.classList.remove('border-slate-300', 'bg-white');
        dot.classList.add('bg-indigo-500', 'border-indigo-500');
    }
    document.getElementById('answered-count').textContent = answeredSet.size;
    document.getElementById('modal-answered').textContent  = answeredSet.size;
}

// Initialise count from saved
document.getElementById('answered-count').textContent = answeredSet.size;
document.getElementById('modal-answered').textContent  = answeredSet.size;

// ── Auto-save ──────────────────────────────────────────────────────────────
function autoSave(questionId, value) {
    fetch('{{ route("online-exams.student.save-answer", $attempt->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':  '{{ csrf_token() }}'
        },
        body: JSON.stringify({ question_id: questionId, answer_text: value })
    }).catch(() => {});
}

// ── Submit confirm ─────────────────────────────────────────────────────────
function confirmSubmit() {
    document.getElementById('confirmModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
