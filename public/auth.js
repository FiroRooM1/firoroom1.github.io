// Discord OAuth認証状態をチェックする共通関数
async function checkAuth() {
    try {
        const response = await fetch('/api/auth/status', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('認証チェックに失敗しました');
        }

        const data = await response.json();
        
        if (data.authenticated) {
            // ユーザー情報をローカルストレージに保存
            localStorage.setItem('user', JSON.stringify(data.user));
            
            // セッションIDも保存（デバッグ用）
            if (data.sessionId) {
                localStorage.setItem('sessionId', data.sessionId);
                console.log('セッションID保存:', data.sessionId);
            }
            
            // 認証成功後にナビゲーションリンクを有効化
            enableNavigationLinks();
            
            // initializeNavigationが利用可能な場合は呼び出す
            if (typeof initializeNavigation === 'function') {
                await initializeNavigation();
            }
            
            return true;
        } else {
            // 認証されていない場合
            localStorage.removeItem('user');
            return false;
        }
    } catch (error) {
        console.error('認証チェックエラー:', error);
        localStorage.removeItem('user');
        return false;
    }
}

// ナビゲーションリンクを有効化する関数
function enableNavigationLinks() {
    const authRequiredLinks = document.querySelectorAll('.auth-required.disabled-link');
    authRequiredLinks.forEach(link => {
        link.classList.remove('auth-required', 'disabled-link');
        link.removeAttribute('data-tooltip');
    });
}

// APIリクエスト用の共通ヘッダーを取得
function getAuthHeaders() {
    return {
        'Content-Type': 'application/json'
    };
}

// ログイン状態の確認（ホームページ以外でのみ実行）
document.addEventListener('DOMContentLoaded', async () => {
    // ホームページの場合は処理をスキップ（index.jsで処理する）
    if (window.location.pathname === '/') {
        return;
    }
    
    const isAuthenticated = await checkAuth();
    
    if (!isAuthenticated) {
        // 保護されたページの場合はホームページにリダイレクト
        const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
        if (protectedPages.includes(window.location.pathname)) {
            window.location.href = '/';
        }
        return;
    }

    // 認証されている場合、ナビゲーションを更新
    if (typeof initializeNavigation === 'function') {
        await initializeNavigation();
    }
});

// Discordログイン処理
function handleDiscordLogin() {
    window.location.href = '/auth/discord';
}

// ログアウト処理
function handleLogout() {
    localStorage.removeItem('user');
    
    // 現在のページが保護されたページかチェック
    const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
    const currentPath = window.location.pathname;
    
    if (protectedPages.includes(currentPath)) {
        window.location.href = '/';
    } else {
        // ホームページにリダイレクト
        window.location.href = '/';
    }
}

// 認証状態の確認
function checkAuthStatus() {
    const user = localStorage.getItem('user');
    const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
    const currentPath = window.location.pathname;
    
    if (!user && protectedPages.includes(currentPath)) {
        window.location.href = '/';
        return false;
    }
    
    return true;
}

// ユーザー情報を取得
function getCurrentUser() {
    const userStr = localStorage.getItem('user');
    if (userStr) {
        try {
            return JSON.parse(userStr);
        } catch (error) {
            console.error('ユーザー情報の解析エラー:', error);
            return null;
        }
    }
    return null;
}
