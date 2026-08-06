@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-3xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Salary Structure</h1>
        <p class="text-xs text-slate-500 mt-1">Set component amounts for <strong>{{ $staff->full_name }}</strong></p>
    </div>

    @include('session-messages')

    <form method="POST" action="{{ route('finance.salary.structure.save', $staff->id) }}">
        @csrf

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden mb-4">
            <div class="px-5 py-3 bg-emerald-50 dark:bg-emerald-900/20 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-emerald-700 dark:text-emerald-400"><i class="bi bi-arrow-up-circle mr-2"></i>Earnings</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($components->where('type','earning') as $i => $comp)
                <div class="flex items-center px-5 py-3 gap-4">
                    <input type="hidden" name="components[{{ $i }}][component_id]" value="{{ $comp->id }}">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $comp->name }}</div>
                        <div class="text-xs text-slate-400">{{ $comp->code }} · {{ ucfirst($comp->calculation) }}</div>
                    </div>
                    <div class="w-36">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                                {{ $comp->calculation === 'percentage' ? '%' : 'Rs.' }}
                            </span>
                            <input type="number" step="0.01" min="0"
                                   name="components[{{ $i }}][amount]"
                                   value="{{ $structures->get($comp->id)?->amount ?? $comp->default_value }}"
                                   class="input-modern w-full pl-9 text-right">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden mb-5">
            <div class="px-5 py-3 bg-rose-50 dark:bg-rose-900/20 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400"><i class="bi bi-arrow-down-circle mr-2"></i>Deductions</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @php $offset = $components->where('type','earning')->count(); @endphp
                @foreach($components->where('type','deduction') as $j => $comp)
                @php $idx = $offset + $j; @endphp
                <div class="flex items-center px-5 py-3 gap-4">
                    <input type="hidden" name="components[{{ $idx }}][component_id]" value="{{ $comp->id }}">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $comp->name }}</div>
                        <div class="text-xs text-slate-400">{{ $comp->code }} · {{ ucfirst($comp->calculation) }}</div>
                    </div>
                    <div class="w-36">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                                {{ $comp->calculation === 'percentage' ? '%' : 'Rs.' }}
                            </span>
                            <input type="number" step="0.01" min="0"
                                   name="components[{{ $idx }}][amount]"
                                   value="{{ $structures->get($comp->id)?->amount ?? $comp->default_value }}"
                                   class="input-modern w-full pl-9 text-right">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm">
                <i class="bi bi-save mr-1"></i> Save Structure
            </button>
            <a href="{{ route('finance.salary.index') }}" class="btn-secondary px-5 py-2.5 rounded-xl text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
