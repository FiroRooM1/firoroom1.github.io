document.addEventListener('DOMContentLoaded', async () => {
    // Discord OAuthの認証状態確認
    try {
        const response = await fetch('/api/auth/status', {
            credentials: 'include'
        });
        
        if (!response.ok || !(await response.json()).authenticated) {
            window.location.href = '/';
            return;
        }
    } catch (error) {
        console.error('認証状態確認エラー:', error);
        window.location.href = '/';
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

    // 投稿を読み込む
    async function loadPosts() {
        try {
            const response = await fetch('/api/posts', {
                credentials: 'include'
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || errorData.error || '投稿の取得に失敗しました');
            }

            const posts = await response.json();
            
            console.log('APIレスポンス:', posts); // デバッグ用
            
            if (!Array.isArray(posts)) {
                console.error('投稿データが配列ではありません:', posts);
                throw new Error('投稿データの形式が正しくありません');
            }
            
            displayPosts(posts);
            
        } catch (error) {
            console.error('投稿取得エラー:', error);
            showError('投稿の取得に失敗しました');
        }
    }

    // 投稿を表示
    function displayPosts(posts) {
        const postsContainer = document.getElementById('postsList');
        if (!postsContainer) {
            console.error('投稿コンテナが見つかりません');
            return;
        }

        postsContainer.innerHTML = '';

        if (!posts || posts.length === 0) {
            postsContainer.innerHTML = '<p class="no-posts">投稿がありません</p>';
            return;
        }

        console.log('表示する投稿:', posts); // デバッグ用

        posts.forEach((post, index) => {
            const postElement = createPostElement(post);
            if (postElement) {
                postsContainer.appendChild(postElement);
            }
        });

        // イベントリスナーを追加
        addEventListeners();
    }

    // イベントリスナーを追加
    function addEventListeners() {
        // 申請ボタンのイベントリスナー
        const applyButtons = document.querySelectorAll('.apply-btn');
        
        applyButtons.forEach((button, index) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const postId = button.getAttribute('data-post-id');
                
                if (postId) {
                    applyToPost(postId);
                } else {
                    console.error('postIdが見つかりません');
                }
            });
        });

        // 削除ボタンのイベントリスナー
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach((button, index) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const postId = button.getAttribute('data-post-id');
                if (postId) {
                    deletePost(postId);
                } else {
                    console.error('postIdが見つかりません');
                }
            });
        });
    }

    // 投稿要素を作成
    function createPostElement(post) {
        if (!post) {
            return null;
        }

        const postDiv = document.createElement('div');
        postDiv.className = 'post-card';

        // 作者情報の取得
        let authorName = 'Unknown';
        let iconUrl = '/default-avatar.png';
        let rankText = 'ランク未設定';

        if (post.author) {
            authorName = post.author.display_name || post.author.username || 'Unknown';
            
            // .dontpushユーザーの場合はTaliyah_1.jpgを使用
            if (post.author.username === '.dontpush') {
                iconUrl = '/IconadminIMG/Taliyah_1.jpg';
            } else if (post.author.summoner_info && post.author.summoner_info.iconUrl) {
                iconUrl = post.author.summoner_info.iconUrl;
            }

            // ランク情報の取得
            if (post.author.summoner_info && post.author.summoner_info.ranks && post.author.summoner_info.ranks.length > 0) {
                const rankInfo = post.author.summoner_info.ranks[0];
                rankText = `${rankInfo.tier} ${rankInfo.rank} (${rankInfo.queueType === 'RANKED_SOLO_5x5' ? 'ソロ/デュオ' : 'フレックス'})`;
            }
        } else {
            // 作者情報がない場合のフォールバック
            console.warn('投稿の作者情報が見つかりません:', post);
        }

        // ゲームモードとレーンの表示名を設定
        const gameModeText = post.game_mode === 'RANKED_SOLO' ? 'ランク' : post.game_mode;
        const laneText = post.main_lane === 'MID' ? 'ミッド' : post.main_lane;

        // 現在のユーザー情報を取得
        const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
        const isOwnPost = post.author && post.author.id === currentUser.id;

        postDiv.innerHTML = `
            <div class="author-info">
                <img src="${iconUrl}" alt="サモナーアイコン" class="author-icon">
                <div class="author-details">
                    <h3>${authorName}</h3>
                    <p>${rankText}</p>
                </div>
            </div>
            
            <div class="post-content">
                <h2>${post.title}</h2>
                <p class="post-description">${post.description || ''}</p>
            </div>
            
            <div class="post-meta">
                <div class="post-info">
                    <span class="tag">${gameModeText}</span>
                    <span class="tag">${laneText}</span>
                </div>
                <span class="post-date">${new Date(post.created_at).toLocaleDateString()}</span>
            </div>
            
            <div class="post-actions">
                ${isOwnPost ? `
                    <button class="delete-btn" data-post-id="${post.id}">
                        <i class="fas fa-trash"></i>
                        削除
                    </button>
                ` : `
                    <button class="apply-btn" data-post-id="${post.id}">
                        <i class="fas fa-handshake"></i>
                        申請する
                    </button>
                `}
            </div>
        `;

        return postDiv;
    }

    // 申請処理
    async function applyToPost(postId) {
        showApplyModal(postId);
    }

    // 申請モーダルを表示
    function showApplyModal(postId) {
        
        // 既存のモーダルがあれば削除
        const existingModal = document.querySelector('.apply-modal');
        if (existingModal) {
            existingModal.remove();
        }

        const modal = document.createElement('div');
        modal.className = 'apply-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0, 0, 0, 0.8);
        `;
        
        modal.innerHTML = `
            <div class="modal-content" style="
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                border-radius: 12px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
                position: relative;
            ">
                <div class="modal-header" style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 25px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                ">
                    <h3 style="color: #ffd700; margin: 0; font-size: 20px;">申請フォーム</h3>
                    <button class="close-btn" style="
                        background: none;
                        border: none;
                        color: rgba(255, 255, 255, 0.6);
                        font-size: 24px;
                        cursor: pointer;
                        padding: 0;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 50%;
                        transition: all 0.3s ease;
                    ">&times;</button>
                </div>
                <form id="applyForm">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="preferredLane" style="
                            display: block;
                            margin-bottom: 8px;
                            color: #ffd700;
                            font-weight: bold;
                            font-size: 14px;
                        ">希望レーン</label>
                        <select id="preferredLane" required style="
                            width: 100%;
                            padding: 12px 15px;
                            border: 1px solid rgba(255, 255, 255, 0.2);
                            border-radius: 8px;
                            background: rgba(255, 255, 255, 0.1);
                            color: #fff;
                            font-size: 14px;
                            transition: all 0.3s ease;
                        ">
                            <option value="">選択してください</option>
                            <option value="TOP">トップ</option>
                            <option value="JUNGLE">ジャングル</option>
                            <option value="MID">ミッド</option>
                            <option value="ADC">ボット</option>
                            <option value="SUPPORT">サポート</option>
                            <option value="AUTOFILL">オートフィル</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="applyMessage" style="
                            display: block;
                            margin-bottom: 8px;
                            color: #ffd700;
                            font-weight: bold;
                            font-size: 14px;
                        ">メッセージ（任意）</label>
                        <textarea id="applyMessage" rows="4" placeholder="自己紹介や希望するプレイスタイルなどを書いてください" style="
                            width: 100%;
                            padding: 12px 15px;
                            border: 1px solid rgba(255, 255, 255, 0.2);
                            border-radius: 8px;
                            background: rgba(255, 255, 255, 0.1);
                            color: #fff;
                            font-size: 14px;
                            transition: all 0.3s ease;
                            resize: vertical;
                            min-height: 100px;
                        "></textarea>
                    </div>
                    <div class="modal-actions" style="
                        display: flex;
                        gap: 15px;
                        justify-content: flex-end;
                        margin-top: 25px;
                    ">
                        <button type="button" class="cancel-btn" style="
                            background: rgba(255, 255, 255, 0.1);
                            color: #fff;
                            border: 1px solid rgba(255, 255, 255, 0.2);
                            padding: 10px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            font-size: 14px;
                        ">キャンセル</button>
                        <button type="submit" class="submit-btn" style="
                            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
                            color: #1a1a2e;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            font-size: 14px;
                            font-weight: bold;
                        ">申請する</button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);

        // フォーム送信処理
        const form = document.getElementById('applyForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await submitApplication(postId);
            });
        } else {
            console.error('フォームが見つかりません');
        }

        // モーダルを閉じるボタンのイベントリスナーを追加
        const closeBtn = modal.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                closeApplyModal();
            });
        } else {
            console.error('閉じるボタンが見つかりません');
        }

        // キャンセルボタンのイベントリスナーを追加
        const cancelBtn = modal.querySelector('.cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                closeApplyModal();
            });
        } else {
            console.error('キャンセルボタンが見つかりません');
        }
    }

    // 申請モーダルを閉じる
    function closeApplyModal() {
        const modal = document.querySelector('.apply-modal');
        if (modal) {
            modal.remove();
        }
    }

    // 申請を送信
    async function submitApplication(postId) {
        try {
            
            const preferredLane = document.getElementById('preferredLane').value;
            const message = document.getElementById('applyMessage').value;

            if (!preferredLane) {
                showError('希望レーンを選択してください');
                return;
            }

            const response = await fetch('/api/requests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    postId,
                    preferredLane,
                    message
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || '申請の送信に失敗しました');
            }

            closeApplyModal();
            showSuccessMessage('申請を送信しました');
        } catch (error) {
            console.error('申請送信エラー:', error);
            showError(error.message);
        }
    }

    // 投稿を削除
    async function deletePost(postId) {
        if (!confirm('この投稿を削除しますか？')) {
            return;
        }

        try {
            const response = await fetch(`/api/posts/${postId}`, {
                method: 'DELETE',
                credentials: 'include'
            });

            if (!response.ok) {
                const result = await response.json();
                throw new Error(result.message || '投稿の削除に失敗しました');
            }

            showSuccessMessage('投稿を削除しました');
            loadPosts(); // 投稿一覧を再読み込み
        } catch (error) {
            showError(error.message);
        }
    }

    // エラーメッセージの表示
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = `
            background-color: #ff6b6b;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        `;
        errorDiv.textContent = message;
        
        const postsContainer = document.getElementById('postsList');
        if (postsContainer) {
            postsContainer.innerHTML = '';
            postsContainer.appendChild(errorDiv);
        } else {
            document.querySelector('.matching-section').appendChild(errorDiv);
        }
        
        // 5秒後にエラーメッセージを削除
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 10000);
    }

    // 成功メッセージの表示
    function showSuccessMessage(message) {
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

    // 初期読み込み
    await loadPosts();
}); 
