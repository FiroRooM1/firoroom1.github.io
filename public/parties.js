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

    const partiesContainer = document.getElementById('partiesContainer');

    // パーティー一覧を読み込み
    async function loadParties() {
        try {
            const response = await fetch('/api/parties', {
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('パーティー一覧の取得に失敗しました');
            }

            const parties = await response.json();
            displayParties(parties);
        } catch (error) {
            showError('パーティー一覧の取得に失敗しました');
        }
    }

    // パーティー一覧を表示
    function displayParties(parties) {
        partiesContainer.innerHTML = '';

        if (parties.length === 0) {
            partiesContainer.innerHTML = '<p class="no-parties">参加しているパーティーがありません</p>';
            return;
        }

        parties.forEach(party => {
            const partyElement = createPartyElement(party);
            partiesContainer.appendChild(partyElement);
        });
    }

    // パーティー要素を作成
    function createPartyElement(party) {
        const partyDiv = document.createElement('div');
        partyDiv.className = 'party-card';

        const memberCount = party.members ? party.members.length : 0;
        const gameMode = party.post ? party.post.game_mode : '不明';
        const createdAt = party.created_at ? new Date(party.created_at).toLocaleDateString() : '不明';
        const isCreator = party.post && party.post.author_id === getCurrentUserId();

        partyDiv.innerHTML = `
            <div class="party-header">
                <h3 class="party-title">${party.post ? party.post.title : 'タイトルなし'}</h3>
            </div>
                <div class="party-meta">
                    <span class="member-count">${memberCount}人</span>
                    <span class="game-mode">${gameMode}</span>
                    <span class="created-at">作成: ${createdAt}</span>
            </div>
            <div class="party-actions">
                <button class="join-btn" onclick="joinParty('${party.id}')">
                    <i class="fas fa-sign-in-alt"></i> 参加する
                </button>
                ${isCreator ? `
                <button class="disband-btn" onclick="disbandParty('${party.id}')">
                    <i class="fas fa-trash"></i> 解散する
                </button>
                ` : ''}
            </div>
        `;

        return partyDiv;
    }

    // パーティーに参加
    window.joinParty = (partyId) => {
        // パーティー詳細ページに遷移
        window.location.href = `/party?id=${partyId}`;
    };

    // パーティーを解散
    window.disbandParty = async (partyId) => {
        if (!confirm('このパーティーを解散しますか？この操作は取り消せません。')) {
            return;
        }

        try {
            const response = await fetch('/api/party/disband', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ partyId })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'パーティーの解散に失敗しました');
            }

            showSuccess('パーティーを解散しました');
            
            // パーティー一覧を再読み込み
            setTimeout(() => {
                loadParties();
            }, 1000);
        } catch (error) {
            showError(error.message);
        }
    };

    // エラーメッセージの表示
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.querySelector('.main-content').insertBefore(errorDiv, document.querySelector('.parties-section'));
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // 成功メッセージの表示
    function showSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        document.querySelector('.main-content').insertBefore(successDiv, document.querySelector('.parties-section'));
        setTimeout(() => successDiv.remove(), 5000);
    }

    // 現在のユーザーIDを取得
    function getCurrentUserId() {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        return user.id;
    }

    // 初期読み込み
    await loadParties();
}); 