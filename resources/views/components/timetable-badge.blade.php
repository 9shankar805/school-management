@props([
    'subject' => '',
    'color' => null,
    'size' => 'md',
])

@php
    $subLower = strtolower($subject);
    
    // Exact icon & color scheme matching reference design
    if (str_contains($subLower, 'math') || str_contains($subLower, 'algebra') || str_contains($subLower, 'geometry') || str_contains($subLower, 'ganit') || str_contains($subLower, 'hisab')) {
        $bgColor = '#E0F2FE'; // Soft blue
        $textColor = '#0284C7'; // Blue icon
        $borderColor = '#BAE6FD';
        $icon = 'bi-calculator';
        $isCode = false;
    } elseif (str_contains($subLower, 'eng') || str_contains($subLower, 'lit') || str_contains($subLower, 'grammar') || str_contains($subLower, 'read')) {
        $bgColor = '#DBEAFE'; // Light indigo/blue
        $textColor = '#2563EB'; // Blue icon
        $borderColor = '#BFDBFE';
        $icon = 'bi-book';
        $isCode = false;
    } elseif (str_contains($subLower, 'sci') || str_contains($subLower, 'phys') || str_contains($subLower, 'chem') || str_contains($subLower, 'bio') || str_contains($subLower, 'vigyan')) {
        $bgColor = '#E0F2FE'; // Soft cyan
        $textColor = '#0284C7'; // Cyan/Blue icon
        $borderColor = '#BAE6FD';
        $icon = 'bi-flask';
        $isCode = false;
    } elseif (str_contains($subLower, 'nep') || str_contains($subLower, 'hindi') || str_contains($subLower, 'lang') || str_contains($subLower, 'sansk') || str_contains($subLower, 'bhasa')) {
        $bgColor = '#DCFCE7'; // Soft mint green
        $textColor = '#16A34A'; // Green icon
        $borderColor = '#BBF7D0';
        $icon = 'bi-flower1';
        $isCode = false;
    } elseif (str_contains($subLower, 'soc') || str_contains($subLower, 'hist') || str_contains($subLower, 'geog') || str_contains($subLower, 'civic') || str_contains($subLower, 'samajik') || str_contains($subLower, 'moral')) {
        $bgColor = '#F3E8FF'; // Soft purple
        $textColor = '#9333EA'; // Purple icon
        $borderColor = '#E9D5FF';
        $icon = 'bi-globe';
        $isCode = false;
    } elseif (str_contains($subLower, 'comp') || str_contains($subLower, 'it') || str_contains($subLower, 'code') || str_contains($subLower, 'tech') || str_contains($subLower, 'info')) {
        $bgColor = '#EDE9FE'; // Soft lavender
        $textColor = '#7C3AED'; // Deep purple/violet
        $borderColor = '#DDD6FE';
        $icon = 'bi-code-slash';
        $isCode = true;
    } elseif (str_contains($subLower, 'phys') || str_contains($subLower, 'sport') || str_contains($subLower, 'gym') || str_contains($subLower, 'pt') || str_contains($subLower, 'health') || str_contains($subLower, 'game') || str_contains($subLower, 'drill') || str_contains($subLower, 'p.e') || str_contains($subLower, 'pe')) {
        $bgColor = '#FFEDD5'; // Soft orange/peach
        $textColor = '#EA580C'; // Orange icon
        $borderColor = '#FED7AA';
        $icon = 'bi-person-walking';
        $isCode = false;
    } elseif (str_contains($subLower, 'art') || str_contains($subLower, 'draw') || str_contains($subLower, 'music') || str_contains($subLower, 'craft') || str_contains($subLower, 'dance')) {
        $bgColor = '#FCE7F3'; // Pink
        $textColor = '#DB2777';
        $borderColor = '#FBCFE8';
        $icon = 'bi-palette';
        $isCode = false;
    } else {
        $bgColor = '#F1F5F9';
        $textColor = '#64748B';
        $borderColor = '#E2E8F0';
        $icon = 'bi-journal-text';
        $isCode = false;
    }
@endphp

<div {{ $attributes->merge(['class' => "w-8 h-8 rounded-lg inline-flex items-center justify-center flex-shrink-0 shadow-2xs"]) }}
     style="background-color: {{ $bgColor }} !important; color: {{ $textColor }} !important; border: 1px solid {{ $borderColor }} !important;">
    @if($isCode)
        <i class="bi bi-code-slash text-xs font-bold" style="color: {{ $textColor }} !important;"></i>
    @else
        <i class="bi {{ $icon }} text-xs" style="color: {{ $textColor }} !important;"></i>
    @endif
</div>
