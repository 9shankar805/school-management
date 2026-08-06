{{--
  Stat Card component
  Usage:
    <x-stat-card
        label="Students"
        :value="$studentCount"
        icon="bi-people-fill"
        icon-color="indigo"
        sub="Enrolled this session"
        trend="up"
        trend-text="+12 this month"
        :href="route('student.list.show')"
    />

  icon-color options: indigo | blue | green | emerald | violet | amber | rose | pink | teal | slate
--}}
@props([
    'label'      => 'Stat',
    'value'      => '—',
    'icon'       => 'bi-circle',
    'iconColor'  => 'indigo',
    'sub'        => null,
    'trend'      => null,       // 'up' | 'down' | null
    'trendText'  => null,
    'href'       => null,
    'format'     => true,       // number_format the value if numeric
])

@php
$colorMap = [
    'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30',  'text' => 'text-indigo-600 dark:text-indigo-400'],
    'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/30',      'text' => 'text-blue-600 dark:text-blue-400'],
    'green'  => ['bg' => 'bg-green-50 dark:bg-green-900/30',    'text' => 'text-green-600 dark:text-green-400'],
    'emerald'=> ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30','text' => 'text-emerald-600 dark:text-emerald-400'],
    'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/30',  'text' => 'text-violet-600 dark:text-violet-400'],
    'amber'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',    'text' => 'text-amber-600 dark:text-amber-400'],
    'rose'   => ['bg' => 'bg-rose-50 dark:bg-rose-900/30',      'text' => 'text-rose-600 dark:text-rose-400'],
    'pink'   => ['bg' => 'bg-pink-50 dark:bg-pink-900/30',      'text' => 'text-pink-600 dark:text-pink-400'],
    'teal'   => ['bg' => 'bg-teal-50 dark:bg-teal-900/30',      'text' => 'text-teal-600 dark:text-teal-400'],
    'slate'  => ['bg' => 'bg-slate-100 dark:bg-slate-800',      'text' => 'text-slate-600 dark:text-slate-400'],
];
$colors     = $colorMap[$iconColor] ?? $colorMap['indigo'];
$displayVal = ($format && is_numeric($value)) ? number_format((float)$value) : $value;
$tag        = $href ? 'a' : 'div';
$tagAttr    = $href ? "href=\"{$href}\"" : '';
@endphp

<{{ $tag }} {{ $tagAttr }} class="stat-card group {{ $href ? 'cursor-pointer' : '' }}">
    <div class="flex items-center justify-between">
        <span class="stat-label">{{ $label }}</span>
        <span class="stat-icon {{ $colors['bg'] }} {{ $colors['text'] }} flex-shrink-0">
            <i class="bi {{ $icon }}"></i>
        </span>
    </div>

    <div class="stat-value">{{ $displayVal }}</div>

    @if($sub || $trendText)
    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
        @if($trendText && $trend === 'up')
            <span class="stat-trend-up flex items-center gap-0.5">
                <i class="bi bi-arrow-up-short"></i>{{ $trendText }}
            </span>
        @elseif($trendText && $trend === 'down')
            <span class="stat-trend-down flex items-center gap-0.5">
                <i class="bi bi-arrow-down-short"></i>{{ $trendText }}
            </span>
        @endif
        @if($sub)
            <span class="stat-sub">{{ $sub }}</span>
        @endif
    </div>
    @endif
</{{ $tag }}>
