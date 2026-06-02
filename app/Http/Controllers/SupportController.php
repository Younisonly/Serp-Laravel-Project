<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Resources\SupportTicketResource;
use App\Models\Support;
use App\Models\SupportReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $tickets = Support::where('sender_id', $userId)
                ->where('hidden_from_sender', false)
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => SupportTicketResource::collection($tickets),
                'meta' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('List support tickets failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function store(StoreSupportTicketRequest $request)
    {
        try {
            $validated = $request->validated();

            $ticket = Support::create([
                'sender_id' => $request->user()->id,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'status' => 'open',
            ]);

            try {
                NotificationTriggered::dispatch('support_ticket_created', [
                    'ticket_id' => $ticket->id,
                    'user_id' => $request->user()->id,
                    'user_name' => $request->user()->name,
                    'ticket_subject' => $validated['title'],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Support ticket notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Support ticket created.',
                'data' => new SupportTicketResource($ticket),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Create support ticket failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function show(Request $request, $ticketId)
    {
        try {
            $query = Support::with('replies.user');

            if ($request->user()->type !== 'admin') {
                $query->where('sender_id', $request->user()->id)
                    ->where('hidden_from_sender', false);
            }

            $ticket = $query->findOrFail($ticketId);

            $replies = $ticket->replies->map(fn($reply) => [
                'id' => $reply->id,
                'content' => $reply->content,
                'user_id' => $reply->user_id,
                'user_name' => $reply->user?->name ?? 'System',
                'is_admin' => $reply->is_admin,
                'is_system' => $reply->is_system,
                'created_at' => $reply->created_at->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'ticket' => new SupportTicketResource($ticket),
                    'replies' => $replies,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Show support ticket failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }
    }

    public function unreadCount(Request $request)
    {
        try {
            $count = Support::where('sender_id', $request->user()->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['unread_count' => $count],
            ]);
        } catch (\Exception $e) {
            Log::error('Unread count failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── SEND MESSAGE (user or admin) ──────────────────────────────────────

    public function reply(Request $request, $ticketId)
    {
        try {
            $request->validate(['content' => 'required|string|max:5000']);

            $ticket = Support::findOrFail($ticketId);
            $user = $request->user();
            $isAdmin = $user->type === 'admin';

            if (!$isAdmin && $ticket->sender_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
            }

            if ($ticket->status === 'closed') {
                return response()->json(['success' => false, 'message' => 'Ticket is closed.'], 422);
            }

            $reply = SupportReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'content' => $request->input('content'),
                'is_admin' => $isAdmin,
                'is_system' => false,
            ]);

            $newStatus = match ($ticket->status) {
                'open' => 'in_progress',
                'resolved' => 'in_progress',
                default => $ticket->status,
            };

            if ($newStatus !== $ticket->status) {
                $ticket->update(['status' => $newStatus]);
            }

            if ($isAdmin) {
                try {
                    NotificationTriggered::dispatch('support_ticket_replied', [
                        'ticket_id' => $ticket->id,
                        'ticket_subject' => $ticket->title,
                        'user_id' => $ticket->sender_id,
                        'user_name' => $user->name,
                        'reply_preview' => mb_substr($reply->content, 0, 100),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Support reply notification skipped: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => $isAdmin ? 'Reply sent.' : 'Message sent.',
                'data' => [
                    'id' => $reply->id,
                    'content' => $reply->content,
                    'user_id' => $reply->user_id,
                    'user_name' => $user->name,
                    'is_admin' => $isAdmin,
                    'is_system' => false,
                    'created_at' => $reply->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Support reply failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── USER: RESOLVE ─────────────────────────────────────────────────────

    public function resolve(Request $request, $ticketId)
    {
        try {
            $ticket = Support::where('sender_id', $request->user()->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->findOrFail($ticketId);

            $ticket->update(['status' => 'resolved']);

            try {
                NotificationTriggered::dispatch('support_ticket_update', [
                    'ticket_id' => $ticket->id,
                    'ticket_subject' => $ticket->title,
                    'user_id' => $request->user()->id,
                    'user_name' => $request->user()->name,
                    'status' => 'resolved',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Resolve notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket resolved.',
                'status' => 'resolved',
            ]);
        } catch (\Exception $e) {
            Log::error('Resolve ticket failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── USER: REOPEN ──────────────────────────────────────────────────────

    public function reopen(Request $request, $ticketId)
    {
        try {
            $ticket = Support::where('sender_id', $request->user()->id)
                ->where('status', 'resolved')
                ->findOrFail($ticketId);

            $ticket->update(['status' => 'in_progress']);

            SupportReply::where('ticket_id', $ticket->id)
                ->where('is_system', true)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ticket reopened.',
                'status' => 'in_progress',
            ]);
        } catch (\Exception $e) {
            Log::error('Reopen ticket failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── ADMIN: LIST ALL TICKETS ───────────────────────────────────────────

    public function allTickets(Request $request)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $status = $request->query('status');

            $query = Support::with('sender')->latest();

            if (in_array($status, ['open', 'in_progress', 'resolved', 'closed'])) {
                $query->where('status', $status);
            }

            $tickets = $query->paginate(20);

            $data = $tickets->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'content' => $t->content,
                'status' => $t->status,
                'sender_name' => $t->sender?->name ?? 'Unknown',
                'reply_count' => $t->replies()->count(),
                'created_at' => $t->created_at->toISOString(),
                'updated_at' => $t->updated_at->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('List all tickets failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── ADMIN: UPDATE STATUS ──────────────────────────────────────────────

    public function updateStatus(Request $request, $ticketId)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $request->validate([
                'status' => 'required|in:open,in_progress,resolved,closed',
            ]);

            $ticket = Support::findOrFail($ticketId);
            $newStatus = $request->input('status');

            $ticket->update(['status' => $newStatus]);

            if ($ticket->sender_id !== $request->user()->id) {
                try {
                    NotificationTriggered::dispatch('support_ticket_update', [
                        'ticket_id' => $ticket->id,
                        'ticket_subject' => $ticket->title,
                        'user_id' => $ticket->sender_id,
                        'user_name' => $ticket->sender?->name ?? '',
                        'status' => $newStatus,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Status update notification skipped: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated.',
                'data' => new SupportTicketResource($ticket),
            ]);
        } catch (\Exception $e) {
            Log::error('Update status failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ─── USER: DELETE (soft hide) ──────────────────────────────────────────

    public function deleteTicket(Request $request, $ticketId)
    {
        try {
            $ticket = Support::where('sender_id', $request->user()->id)
                ->where('hidden_from_sender', false)
                ->findOrFail($ticketId);

            $ticket->update(['hidden_from_sender' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket deleted.',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete ticket failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
