<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ParentTeacherMessage;
use App\Models\StudentStatus;
use App\Models\AssignedTeacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * GET /api/messages/conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $parentId = $request->user()->id;

        $conversations = Conversation::with(['teacher', 'student', 'latestMessage'])
            ->where('parent_id', $parentId)
            ->get()
            ->map(function ($conv) use ($parentId) {
                $conv->unread_count = $conv->getUnreadCountForUser($parentId);
                return $conv;
            })
            ->sortByDesc(function ($conv) {
                return $conv->latestMessage ? $conv->latestMessage->created_at : $conv->created_at;
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $conversations,
        ]);
    }

    /**
     * POST /api/messages/conversations
     */
    public function startConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        // Verify child belongs to parent
        $request->user()->children()->findOrFail($validated['student_id']);

        $conversation = Conversation::create([
            'school_id'  => $request->user()->school_id ?? 1, // Fallback if no school_id
            'parent_id'  => $request->user()->id,
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'subject'    => $validated['subject'],
        ]);

        $message = ParentTeacherMessage::create([
            'school_id'       => $conversation->school_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $request->user()->id,
            'body'            => $validated['message'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Conversation started successfully.',
            'data'    => $conversation->load(['teacher', 'latestMessage']),
        ]);
    }

    /**
     * GET /api/messages/conversations/{id}
     */
    public function conversationShow(Request $request, $id): JsonResponse
    {
        $conversation = Conversation::with(['teacher', 'student'])
            ->where('parent_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $conversation,
        ]);
    }

    /**
     * GET /api/messages/conversations/{id}/messages
     */
    public function messages(Request $request, $id): JsonResponse
    {
        $conversation = Conversation::where('parent_id', $request->user()->id)->findOrFail($id);

        $messages = ParentTeacherMessage::with('sender')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        ParentTeacherMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'data'   => $messages,
        ]);
    }

    /**
     * POST /api/messages/conversations/{id}/messages
     */
    public function sendMessage(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $conversation = Conversation::where('parent_id', $request->user()->id)->findOrFail($id);

        $message = ParentTeacherMessage::create([
            'school_id'       => $conversation->school_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $request->user()->id,
            'body'            => $validated['message'],
        ]);

        // Touch conversation to update 'updated_at'
        $conversation->touch();

        return response()->json([
            'status' => 'success',
            'data'   => $message->load('sender'),
        ]);
    }

    /**
     * GET /api/messages/contacts
     * Gets teachers that teach the parent's children
     */
    public function contacts(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignedTeachers = AssignedTeacher::with(['teacher', 'course'])
            ->whereIn('class_id', $classIds)
            ->get()
            ->groupBy('teacher_id')
            ->map(function ($assignments) {
                $teacher = $assignments->first()->teacher;
                return collect($teacher)->merge([
                    'subjects' => $assignments->pluck('course.course_name')->unique()->values()
                ]);
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $assignedTeachers,
        ]);
    }

    /**
     * GET /api/messages/online
     */
    public function online(Request $request): JsonResponse
    {
        // Placeholder for online status, if websockets are used
        return response()->json([
            'status' => 'success',
            'data'   => [],
            'message'=> 'Real-time online status not implemented via REST.'
        ]);
    }

    /**
     * PUT /api/messages/{id}/read
     */
    public function markRead(Request $request, $id): JsonResponse
    {
        $conversation = Conversation::where('parent_id', $request->user()->id)->findOrFail($id);

        ParentTeacherMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    /**
     * DELETE /api/messages/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $conversation = Conversation::where('parent_id', $request->user()->id)->findOrFail($id);
        
        // This will soft delete if soft deletes are configured, otherwise hard delete
        $conversation->delete();

        return response()->json(['status' => 'success']);
    }
}
