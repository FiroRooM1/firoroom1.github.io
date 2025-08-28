<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyMember;
use App\Models\PartyMessage;
use App\Models\Recruitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PartyController extends Controller
{
    /**
     * パーティー一覧を表示
     */
    public function index()
    {
        $user = Auth::user();
        
        // ユーザーが参加しているパーティーを取得
        $parties = Party::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['recruitment', 'members.user' => function($query) {
            $query->select('id', 'name', 'summoner_icon', 'solo_rank', 'flex_rank');
        }])->get();

        // 未読通知数を取得
        $unreadNotificationsCount = $user->unreadNotifications()->count();

        return view('parties.index', compact('parties', 'unreadNotificationsCount'));
    }

    /**
     * パーティールームを表示
     */
    public function show(Party $party)
    {
        $user = Auth::user();
        
        // パーティーメンバーかチェック
        if (!$party->hasMember($user->id)) {
            return redirect()->route('parties.index')->withErrors(['error' => 'このパーティーにアクセスする権限がありません。']);
        }

        $party->load(['recruitment', 'members.user' => function($query) {
            $query->select('id', 'name', 'summoner_icon', 'solo_rank', 'flex_rank');
        }, 'messages.user' => function($query) {
            $query->select('id', 'name', 'summoner_icon');
        }]);
        
        return view('parties.show', compact('party'));
    }

    /**
     * チャットメッセージを送信
     */
    public function sendMessage(Request $request, Party $party)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        
        // パーティーメンバーかチェック
        if (!$party->hasMember($user->id)) {
            return response()->json(['error' => '権限がありません。'], 403);
        }

        $message = PartyMessage::create([
            'party_id' => $party->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        $message->load('user');

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'user_name' => $message->user->name,
                'message' => $message->message,
                'created_at' => $message->created_at->setTimezone('Asia/Tokyo')->format('H:i'),
            ]
        ]);
    }

    /**
     * チャットメッセージを取得（リアルタイム更新用）
     */
    public function getMessages(Party $party)
    {
        $user = Auth::user();
        
        // パーティーメンバーかチェック
        if (!$party->hasMember($user->id)) {
            return response()->json(['error' => '権限がありません。'], 403);
        }

        $messages = $party->messages()->with(['user' => function($query) {
            $query->select('id', 'name', 'summoner_icon');
        }])->orderBy('created_at', 'asc')->get();
        
        $formattedMessages = $messages->map(function($message) {
            return [
                'id' => $message->id,
                'user_name' => $message->user->name,
                'message' => $message->message,
                'created_at' => $message->created_at->setTimezone('Asia/Tokyo')->format('H:i'),
                'is_own' => $message->user_id === Auth::id(),
            ];
        });

        return response()->json(['messages' => $formattedMessages]);
    }

    /**
     * パーティーを閉じる（リーダーのみ）
     */
    public function close(Party $party)
    {
        $user = Auth::user();
        
        // パーティーリーダーかチェック
        if (!$party->isLeader($user->id)) {
            return back()->withErrors(['error' => 'パーティーを閉じる権限がありません。']);
        }

        $party->update(['status' => 'closed']);
        
        return back()->with('success', 'パーティーを閉じました。');
    }
}
