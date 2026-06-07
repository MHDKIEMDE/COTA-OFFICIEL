<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    // POST /api/notifications/register
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token'   => 'required|string|max:500',
            'device_type' => 'nullable|string|in:ios,android',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $user->update([
            'fcm_token'   => $request->fcm_token,
            'device_type' => $request->device_type ?? $user->device_type,
        ]);

        Log::info('FCM token registered', ['user_id' => $user->id, 'device_type' => $user->device_type]);

        return response()->json([
            'success' => true,
            'message' => 'Token FCM enregistré.',
            'data'    => ['fcm_token_registered' => true, 'device_type' => $user->device_type],
        ]);
    }

    // DELETE /api/notifications/unregister
    public function unregister(Request $request): JsonResponse
    {
        $request->user()->update(['fcm_token' => null]);

        Log::info('FCM token unregistered', ['user_id' => $request->user()->id]);

        return response()->json(['success' => true, 'message' => 'Token FCM supprimé.']);
    }

    // GET /api/notifications
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return NotificationResource::collection($notifications);
    }

    // GET /api/notifications/unread-count
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::forUser($request->user()->id)->unread()->count();

        return response()->json(['success' => true, 'data' => ['count' => $count]]);
    }

    // PUT /api/notifications/{id}/read
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::forUser($request->user()->id)->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marquée comme lue.']);
    }

    // PUT /api/notifications/read-all
    public function markAllRead(Request $request): JsonResponse
    {
        $count = Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => "$count notification(s) marquée(s) comme lues."]);
    }

    // DELETE /api/notifications/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = Notification::forUser($request->user()->id)->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Notification supprimée.']);
    }

    // DELETE /api/notifications
    public function destroyAll(Request $request): JsonResponse
    {
        $count = Notification::forUser($request->user()->id)->delete();

        return response()->json(['success' => true, 'message' => "$count notification(s) supprimée(s)."]);
    }

    // GET /api/notifications/settings
    public function getSettings(Request $request): JsonResponse
    {
        $settings = $request->user()->notification_settings ?? [
            'push_enabled'        => true,
            'predictions_enabled' => true,
            'live_scores_enabled' => true,
            'goals_enabled'       => true,
            'combined_enabled'    => true,
            'promotions_enabled'  => false,
        ];

        return response()->json(['success' => true, 'data' => ['settings' => $settings]]);
    }

    // PUT /api/notifications/settings
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'push_enabled'        => 'sometimes|boolean',
            'predictions_enabled' => 'sometimes|boolean',
            'live_scores_enabled' => 'sometimes|boolean',
            'goals_enabled'       => 'sometimes|boolean',
            'combined_enabled'    => 'sometimes|boolean',
            'promotions_enabled'  => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user     = $request->user();
        $current  = $user->notification_settings ?? [];
        $updated  = array_merge($current, $request->only([
            'push_enabled', 'predictions_enabled', 'live_scores_enabled',
            'goals_enabled', 'combined_enabled', 'promotions_enabled',
        ]));

        $user->update(['notification_settings' => $updated]);

        Log::info('Notification settings updated', ['user_id' => $user->id]);

        return response()->json(['success' => true, 'message' => 'Paramètres mis à jour.', 'data' => ['settings' => $updated]]);
    }

    // GET /api/notifications/routine-preferences
    public function getRoutinePreferences(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $defaults = array_fill_keys(NotificationPreference::TYPES, [
            'enabled'           => true,
            'quiet_hours_start' => 23,
            'quiet_hours_end'   => 7,
        ]);

        $prefs = NotificationPreference::where('user_id', $userId)->get();
        foreach ($prefs as $pref) {
            $defaults[$pref->type] = [
                'enabled'           => $pref->enabled,
                'quiet_hours_start' => $pref->quiet_hours_start,
                'quiet_hours_end'   => $pref->quiet_hours_end,
            ];
        }

        return response()->json(['success' => true, 'data' => ['preferences' => $defaults]]);
    }

    // PUT /api/notifications/routine-preferences
    public function updateRoutinePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            '*.enabled'           => 'sometimes|boolean',
            '*.quiet_hours_start' => 'sometimes|integer|min:0|max:23',
            '*.quiet_hours_end'   => 'sometimes|integer|min:0|max:23',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $types  = NotificationPreference::TYPES;

        foreach ($types as $type) {
            if (!$request->has($type)) continue;

            $data = $request->input($type);
            NotificationPreference::updateOrCreate(
                ['user_id' => $userId, 'type' => $type],
                array_filter([
                    'enabled'           => $data['enabled']           ?? null,
                    'quiet_hours_start' => $data['quiet_hours_start'] ?? null,
                    'quiet_hours_end'   => $data['quiet_hours_end']   ?? null,
                ], fn($v) => $v !== null)
            );
        }

        Log::info('Routine preferences updated', ['user_id' => $userId]);

        return response()->json(['success' => true, 'message' => 'Préférences routines mises à jour.']);
    }
}
