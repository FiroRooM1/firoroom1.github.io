<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * ユーザーの全通知を既読状態にする
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();
            
            // ユーザーの未読通知を全て既読にする
            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => '全ての通知を既読にしました',
                'unread_count' => 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '通知の既読処理に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
