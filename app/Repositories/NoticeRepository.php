<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepository {
    public function store($data) {
        try {
            $attachmentPath = null;
            if (request()->hasFile('attachment')) {
                $attachmentPath = request()->file('attachment')->store('notices', 'public');
            }

            return Notice::create([
                'title'           => $data['title'] ?? null,
                'priority'        => $data['priority'] ?? 'normal',
                'audience'        => $data['audience'] ?? 'all',
                'expiry_date'     => $data['expiry_date'] ?? null,
                'notice'          => $data['notice'],
                'attachment_path' => $attachmentPath,
                'published_by'    => auth()->id(),
                'session_id'      => $data['session_id'],
                'school_id'       => auth()->user()->school_id ?? 1,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to save Notice. '.$e->getMessage());
        }
    }

    public function getAll($session_id, $audience = null, $search = null) {
        $query = Notice::with('publisher')
            ->where('session_id', $session_id)
            ->latest('id');

        if ($audience && $audience !== 'all') {
            $query->where(function ($q) use ($audience) {
                $q->where('audience', $audience)
                  ->orWhere('audience', 'all');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notice', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15);
    }
}