<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $party->name }} - パーティールーム</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">LoL フレンド募集</div>
            <div class="nav-links">
                <span>ようこそ、{{ Auth::user()->name ?? 'サモナー' }}さん！</span>
                <a href="{{ route('parties.index') }}" class="nav-link">パーティー一覧</a>
                <a href="{{ route('friends.index') }}" class="nav-link">ダッシュボード</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

    <div class="party-room-container">
        <div class="party-info-header">
            <div class="party-details">
                <h1 class="party-name">{{ $party->name }}</h1>
                <div class="party-meta">
                    <span class="game-mode">{{ $party->recruitment->game_mode }}</span>
                    <span class="lane">{{ $party->recruitment->lane }}</span>
                    <span class="party-status {{ $party->status }}">{{ $party->status === 'active' ? '活動中' : '終了' }}</span>
                </div>
            </div>
            <div class="party-actions">
                @if($party->isLeader(Auth::id()))
                    <button type="button" class="close-btn" onclick="showClosePartyDialog()">
                        <span class="close-icon">🔒</span>
                        パーティーを閉じる
                    </button>
                @endif
            </div>
        </div>

        <div class="party-room-content">
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages">
                    @foreach($party->messages as $message)
                        <div class="message-block {{ $message->user_id === Auth::id() ? 'own-message' : '' }}">
                            <div class="message-header">
                                <div class="user-avatar">
                                    @if($message->user->summoner_icon)
                                        <img src="https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/{{ $message->user->summoner_icon }}.png" 
                                             alt="サモナーアイコン" class="avatar-image">
                                    @else
                                        <div class="avatar-placeholder">?</div>
                                    @endif
                                </div>
                                <div class="message-info">
                                    <div class="user-name">{{ $message->user->name }}</div>
                                    <div class="message-time">{{ $message->created_at->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}</div>
                                </div>
                            </div>
                            <div class="message-content">
                                {{ $message->message }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="chat-input-container">
                    <form id="chat-form" class="chat-form">
                        @csrf
                        <div class="input-group">
                            <input type="text" id="message-input" name="message" placeholder="メッセージを入力..." maxlength="1000" required>
                            <button type="submit" class="send-btn">
                                送信
                            </button>
                        </div>
                        <div class="char-count">
                            <span id="char-count">0</span>/1000
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="members-section">
                <h3>パーティーメンバー</h3>
                <div class="members-list">
                    @foreach($party->members as $member)
                        <div class="member-item">
                            <div class="member-avatar">
                                @if($member->user->summoner_icon)
                                    <img src="https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/{{ $member->user->summoner_icon }}.png" 
                                         alt="サモナーアイコン" class="avatar-image">
                                @else
                                    <div class="avatar-placeholder">?</div>
                                @endif
                            </div>
                            <div class="member-info">
                                <div class="member-name">{{ $member->user->name }}</div>
                                <div class="member-role {{ $member->role }}">
                                    {{ $member->role === 'leader' ? 'リーダー' : 'メンバー' }}
                                </div>
                                @if($member->user->solo_rank)
                                    <div class="member-rank">
                                        <img src="/images/rankIMG/Rank={{ ucfirst(strtolower($member->user->solo_rank['tier'])) }}.png" 
                                             alt="{{ $member->user->solo_rank['tier'] }} {{ $member->user->solo_rank['rank'] }}" 
                                             class="rank-icon">
                                        <span>{{ $member->user->solo_rank['tier'] }} {{ $member->user->solo_rank['rank'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

<!-- パーティーを閉じる確認ダイアログ -->
<div id="close-party-modal" class="custom-modal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h2>パーティーを閉じる</h2>
            <span class="close-modal-btn" onclick="hideClosePartyDialog()">&times;</span>
        </div>
        <div class="custom-modal-body">
            <p>このパーティーを閉じますか？</p>
            <p class="warning-text">この操作は取り消すことができません。</p>
        </div>
        <div class="custom-modal-footer">
            <form method="POST" action="{{ route('parties.close', $party) }}" style="display: inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="confirm-btn">閉じる</button>
            </form>
            <button type="button" class="cancel-btn" onclick="hideClosePartyDialog()">キャンセル</button>
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

.party-room-container {
    min-height: 100vh;
    background: url('/images/Teemo_47.jpg') no-repeat center center;
    background-size: cover;
    background-attachment: scroll;
    padding: 2rem;
    padding-top: 6rem;
    color: white;
}

.party-info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(0, 0, 0, 0.7);
    padding: 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.party-name {
    color: #f0c040;
    font-size: 2rem;
    font-weight: bold;
    margin: 0 0 1rem 0;
}

.party-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.game-mode, .lane, .party-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
    text-transform: uppercase;
}

.game-mode {
    background: #00bfff;
    color: #000;
}

.lane {
    background: #6c5ce7;
    color: #ffffff;
}

.party-status.active {
    background: #28a745;
    color: white;
}

.party-status.closed {
    background: #6c757d;
    color: white;
}

.close-btn {
    background: #dc3545;
    color: #ffffff;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: #c82333;
}

.party-room-content {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
}

.chat-container {
    background: rgba(0, 0, 0, 0.8);
    border: 2px solid #f0c040;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    display: flex;
    flex-direction: column;
    height: 70vh;
    width: 100%; /* Ensure it takes full width of the grid cell */
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message-block {
    display: flex;
    flex-direction: column;
    max-width: 80%;
    padding: 1rem;
    border-radius: 15px;
    background: rgba(0, 0, 0, 0.6);
    border: 1px solid rgba(0, 191, 255, 0.5); /* ボーダーを薄く、透明度を下げる */
    position: relative;
    align-self: flex-start; /* デフォルトで左寄せ */
}

.message-block.own-message {
    align-self: flex-end; /* 自分のメッセージは右寄せ */
    background: rgba(240, 192, 64, 0.3); /* ゴールド系の背景 */
    border-color: rgba(240, 192, 64, 0.5); /* ゴールド色のボーダー、透明度を下げる */
    border-bottom-right-radius: 5px; /* 右下角を小さく */
}

.message-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 0.75rem;
}

.user-avatar {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid #f0c040; /* ボーダーを薄く */
    background: transparent; /* 背景を透明に */
}

/* 自分のメッセージのアバターは特別な設定なし */
.message-block.own-message .user-avatar {
    border-color: #f0c040; /* 同じゴールド色を維持 */
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block; /* インライン要素の余白を削除 */
    border-radius: 50%; /* 画像自体も円形に */
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #f0c040;
    font-weight: bold;
    background: rgba(0, 0, 0, 0.3); /* プレースホルダーのみ背景を設定 */
}

.message-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
}

.user-name {
    font-weight: bold;
    color: #ffffff;
    font-size: 1rem;
}

.message-block.own-message .user-name {
    color: #ffffff; /* 自分のメッセージでも白文字 */
}

.message-time {
    color: #888;
    font-size: 0.8rem;
}

.message-block.own-message .message-time {
    color: rgba(255, 255, 255, 0.8); /* 自分のメッセージでは少し明るいグレー */
}

.message-content {
    color: #ffffff;
    font-size: 1rem;
    line-height: 1.4;
    word-wrap: break-word;
}

.chat-input-container {
    padding: 1.5rem;
    border-top: 1px solid #f0c040;
    background: rgba(0, 0, 0, 0.9);
    border-radius: 0 0 15px 15px;
}

.chat-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.input-group {
    display: flex;
    gap: 1rem;
    align-items: center;
}

#message-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #00bfff;
    border-radius: 25px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
}

#message-input:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 10px rgba(240, 192, 64, 0.3);
}

.send-btn {
    background: #f0c040;
    color: #000;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 60px;
    justify-content: center;
}

.send-btn:hover {
    background: #d4af37;
    transform: translateY(-1px);
}

.send-icon {
    font-size: 1.2rem;
}

.char-count {
    text-align: right;
    color: #888;
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

.chat-closed {
    padding: 2rem;
    text-align: center;
    color: #888;
}

.members-section {
    background: rgba(0, 0, 0, 0.8);
    border: 2px solid #00bfff;
    border-radius: 15px;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
    height: fit-content;
}

.members-section h3 {
    color: #f0c040;
    margin: 0 0 1.5rem 0;
    font-size: 1.3rem;
    text-align: center;
    border-bottom: 2px solid #00bfff;
    padding-bottom: 0.5rem;
}

.members-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.member-item {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 10px;
    border: 1px solid #00bfff;
}

.member-avatar {
    flex-shrink: 0;
}

.avatar-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #f0c040;
}

.avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #f0c040;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: #f0c040;
    font-weight: bold;
}

.member-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.member-name {
    color: #f0c040;
    font-weight: bold;
    font-size: 1rem;
}

.member-role {
    padding: 0.25rem 0.5rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: bold;
    align-self: flex-start;
}

.member-role.leader {
    background: #f0c040;
    color: #000;
}

.member-role.member {
    background: #00bfff;
    color: #000;
}

.member-rank {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #c89b3c;
}

.rank-icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
}

@media (max-width: 1024px) {
    .party-room-content {
        grid-template-columns: 1fr;
    }
    
    .members-section {
        order: -1;
    }
}

/* レスポンシブデザイン */
@media (max-width: 768px) {
    .party-room-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .chat-container {
        height: 60vh;
    }
    
    .message-block {
        max-width: 95%;
    }
    
    .user-avatar {
        width: 35px;
        height: 35px;
    }
    
    .user-name {
        font-size: 0.9rem;
    }
    
    .message-time {
        font-size: 0.7rem;
    }
    
    .message-content {
        font-size: 0.9rem;
    }
    
    .input-group {
        gap: 0.5rem;
    }
    
    #message-input {
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .send-btn {
        padding: 0.6rem 0.8rem;
        min-width: 50px;
    }
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
    .party-room-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .chat-container {
        height: 60vh;
    }
    
    .message-block {
        max-width: 95%;
    }
    
    .user-avatar {
        width: 35px;
        height: 35px;
    }
    
    .user-name {
        font-size: 0.9rem;
    }
    
    .message-time {
        font-size: 0.7rem;
    }
    
    .message-content {
        font-size: 0.9rem;
    }
    
    .input-group {
        gap: 0.5rem;
    }
    
    #message-input {
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .send-btn {
        padding: 0.6rem 0.8rem;
        min-width: 50px;
    }
}
</style>

<script>
// 現在のユーザーのサモナーアイコンIDを取得
const currentUserIconId = '{{ Auth::user()->summoner_icon ?? "" }}';

document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const charCount = document.getElementById('char-count');
    
    // 文字数カウント
    if (messageInput && charCount) {
        messageInput.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 1000) {
                this.style.borderColor = '#dc3545';
                charCount.style.color = '#dc3545';
            } else {
                this.style.borderColor = '#00bfff';
                charCount.style.color = '#888';
            }
        });
    }
    
    // チャット送信
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            // メッセージを送信
            fetch(`/parties/{{ $party->id }}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // メッセージをチャットに追加
                    addMessageToChat(data.message.user_name, data.message.message, data.message.created_at, true);
                    messageInput.value = '';
                    charCount.textContent = '0';
                    messageInput.style.borderColor = '#00bfff';
                    charCount.style.color = '#888';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('メッセージの送信に失敗しました。');
            });
        });
    }
    
    // チャットにメッセージを追加
    function addMessageToChat(userName, message, time, isOwn) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-block ${isOwn ? 'own-message' : ''}`;
        
        // 自分のメッセージの場合はサモナーアイコンを表示、他人の場合はプレースホルダー
        const avatarHtml = isOwn && currentUserIconId 
            ? `<img src="https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/${currentUserIconId}.png" alt="サモナーアイコン" class="avatar-image">`
            : `<div class="avatar-placeholder">?</div>`;
        
        messageDiv.innerHTML = `
            <div class="message-header">
                <div class="user-avatar">
                    ${avatarHtml}
                </div>
                <div class="message-info">
                    <div class="user-name">${userName}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
            <div class="message-content">${message}</div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // 定期的にメッセージを更新（簡易的なリアルタイム更新）
    setInterval(function() {
        fetch(`/parties/{{ $party->id }}/messages`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                // 新しいメッセージがあるかチェック
                const currentMessageCount = chatMessages.children.length;
                if (data.messages.length > currentMessageCount) {
                    // 新しいメッセージを追加
                    for (let i = currentMessageCount; i < data.messages.length; i++) {
                        const msg = data.messages[i];
                        addMessageToChat(msg.user_name, msg.message, msg.created_at, msg.is_own);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
        });
    }, 3000); // 3秒ごとに更新
});

// パーティーを閉じるダイアログの表示
function showClosePartyDialog() {
    document.getElementById('close-party-modal').style.display = 'block';
}

// パーティーを閉じるダイアログの非表示
function hideClosePartyDialog() {
    document.getElementById('close-party-modal').style.display = 'none';
}

// モーダル外クリックでダイアログを閉じる
window.onclick = function(event) {
    const modal = document.getElementById('close-party-modal');
    if (event.target === modal) {
        hideClosePartyDialog();
    }
}
</script>
</body>
</html>
