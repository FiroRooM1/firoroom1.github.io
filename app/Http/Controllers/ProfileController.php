<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * プロフィールページを表示
     */
    public function show()
    {
        $user = Auth::user();
        
        // デバッグ用：ユーザー情報をログに出力
        \Log::info('プロフィール表示時のユーザー情報:', [
            'user_id' => $user->id,
            'name' => $user->name,
            'summoner_name' => $user->summoner_name,
            'summoner_icon' => $user->summoner_icon,
            'summoner_level' => $user->summoner_level,
            'solo_rank' => $user->solo_rank,
            'flex_rank' => $user->flex_rank
        ]);
        
        // サモナー名がある場合、Riot APIから最新のランク情報を取得
        $summonerInfo = null;
        $currentRanks = null;
        if ($user->summoner_name) {
            try {
                $riotApiService = new \App\Services\RiotApiService();
                $summonerInfo = $riotApiService->getSummonerInfo($user->summoner_name);
                
                // ランク情報を処理
                if (isset($summonerInfo['ranks']) && is_array($summonerInfo['ranks'])) {
                    $currentRanks = $this->processRankData($summonerInfo['ranks']);
                    
                    // データベースのランク情報を更新
                    $this->updateUserRankInfo($user, $currentRanks);
                }
            } catch (\Exception $e) {
                // エラーが発生してもプロフィールは表示する
                \Log::warning('プロフィール表示時のRiot API取得エラー: ' . $e->getMessage());
            }
        }
        
        // 未読通知数を取得
        $unreadNotificationsCount = $user->unreadNotifications()->count();
        
        return view('profile.show', compact('user', 'summonerInfo', 'currentRanks', 'unreadNotificationsCount'));
    }

    /**
     * プロフィール編集ページを表示
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * プロフィール情報を更新
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        // 表示名とメールアドレスを更新
        $user->name = $request->name;
        $user->email = $request->email;

        // パスワードが入力されている場合は更新
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'プロフィールが更新されました。');
    }

    /**
     * ランクデータを処理してソロランクとフレックスランクに分類
     */
    private function processRankData($ranks)
    {
        $soloRank = null;
        $flexRank = null;
        
        foreach ($ranks as $rank) {
            if (isset($rank['queueType'])) {
                if ($rank['queueType'] === 'RANKED_SOLO_5x5') {
                    $soloRank = [
                        'tier' => $rank['tier'],
                        'tier_normalized' => $this->normalizeTierName($rank['tier']),
                        'rank' => $rank['rank'],
                        'leaguePoints' => $rank['leaguePoints'],
                        'wins' => $rank['wins'],
                        'losses' => $rank['losses']
                    ];
                } elseif ($rank['queueType'] === 'RANKED_FLEX_SR') {
                    $flexRank = [
                        'tier' => $rank['tier'],
                        'tier_normalized' => $this->normalizeTierName($rank['tier']),
                        'rank' => $rank['rank'],
                        'leaguePoints' => $rank['leaguePoints'],
                        'wins' => $rank['wins'],
                        'losses' => $rank['losses']
                    ];
                }
            }
        }
        
        return [
            'solo_rank' => $soloRank,
            'flex_rank' => $flexRank
        ];
    }

    /**
     * ティア名を画像ファイル名に適した形式に正規化
     */
    private function normalizeTierName($tier)
    {
        // 大文字のティア名を適切な形式に変換
        $tierMap = [
            'IRON' => 'Iron',
            'BRONZE' => 'Bronze',
            'SILVER' => 'Silver',
            'GOLD' => 'Gold',
            'PLATINUM' => 'Platinum',
            'EMERALD' => 'Emerald',
            'DIAMOND' => 'Diamond',
            'MASTER' => 'Master',
            'GRANDMASTER' => 'Grandmaster',
            'CHALLENGER' => 'Challenger'
        ];
        
        return $tierMap[strtoupper($tier)] ?? $tier;
    }

    /**
     * ユーザーのランク情報を更新
     */
    private function updateUserRankInfo($user, $ranks)
    {
        $updated = false;
        
        if (isset($ranks['solo_rank']) && $ranks['solo_rank'] !== $user->solo_rank) {
            $user->solo_rank = $ranks['solo_rank'];
            $updated = true;
        }
        
        if (isset($ranks['flex_rank']) && $ranks['flex_rank'] !== $user->flex_rank) {
            $user->flex_rank = $ranks['flex_rank'];
            $updated = true;
        }
        
        if ($updated) {
            $user->save();
            \Log::info('ユーザーランク情報を更新しました:', [
                'user_id' => $user->id,
                'solo_rank' => $user->solo_rank,
                'flex_rank' => $user->flex_rank
            ]);
        }
    }
}
