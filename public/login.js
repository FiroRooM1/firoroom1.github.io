document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginTab = document.querySelector('[data-tab="login"]');
    const registerTab = document.querySelector('[data-tab="register"]');

    // タブ切り替え機能
    if (loginTab && registerTab) {
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
    }

    // 新規登録フォームの処理
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('registerUsername').value;
            const displayName = document.getElementById('displayName').value;
            const summonerName = document.getElementById('summonerName').value;
            const password = document.getElementById('registerPassword').value;

            if (!username || !displayName || !summonerName || !password) {
                showError('全ての項目を入力してください');
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
                if (loginTab) {
                    loginTab.click();
                }
            } catch (error) {
                showError(error.message);
            }
        });
    }

    // ログインフォームの処理
    if (loginForm) {
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

                // ログイン成功メッセージを表示
                showSuccess('ログインが完了しました。リダイレクトしています...');

                // 少し待ってからホームページにリダイレクト
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000);
            } catch (error) {
                showError(error.message);
            }
        });
    }

    // エラーメッセージの表示
    function showError(message) {
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.querySelector('.auth-container').insertBefore(errorDiv, document.querySelector('.auth-form.active'));
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // 成功メッセージの表示
    function showSuccess(message) {
        const existingSuccess = document.querySelector('.success-message');
        if (existingSuccess) {
            existingSuccess.remove();
        }

        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        document.querySelector('.auth-container').insertBefore(successDiv, document.querySelector('.auth-form.active'));
    }
}); 