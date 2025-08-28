<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Recruitment;
use App\Models\Party;
use App\Models\PartyMember;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 募集一覧を表示
     */
    public function index()
    {
        $recruitments = Recruitment::with('user')->latest()->get();
        
        // 未読通知数を取得
        $user = Auth::user();
        $unreadNotificationsCount = $user->unreadNotifications()->count();
        
        return view('recruitment.index', compact('recruitments', 'unreadNotificationsCount'));
    }

    /**
     * 新規募集作成フォームを表示
     */
    public function create()
    {
        return view('recruitment.create');
    }

    /**
     * 新規募集を保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'game_mode' => 'required|string|max:50',
            'lane' => 'required|string|max:50',
            'content' => 'required|string|max:1000',
        ]);

        $recruitment = Recruitment::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'game_mode' => $request->game_mode,
            'lane' => $request->lane,
            'content' => $request->content,
        ]);

        // パーティーを自動作成
        $party = Party::create([
            'recruitment_id' => $recruitment->id,
            'name' => $request->title,
            'description' => $request->content,
        ]);

        // 募集主をパーティーリーダーとして追加
        PartyMember::create([
            'party_id' => $party->id,
            'user_id' => Auth::id(),
            'role' => 'leader',
        ]);

        return redirect()->route('recruitment.index')->with('success', '募集を投稿しました！');
    }

    /**
     * 募集を削除
     */
    public function destroy(Recruitment $recruitment)
    {
        if ($recruitment->user_id !== Auth::id()) {
            abort(403);
        }

        $recruitment->delete();
        return redirect()->route('recruitment.index')->with('success', '募集を削除しました。');
    }
}
