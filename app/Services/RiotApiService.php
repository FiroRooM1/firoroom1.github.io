<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RiotApiService
{
    private $apiKey;
    private $baseUrl = 'https://jp1.api.riotgames.com';
    private $asiaUrl = 'https://asia.api.riotgames.com';

    public function __construct()
    {
        $this->apiKey = config('services.riot.api_key');
        
        if (!$this->apiKey) {
            Log::warning('Riot API Keyが設定されていません。.envファイルを確認してください。');
        }
    }

    /**
     * サモナー情報を取得
     */
    public function getSummonerInfo($riotId)
    {
        try {
            if (!$this->apiKey) {
                throw new Exception('Riot API Keyが設定されていません');
            }

            // Riot IDの形式を確認
            if (!str_contains($riotId, '#')) {
                throw new Exception('Riot IDの形式が正しくありません（例: Test#JP1）');
            }

            // Riot IDを分解
            [$gameName, $tagLine] = explode('#', $riotId, 2);

            // 1. Riot IDからアカウント情報を取得（PUUIDを取得）
            $accountData = $this->getAccountInfo($gameName, $tagLine);
            $puuid = $accountData['puuid'];

            // 2. PUUIDからサモナー情報を取得（JP1サーバー）
            $summonerData = $this->getSummonerByPuuid($puuid);

            // 3. PUUIDからランク情報を取得（JP1サーバー）
            $rankData = $this->getRankInfo($puuid);

            // デバッグ用：APIレスポンスの内容をログに記録
            Log::info('Riot API レスポンス:', [
                'summonerData' => $summonerData,
                'rankData' => $rankData
            ]);

            // ランク情報の詳細ログ
            if (is_array($rankData)) {
                Log::info('ランク情報詳細:', [
                    'rankCount' => count($rankData),
                    'ranks' => $rankData
                ]);
                
                foreach ($rankData as $index => $rank) {
                    Log::info("ランク {$index}:", [
                        'queueType' => $rank['queueType'] ?? 'unknown',
                        'tier' => $rank['tier'] ?? 'unknown',
                        'rank' => $rank['rank'] ?? 'unknown',
                        'leaguePoints' => $rank['leaguePoints'] ?? 0,
                        'wins' => $rank['wins'] ?? 0,
                        'losses' => $rank['losses'] ?? 0
                    ]);
                }
            } else {
                Log::warning('ランク情報が配列ではありません:', [
                    'type' => gettype($rankData),
                    'value' => $rankData
                ]);
            }

            // アイコンURLを生成（最新バージョンを使用）
            $iconUrl = "https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/{$summonerData['profileIconId']}.png";

            return [
                'summoner_name' => $riotId, // サモナー名 (Riot ID)
                'level' => $summonerData['summonerLevel'] ?? 0, // サモナーレベル
                'icon_id' => $summonerData['profileIconId'], // サモナーアイコンID
                'ranks' => $rankData, // ランク情報
                'puuid' => $puuid // 内部管理用
            ];

        } catch (Exception $e) {
            Log::error('getSummonerInfo詳細エラー:', [
                'message' => $e->getMessage(),
                'riotId' => $riotId,
                'apiKey' => $this->apiKey ? '設定済み' : '未設定'
            ]);

            throw $e;
        }
    }

    /**
     * アカウント情報を取得
     */
    private function getAccountInfo($gameName, $tagLine)
    {
        $url = "{$this->asiaUrl}/riot/account/v1/accounts/by-riot-id/{$gameName}/{$tagLine}";
        
        $response = Http::timeout(10)
            ->withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get($url);

        if ($response->status() === 403) {
            throw new Exception('Riot APIアクセス拒否 (403): APIキーの確認が必要です');
        } elseif ($response->status() === 404) {
            throw new Exception("Riot IDが見つかりません (404): {$gameName}#{$tagLine} が存在しないか、地域が異なります");
        } elseif ($response->status() === 429) {
            throw new Exception('APIレート制限 (429): しばらく待ってから再試行してください');
        } elseif (!$response->successful()) {
            throw new Exception("アカウント情報の取得に失敗しました: HTTP {$response->status()}");
        }

        return $response->json();
    }

    /**
     * PUUIDからサモナー情報を取得
     */
    private function getSummonerByPuuid($puuid)
    {
        $url = "{$this->baseUrl}/lol/summoner/v4/summoners/by-puuid/{$puuid}";
        
        $response = Http::timeout(10)
            ->withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get($url);

        if (!$response->successful()) {
            throw new Exception("サモナー情報の取得に失敗しました: HTTP {$response->status()}");
        }

        return $response->json();
    }

    /**
     * PUUIDからランク情報を取得
     */
    private function getRankInfo($puuid)
    {
        $url = "{$this->baseUrl}/lol/league/v4/entries/by-puuid/{$puuid}";
        
        $response = Http::timeout(10)
            ->withHeaders(['X-Riot-Token' => $this->apiKey])
            ->get($url);

        if (!$response->successful()) {
            throw new Exception("ランク情報の取得に失敗しました: HTTP {$response->status()}");
        }

        return $response->json();
    }

    /**
     * サモナー名の検証
     */
    public function validateSummonerName($riotId)
    {
        try {
            $this->getSummonerInfo($riotId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
