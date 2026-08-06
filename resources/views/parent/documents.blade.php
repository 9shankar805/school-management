@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Student Documents'])

        @include('session-messages')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800">Uploaded Documents</h3>
                        <span class="text-xs font-semibold text-slate-500 bg-white px-2 py-1 rounded shadow-sm border border-slate-200">
                            {{ count($documents) }} Files
                        </span>
                    </div>
                    
                    <div class="p-0">
                        @forelse($documents as $doc)
                            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl shrink-0">
                                        @if(Str::contains($doc->mime_type, 'pdf'))
                                            <i class="bi bi-file-earmark-pdf-fill text-rose-500"></i>
                                        @elseif(Str::contains($doc->mime_type, 'image'))
                                            <i class="bi bi-file-image-fill text-blue-500"></i>
                                        @else
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-800 truncate">{{ $doc->title }}</h4>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                                            <span class="uppercase tracking-wider font-semibold text-slate-400">{{ $doc->document_type }}</span>
                                            <span>•</span>
                                            <span>{{ number_format($doc->file_size / 1024, 1) }} KB</span>
                                            <span>•</span>
                                            <span>{{ $doc->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 ml-4">
                                    @if($doc->is_verified)
                                        <span class="hidden sm:inline-flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                                            <i class="bi bi-check-circle-fill"></i> Verified
                                        </span>
                                    @else
                                        <span class="hidden sm:inline-flex items-center gap-1 text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-md">
                                            <i class="bi bi-hourglass-split"></i> Pending
                                        </span>
                                    @endif
                                    <a href="{{ route('parent.documents.download', ['studentId' => $child->id, 'id' => $doc->id]) }}" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bi bi-folder2-open text-2xl text-slate-400"></i>
                                </div>
                                <h3 class="font-bold text-slate-700 mb-1">No Documents Uploaded</h3>
                                <p class="text-slate-500 text-sm">Upload birth certificates, previous transcripts, or ID cards here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Upload Form --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 sticky top-6">
                    <h3 class="font-bold text-slate-800 mb-1">Upload New Document</h3>
                    <p class="text-sm text-slate-500 mb-6">Supported formats: PDF, JPG, PNG (Max 5MB).</p>

                    <form action="{{ route('parent.documents.upload', $child->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Document Type</label>
                            <select name="document_type" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="birth_certificate">Birth Certificate</option>
                                <option value="previous_transcript">Previous Transcript</option>
                                <option value="id_card">ID Card / Passport</option>
                                <option value="medical_certificate">Medical Certificate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Document Title</label>
                            <input type="text" name="title" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Birth Certificate - Translated" required>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">File</label>
                            <input type="file" name="file" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i class="bi bi-cloud-arrow-up"></i> Upload Document
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
