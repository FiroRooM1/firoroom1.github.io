<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>募集一覧 - League of Legends フレンド募集</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">LoL フレンド募集</div>
            <div class="nav-links">
                <span class="welcome-text">{{ Auth::user()->name ?? 'サモナー' }}</span>
                <div class="notification-bell" id="notification-bell">
                    <span class="bell-icon">🔔</span>
                    <span class="notification-text">お知らせ</span>
                    <div class="notification-badge" id="notification-badge" 
                         style="display: {{ $unreadNotificationsCount > 0 ? 'flex' : 'none' }};">
                        {{ $unreadNotificationsCount ?? 0 }}
                    </div>
                </div>
                <a href="{{ route('friends.index') }}" class="nav-link">ダッシュボード</a>
                <a href="{{ route('profile.show') }}" class="nav-link">プロフィール</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

    <div class="recruitment-container">
        <!-- ヘッダーセクション -->
        <div class="recruitment-header">
            <h1 class="recruitment-title">募集一覧</h1>
            <a href="{{ route('recruitment.create') }}" class="new-recruitment-btn">
                <span class="plus-icon">+</span>
                新規募集
            </a>
        </div>

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        <!-- フィルターセクション -->
        <div class="filter-section">
            <div class="filter-group">
                <label for="game_mode">ゲームモード</label>
                <select id="game_mode" class="filter-select">
                    <option value="">全て</option>
                    <option value="ノーマル">ノーマル</option>
                    <option value="ランク（デュオ）">ランク（デュオ）</option>
                    <option value="ランク（フレックス）">ランク（フレックス）</option>
                    <option value="ランダムミッド">ランダムミッド</option>
                    <option value="アリーナ">アリーナ</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="lane">レーン</label>
                <select id="lane" class="filter-select">
                    <option value="">全て</option>
                    <option value="トップ">トップ</option>
                    <option value="ジャングル">ジャングル</option>
                    <option value="ミッド">ミッド</option>
                    <option value="ボット">ボット</option>
                    <option value="サポート">サポート</option>
                    <option value="オートフィル">オートフィル</option>
                </select>
            </div>
        </div>

        <!-- 募集投稿一覧 -->
        <div class="recruitment-list">
            @forelse($recruitments as $recruitment)
                <div class="recruitment-card">
                    <div class="user-info">
                        <div class="user-avatar">
                            @if($recruitment->user->summoner_icon)
                                <img src="https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/{{ $recruitment->user->summoner_icon }}.png" 
                                     alt="サモナーアイコン" class="avatar-image">
                            @else
                                <div class="avatar-placeholder">?</div>
                            @endif
                        </div>
                        <div class="user-details">
                            <div class="username">{{ $recruitment->user->name }}</div>
                            <div class="user-rank">
                                @if($recruitment->user->summoner_level)
                                    @if($recruitment->game_mode == 'ランク（デュオ）' && $recruitment->user->solo_rank)
                                        <div class="rank-display">
                                            <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($recruitment->user->solo_rank['tier'])) }}.png" 
                                                 alt="{{ $recruitment->user->solo_rank['tier'] }} {{ $recruitment->user->solo_rank['rank'] }}" 
                                                 class="rank-icon">
                                            <span class="rank-text">{{ $recruitment->user->solo_rank['tier'] }} {{ $recruitment->user->solo_rank['rank'] }}</span>
                                        </div>
                                    @elseif($recruitment->game_mode == 'ランク（フレックス）' && $recruitment->user->flex_rank)
                                        <div class="rank-display">
                                            <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($recruitment->user->flex_rank['tier'])) }}.png" 
                                                 alt="{{ $recruitment->user->flex_rank['tier'] }} {{ $recruitment->user->flex_rank['rank'] }}" 
                                                 class="rank-icon">
                                            <span class="rank-text">{{ $recruitment->user->flex_rank['tier'] }} {{ $recruitment->user->flex_rank['rank'] }}</span>
                                        </div>
                                    @elseif(in_array($recruitment->game_mode, ['ノーマル', 'ランダムミッド', 'アリーナ']) && $recruitment->user->solo_rank)
                                        <div class="rank-display">
                                            <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($recruitment->user->solo_rank['tier'])) }}.png" 
                                                 alt="{{ $recruitment->user->solo_rank['tier'] }} {{ $recruitment->user->solo_rank['rank'] }}" 
                                                 class="rank-icon">
                                            <span class="rank-text">{{ $recruitment->user->solo_rank['tier'] }} {{ $recruitment->user->solo_rank['rank'] }}</span>
                                        </div>
                                    @elseif($recruitment->user->solo_rank)
                                        <div class="rank-display">
                                            <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($recruitment->user->solo_rank['tier'])) }}.png" 
                                                 alt="{{ $recruitment->user->solo_rank['tier'] }} {{ $recruitment->user->solo_rank['rank'] }}" 
                                                 class="rank-icon">
                                            <span class="rank-text">{{ $recruitment->user->solo_rank['tier'] }} {{ $recruitment->user->solo_rank['rank'] }}</span>
                                        </div>
                                    @else
                                        <div class="rank-display">
                                            <img src="/images/rankIMG/Rank=Unranked.png" alt="未ランク" class="rank-icon">
                                            <span class="rank-text">ランク未設定</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="rank-display">
                                        <img src="/images/rankIMG/Rank=Unranked.png" alt="未ランク" class="rank-icon">
                                        <span class="rank-text">ランク未設定</span>
                                    </div>
                                @endif
                            </div>
                            <div class="user-lane">{{ $recruitment->lane }}</div>
                        </div>
                    </div>
                    <div class="post-content">
                        <h3 class="post-title">{{ $recruitment->title }}</h3>
                        <p class="post-text">{{ $recruitment->content }}</p>
                    </div>
                    <div class="post-tags">
                        <span class="tag game-mode">{{ $recruitment->game_mode }}</span>
                        <span class="tag lane">{{ $recruitment->lane }}</span>
                    </div>
                    <div class="post-footer">
                        <span class="post-date">{{ $recruitment->created_at->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}</span>
                        <div class="post-actions">
                            @if($recruitment->user_id !== Auth::id())
                                @if($recruitment->hasAppliedBy(Auth::id()))
                                    <span class="applied-badge">申請済み</span>
                                @else
                                    <button type="button" class="apply-btn" onclick="openApplyModal({{ $recruitment->id }}, '{{ $recruitment->title }}')">
                                        <span class="apply-icon">📝</span>
                                        申請する
                                    </button>
                                @endif
                            @else
                                <button type="button" class="delete-btn" onclick="showDeleteRecruitmentDialog({{ $recruitment->id }}, '{{ $recruitment->title }}')">
                                    <span class="trash-icon">🗑</span>
                                    削除
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-recruitments">
                    <p>まだ募集投稿がありません。</p>
                    <a href="{{ route('recruitment.create') }}" class="create-first-btn">最初の募集を作成する</a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 申請モーダル -->
    <div id="applyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>募集に申請する</h2>
                <span class="close" onclick="closeApplyModal()">&times;</span>
            </div>
            <form id="applyForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recruitment_title">募集タイトル</label>
                        <input type="text" id="recruitment_title" readonly>
                    </div>
                    <div class="form-group">
                        <label for="preferred_lane">希望レーン *</label>
                        <select id="preferred_lane" name="preferred_lane" required>
                            <option value="">選択してください</option>
                            <option value="トップ">トップ</option>
                            <option value="ジャングル">ジャングル</option>
                            <option value="ミッド">ミッド</option>
                            <option value="ボット">ボット</option>
                            <option value="サポート">サポート</option>
                            <option value="オートフィル">オートフィル</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">メッセージ *</label>
                        <textarea id="message" name="message" rows="4" required 
                                  placeholder="自己紹介や参加したい理由などを記入してください"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="cancel-btn" onclick="closeApplyModal()">キャンセル</button>
                    <button type="submit" class="submit-btn">申請を送信</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 募集削除確認ダイアログ -->
    <div id="delete-recruitment-modal" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h2>募集を削除</h2>
                <span class="close-modal-btn" onclick="hideDeleteRecruitmentDialog()">&times;</span>
            </div>
            <div class="custom-modal-body">
                <p>以下の募集を削除しますか？</p>
                <p class="recruitment-title" id="delete-recruitment-title"></p>
                <p class="warning-text">この操作は取り消すことができません。</p>
            </div>
            <div class="custom-modal-footer">
                <form id="delete-recruitment-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="confirm-btn">削除</button>
                </form>
                <button type="button" class="cancel-btn" onclick="hideDeleteRecruitmentDialog()">キャンセル</button>
            </div>
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

.recruitment-container {
    min-height: 100vh;
    background: url('/images/Teemo_47.jpg') no-repeat center center;
    background-size: cover;
    background-attachment: scroll;
    padding: 2rem;
    padding-top: 6rem;
    color: white;
}

.recruitment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(0, 0, 0, 0.7);
    padding: 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.recruitment-title {
    color: #f0c040;
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    margin: 0;
}

.new-recruitment-btn {
    background: #f0c040;
    color: #000;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: 2px solid #f0c040;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.new-recruitment-btn:hover {
    background: #d4af37;
    border-color: #d4af37;
    transform: translateY(-2px);
}

.plus-icon {
    font-size: 1.2rem;
    font-weight: bold;
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

.filter-section {
    background: rgba(0, 0, 0, 0.7);
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    color: #f0c040;
    font-weight: bold;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.filter-select {
    padding: 0.5rem;
    border: 2px solid #00bfff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 1rem;
    min-width: 150px;
}

.filter-select:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 10px rgba(240, 192, 64, 0.5);
}

.recruitment-list {
    display: grid;
    gap: 1.5rem;
}

.recruitment-card {
    background: rgba(0, 0, 0, 0.8);
    border: 2px solid #f0c040;
    border-radius: 15px;
    padding: 2rem;
    backdrop-filter: blur(10px);
    position: relative;
}

.user-info {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.user-avatar {
    flex-shrink: 0;
}

.avatar-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid #f0c040;
    box-shadow: 0 0 15px rgba(240, 192, 64, 0.5);
}

.avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid #f0c040;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #f0c040;
    font-weight: bold;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.username {
    color: #f0c040;
    font-weight: bold;
    font-size: 1.2rem;
}

.user-rank {
    color: #e6f3ff;
    font-size: 0.9rem;
}

.rank-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rank-icon {
    width: 24px;
    height: 24px;
    object-fit: contain;
}

.rank-text {
    font-size: 0.9rem;
    color: #c89b3c;
    font-weight: 500;
}

.user-lane {
    color: #00bfff;
    font-weight: bold;
    font-size: 1rem;
}

.post-content {
    margin-bottom: 1.5rem;
}

.post-title {
    color: #f0c040;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 1rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.post-text {
    color: #ffffff;
    font-size: 1.1rem;
    line-height: 1.6;
    margin: 0;
}

.post-tags {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.tag {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.tag.game-mode {
    background: #00bfff;
    color: #000;
}

.tag.lane {
    background: #6c5ce7;
    color: #ffffff;
}

.post-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.post-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.post-date {
    color: #888;
    font-size: 0.9rem;
}

.delete-btn {
    background: #dc3545;
    color: #ffffff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.delete-btn:hover {
    background: #c82333;
}

.apply-btn {
    background: #28a745;
    color: #ffffff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.apply-btn:hover {
    background: #218838;
    transform: translateY(-1px);
}

.apply-icon {
    font-size: 1rem;
}

.trash-icon {
    font-size: 1rem;
}

.applied-badge {
    background: #6c757d;
    color: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: bold;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: default;
}

.no-recruitments {
    text-align: center;
    background: rgba(0, 0, 0, 0.8);
    padding: 3rem;
    border-radius: 15px;
    border: 2px solid #f0c040;
    backdrop-filter: blur(10px);
}

.no-recruitments p {
    color: #e6f3ff;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.create-first-btn {
    background: #f0c040;
    color: #000;
    padding: 1rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: 2px solid #f0c040;
}

.create-first-btn:hover {
    background: #d4af37;
    border-color: #d4af37;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .recruitment-container {
        padding: 1rem;
    }
    
    .recruitment-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .filter-section {
        flex-direction: column;
        align-items: center;
    }
    
    .filter-select {
        min-width: 200px;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
    
    .user-avatar {
        align-self: center;
    }
    
    .post-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

/* モーダルスタイル */
.modal {
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

.modal-content {
    background: rgba(0, 0, 0, 0.95);
    border: 2px solid #f0c040;
    border-radius: 15px;
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 500px;
    backdrop-filter: blur(10px);
}

.modal-header {
    background: rgba(240, 192, 64, 0.1);
    padding: 1.5rem;
    border-bottom: 1px solid #f0c040;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    color: #f0c040;
    margin: 0;
    font-size: 1.5rem;
}

.close {
    color: #f0c040;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close:hover {
    color: #d4af37;
}

.modal-body {
    padding: 1.5rem;
}

.modal-body .form-group {
    margin-bottom: 1.5rem;
}

.modal-body label {
    display: block;
    color: #f0c040;
    font-weight: bold;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.modal-body input,
.modal-body select,
.modal-body textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #00bfff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.modal-body input:focus,
.modal-body select:focus,
.modal-body textarea:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 10px rgba(240, 192, 64, 0.5);
}

.modal-body input[readonly] {
    background: rgba(0, 0, 0, 0.3);
    color: #888;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #f0c040;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    border-radius: 0 0 15px 15px;
}

.cancel-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cancel-btn:hover {
    background: #5a6268;
}

.submit-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: #218838;
    transform: translateY(-1px);
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

/* カスタムモーダルスタイル */
.custom-modal {
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

.custom-modal-content {
    background: rgba(0, 0, 0, 0.95);
    border: 2px solid #f0c040;
    border-radius: 15px;
    margin: 10% auto;
    padding: 0;
    width: 90%;
    max-width: 500px;
    backdrop-filter: blur(10px);
    box-shadow: 0 0 30px rgba(240, 192, 64, 0.3);
}

.custom-modal-header {
    background: rgba(240, 192, 64, 0.1);
    padding: 1.5rem;
    border-bottom: 1px solid #f0c040;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-header h2 {
    color: #f0c040;
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
}

.close-modal-btn {
    color: #f0c040;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
    line-height: 1;
}

.close-modal-btn:hover {
    color: #d4af37;
}

.custom-modal-body {
    padding: 2rem;
    text-align: center;
}

.custom-modal-body p {
    color: #ffffff;
    margin: 0.5rem 0;
    font-size: 1.1rem;
}

.recruitment-title {
    color: #f0c040 !important;
    font-weight: bold;
    font-size: 1.2rem;
    margin: 1rem 0;
    padding: 0.5rem;
    background: rgba(240, 192, 64, 0.1);
    border-radius: 8px;
}

.warning-text {
    color: #ff6b6b !important;
    font-weight: bold;
    font-size: 1rem;
}

.custom-modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #f0c040;
    border-radius: 0 0 15px 15px;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.confirm-btn {
    background: #dc3545;
    color: #ffffff;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.confirm-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
}

.cancel-btn {
    background: #6c757d;
    color: #ffffff;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.cancel-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .recruitment-container {
        padding: 1rem;
    }
    
    .recruitment-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .filter-section {
        flex-direction: column;
        align-items: center;
    }
    
    .filter-select {
        min-width: 200px;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
    
    .user-avatar {
        align-self: center;
    }
    
    .post-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<script>
    // 募集削除ダイアログの表示
    function showDeleteRecruitmentDialog(recruitmentId, title) {
        document.getElementById('delete-recruitment-title').textContent = title;
        document.getElementById('delete-recruitment-form').action = `/recruitment/${recruitmentId}`;
        document.getElementById('delete-recruitment-modal').style.display = 'block';
    }

    // 募集削除ダイアログの非表示
    function hideDeleteRecruitmentDialog() {
        document.getElementById('delete-recruitment-modal').style.display = 'none';
    }

    // モーダル外クリックでダイアログを閉じる
    window.onclick = function(event) {
        const modal = document.getElementById('delete-recruitment-modal');
        if (event.target === modal) {
            hideDeleteRecruitmentDialog();
        }
    }

    // 申請モーダルの表示
    function openApplyModal(recruitmentId, title) {
        document.getElementById('recruitment_title').value = title;
        document.getElementById('applyForm').action = `/recruitment/${recruitmentId}/apply`;
        document.getElementById('applyModal').style.display = 'block';
    }

    // 申請モーダルの非表示
    function closeApplyModal() {
        document.getElementById('applyModal').style.display = 'none';
    }

    // 申請モーダル外クリックでダイアログを閉じる
    window.onclick = function(event) {
        const applyModal = document.getElementById('applyModal');
        const deleteModal = document.getElementById('delete-recruitment-modal');
        if (event.target === applyModal) {
            closeApplyModal();
        }
        if (event.target === deleteModal) {
            hideDeleteRecruitmentDialog();
        }
    }
</script>

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
