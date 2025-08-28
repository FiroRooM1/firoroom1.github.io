<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DeleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete {id? : ユーザーIDまたは"all"で全削除}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ユーザーを削除';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        if (!$id) {
            $this->error('ユーザーIDまたは"all"を指定してください。');
            $this->info('使用例: php artisan users:delete 1');
            $this->info('全削除: php artisan users:delete all');
            return Command::FAILURE;
        }

        if ($id === 'all') {
            if ($this->confirm('本当に全ユーザーを削除しますか？この操作は取り消せません。')) {
                $count = User::count();
                User::truncate();
                $this->info("{$count}人のユーザーを削除しました。");
            } else {
                $this->info('削除をキャンセルしました。');
            }
            return Command::SUCCESS;
        }

        $user = User::find($id);
        
        if (!$user) {
            $this->error("ID {$id} のユーザーが見つかりません。");
            return Command::FAILURE;
        }

        if ($this->confirm("ユーザー「{$user->name}」を削除しますか？")) {
            $user->delete();
            $this->info("ユーザー「{$user->name}」を削除しました。");
        } else {
            $this->info('削除をキャンセルしました。');
        }

        return Command::SUCCESS;
    }
}
