document.addEventListener('DOMContentLoaded', async () => {
    // ナビゲーション初期化
    if (typeof initializeNavigation === 'function') {
        initializeNavigation();
    }

    const loadingIndicator = document.getElementById('loadingIndicator');
    const authContainer = document.getElementById('authContainer');
    const welcomeSection = document.querySelector('.welcome-section');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginTab = document.querySelector('[data-tab="login"]');
    const registerTab = document.querySelector('[data-tab="register"]');

    // 初期状態：ローディングを表示
    loadingIndicator.style.display = 'flex';
    authContainer.style.display = 'none';
    welcomeSection.style.display = 'none';

    // 念のため、3秒後に強制的にローディングを非表示にする
    setTimeout(() => {
        if (loadingIndicator && loadingIndicator.style.display === 'flex') {
            loadingIndicator.style.display = 'none';
            if (authContainer) {
                authContainer.style.display = 'block';
            }
        }
    }, 3000);

    // 認証状態をチェック
    const token = localStorage.getItem('token');
    
    if (token) {
        // ログイン済みの場合
        try {
            // タイムアウト処理を追加（3秒に短縮）
            const timeoutPromise = new Promise((_, reject) => {
                setTimeout(() => reject(new Error('認証チェックがタイムアウトしました')), 3000);
            });

            const authPromise = fetch('/api/auth/check', {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'include'
            });

            const response = await Promise.race([authPromise, timeoutPromise]);

            if (response.ok) {
                const userData = await response.json();
                
                // 認証成功 - ウェルカムセクションを表示
                
                // 確実にローディングを非表示にしてウェルカムセクションを表示
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                if (authContainer) {
                    authContainer.style.display = 'none';
                }
                if (welcomeSection) {
                    welcomeSection.style.display = 'block';
                }
                
                // ナビゲーションリンクを有効化
                if (typeof enableNavigationLinks === 'function') {
                    enableNavigationLinks();
                }
                
                // ログイン済みの場合はここで終了
                return;
            } else {
                // 認証失敗 - トークンを削除
                localStorage.removeItem('token');
                localStorage.removeItem('user');
            }
        } catch (error) {
            // エラーが発生した場合もトークンを削除
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            console.error('認証チェックエラー:', error);
        }
    }

    // 未ログインの場合のみログインフォームを表示
    if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
    }
    if (authContainer) {
    authContainer.style.display = 'block';
    }
    if (welcomeSection) {
    welcomeSection.style.display = 'none';
    }

    // タブ切り替え
    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
    });

    registerTab.addEventListener('click', () => {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
    });

    // ログインフォームの処理
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('loginUsername').value;
        const password = document.getElementById('loginPassword').value;

        if (!username || !password) {
            showError('ユーザー名とパスワードを入力してください');
            return;
        }

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'ログインに失敗しました');
            }

            // トークンをローカルストレージに保存
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));

            // ナビゲーションリンクを有効化
            if (typeof enableNavigationLinks === 'function') {
                enableNavigationLinks();
            }

            // ログイン成功メッセージを表示
            showSuccess('ログインが完了しました！');

            // フォームを非表示にしてウェルカムセクションを表示
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            if (authContainer) {
            authContainer.style.display = 'none';
            }
            if (welcomeSection) {
            welcomeSection.style.display = 'block';
            }

            // フォームをリセット
            loginForm.reset();
        } catch (error) {
            showError(error.message);
        }
    });

    // 新規登録フォームの処理
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('registerUsername').value;
        const displayName = document.getElementById('displayName').value;
        const summonerName = document.getElementById('summonerName').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('registerConfirmPassword').value;

        // 入力検証
        if (!username || !displayName || !summonerName || !password || !confirmPassword) {
            showError('全ての項目を入力してください');
            return;
        }

        if (password !== confirmPassword) {
            showError('パスワードが一致しません');
            return;
        }

        if (password.length < 6) {
            showError('パスワードは6文字以上で入力してください');
            return;
        }

        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    username,
                    displayName,
                    summonerName,
                    password
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || '登録に失敗しました');
            }

            showSuccess('登録が完了しました。ログインしてください。');
            
            // フォームをクリア
            registerForm.reset();
            
            // ログインタブに切り替え
            loginTab.click();
        } catch (error) {
            showError(error.message);
        }
    });

    // エラーメッセージの表示
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f44336;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            font-weight: bold;
            text-align: center;
        `;
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.style.opacity = '0';
            errorDiv.style.transition = 'opacity 0.5s ease';
            setTimeout(() => errorDiv.remove(), 500);
        }, 3000);
    }

    // 成功メッセージの表示
    function showSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            font-weight: bold;
            text-align: center;
        `;
        successDiv.textContent = message;
        document.body.appendChild(successDiv);

        setTimeout(() => {
            successDiv.style.opacity = '0';
            successDiv.style.transition = 'opacity 0.5s ease';
            setTimeout(() => successDiv.remove(), 500);
        }, 3000);
    }
}); 