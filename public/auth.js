// 認証状態をチェックする共通関数
async function checkAuth() {
    const token = localStorage.getItem('token');

    if (!token) {
        // トークンが存在しない場合はログインページにリダイレクト
        if (window.location.pathname !== '/login' && window.location.pathname !== '/') {
            window.location.href = '/login';
        }
        return false;
    }

    try {
        const response = await fetch('/api/user/profile', {
            headers: {
                'Authorization': `Bearer ${token}`
            },
            credentials: 'include'
        });

        if (!response.ok) {
            if (response.status === 401) {
                // トークンが無効な場合は削除
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                
                // 現在のページが保護されたページの場合はログインページにリダイレクト
                const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
                if (protectedPages.includes(window.location.pathname)) {
                    window.location.href = '/login';
                }
                return false;
            }
            throw new Error('認証チェックに失敗しました');
        }

        const data = await response.json();
        
        // ユーザー情報をローカルストレージに更新
        localStorage.setItem('user', JSON.stringify(data));
        
        // 認証成功後にナビゲーションリンクを有効化
        enableNavigationLinks();
        
        // initializeNavigationが利用可能な場合は呼び出す
        if (typeof initializeNavigation === 'function') {
            await initializeNavigation();
        }
        
        return true;
    } catch (error) {
        // エラーが発生した場合もトークンを削除
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        // 保護されたページの場合はログインページにリダイレクト
        const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
        if (protectedPages.includes(window.location.pathname)) {
            window.location.href = '/login';
        }
        
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
    const token = localStorage.getItem('token');
    return {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    };
}

// ログイン状態の確認
document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    if (!token) {
        if (window.location.pathname !== '/login' && window.location.pathname !== '/') {
            window.location.href = '/login';
        }
        return;
    }

    // トークンが存在する場合、ユーザー情報を取得してナビゲーションを更新
    initializeNavigation();
});

// ログイン成功後の処理
function handleLoginSuccess(token, user) {
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    
    // 現在のページが保護されたページかチェック
    const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
    const currentPath = window.location.pathname;
    
    if (protectedPages.includes(currentPath)) {
        // 既に保護されたページにいる場合はリロード
        window.location.reload();
    } else {
        // ホームページにリダイレクト
        window.location.href = '/';
    }
}

// ログアウト処理
function handleLogout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    
    // 現在のページが保護されたページかチェック
    const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
    const currentPath = window.location.pathname;
    
    if (protectedPages.includes(currentPath)) {
        window.location.href = '/login';
    } else {
        // ホームページにリダイレクト
        window.location.href = '/';
    }
}

// 認証状態の確認
function checkAuthStatus() {
    const token = localStorage.getItem('token');
    const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
    const currentPath = window.location.pathname;
    
    if (!token && protectedPages.includes(currentPath)) {
        window.location.href = '/login';
        return false;
    }
    
    return true;
} 