<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ShowUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '登録されているユーザー一覧を表示';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->info('登録されているユーザーはありません。');
            return Command::SUCCESS;
        }

        $this->info('=== 登録済みユーザー一覧 ===');
        $this->table(
            ['ID', '表示名', 'サモナー名', 'メールアドレス', '登録日時'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->summoner_name ?? 'なし',
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            })
        );
        
        return Command::SUCCESS;
    }
}
