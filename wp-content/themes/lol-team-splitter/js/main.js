// LoL Team Splitter - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    checkExistingRoom();
    initializeEventListeners();
});

// イベントリスナーの初期化
function initializeEventListeners() {
    // ルーム作成機能
    const searchSummonerBtn = document.getElementById('searchSummoner');
    if (searchSummonerBtn) {
        searchSummonerBtn.addEventListener('click', handleCreateRoom);
    }
    
    // ルーム参加機能
    const joinRoomBtn = document.getElementById('joinRoomBtn');
    if (joinRoomBtn) {
        joinRoomBtn.addEventListener('click', handleJoinRoom);
    }
}

// ルーム作成ハンドラー
function handleCreateRoom() {
    const summonerName = document.getElementById('summonerName').value.trim();
    const roomPassword = document.getElementById('roomPassword').value.trim();
    const loadingAlert = document.getElementById('loadingAlert');
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    
    if (!summonerName) {
        errorMessage.textContent = 'Riot IDを入力してください';
        errorAlert.classList.remove('d-none');
        return;
    }
    
    // 既存のルームがあるかチェック
    const existingRoom = localStorage.getItem('currentRoom');
    if (existingRoom) {
        try {
            const room = JSON.parse(existingRoom);
            // ルームの有効性をチェック
            fetch(`${ajax_object.ajax_url}?action=get_room_info&room_id=${room.roomId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    errorMessage.textContent = '既にルームを作成しています。既存のルームを閉じてから新しいルームを作成してください。';
                    errorAlert.classList.remove('d-none');
                    return;
                } else {
                    // ルームが無効な場合は続行
                    createRoom();
                }
            })
            .catch(error => {
                // エラーの場合は続行
                createRoom();
            });
        } catch (error) {
            // パースエラーの場合は続行
            createRoom();
        }
    } else {
        // 既存のルームがない場合は続行
        createRoom();
    }
    
    function createRoom() {
        // ローディング表示
        loadingAlert.classList.remove('d-none');
        errorAlert.classList.add('d-none');
        
        // ルームを作成
        const formData = new FormData();
        formData.append('action', 'create_room');
        formData.append('nonce', ajax_object.nonce);
        formData.append('summoner_name', summonerName);
        formData.append('room_password', roomPassword);
        
        fetch(ajax_object.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // サモナー情報を保存
                localStorage.setItem('currentPlayerName', summonerName);
                
                // ルーム情報を保存（ルームに戻る機能用）
                const roomInfo = {
                    roomId: data.data.room_id,
                    hostName: summonerName,
                    createdAt: new Date().toISOString(),
                    roomUrl: `${window.location.origin}/team-split/room/${data.data.room_id}/host`
                };
                localStorage.setItem('currentRoom', JSON.stringify(roomInfo));
                
                // 成功時はチーム分けページに遷移
                loadingAlert.innerHTML = '<i class="fas fa-check me-2"></i>ルーム作成完了！ページを移動中...';
                
                // ページ遷移（より確実な方法）
                const redirectUrl = `${window.location.origin}/team-split/room/${data.data.room_id}/host`;
                
                // 直接遷移を試す
                window.location.href = redirectUrl;
            } else {
                // エラー表示
                errorMessage.textContent = data.data.message || 'ルーム作成に失敗しました';
                errorAlert.classList.remove('d-none');
                loadingAlert.classList.add('d-none');
            }
        })
        .catch(error => {
            errorMessage.textContent = '通信エラーが発生しました: ' + error.message;
            errorAlert.classList.remove('d-none');
            loadingAlert.classList.add('d-none');
        });
    }
}

// ルーム参加ハンドラー
function handleJoinRoom() {
    const roomId = document.getElementById('roomId').value.trim();
    const summonerName = document.getElementById('joinSummonerName').value.trim();
    const roomPassword = document.getElementById('joinRoomPassword').value.trim();
    const loadingAlert = document.getElementById('joinLoadingAlert');
    const errorAlert = document.getElementById('joinErrorAlert');
    const errorMessage = document.getElementById('joinErrorMessage');
    
    if (!roomId) {
        errorMessage.textContent = 'ルームIDを入力してください';
        errorAlert.classList.remove('d-none');
        return;
    }
    
    if (!summonerName) {
        errorMessage.textContent = 'Riot IDを入力してください';
        errorAlert.classList.remove('d-none');
        return;
    }
    
    if (roomId.length !== 6 || !/^\d+$/.test(roomId)) {
        errorMessage.textContent = 'ルームIDは6桁の数字で入力してください';
        errorAlert.classList.remove('d-none');
        return;
    }
    
    // ローディング表示
    loadingAlert.classList.remove('d-none');
    errorAlert.classList.add('d-none');
    
    // ルームに参加
    const formData = new FormData();
    formData.append('action', 'join_room');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', roomId);
    formData.append('summoner_name', summonerName);
    formData.append('room_password', roomPassword);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // サモナー情報を保存
            localStorage.setItem('currentPlayerName', summonerName);
            
            // チーム分けページに遷移
            setTimeout(() => {
                window.location.href = `${window.location.origin}/team-split/room/${roomId}`;
            }, 500);
        } else {
            // エラー表示
            errorMessage.textContent = data.data.message || 'ルーム参加に失敗しました';
            errorAlert.classList.remove('d-none');
            loadingAlert.classList.add('d-none');
        }
    })
    .catch(error => {
        errorMessage.textContent = '通信エラーが発生しました: ' + error.message;
        errorAlert.classList.remove('d-none');
        loadingAlert.classList.add('d-none');
    });
}

// 既存のルームをチェックする関数
function checkExistingRoom() {
    const roomInfo = localStorage.getItem('currentRoom');
    
    if (!roomInfo) {
        return;
    }
    
    try {
        const room = JSON.parse(roomInfo);
        const returnBtn = document.getElementById('returnToRoomBtn');
        
        if (!returnBtn) {
            return;
        }
        
        // ルームの有効性をサーバーでチェック
        fetch(`${ajax_object.ajax_url}?action=get_room_info&room_id=${room.roomId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ルームが有効な場合、ボタンを表示
                returnBtn.classList.remove('d-none');
                returnBtn.onclick = function() {
                    window.location.href = room.roomUrl;
                };
                
                // ボタンのテキストを更新（シンプルな方法）
                const roomIdDisplay = document.getElementById('roomIdDisplay');
                if (roomIdDisplay) {
                    roomIdDisplay.textContent = ' (ID: ' + room.roomId + ')';
                }
                
            } else {
                // ルームが無効な場合、localStorageから削除
                localStorage.removeItem('currentRoom');
                returnBtn.classList.add('d-none');
            }
        })
        .catch(error => {
            // エラーの場合もlocalStorageから削除
            localStorage.removeItem('currentRoom');
            returnBtn.classList.add('d-none');
        });
    } catch (error) {
        localStorage.removeItem('currentRoom');
    }
}