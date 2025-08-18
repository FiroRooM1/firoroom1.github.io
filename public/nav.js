// ナビゲーション初期化関数
async function initializeNavigation() {
    try {
        const response = await fetch('/api/user/profile', {
            credentials: 'include'
        });

        if (!response.ok) {
            if (response.status === 401) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/';
                return null;
            }
            
            const errorData = await response.json();
            throw new Error(errorData.message || 'プロフィール取得に失敗しました');
        }

        const profile = await response.json();
        
        // ナビゲーションバーの要素を更新
        const navProfileImage = document.getElementById('navProfileImage');
        const navDisplayName = document.getElementById('navDisplayName');
        const navLogoutBtn = document.getElementById('navLogoutBtn');

        // プロフィール画像の設定
        if (navProfileImage) {
            let iconUrl = '/default-avatar.png';
            
            // .dontpushユーザーの場合はTaliyah_1.jpgを使用
            if (profile.username === '.dontpush') {
                iconUrl = '/IconadminIMG/Taliyah_1.jpg';
            } else if (profile.summoner_info && profile.summoner_info.iconUrl) {
                iconUrl = profile.summoner_info.iconUrl;
            }
            
            navProfileImage.style.backgroundImage = `url(${iconUrl})`;
            navProfileImage.style.backgroundSize = 'cover';
            navProfileImage.style.backgroundPosition = 'center';
            navProfileImage.style.display = 'block';
            
            // プロフィール画像にクリックイベントを追加
            navProfileImage.style.cursor = 'pointer';
            navProfileImage.title = 'プロフィールページへ';
            navProfileImage.addEventListener('click', () => {
                window.location.href = '/profile';
            });
        }
        
        if (navDisplayName) {
            navDisplayName.textContent = profile.display_name || profile.username;
            
            // 表示名にクリックイベントを追加
            navDisplayName.style.cursor = 'pointer';
            navDisplayName.title = 'プロフィールページへ';
            navDisplayName.addEventListener('click', () => {
                window.location.href = '/profile';
            });
        }

        // ログアウトボタンのイベントリスナー
        if (navLogoutBtn) {
            navLogoutBtn.addEventListener('click', async () => {
                if (confirm('ログアウトしますか？')) {
                    try {
                        // サーバー側のログアウトAPIを呼び出し
                        await fetch('/api/logout', {
                            method: 'POST',
                            credentials: 'include'
                        });
                    } catch (error) {
                        // エラーが発生しても続行
                    }
                    
                    // ローカルストレージをクリア
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    
                    // トップページにリダイレクト
                    window.location.href = '/';
                }
            });
        }

        // 認証後にナビゲーションリンクを有効化
        enableNavigationLinks();

        return profile;
    } catch (error) {
        // エラーが発生してもナビゲーションリンクは有効化
        enableNavigationLinks();
        
        return null;
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

// ページ読み込み時に実行
document.addEventListener('DOMContentLoaded', async () => {
    // Discord OAuthの認証状態確認
    try {
        const response = await fetch('/api/auth/status', {
            credentials: 'include'
        });
        
        if (response.ok && (await response.json()).authenticated) {
            // 認証済みの場合、ナビゲーションリンクを有効化
            enableNavigationLinks();
            await initializeNavigation();
        } else {
            // 未認証の場合、保護されたページへのアクセスを制限
            const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
            const currentPage = window.location.pathname;
            
            if (protectedPages.includes(currentPage)) {
                window.location.href = '/';
                return;
            }
        }
    } catch (error) {
        console.error('認証状態確認エラー:', error);
        // エラーの場合、保護されたページへのアクセスを制限
        const protectedPages = ['/matching', '/posts', '/requests', '/party', '/profile', '/parties'];
        const currentPage = window.location.pathname;
        
        if (protectedPages.includes(currentPage)) {
            window.location.href = '/';
            return;
        }
    }
});

// エラーメッセージの表示
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    document.body.insertBefore(errorDiv, document.body.firstChild);
    setTimeout(() => errorDiv.remove(), 5000);
}

// エクスポート
window.initializeNavigation = initializeNavigation; 