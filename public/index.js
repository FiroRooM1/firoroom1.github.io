// トップページのメイン処理
document.addEventListener('DOMContentLoaded', async function() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const authContainer = document.getElementById('authContainer');
    const welcomeSection = document.getElementById('welcomeSection');

    try {
        console.log('認証状態をチェック中...');
        // 認証状態をチェック
        const isAuthenticated = await checkAuth();
        console.log('認証状態:', isAuthenticated);
        
        if (isAuthenticated) {
            // 認証済みの場合
            const user = getCurrentUser();
            console.log('ユーザー情報:', user);
            
            if (user && user.isComplete) {
                // ユーザー登録完了済み：ウェルカムセクションを表示
                console.log('ウェルカムセクションを表示');
                showWelcomeSection();
            } else {
                // ユーザー登録未完了：登録完了ページにリダイレクト
                console.log('登録完了ページにリダイレクト');
                window.location.href = '/complete-registration';
            }
        } else {
            // 未認証の場合：Discordログイン画面を表示
            console.log('Discordログイン画面を表示');
            showAuthContainer();
        }
    } catch (error) {
        console.error('認証チェックエラー:', error);
        // エラーが発生した場合もログイン画面を表示
        showAuthContainer();
    } finally {
        // ローディングインジケーターを非表示
        loadingIndicator.style.display = 'none';
    }
});

// Discordログイン画面を表示
function showAuthContainer() {
    console.log('showAuthContainer呼び出し');
    const authContainer = document.getElementById('authContainer');
    const welcomeSection = document.getElementById('welcomeSection');
    
    console.log('authContainer:', authContainer);
    console.log('welcomeSection:', welcomeSection);
    
    if (authContainer) {
        authContainer.style.display = 'block';
        console.log('Discordログイン画面を表示しました');
    }
    if (welcomeSection) {
        welcomeSection.style.display = 'none';
        console.log('ウェルカムセクションを非表示にしました');
    }
}

// ウェルカムセクションを表示
function showWelcomeSection() {
    console.log('showWelcomeSection呼び出し');
    const authContainer = document.getElementById('authContainer');
    const welcomeSection = document.getElementById('welcomeSection');
    
    console.log('authContainer:', authContainer);
    console.log('welcomeSection:', welcomeSection);
    
    if (authContainer) {
        authContainer.style.display = 'none';
        console.log('Discordログイン画面を非表示にしました');
    }
    if (welcomeSection) {
        welcomeSection.style.display = 'block';
        console.log('ウェルカムセクションを表示しました');
    }
}

// Discordログインボタンのクリックイベント
document.addEventListener('click', function(e) {
    if (e.target.closest('.discord-login-btn')) {
        e.preventDefault();
        handleDiscordLogin();
    }
});

// ログアウトボタンのクリックイベント
document.addEventListener('click', function(e) {
    if (e.target.closest('#navLogoutBtn')) {
        e.preventDefault();
        handleLogout();
    }
}); 