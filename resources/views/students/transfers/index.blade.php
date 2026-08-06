@extends('layouts.app')
@section('title', 'Student Transfers')

@section('content')
<style>
  .header-icon-box-transfer {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }
  .header-icon-box-transfer i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box-transfer">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Student Transfers & Migration</h2>
                    <p class="text-slate-500 text-sm mb-0">Track inter-class, inter-section, and inter-school student transfers</p>
                </div>
            </div>
        </div>

        <a href="{{ route('student.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to All Students
        </a>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Transfer Requests Directory</h5>
                <p class="text-xs text-slate-400 mb-0">Recent student section/class transfer applications</p>
            </div>
            <span class="badge bg-indigo-50 text-indigo-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Requests: {{ $transfers->total() }}
            </span>
        </div>

        @if($transfers->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Student Name</th>
                        <th class="py-3 px-4">From Class/Section</th>
                        <th class="py-3 px-4">To Target Class/Section</th>
                        <th class="py-3 px-4">Transfer Reason</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($transfers as $tr)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">
                                {{ $tr->student->first_name ?? '' }} {{ $tr->student->last_name ?? 'Student' }}
                            </div>
                            <div class="text-xs text-slate-400">ID: #{{ $tr->student_id }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-slate-100 text-slate-700 font-semibold text-xs px-2.5 py-1 rounded-lg">
                                {{ $tr->fromClass->class_name ?? 'N/A' }} {{ $tr->fromSection->section_name ? '('.$tr->fromSection->section_name.')' : '' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-indigo-100 text-indigo-700 font-semibold text-xs px-2.5 py-1 rounded-lg">
                                {{ $tr->toClass->class_name ?? 'N/A' }} {{ $tr->toSection->section_name ? '('.$tr->toSection->section_name.')' : '' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-xs font-medium">
                            {{ $tr->reason ?? 'Standard academic transfer' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($tr->status === 'approved')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Approved
                            </span>
                            @elseif($tr->status === 'pending')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-clock-history me-1"></i> Pending
                            </span>
                            @else
                            <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-x-circle-fill me-1"></i> Rejected
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            @if($tr->status === 'pending')
                            <form method="POST" action="{{ route('student.transfer.approve', $tr->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-emerald bg-emerald-600 text-white font-semibold text-xs py-1 px-2.5 rounded-lg shadow-sm">
                                    <i class="bi bi-check2 me-1"></i> Approve
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('student.transfer.destroy', $tr->id) }}" class="d-inline" onsubmit="return confirm('Remove transfer record?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-2.5 rounded-lg ms-1">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $transfers->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-arrow-left-right display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Student Transfer Records</h6>
            <p class="mb-0 text-xs">When a student requests a class/section transfer, it will appear here for administrative approval.</p>
        </div>
        @endif
    </div>
</div>
@endsection
