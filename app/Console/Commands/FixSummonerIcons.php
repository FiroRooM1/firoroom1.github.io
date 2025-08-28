<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixSummonerIcons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:summoner-icons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix summoner icons by extracting icon ID from URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('サモナーアイコンの修正を開始します...');

        $users = User::whereNotNull('summoner_icon')->get();
        $fixedCount = 0;

        foreach ($users as $user) {
            if (str_contains($user->summoner_icon, 'ddragon.leagueoflegends.com')) {
                // URLからアイコンIDを抽出
                if (preg_match('/profileicon\/(\d+)\.png/', $user->summoner_icon, $matches)) {
                    $iconId = $matches[1];
                    $user->update(['summoner_icon' => $iconId]);
                    $this->info("ユーザー {$user->name} のアイコンを {$iconId} に修正しました");
                    $fixedCount++;
                }
            }
        }

        $this->info("修正完了: {$fixedCount} 件のアイコンを修正しました");
    }
}
