<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集 - League of Legends フレンド募集</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">LoL フレンド募集</div>
            <div class="nav-links">
                <span>ようこそ、{{ $user->name ?? 'サモナー' }}さん！</span>
                <a href="{{ route('profile.show') }}" class="nav-link">プロフィール</a>
                <a href="{{ route('friends.index') }}" class="nav-link">ダッシュボード</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

<div class="profile-edit-container">
    <div class="profile-edit-header">
        <h1 class="profile-edit-title">プロフィール編集</h1>
        <a href="{{ route('profile.show') }}" class="back-button">戻る</a>
    </div>

    <div class="profile-edit-content">
        <form method="POST" action="{{ route('profile.update') }}" class="edit-form">
            @csrf
            @method('PATCH')

            <!-- 表示名 -->
            <div class="form-group">
                <label for="name" class="form-label">表示名</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                       class="form-input @error('name') error @enderror" required>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- メールアドレス -->
            <div class="form-group">
                <label for="email" class="form-label">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                       class="form-input @error('email') error @enderror" required>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- パスワード変更セクション -->
            <div class="password-section">
                <h3 class="password-title">パスワード変更</h3>
                <p class="password-note">パスワードを変更しない場合は空欄のままにしてください</p>
                
                <!-- 現在のパスワード -->
                <div class="form-group">
                    <label for="current_password" class="form-label">現在のパスワード</label>
                    <input type="password" id="current_password" name="current_password" 
                           class="form-input @error('current_password') error @enderror">
                    @error('current_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 新しいパスワード -->
                <div class="form-group">
                    <label for="new_password" class="form-label">新しいパスワード</label>
                    <input type="password" id="new_password" name="new_password" 
                           class="form-input @error('new_password') error @enderror" 
                           minlength="8">
                    @error('new_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 新しいパスワード確認 -->
                <div class="form-group">
                    <label for="new_password_confirmation" class="form-label">新しいパスワード確認</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" 
                           class="form-input @error('new_password_confirmation') error @enderror" 
                           minlength="8">
                    @error('new_password_confirmation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- ボタン -->
            <div class="form-actions">
                <button type="submit" class="save-button">保存</button>
                <a href="{{ route('profile.show') }}" class="cancel-button">キャンセル</a>
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

.profile-edit-container {
    min-height: 100vh;
    background: url('/images/Teemo_47.jpg') no-repeat center center;
    background-size: cover;
    background-attachment: scroll;
    padding: 2rem;
    padding-top: 6rem;
    color: white;
}

.profile-edit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: rgba(0, 0, 0, 0.7);
    padding: 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.profile-edit-title {
    color: #f0c040;
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    margin: 0;
}

.back-button {
    background: rgba(0, 0, 0, 0.8);
    color: #f0c040;
    padding: 0.75rem 1.5rem;
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

.profile-edit-content {
    max-width: 600px;
    margin: 0 auto;
}

.edit-form {
    background: rgba(0, 0, 0, 0.8);
    padding: 2rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    border: 2px solid #f0c040;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    color: #f0c040;
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #00bfff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #f0c040;
    box-shadow: 0 0 10px rgba(240, 192, 64, 0.5);
    background: rgba(0, 0, 0, 0.7);
}

.form-input.error {
    border-color: #ef4444;
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
}

.error-message {
    color: #ef4444;
    font-size: 0.9rem;
    margin-top: 0.25rem;
    display: block;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.password-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #f0c040;
}

.password-title {
    color: #f0c040;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
}

.password-note {
    color: #e6f3ff;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    font-style: italic;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.save-button {
    background: #00bfff;
    color: #000;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #00bfff;
    font-size: 1rem;
}

.save-button:hover {
    background: #0099cc;
    border-color: #0099cc;
    transform: translateY(-2px);
}

.cancel-button {
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
    display: inline-block;
}

.cancel-button:hover {
    background: #f0c040;
    color: #000;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .profile-edit-container {
        padding: 1rem;
    }
    
    .profile-edit-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .save-button, .cancel-button {
        width: 100%;
        max-width: 300px;
        text-align: center;
    }
    }
</style>
</body>
</html>
