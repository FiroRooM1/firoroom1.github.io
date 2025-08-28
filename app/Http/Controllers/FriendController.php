<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // 未読通知数を取得
        $unreadNotificationsCount = $user->unreadNotificationsCount();
        
        return view('friends.index', compact('user', 'unreadNotificationsCount'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        // フレンド検索ロジックをここに実装
        return view('friends.search', compact('query'));
    }
}
