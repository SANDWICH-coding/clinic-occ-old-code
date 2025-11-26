<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $perPage = $request->get('per_page', 10);
            $notifications = $user->notifications()->paginate($perPage);

            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'unread_count' => $user->unreadNotifications()->count(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load notifications'
            ], 500);
        }
    }

    /**
     * Get unread notifications count - FIXED VERSION
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => true,
                    'count' => 0
                ]);
            }

            $count = $user->unreadNotifications()->count();
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting unread notification count', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            // Return 0 instead of error to prevent frontend issues
            return response()->json([
                'success' => true,
                'count' => 0
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'error' => $e->getMessage(),
                'notification_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $user->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $notification = $user->notifications()->findOrFail($id);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting notification', [
                'error' => $e->getMessage(),
                'notification_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete notification'
            ], 500);
        }
    }

    /**
     * Clear all read notifications
     */
    public function clearRead(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $user->notifications()
                ->whereNotNull('read_at')
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Read notifications cleared'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error clearing read notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear read notifications'
            ], 500);
        }
    }

    /**
     * Additional methods that might be referenced in your routes
     */
    
    /**
     * Mark multiple notifications as read
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:notifications,id'
            ]);

            $user->notifications()
                ->whereIn('id', $request->ids)
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Notifications marked as read'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error marking multiple notifications as read', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notifications as read'
            ], 500);
        }
    }

    /**
     * Delete multiple notifications
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:notifications,id'
            ]);

            $user->notifications()
                ->whereIn('id', $request->ids)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifications deleted'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting multiple notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete notifications'
            ], 500);
        }
    }

    /**
     * Clear all notifications
     */
    public function clearAll(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $user->notifications()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error clearing all notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear all notifications'
            ], 500);
        }
    }

    /**
     * Get notification stats
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $total = $user->notifications()->count();
            $unread = $user->unreadNotifications()->count();
            $read = $total - $unread;

            return response()->json([
                'success' => true,
                'stats' => [
                    'total' => $total,
                    'unread' => $unread,
                    'read' => $read
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting notification stats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get notification stats'
            ], 500);
        }
    }

    /**
     * Show individual notification
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $notification = $user->notifications()->findOrFail($id);

            // Mark as read when viewed
            if (!$notification->read_at) {
                $notification->markAsRead();
            }

            return response()->json([
                'success' => true,
                'notification' => $notification
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching notification', [
                'error' => $e->getMessage(),
                'notification_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load notification'
            ], 500);
        }
    }
}