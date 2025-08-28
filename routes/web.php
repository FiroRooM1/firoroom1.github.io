<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FriendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// ダッシュボードルートを削除

        Route::middleware('auth')->group(function () {
            // プロフィール関連
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            
            // フレンド募集ページ
            Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
            Route::get('/friends/search', [FriendController::class, 'search'])->name('friends.search');
            
            // 募集関連
            Route::resource('recruitment', \App\Http\Controllers\RecruitmentController::class);
            
            // 申請関連
            Route::post('/recruitment/{recruitment}/apply', [\App\Http\Controllers\ApplicationController::class, 'store'])->name('applications.store');
            Route::patch('/applications/{application}/approve', [\App\Http\Controllers\ApplicationController::class, 'approve'])->name('applications.approve');
            Route::patch('/applications/{application}/reject', [\App\Http\Controllers\ApplicationController::class, 'reject'])->name('applications.reject');
            Route::get('/applications', [\App\Http\Controllers\ApplicationController::class, 'index'])->name('applications.index');
            
            // パーティー関連
            Route::get('/parties', [\App\Http\Controllers\PartyController::class, 'index'])->name('parties.index');
            Route::get('/parties/{party}', [\App\Http\Controllers\PartyController::class, 'show'])->name('parties.show');
            Route::post('/parties/{party}/messages', [\App\Http\Controllers\PartyController::class, 'sendMessage'])->name('parties.sendMessage');
            Route::get('/parties/{party}/messages', [\App\Http\Controllers\PartyController::class, 'getMessages'])->name('parties.getMessages');
            Route::patch('/parties/{party}/close', [\App\Http\Controllers\PartyController::class, 'close'])->name('parties.close');
            
            // 通知関連
            Route::patch('/notifications/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        });

require __DIR__.'/auth.php';
