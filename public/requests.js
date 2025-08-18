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

    // タブ切り替え
    const tabBtns = document.querySelectorAll('.tab-btn');
    const requestsLists = document.querySelectorAll('.requests-list');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.getAttribute('data-tab');
            
            // タブボタンのアクティブ状態を切り替え
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // コンテンツの表示を切り替え
            requestsLists.forEach(list => {
                list.classList.remove('active');
                if (list.id === `${tabName}Requests`) {
                    list.classList.add('active');
                }
            });
            
            // 該当する申請を読み込み
            if (tabName === 'received') {
                loadReceivedRequests();
            } else {
                loadSentRequests();
            }
        });
    });

    // 受け取った申請を読み込み
    async function loadReceivedRequests() {
        try {
            const response = await fetch('/api/requests', {
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('申請の取得に失敗しました');
            }

            const requests = await response.json();
            
            // 現在のユーザー情報を取得
            const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
            
            // 受け取った申請と送信した申請を分離
            const receivedRequests = requests.filter(req => {
                return req.post && req.post.author && req.post.author.id === currentUser.id;
            });

            const sentRequests = requests.filter(req => {
                return req.applicant && req.applicant.id === currentUser.id;
            });
            
            displayReceivedRequests(receivedRequests);
        } catch (error) {
            console.error('受け取った申請の取得エラー:', error);
            showError('申請の取得に失敗しました: ' + error.message);
        }
    }

    // 送信した申請を読み込み
    async function loadSentRequests() {
        try {
            const response = await fetch('/api/requests', {
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('申請の取得に失敗しました');
            }

            const requests = await response.json();
            
            // 現在のユーザー情報を取得
            const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
            
            // 現在のユーザーが申請者である申請をフィルタリング
            const sentRequests = requests.filter(req => 
                req.applicant && req.applicant.id === currentUser.id
            );
            
            displaySentRequests(sentRequests);
        } catch (error) {
            console.error('送信した申請の取得エラー:', error);
            showError('申請の取得に失敗しました: ' + error.message);
        }
    }

    // 受け取った申請を表示
    function displayReceivedRequests(requests) {
        const container = document.getElementById('receivedRequests');
        container.innerHTML = '';

        if (!requests || requests.length === 0) {
            container.innerHTML = '<p class="no-requests">受け取った申請はありません</p>';
            return;
        }

        requests.forEach(request => {
            const requestElement = createReceivedRequestElement(request);
            container.appendChild(requestElement);
        });
    }

    // 送信した申請を表示
    function displaySentRequests(requests) {
        const container = document.getElementById('sentRequests');
        container.innerHTML = '';

        if (!requests || requests.length === 0) {
            container.innerHTML = '<p class="no-requests">送信した申請はありません</p>';
            return;
        }

        requests.forEach(request => {
            const requestElement = createSentRequestElement(request);
            container.appendChild(requestElement);
        });
    }

    // HTMLエスケープ関数
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // 受け取った申請要素を作成
    function createReceivedRequestElement(request) {
        const div = document.createElement('div');
        div.className = 'request-card received';
        
        let statusClass = '';
        let statusText = '';
        switch (request.status) {
            case 'pending':
                statusClass = 'status-pending';
                statusText = '承認待ち';
                break;
            case 'accepted':
                statusClass = 'status-accepted';
                statusText = '承認済み';
                break;
            case 'rejected':
                statusClass = 'status-rejected';
                statusText = '拒否済み';
                break;
        }

        div.innerHTML = `
            <div class="request-header">
                <div class="applicant-info">
                    <img src="${escapeHtml(request.applicant.summoner_info?.iconUrl || '/default-avatar.png')}" alt="申請者アイコン" class="applicant-icon">
                    <div>
                        <h3>${escapeHtml(request.applicant.display_name || request.applicant.username)}</h3>
                        <p>${escapeHtml(request.applicant.summoner_info?.ranks?.[0]?.tier || 'ランク未設定')}</p>
                    </div>
                </div>
                <span class="status ${statusClass}">${escapeHtml(statusText)}</span>
            </div>
            <div class="request-content">
                <h4>${escapeHtml(request.post.title)}</h4>
                <p><strong>希望レーン:</strong> ${escapeHtml(request.preferred_lane)}</p>
                ${request.message ? `<p><strong>メッセージ:</strong> ${escapeHtml(request.message)}</p>` : ''}
            </div>
            ${request.status === 'pending' ? `
                <div class="request-actions">
                    <button class="accept-btn" onclick="acceptRequest('${escapeHtml(request.id)}')">
                        <i class="fas fa-check"></i> 承認
                    </button>
                    <button class="reject-btn" onclick="rejectRequest('${escapeHtml(request.id)}')">
                        <i class="fas fa-times"></i> 拒否
                    </button>
                </div>
            ` : ''}
        `;

        return div;
    }

    // 送信した申請要素を作成
    function createSentRequestElement(request) {
        const div = document.createElement('div');
        div.className = 'request-card sent';
        
        let statusClass = '';
        let statusText = '';
        switch (request.status) {
            case 'pending':
                statusClass = 'status-pending';
                statusText = '承認待ち';
                break;
            case 'accepted':
                statusClass = 'status-accepted';
                statusText = '承認済み';
                break;
            case 'rejected':
                statusClass = 'status-rejected';
                statusText = '拒否済み';
                break;
        }

        div.innerHTML = `
            <div class="request-header">
                <div class="post-info">
                    <h3>${escapeHtml(request.post.title)}</h3>
                    <p>投稿者: ${escapeHtml(request.post.author.display_name || request.post.author.username)}</p>
                </div>
                <span class="status ${statusClass}">${escapeHtml(statusText)}</span>
            </div>
            <div class="request-content">
                <p><strong>希望レーン:</strong> ${escapeHtml(request.preferred_lane)}</p>
                ${request.message ? `<p><strong>メッセージ:</strong> ${escapeHtml(request.message)}</p>` : ''}
            </div>
        `;

        return div;
    }

    // 申請を承認
    window.acceptRequest = async (requestId) => {
        try {
                    const response = await fetch(`/api/requests/${requestId}/accept`, {
            method: 'POST',
            credentials: 'include'
        });

            if (!response.ok) {
                throw new Error('申請の承認に失敗しました');
            }

            showSuccessMessage('申請を承認しました');
            loadReceivedRequests();
        } catch (error) {
            showError(error.message);
        }
    };

    // 申請を拒否
    window.rejectRequest = async (requestId) => {
        try {
                    const response = await fetch(`/api/requests/${requestId}/reject`, {
            method: 'POST',
            credentials: 'include'
        });

            if (!response.ok) {
                throw new Error('申請の拒否に失敗しました');
            }

            showSuccessMessage('申請を拒否しました');
            loadReceivedRequests();
        } catch (error) {
            showError(error.message);
        }
    };

    // エラーメッセージの表示
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = `
            background-color: #ff6b6b;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        `;
        errorDiv.textContent = message;
        
        const container = document.querySelector('.requests-section');
        container.insertBefore(errorDiv, container.firstChild);
        
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }

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

    // 初期読み込み
    loadReceivedRequests();
}); 
