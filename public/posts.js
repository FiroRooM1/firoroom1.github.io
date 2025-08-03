document.addEventListener('DOMContentLoaded', async () => {
    // ログイン状態の確認
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // ナビゲーション初期化
    if (typeof initializeNavigation === 'function') {
        try {
            await initializeNavigation();
        } catch (error) {
            // エラーが発生しても続行
        }
    }

    const postForm = document.getElementById('postForm');
    const titleInput = document.getElementById('title');
    const gameModeSelect = document.getElementById('gameMode');
    const mainLaneSelect = document.getElementById('mainLane');
    const descriptionTextarea = document.getElementById('description');
    const cancelBtn = document.getElementById('cancelBtn');

    // キャンセルボタンの処理
    cancelBtn.addEventListener('click', () => {
        window.location.href = '/matching';
    });

    // 投稿フォームの送信処理
    postForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const title = titleInput.value.trim();
        const gameMode = gameModeSelect.value;
        const mainLane = mainLaneSelect.value;
        const description = descriptionTextarea.value.trim();

        if (!title || !gameMode || !mainLane) {
            showError('タイトル、ゲームモード、メインレーンは必須です');
            return;
        }

        try {
            const formData = {
                title,
                gameMode,
                mainLane,
                description
            };

            const response = await fetch('/api/posts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                const result = await response.json();
                
                // 成功メッセージを表示
                showSuccessMessage('投稿が作成されました！');
                
                // 募集ページにリダイレクト
                setTimeout(() => {
                    window.location.href = '/matching';
                }, 1500);
            } else {
                const result = await response.json();
                let errorMessage = result.message || result.error || '投稿の作成に失敗しました';
                
                // エラーコードに応じたメッセージ
                if (result.code === '23503') {
                    errorMessage = 'ユーザー情報が無効です。ログインし直してください。';
                } else if (result.code === '42P01') {
                    errorMessage = 'データベースエラーが発生しました。';
                } else if (result.code === '23505') {
                    errorMessage = '重複した投稿です。';
                }
                
                throw new Error(errorMessage);
            }
        } catch (error) {
            showError(error.message);
        }
    });

    // 成功メッセージの表示
    function showSuccessMessage(message) {
        const successDiv = document.createElement('div');
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

    // エラーメッセージの表示
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.querySelector('.post-form').insertBefore(errorDiv, document.querySelector('.form-header'));
        setTimeout(() => errorDiv.remove(), 5000);
    }
}); 