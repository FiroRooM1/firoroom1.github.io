<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RiotApiService;

class TestRiotApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'riot:test {riotId : テストするRiot ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Riot APIの動作をテスト';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $riotId = $this->argument('riotId');
        
        $this->info("Riot ID: {$riotId} でテストを開始します...");
        
        try {
            $riotApiService = new RiotApiService();
            $summonerInfo = $riotApiService->getSummonerInfo($riotId);
            
            $this->info('✅ サモナー情報の取得に成功しました！');
            $this->info('取得されたデータの構造:');
            $this->line(json_encode($summonerInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $this->table(
                ['項目', '値'],
                [
                    ['サモナー名', $summonerInfo['summoner_name'] ?? 'N/A'],
                    ['レベル', $summonerInfo['level'] ?? 'N/A'],
                    ['アイコンURL', $summonerInfo['icon_url'] ?? 'N/A'],
                    ['ランク数', count($summonerInfo['ranks'] ?? [])],
                    ['PUUID', $summonerInfo['puuid'] ?? 'N/A'],
                ]
            );
            
        } catch (\Exception $e) {
            $this->error('❌ エラーが発生しました:');
            $this->error($e->getMessage());
            
            // 設定情報も表示
            $this->info('設定情報:');
            $this->info('API Key: ' . (config('services.riot.api_key') ? '設定済み' : '未設定'));
            $this->info('Config Path: ' . config_path('services.php'));
            
            // スタックトレースも表示
            $this->error('スタックトレース:');
            $this->error($e->getTraceAsString());
        }
        
        return Command::SUCCESS;
    }
}
