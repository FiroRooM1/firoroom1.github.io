<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>プロフィール - League of Legends フレンド募集</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">LoL フレンド募集</div>
            <div class="nav-links">
                <span class="welcome-text">{{ $user->name ?? 'サモナー' }}</span>
                <div class="notification-bell" id="notification-bell">
                    <span class="bell-icon">🔔</span>
                    <span class="notification-text">お知らせ</span>
                    <div class="notification-badge" id="notification-badge" 
                         style="display: {{ $unreadNotificationsCount > 0 ? 'flex' : 'none' }};">
                        {{ $unreadNotificationsCount ?? 0 }}
                    </div>
                </div>
                <a href="{{ route('friends.index') }}" class="nav-link">ダッシュボード</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

<div class="profile-container">
    <div class="profile-header">
        <h1 class="profile-title">プロフィール</h1>
        <a href="{{ route('profile.edit') }}" class="edit-button">編集</a>
    </div>

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <div class="profile-content">
        <!-- サモナー情報セクション -->
        <div class="summoner-section">
            <h2 class="section-title">サモナー情報</h2>
            <div class="summoner-info">
                <div class="summoner-avatar">
                    @if($user->summoner_icon)
                        <img src="https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/{{ $user->summoner_icon }}.png" 
                             alt="サモナーアイコン" class="avatar-image">
                        <div class="level-badge">{{ $user->summoner_level }}</div>
                    @else
                        <div class="avatar-placeholder">
                            <span>?</span>
                        </div>
                    @endif
                </div>
                <div class="summoner-details">
                    <h3 class="summoner-name">{{ $summonerInfo['summoner_name'] ?? $user->summoner_name ?? '未設定' }}</h3>
                    <p class="summoner-level">レベル: {{ $summonerInfo['level'] ?? $user->summoner_level ?? '不明' }}</p>
                    @if($user->summoner_level)
                        <div class="rank-info">
                            <h4>ランク情報</h4>
                            
                            <!-- デバッグ情報（開発時のみ表示） -->
                            @if(config('app.debug'))
                                <div class="debug-info" style="background: rgba(255, 0, 0, 0.1); padding: 10px; margin: 10px 0; border: 1px solid red; border-radius: 5px;">
                                    <strong>デバッグ情報:</strong><br>
                                    currentRanks: {{ json_encode($currentRanks) }}<br>
                                    user->solo_rank: {{ json_encode($user->solo_rank) }}<br>
                                    user->flex_rank: {{ json_encode($user->flex_rank) }}
                                </div>
                            @endif
                            
                            <div class="rank-list">
                                @if($currentRanks && $currentRanks['solo_rank'])
                                    <div class="rank-item">
                                        <div class="rank-image">
                                            <img src="/images/rankIMG/Rank={{ $currentRanks['solo_rank']['tier_normalized'] }}.png" 
                                                 alt="{{ $currentRanks['solo_rank']['tier'] }} {{ $currentRanks['solo_rank']['rank'] }}" 
                                                 class="rank-icon">
                                        </div>
                                        <div class="rank-details">
                                            <span class="rank-label">ソロランク:</span>
                                            <span class="rank-tier">{{ $currentRanks['solo_rank']['tier'] }} {{ $currentRanks['solo_rank']['rank'] }}</span>
                                            <span class="rank-lp">{{ $currentRanks['solo_rank']['leaguePoints'] }}LP</span>
                                            <span class="rank-record">({{ $currentRanks['solo_rank']['wins'] }}勝{{ $currentRanks['solo_rank']['losses'] }}敗)</span>
                                        </div>
                                    </div>
                                @elseif($user->solo_rank)
                                    <div class="rank-item">
                                        <div class="rank-image">
                                            <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($user->solo_rank['tier'])) }}.png" 
                                                 alt="{{ $user->solo_rank['tier'] }} {{ $user->solo_rank['rank'] }}" 
                                                 class="rank-icon">
                                        </div>
                                        <div class="rank-details">
                                            <span class="rank-label">ソロランク:</span>
                                            <span class="rank-tier">{{ $user->solo_rank['tier'] }} {{ $user->solo_rank['rank'] }}</span>
                                            <span class="rank-lp">{{ $user->solo_rank['leaguePoints'] }}LP</span>
                                            <span class="rank-record">({{ $user->solo_rank['wins'] }}勝{{ $user->solo_rank['losses'] }}敗)</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="rank-item">
                                        <div class="rank-image">
                                            <img src="/images/rankIMG/Rank=Unranked.png" alt="未ランク" class="rank-icon">
                                        </div>
                                        <div class="rank-details">
                                            <span class="rank-label">ソロランク:</span>
                                            <span class="rank-unranked">未ランク</span>
                                        </div>
                                    </div>
                                @endif

                                @if($currentRanks && $currentRanks['flex_rank'])
                                    <div class="rank-item">
                                        <div class="rank-image">
                                            <img src="/images/rankIMG/Rank={{ $currentRanks['flex_rank']['tier_normalized'] }}.png" 
                                                 alt="{{ $currentRanks['flex_rank']['tier'] }} {{ $currentRanks['flex_rank']['rank'] }}" 
                                                 class="rank-icon">
                                        </div>
                                        <div class="rank-details">
                                            <span class="rank-label">フレックスランク:</span>
                                            <span class="rank-tier">{{ $currentRanks['flex_rank']['tier'] }} {{ $currentRanks['flex_rank']['rank'] }}</span>
                                            <span class="rank-lp">{{ $currentRanks['flex_rank']['leaguePoints'] }}LP</span>
                                            <span class="rank-record">({{ $currentRanks['flex_rank']['wins'] }}勝{{ $currentRanks['flex_rank']['losses'] }}敗)</span>
                                        </div>
                                    </div>
                                @elseif($user->flex_rank)
                                    <div class="rank-item">
                                        <div class="rank-image">
                                            <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($user->flex_rank['tier'])) }}.png" 
                                                 alt="{{ $user->flex_rank['tier'] }} {{ $user->flex_rank['rank'] }}" 
                                                 class="rank-icon">
                                        </div>
                                        <div class="rank-details">
                                            <span class="rank-label">フレックスランク:</span>
                                            <span class="rank-tier">{{ $user->flex_rank['tier'] }} {{ $user->flex_rank['rank'] }}</span>
                                            <span class="rank-lp">{{ $user->flex_rank['leaguePoints'] }}LP</span>
                                            <span class="rank-record">({{ $user->flex_rank['wins'] }}勝{{ $user->flex_rank['losses'] }}敗)</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="rank-item">
                                        <div class="rank-image">
                                            <img src="/images/rankIMG/Rank=Unranked.png" alt="未ランク" class="rank-icon">
                                        </div>
                                        <div class="rank-details">
                                            <span class="rank-label">フレックスランク:</span>
                                            <span class="rank-unranked">未ランク</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- アカウント情報セクション -->
        <div class="account-section">
            <h2 class="section-title">アカウント情報</h2>
            <div class="account-info">
                <div class="info-item">
                    <label class="info-label">表示名</label>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">メールアドレス</label>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-item">
                    <label class="info-label">登録日</label>
                    <span class="info-value">{{ $user->created_at->format('Y年m月d日') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-actions">
        <a href="{{ route('friends.index') }}" class="back-button">ダッシュボードに戻る</a>
    </div>
</div>

<style>
.header {
    background: rgba(0, 0, 0, 0.8);
    border-bottom: 2px solid #c89b3c;
    padding: 1rem 0;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    backdrop-filter: blur(10px);
}

.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #f0c040;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.nav-links span {
    color: #ffffff;
    margin-right: 1rem;
}

.nav-link {
    color: #ffffff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    border-color: #c89b3c;
    background: rgba(200, 155, 60, 0.2);
}

.logout-btn {
    background: #dc3545;
    color: #ffffff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #c82333;
}

.profile-container {
    min-height: 100vh;
    background: url('/images/Teemo_47.jpg') no-repeat center center;
    background-size: cover;
    background-attachment: scroll;
    padding: 2rem;
    padding-top: 6rem;
    color: white;
}

.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(0, 0, 0, 0.7);
    padding: 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.profile-title {
    color: #f0c040;
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    margin: 0;
}

.edit-button {
    background: #00bfff;
    color: #000;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: 2px solid #00bfff;
}

.edit-button:hover {
    background: #0099cc;
    border-color: #0099cc;
    transform: translateY(-2px);
}

.success-message {
    background: rgba(34, 197, 94, 0.9);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
    font-weight: bold;
    backdrop-filter: blur(10px);
}

.profile-content {
    display: grid;
    gap: 2rem;
    margin-bottom: 2rem;
}

.summoner-section, .account-section {
    background: rgba(0, 0, 0, 0.8);
    padding: 2rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    border: 2px solid #f0c040;
}

.section-title {
    color: #f0c040;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.summoner-info {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.summoner-avatar {
    position: relative;
    flex-shrink: 0;
}

.avatar-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #f0c040;
    box-shadow: 0 0 20px rgba(240, 192, 64, 0.5);
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #f0c040;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #f0c040;
}

.level-badge {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: #f0c040;
    color: #000;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
    border: 2px solid #000;
}

.summoner-details {
    flex: 1;
}

.summoner-name {
    color: #f0c040;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.summoner-level {
    color: #e6f3ff;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.rank-info h4 {
    color: #f0c040;
    font-size: 1.3rem;
    margin-bottom: 1rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.rank-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.rank-item {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 0.5rem;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    border-left: 3px solid #f0c040;
}

.rank-image {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rank-icon {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.2);
    padding: 4px;
    border: 2px solid #f0c040;
    box-shadow: 0 0 10px rgba(240, 192, 64, 0.3);
}

.rank-details {
    flex: 1;
    display: flex;
    gap: 1rem;
    align-items: center;
}

.rank-label {
    color: #e6f3ff;
    font-weight: bold;
    min-width: 100px;
}

.rank-tier {
    color: #f0c040;
    font-weight: bold;
    min-width: 80px;
}

.rank-lp {
    color: #00bfff;
    font-weight: bold;
    min-width: 60px;
}

.rank-record {
    color: #e6f3ff;
}

.rank-unranked {
    color: #888;
    font-style: italic;
}

.rank-loading {
    color: #00bfff;
    font-style: italic;
    text-align: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    border-left: 3px solid #00bfff;
}

.account-info {
    display: grid;
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    border-left: 3px solid #00bfff;
}

.info-label {
    color: #e6f3ff;
    font-weight: bold;
    font-size: 1.1rem;
}

.info-value {
    color: #f0c040;
    font-weight: bold;
    font-size: 1.1rem;
}

.profile-actions {
    text-align: center;
}

.back-button {
    background: rgba(0, 0, 0, 0.8);
    color: #f0c040;
    padding: 1rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: 2px solid #f0c040;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.back-button:hover {
    background: #f0c040;
    color: #000;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .profile-container {
        padding: 1rem;
    }
    
    .summoner-info {
        flex-direction: column;
        text-align: center;
    }
    
    .summoner-avatar {
        align-self: center;
    }
    
    .rank-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .rank-image {
        align-self: center;
    }
    
    .rank-details {
        flex-direction: column;
        gap: 0.25rem;
        text-align: center;
    }
    
    .info-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    }

    /* ヘッダー内お知らせアイコンスタイル */
    .welcome-text {
        color: #ffffff;
        font-weight: bold;
        margin-right: 1rem;
    }

    .notification-bell {
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(240, 192, 64, 0.1);
        border: 1px solid #f0c040;
        border-radius: 8px;
        margin-right: 1rem;
    }

    .notification-bell:hover {
        background: rgba(240, 192, 64, 0.2);
        transform: scale(1.05);
    }

    .bell-icon {
        font-size: 1.2rem;
        color: #f0c040;
    }

    .notification-text {
        color: #f0c040;
        font-size: 0.9rem;
        font-weight: bold;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: bold;
        border: 2px solid #000;
        animation: pulse 2s infinite;
        z-index: 1001;
        min-width: 22px;
        min-height: 22px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    /* 通知モーダルスタイル */
    .notification-modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
    }

    .notification-modal-content {
        background: rgba(0, 0, 0, 0.95);
        border: 2px solid #f0c040;
        border-radius: 15px;
        margin: 5% auto;
        padding: 0;
        width: 90%;
        max-width: 600px;
        backdrop-filter: blur(10px);
        max-height: 80vh;
        overflow: hidden;
    }

    .notification-modal-header {
        background: rgba(240, 192, 64, 0.1);
        padding: 1.5rem;
        border-bottom: 1px solid #f0c040;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-modal-header h2 {
        color: #f0c040;
        margin: 0;
        font-size: 1.5rem;
    }

    .close-notification-modal {
        color: #f0c040;
        font-size: 2rem;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .close-notification-modal:hover {
        color: #d4af37;
    }

    .notification-modal-body {
        padding: 1.5rem;
        max-height: 60vh;
        overflow-y: auto;
    }

    .notification-item {
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid #00bfff;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        background: rgba(0, 0, 0, 0.7);
        border-color: #f0c040;
    }

    .notification-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .notification-icon {
        font-size: 1.2rem;
    }

    .notification-title {
        color: #f0c040;
        font-weight: bold;
        font-size: 1rem;
    }

    .notification-message {
        color: #ffffff;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .notification-time {
        font-size: 0.8rem;
        color: #888;
        text-align: right;
    }

    .no-notifications {
        text-align: center;
        color: #888;
        padding: 2rem;
        font-style: italic;
    }

    /* 通知アクションのスタイル */
    .notification-actions {
        margin-top: 1rem;
    }

    .notification-actions p {
        color: #888;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .notification-actions .nav-link {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #f0c040;
        color: #000;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .notification-actions .nav-link:hover {
        background: #d4af37;
        transform: translateY(-2px);
    }
</style>

<!-- 通知モーダル -->
<div id="notification-modal" class="notification-modal">
    <div class="notification-modal-content">
        <div class="notification-modal-header">
            <h2>お知らせ一覧</h2>
            <span class="close-notification-modal">&times;</span>
        </div>
        <div class="notification-modal-body">
            <div id="notification-list">
                <!-- 通知がここに表示されます -->
            </div>
            <div id="no-notifications" class="no-notifications">
                現在、新しいお知らせはありません。
            </div>
        </div>
    </div>
</div>

<script>
    // お知らせカウンター管理
    class NotificationCounter {
        constructor() {
            this.badge = document.getElementById('notification-badge');
            this.count = {{ $unreadNotificationsCount ?? 0 }};
            this.updateDisplay();
        }

        // カウントを増やす
        increment() {
            this.count++;
            this.updateDisplay();
        }

        // カウントをリセット（実際の通知数に基づいて）
        reset() {
            // データベースの実際の通知数に基づいてリセット
            this.count = {{ $unreadNotificationsCount ?? 0 }};
            this.updateDisplay();
        }

        // カウントを0にリセット（通知を見た時）
        clearCount() {
            this.count = 0;
            this.updateDisplay();
        }

        // 表示を更新
        updateDisplay() {
            if (this.badge) {
                if (this.count > 0) {
                    this.badge.style.display = 'flex';
                    this.badge.textContent = this.count > 99 ? '99+' : this.count;
                } else {
                    this.badge.style.display = 'none';
                }
            }
        }

        // 実際の通知数で更新
        updateFromServer() {
            this.count = {{ $unreadNotificationsCount ?? 0 }};
            this.updateDisplay();
        }
    }

    // グローバルなお知らせカウンターインスタンス
    window.notificationCounter = new NotificationCounter();

    // お知らせアイコンクリック時の処理
    document.addEventListener('DOMContentLoaded', function() {
        const notificationBell = document.getElementById('notification-bell');
        if (notificationBell) {
            notificationBell.addEventListener('click', function() {
                // 通知モーダルを表示
                showNotificationModal();
            });
        }
    });

    // 通知モーダルの表示・非表示
    function showNotificationModal() {
        const modal = document.getElementById('notification-modal');
        if (modal) {
            modal.style.display = 'block';
            loadNotifications();
        }
    }

    function hideNotificationModal() {
        const modal = document.getElementById('notification-modal');
        if (modal) {
            modal.style.display = 'none';
            
            // 通知モーダルを閉じた後に通知を既読状態にする
            markNotificationsAsRead();
        }
    }

    // 通知を既読状態にする
    async function markNotificationsAsRead() {
        try {
            const response = await fetch('{{ route("notifications.markAllAsRead") }}', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                // 通知カウンターを0にリセット
                if (window.notificationCounter) {
                    window.notificationCounter.count = 0;
                    window.notificationCounter.updateDisplay();
                }
            } else {
                console.error('通知の既読処理に失敗しました');
            }
        } catch (error) {
            console.error('通知の既読処理中にエラーが発生しました:', error);
        }
    }

    // 通知の読み込み
    function loadNotifications() {
        const notificationList = document.getElementById('notification-list');
        const noNotifications = document.getElementById('no-notifications');
        
        if (!notificationList || !noNotifications) {
            console.error('通知要素が見つかりません');
            return;
        }
        
        // データベースの通知数を取得して表示
        const unreadCount = {{ $unreadNotificationsCount ?? 0 }};
        
        if (unreadCount === 0) {
            notificationList.style.display = 'none';
            noNotifications.style.display = 'block';
            noNotifications.innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <p style="color: #888; margin-bottom: 1rem;">現在、新しいお知らせはありません。</p>
                </div>
            `;
        } else {
            notificationList.style.display = 'block';
            noNotifications.style.display = 'none';
            
            // 個別通知の表示（実際の通知データを使用）
            notificationList.innerHTML = `
                <div class="notification-item">
                    <div class="notification-header">
                        <span class="notification-icon">📝</span>
                        <span class="notification-title">申請受信</span>
                    </div>
                    <div class="notification-message">
                        未読の通知が<strong style="color: #f0c040;">${unreadCount}</strong>件あります。
                    </div>
                    <div class="notification-actions">
                        <p>申請や承認などの通知が届いています。<br>詳細は申請一覧ページで確認してください。</p>
                        <a href="{{ route('applications.index') }}" class="nav-link">
                            申請一覧を見る
                        </a>
                    </div>
                </div>
            `;
        }
    }

    // モーダルを閉じる処理
    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.querySelector('.close-notification-modal');
        const modal = document.getElementById('notification-modal');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', hideNotificationModal);
        }
        
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideNotificationModal();
                }
            });
        }
    });
</script>
</body>
</html>
