{{--
  Breadcrumb component
  Usage:
    <x-breadcrumb :items="[
        ['label' => 'Home',    'url' => route('home')],
        ['label' => 'Students','url' => route('student.list.show')],
        ['label' => 'Profile'],          {{-- last item = current, no url needed --}}
    ]"/>
--}}
@props(['items' => []])

@if(count($items))
<nav aria-label="breadcrumb" class="breadcrumb-modern mb-0">
    @foreach($items as $i => $item)
        @if(!$loop->last)
            <a href="{{ $item['url'] ?? '#' }}">
                @if(isset($item['icon']))<i class="bi {{ $item['icon'] }} me-1"></i>@endif
                {{ $item['label'] }}
            </a>
            <span class="sep" aria-hidden="true"><i class="bi bi-chevron-right" style="font-size:.6rem"></i></span>
        @else
            <span class="current" aria-current="page">
                @if(isset($item['icon']))<i class="bi {{ $item['icon'] }} me-1"></i>@endif
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
@endif
