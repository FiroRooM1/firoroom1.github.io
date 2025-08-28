<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Recruitment;
use App\Models\PartyMember;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function store(Request $request, Recruitment $recruitment)
    {
        // 自分自身には申請できない
        if ($recruitment->user_id === Auth::id()) {
            return back()->withErrors(['error' => '自分自身の募集には申請できません。']);
        }

        // 既に申請済みかチェック
        $existingApplication = Application::where('recruitment_id', $recruitment->id)
            ->where('applicant_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return back()->withErrors(['error' => '既に申請済みです。']);
        }

        $request->validate([
            'preferred_lane' => 'required|string|max:50',
            'message' => 'required|string|max:1000',
        ]);

        Application::create([
            'recruitment_id' => $recruitment->id,
            'applicant_id' => Auth::id(),
            'preferred_lane' => $request->preferred_lane,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // 投稿主に通知を送信
        NotificationService::applicationReceived(
            $recruitment->id,
            Auth::user()->name,
            $recruitment->title
        );

        return back()->with('success', '申請が完了しました！');
    }

    public function approve(Application $application)
    {
        // 募集主のみ承認可能
        if ($application->recruitment->user_id !== Auth::id()) {
            return back()->withErrors(['error' => '権限がありません。']);
        }

        $application->update(['status' => 'approved']);

        // 申請者に承認通知を送信
        NotificationService::applicationApproved(
            $application->recruitment->id,
            $application->recruitment->title,
            $application->applicant_id
        );

        // 承認された申請者をパーティーに自動参加させる
        if ($application->recruitment->party) {
            PartyMember::create([
                'party_id' => $application->recruitment->party->id,
                'user_id' => $application->applicant_id,
                'role' => 'member',
            ]);
        }

        return back()->with('success', '申請を承認しました！');
    }

    public function reject(Application $application)
    {
        // 募集主のみ拒否可能
        if ($application->recruitment->user_id !== Auth::id()) {
            return back()->withErrors(['error' => '権限がありません。']);
        }

        $application->update(['status' => 'rejected']);

        // 申請者に拒否通知を送信
        NotificationService::applicationRejected(
            $application->recruitment->id,
            $application->recruitment->title,
            $application->applicant_id
        );

        return back()->with('success', '申請を拒否しました。');
    }

    public function index()
    {
        // 自分が投稿した募集への申請一覧
        $recruitments = Recruitment::where('user_id', Auth::id())
            ->with(['applications.applicant' => function($query) {
                $query->select('id', 'name', 'summoner_icon', 'solo_rank', 'flex_rank');
            }])
            ->get();

        // 未読通知数を取得
        $unreadNotificationsCount = Auth::user()->unreadNotificationsCount();

        return view('applications.index', compact('recruitments', 'unreadNotificationsCount'));
    }
}
