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

    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const chatForm = document.getElementById('chatForm');
    const membersList = document.querySelector('.members-list');

    // 要素の存在チェック
    if (!chatMessages) {
        console.error('chatMessages要素が見つかりません');
    }
    if (!messageInput) {
        console.error('messageInput要素が見つかりません');
    }
    if (!chatForm) {
        console.error('chatForm要素が見つかりません');
    }
    if (!membersList) {
        console.error('membersList要素が見つかりません');
    }

    let party = null;
    let isSubscribed = false;
    let isSending = false;
    const recentReceivedMessages = new Map();

    // Pusher接続を設定
    const pusher = new Pusher('8ebcc71b2fe50be4967d', {
        cluster: 'ap3'
    });

    // パーティー情報を取得
    async function loadParty() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const partyId = urlParams.get('id');

            if (!partyId) {
                showError('パーティーIDが指定されていません');
                return;
            }

            const response = await fetch(`/api/parties/${partyId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('パーティー情報の取得に失敗しました');
            }

            party = await response.json();
            
            displayPartyInfo();
            displayMembers();
            loadChatMessages();
            subscribeToChat();
        } catch (error) {
            console.error('パーティー情報取得エラー:', error);
            showError(error.message);
        }
    }

    // パーティー情報を表示
    function displayPartyInfo() {
        const partyTitle = document.querySelector('.party-title');
        const gameMode = document.querySelector('.game-mode');
        const createdAt = document.querySelector('.created-at');
        
        if (party && party.post) {
            partyTitle.textContent = party.post.title;
            gameMode.textContent = party.post.game_mode;
            createdAt.textContent = `作成: ${new Date(party.created_at).toLocaleDateString()}`;
        }
    }

    // メンバーを表示
    function displayMembers() {
        if (!membersList) {
            console.error('メンバーリスト要素が見つかりません');
            return;
        }

        membersList.innerHTML = '';

        if (!party || !party.members || party.members.length === 0) {
            membersList.innerHTML = '<p class="no-members">メンバーが表示されません</p>';
            return;
        }

        party.members.forEach(member => {
            const memberElement = document.createElement('div');
            memberElement.className = 'member-item';
            
            memberElement.innerHTML = `
                <div class="member-info">
                    <div class="member-avatar">
                        <img src="${member.summoner_info?.iconUrl || '/default-avatar.png'}" alt="アバター">
                    </div>
                    <div class="member-details">
                        <div class="member-name">${member.display_name || member.username}</div>
                        <div class="member-summoner">${member.summoner_name || '不明'}</div>
                    </div>
                </div>
            `;
            
            membersList.appendChild(memberElement);
        });
    }

    // チャットメッセージを読み込み
    async function loadChatMessages() {
        try {
            const response = await fetch(`/api/chat/${party.id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'include'
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('APIエラー:', errorText);
                throw new Error('メッセージの取得に失敗しました');
            }

            const messages = await response.json();
            
            if (Array.isArray(messages)) {
                displayMessages(messages);
            } else {
                console.error('メッセージが配列ではありません:', messages);
                displayMessages([]);
            }
        } catch (error) {
            console.error('メッセージ取得エラー詳細:', error);
            console.error('エラースタック:', error.stack);
            // エラーが発生しても空の配列で表示
            displayMessages([]);
        }
    }

    // メッセージを表示
    function displayMessages(messages) {
        if (!chatMessages) {
            console.error('チャットメッセージ要素が見つかりません');
            return;
        }

        chatMessages.innerHTML = '';
        
        if (!Array.isArray(messages) || messages.length === 0) {
            chatMessages.innerHTML = '<p class="no-messages">メッセージはありません</p>';
            return;
        }

        messages.forEach((message, index) => {
            const messageElement = createMessageElement(message);
            chatMessages.appendChild(messageElement);
        });
        
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // メッセージ要素を作成
    function createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message';
        
        const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
        const isOwnMessage = message.sender_id === currentUser.id;
        messageDiv.classList.add(isOwnMessage ? 'own-message' : 'other-message');

        // ユーザー情報を取得
        const userName = message.user ? (message.user.display_name || message.user.username) : 'Unknown';
        const userAvatar = message.user && message.user.summoner_info ? message.user.summoner_info.iconUrl : '/default-avatar.png';

        // 日付処理を改善
        let timeString = 'Unknown';
        try {
            let dateValue = message.created_at || message.timestamp;
            
            if (dateValue) {
                const messageDate = new Date(dateValue);
                
                if (!isNaN(messageDate.getTime())) {
                    timeString = messageDate.toLocaleString('ja-JP', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } else {
                    console.error('無効な日付:', dateValue);
                }
            } else {
                console.error('日付フィールドが見つかりません');
            }
        } catch (error) {
            console.error('日付処理エラー:', error);
        }

        // 自分のメッセージかどうかで表示を変える
        if (isOwnMessage) {
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <img src="${userAvatar}" alt="ユーザーアバター">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-sender own-sender">${userName}</span>
                        <span class="message-time">${timeString}</span>
                    </div>
                    <div class="message-text">${message.content}</div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <img src="${userAvatar}" alt="ユーザーアバター">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-sender">${userName}</span>
                        <span class="message-time">${timeString}</span>
                    </div>
                    <div class="message-text">${message.content}</div>
                </div>
            `;
        }

        return messageDiv;
    }

    // チャットに購読
    function subscribeToChat() {
        if (isSubscribed) return;

        const pusherChannel = pusher.subscribe(`party-${party.id}`);
        
        pusherChannel.bind('chat-message', (message) => {
            // 重複メッセージチェック
            const messageKey = `${message.userId}-${message.content}`;
            if (recentReceivedMessages.has(messageKey)) {
                return;
            }

            recentReceivedMessages.set(messageKey, true);
            setTimeout(() => recentReceivedMessages.delete(messageKey), 5000);

            const messageElement = createMessageElement(message);
            if (chatMessages) {
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            } else {
                console.error('chatMessages要素が見つかりません');
            }
        });

        isSubscribed = true;
    }

    // メッセージ送信
    async function sendMessage() {
        if (isSending) return;

        if (!messageInput) {
            console.error('messageInput要素が見つかりません');
            return;
        }

        const content = messageInput.value.trim();
        if (!content) return;

        isSending = true;
        const sendButton = chatForm ? chatForm.querySelector('.send-button') : null;
        if (sendButton) sendButton.disabled = true;

        try {
            const response = await fetch('/api/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'include',
                body: JSON.stringify({
                    partyId: party.id,
                    content: content
                })
            });

            if (!response.ok) {
                const errorData = await response.text();
                console.error('APIエラーレスポンス:', errorData);
                throw new Error(`メッセージの送信に失敗しました (${response.status}): ${errorData}`);
            }

            const result = await response.json();

            messageInput.value = '';
        } catch (error) {
            console.error('メッセージ送信エラー:', error);
            showError(error.message);
        } finally {
            isSending = false;
            if (sendButton) sendButton.disabled = false;
        }
    }

    // イベントリスナーを設定
    if (chatForm) {
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            sendMessage();
        });
    } else {
        console.error('chatForm要素が見つかりません');
    }

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
        
        const container = document.querySelector('.main-content');
        if (container) {
            container.insertBefore(errorDiv, container.firstChild);
        }
        
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }

    // 初期読み込み
    await loadParty();
}); 
