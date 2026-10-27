@extends('school-landing.layout')

@section('title', 'Campus Life & Visual Photo Gallery')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Gallery</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Campus Visual Gallery</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            A visual chronicle of our vibrant scholastic community: academic excellence, state-of-the-art laboratories, sports tournaments, and cultural festivities.
        </p>
    </div>
</div>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Category Filters --}}
        <div class="flex items-center justify-center gap-2 sm:gap-3 flex-wrap mb-12" id="galleryFilterNav">
            <button onclick="filterGallery('all')" class="gallery-filter-btn active px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white transition shadow-sm">
                All Moments
            </button>
            <button onclick="filterGallery('campus')" class="gallery-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition shadow-sm">
                Campus & Architecture
            </button>
            <button onclick="filterGallery('stem')" class="gallery-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition shadow-sm">
                STEM & Labs
            </button>
            <button onclick="filterGallery('sports')" class="gallery-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition shadow-sm">
                Sports & Athletics
            </button>
            <button onclick="filterGallery('cultural')" class="gallery-filter-btn px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 transition shadow-sm">
                Annual Fest & Arts
            </button>
        </div>

        @php
            $items = [
                ['url' => asset('imgs/hero/hero_1.webp'), 'cat' => 'campus', 'title' => 'Main Academic Quadrangle & Campus', 'tag' => 'Infrastructure'],
                ['url' => asset('imgs/hero/hero_2.webp'), 'cat' => 'stem', 'title' => 'Advanced Digital STEM & Computing Lab', 'tag' => 'Experiential Learning'],
                ['url' => asset('imgs/hero/hero_3.webp'), 'cat' => 'sports', 'title' => 'Olympic Standard Athletic Track & Sports Ground', 'tag' => 'Athletics & ECA'],
                ['url' => asset('imgs/kathmandu.webp'), 'cat' => 'campus', 'title' => 'Kathmandu Valley Academic Excursion', 'tag' => 'Field Expedition'],
                ['url' => asset('imgs/schools.webp'), 'cat' => 'cultural', 'title' => 'Annual Scholastic Day & Awards Ceremony', 'tag' => 'Cultural Fest'],
                ['url' => asset('imgs/school_connection.webp'), 'cat' => 'cultural', 'title' => 'Student Mentorship & Collaborative Projects', 'tag' => 'Scholastic Life'],
            ];

            if ($school->gallery_images && is_array($school->gallery_images) && count($school->gallery_images) > 0) {
                foreach($school->gallery_images as $idx => $img) {
                    $items[] = [
                        'url' => asset('storage/' . $img),
                        'cat' => $idx % 2 === 0 ? 'campus' : 'cultural',
                        'title' => 'Official Campus Event Record #' . ($idx + 1),
                        'tag' => 'Campus Record'
                    ];
                }
            }
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="galleryGrid">
            @foreach($items as $item)
            <div class="gallery-card group relative rounded-2xl overflow-hidden shadow-sm border border-slate-200 bg-white aspect-[4/3] card-lift" data-cat="{{ $item['cat'] }}">
                <img src="{{ $item['url'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-90 group-hover:opacity-100 transition">
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <span class="text-[10px] uppercase font-bold tracking-widest bg-slate-900/80 backdrop-blur-sm px-2.5 py-1 rounded-md mb-2 inline-block border border-white/20">
                            {{ $item['tag'] }}
                        </span>
                        <h4 class="font-bold text-sm leading-snug">{{ $item['title'] }}</h4>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

@push('scripts')
<script>
function filterGallery(cat) {
    const btns = document.querySelectorAll('.gallery-filter-btn');
    btns.forEach(b => {
        b.classList.remove('active', 'bg-slate-900', 'text-white');
        b.classList.add('bg-slate-100', 'text-slate-800');
    });

    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active', 'bg-slate-900', 'text-white');
        event.currentTarget.classList.remove('bg-slate-100', 'text-slate-800');
    }

    const cards = document.querySelectorAll('.gallery-card');
    cards.forEach(card => {
        const cardCat = card.getAttribute('data-cat');
        if (cat === 'all' || cardCat === cat) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
@endpush

@endsection
