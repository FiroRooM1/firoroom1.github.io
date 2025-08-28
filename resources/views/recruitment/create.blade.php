<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規募集作成 - League of Legends フレンド募集</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">LoL フレンド募集</div>
            <div class="nav-links">
                <span>ようこそ、{{ Auth::user()->name ?? 'サモナー' }}さん！</span>
                <a href="{{ route('friends.index') }}" class="nav-link">ダッシュボード</a>
                <a href="{{ route('recruitment.index') }}" class="nav-link">募集一覧</a>
                <a href="{{ route('profile.show') }}" class="nav-link">プロフィール</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

    <div class="create-container">
        <!-- ヘッダーセクション -->
        <div class="create-header">
            <h1 class="create-title">新規募集作成</h1>
            <a href="{{ route('recruitment.index') }}" class="back-btn">
                <span class="back-icon">←</span>
                募集一覧に戻る
            </a>
        </div>

        @if($errors->any())
            <div class="error-message">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 募集作成フォーム -->
        <div class="create-form-container">
            <form method="POST" action="{{ route('recruitment.store') }}" class="create-form">
                @csrf
                
                <div class="form-section">
                    <h2 class="section-title">募集内容</h2>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">募集タイトル *</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="{{ old('title') }}" required 
                               placeholder="募集のタイトルを入力してください">
                    </div>

                    <div class="form-group">
                        <label for="game_mode" class="form-label">ゲームモード *</label>
                        <select id="game_mode" name="game_mode" class="form-select" required>
                            <option value="">選択してください</option>
                            <option value="ノーマル" {{ old('game_mode') == 'ノーマル' ? 'selected' : '' }}>ノーマル</option>
                            <option value="ランク（デュオ）" {{ old('game_mode') == 'ランク（デュオ）' ? 'selected' : '' }}>ランク（デュオ）</option>
                            <option value="ランク（フレックス）" {{ old('game_mode') == 'ランク（フレックス）' ? 'selected' : '' }}>ランク（フレックス）</option>
                            <option value="ランダムミッド" {{ old('game_mode') == 'ランダムミッド' ? 'selected' : '' }}>ランダムミッド</option>
                            <option value="アリーナ" {{ old('game_mode') == 'アリーナ' ? 'selected' : '' }}>アリーナ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lane" class="form-label">レーン *</label>
                        <select id="lane" name="lane" class="form-select" required>
                            <option value="">選択してください</option>
                            <option value="トップ" {{ old('lane') == 'トップ' ? 'selected' : '' }}>トップ</option>
                            <option value="ジャングル" {{ old('lane') == 'ジャングル' ? 'selected' : '' }}>ジャングル</option>
                            <option value="ミッド" {{ old('lane') == 'ミッド' ? 'selected' : '' }}>ミッド</option>
                            <option value="ボット" {{ old('lane') == 'ボット' ? 'selected' : '' }}>ボット</option>
                            <option value="サポート" {{ old('lane') == 'サポート' ? 'selected' : '' }}>サポート</option>
                            <option value="オートフィル" {{ old('lane') == 'オートフィル' ? 'selected' : '' }}>オートフィル</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">募集メッセージ *</label>
                        <textarea id="content" name="content" class="form-textarea" rows="6" placeholder="募集内容を詳しく書いてください（例：ランク戦で一緒にプレイできる人を探しています。楽しくプレイしましょう！）" required>{{ old('content') }}</textarea>
                        <div class="char-count">
                            <span id="char-count">0</span> / 1000文字
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <span class="submit-icon">📝</span>
                        募集を投稿する
                    </button>
                    <a href="{{ route('recruitment.index') }}" class="cancel-btn">キャンセル</a>
                </div>
            </form>
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

.create-container {
    min-height: 100vh;
    background: url('/images/Teemo_47.jpg') no-repeat center center;
    background-size: cover;
    background-attachment: scroll;
    padding: 2rem;
    padding-top: 6rem;
    color: white;
}

.create-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(0, 0, 0, 0.7);
    padding: 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.create-title {
    color: #f0c040;
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    margin: 0;
}

.back-btn {
    background: rgba(0, 0, 0, 0.8);
    color: #f0c040;
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

.back-btn:hover {
    background: #f0c040;
    color: #000;
    transform: translateY(-2px);
}

.back-icon {
    font-size: 1.2rem;
    font-weight: bold;
}

.error-message {
    background: rgba(220, 53, 69, 0.9);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    backdrop-filter: blur(10px);
}

.error-message ul {
    margin: 0;
    padding-left: 1.5rem;
}

.error-message li {
    margin-bottom: 0.5rem;
}

.create-form-container {
    background: rgba(0, 0, 0, 0.8);
    border: 2px solid #f0c040;
    border-radius: 15px;
    padding: 2rem;
    backdrop-filter: blur(10px);
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: 2rem;
}

.section-title {
    color: #f0c040;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    border-bottom: 2px solid #f0c040;
    padding-bottom: 0.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    color: #f0c040;
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.form-select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #00bfff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 15px rgba(240, 192, 64, 0.5);
    background: rgba(0, 0, 0, 0.9);
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #00bfff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 15px rgba(240, 192, 64, 0.5);
    background: rgba(0, 0, 0, 0.9);
}

.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #00bfff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 1rem;
    font-family: inherit;
    resize: vertical;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-textarea:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 15px rgba(240, 192, 64, 0.5);
    background: rgba(0, 0, 0, 0.9);
}

.char-count {
    text-align: right;
    color: #888;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.submit-btn {
    background: #f0c040;
    color: #000;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #f0c040;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
}

.submit-btn:hover {
    background: #d4af37;
    border-color: #d4af37;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(240, 192, 64, 0.4);
}

.submit-icon {
    font-size: 1.2rem;
}

.cancel-btn {
    background: rgba(0, 0, 0, 0.8);
    color: #888;
    padding: 1rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: 2px solid #888;
}

.cancel-btn:hover {
    background: #888;
    color: #000;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .create-container {
        padding: 1rem;
    }
    
    .create-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .create-form-container {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .submit-btn, .cancel-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// 文字数カウント機能
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const charCountSpan = document.getElementById('char-count');
    
    if (contentTextarea && charCountSpan) {
        contentTextarea.addEventListener('input', function() {
            const charCount = this.value.length;
            charCountSpan.textContent = charCount;
            
            // 1000文字を超えた場合の警告
            if (charCount > 1000) {
                this.style.borderColor = '#dc3545';
                charCountSpan.style.color = '#dc3545';
            } else {
                this.style.borderColor = '#00bfff';
                charCountSpan.style.color = '#888';
            }
        });
    }
});
</script>
</body>
</html>
