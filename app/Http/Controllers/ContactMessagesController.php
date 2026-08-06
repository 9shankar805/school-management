<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessagesController extends Controller
{
    public function __construct()
    {
        // Auth + role enforced by route group middleware
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = ContactMessage::latest();

        if ($status !== 'all') {
            $query->byStatus($status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('email',       'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('subject',     'like', "%{$search}%")
                  ->orWhere('message',     'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20)->withQueryString();

        $counts = [
            'all'      => ContactMessage::count(),
            'unread'   => ContactMessage::where('status', 'unread')->count(),
            'read'     => ContactMessage::where('status', 'read')->count(),
            'replied'  => ContactMessage::where('status', 'replied')->count(),
            'archived' => ContactMessage::where('status', 'archived')->count(),
        ];

        return view('super-admin.contact-messages.index', compact('messages', 'counts', 'status', 'search'));
    }

    public function show(ContactMessage $contactMessage)
    {
        // Auto-mark as read when opened
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('super-admin.contact-messages.show', compact('contactMessage'));
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'status' => 'required|in:unread,read,replied,archived',
        ]);

        $contactMessage->update(['status' => $request->status]);

        return back()->with('success', 'Message status updated.');
    }

    public function saveNotes(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $contactMessage->update(['admin_notes' => $request->admin_notes]);

        return back()->with('success', 'Notes saved.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('super-admin.contact-messages.index')
                         ->with('success', 'Message deleted.');
    }
}
