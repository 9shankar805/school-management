<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ParentTeacherMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * GET /student-app/messages/conversations
     * All conversations for the student.
     */
    public function conversations(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        // Students participate in conversations where student_id = their id
        $conversations = Conversation::where('student_id', $studentId)
            ->with(['teacher:id,first_name,last_name,photo'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($c) => [
                'id'              => $c->id,
                'subject'         => $c->subject,
                'with'            => $c->teacher?->full_name ?? 'Unknown',
                'with_photo'      => $c->teacher?->avatar,
                'last_message_at' => $c->updated_at,
                'unread_count'    => ParentTeacherMessage::where('conversation_id', $c->id)
                    ->where('sender_id', '!=', $studentId)
                    ->whereNull('read_at')
                    ->count(),
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $conversations,
        ]);
    }

    /**
     * GET /student-app/messages/conversations/{id}
     * Single conversation thread.
     */
    public function conversationMessages(Request $request, int $id): JsonResponse
    {
        $studentId = $request->user()->id;

        $conversation = Conversation::where('student_id', $studentId)
            ->findOrFail($id);

        $messages = ParentTeacherMessage::where('conversation_id', $id)
            ->with('sender:id,first_name,last_name,photo')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'        => $m->id,
                'message'   => $m->body,          // actual column: body
                'sender'    => $m->sender?->full_name,
                'is_mine'   => $m->sender_id === $studentId,
                'read_at'   => $m->read_at,
                'sent_at'   => $m->created_at,
            ]);

        // Mark incoming messages as read
        ParentTeacherMessage::where('conversation_id', $id)
            ->where('sender_id', '!=', $studentId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'data'   => $messages,
        ]);
    }

    /**
     * POST /student-app/messages/conversations/{id}
     * Send a message in an existing conversation.
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $studentId = $request->user()->id;

        $conversation = Conversation::where('student_id', $studentId)
            ->findOrFail($id);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ParentTeacherMessage::create([
            'conversation_id' => $id,
            'sender_id'       => $studentId,
            'school_id'       => $request->user()->school_id,
            'body'            => $validated['message'],  // actual column: body
        ]);

        $conversation->touch();

        return response()->json([
            'status'  => 'success',
            'message' => 'Message sent.',
            'data'    => $message,
        ], 201);
    }

    /**
     * POST /student-app/messages/conversations
     * Start a new conversation with a teacher.
     */
    public function startConversation(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'message'    => ['required', 'string', 'max:5000'],
        ]);

        // Check for existing open conversation
        $conversation = Conversation::where('student_id', $studentId)
            ->where('teacher_id', $validated['teacher_id'])
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'student_id' => $studentId,
                'teacher_id' => $validated['teacher_id'],
                'school_id'  => $request->user()->school_id,
            ]);
        }

        ParentTeacherMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $studentId,
            'school_id'       => $request->user()->school_id,
            'body'            => $validated['message'],  // actual column: body
        ]);

        $conversation->touch();

        return response()->json([
            'status'  => 'success',
            'message' => 'Conversation started.',
            'data'    => ['conversation_id' => $conversation->id],
        ], 201);
    }
}
