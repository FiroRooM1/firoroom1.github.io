<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\RiotApiService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'summoner_name' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Riot APIでサモナー名を検証
        try {
            $riotApiService = new RiotApiService();
            $summonerInfo = $riotApiService->getSummonerInfo($request->summoner_name);
            
            $user = User::create([
                'name' => $request->display_name,
                'summoner_name' => $request->summoner_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'summoner_level' => $summonerInfo['level'] ?? null,
                'puuid' => $summonerInfo['puuid'] ?? null,
                'summoner_icon' => $summonerInfo['icon_id'] ?? null,
            ]);

            // ランク情報を保存
            if (isset($summonerInfo['ranks'])) {
                $soloRank = collect($summonerInfo['ranks'])->firstWhere('queueType', 'RANKED_SOLO_5x5');
                $flexRank = collect($summonerInfo['ranks'])->firstWhere('queueType', 'RANKED_FLEX_SR');
                $user->setRankInfo($soloRank, $flexRank);
            }
        } catch (\Exception $e) {
            // デバッグ用：エラーの詳細をログに記録
            \Log::error('ユーザー登録時のRiot APIエラー:', [
                'message' => $e->getMessage(),
                'summoner_name' => $request->summoner_name,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Riot APIでサモナーが見つからない場合
            return back()
                ->withInput()
                ->withErrors(['summoner_name' => '入力されたサモナー名がLeague of Legendsで見つかりません。正しいRiot ID（例: Test#JP1）を入力してください。']);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
