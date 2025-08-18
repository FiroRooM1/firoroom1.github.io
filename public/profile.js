document.addEventListener('DOMContentLoaded', async () => {
    // Discord OAuthの認証状態確認
    try {
        console.log('認証状態を確認中...');
        const response = await fetch('/api/auth/status', {
            credentials: 'include'
        });
        
        console.log('認証状態レスポンス:', response.status, response.statusText);
        
        if (!response.ok) {
            console.error('認証状態確認が失敗:', response.status);
            window.location.href = '/';
            return;
        }
        
        const authData = await response.json();
        console.log('認証データ:', authData);
        
        if (!authData.authenticated) {
            console.log('認証されていません');
            window.location.href = '/';
            return;
        }
        
        console.log('認証成功');
    } catch (error) {
        console.error('認証状態確認エラー:', error);
        window.location.href = '/';
        return;
    }

    const displayNameInput = document.getElementById('displayName');
    const summonerNameInput = document.getElementById('summonerName');
    const passwordInput = document.getElementById('password');
    const editBtn = document.getElementById('editBtn');
    const submitBtn = document.getElementById('submitBtn');
    const profileForm = document.getElementById('profileForm');
    const cancelBtn = document.getElementById('cancelBtn');
    const navProfileImage = document.getElementById('navProfileImage');
    const navDisplayName = document.getElementById('navDisplayName');
    const navLogoutBtn = document.getElementById('navLogoutBtn');
    const summonerNameDisplay = document.getElementById('summonerNameDisplay');
    const refreshBtn = document.getElementById('refreshBtn');

    // プロフィール情報を取得
    async function loadProfile() {
        try {

            const response = await fetch('/api/user/profile', {
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('プロフィール情報の取得に失敗しました');
            }

            const profile = await response.json();
            
            if (profile) {
                displayNameInput.value = profile.display_name || '';
                summonerNameInput.value = profile.summoner_name || '';
                summonerNameDisplay.textContent = profile.summoner_name || '';
                
                // ナビゲーションバーの表示を更新
                if (navDisplayName) {
                    navDisplayName.textContent = profile.display_name;
                }
                if (navProfileImage && profile.summoner_info) {
                    navProfileImage.style.backgroundImage = `url(${profile.summoner_info.iconUrl})`;
                }

                // サモナー情報を表示
                if (profile.summoner_info) {
                    updateSummonerInfo(profile.summoner_info);
                } else if (profile.summoner_name) {
                    // サモナー名は設定されているが、サモナー情報がない場合は更新ボタンを促すメッセージを表示
                    summonerNameDisplay.textContent = profile.summoner_name || '';
                    
                    // サモナー情報がない場合のプレースホルダー表示
                    const summonerIcon = document.getElementById('summonerIcon');
                    const summonerLevel = document.getElementById('summonerLevel');
                    const rankInfo = document.getElementById('rankInfo');
                    
                    if (summonerIcon) {
                        summonerIcon.src = '/default-avatar.png';
                        summonerIcon.alt = 'サモナーアイコン';
                    }
                    
                    if (summonerLevel) {
                        summonerLevel.textContent = '更新ボタンを押してサモナー情報を取得してください';
                        summonerLevel.style.color = '#937341';
                        summonerLevel.style.fontStyle = 'italic';
                    }
                    
                    if (rankInfo) {
                        rankInfo.innerHTML = `
                            <div style="text-align: center; color: #937341; font-style: italic; padding: 20px;">
                                <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                                更新ボタンを押して最新のランク情報を取得してください
                            </div>
                        `;
                    }
                }
                
                // ナビゲーションリンクを有効化
                if (typeof enableNavigationLinks === 'function') {
                    enableNavigationLinks();
                }
            }

            // フォームを無効化
            disableForm();
        } catch (error) {
            console.error('プロフィール取得エラー:', error);
            showError(error.message);
        }
    }

    // プロフィール編集を有効化
    editBtn.addEventListener('click', () => {
        enableForm();
    });

    // プロフィール更新
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            const updateData = {
                displayName: displayNameInput.value,
                summonerName: summonerNameInput.value
            };

            // パスワードが入力されている場合のみ追加
            if (passwordInput.value.trim()) {
                updateData.password = passwordInput.value;
            }

            const response = await fetch('/api/user/profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(updateData)
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'プロフィールの更新に失敗しました');
            }

            showSuccessMessage('プロフィールの更新が完了しました');
            passwordInput.value = ''; // パスワードフィールドをクリア
            disableForm();
            
            // ローカルストレージのユーザー情報を更新
            localStorage.setItem('user', JSON.stringify(result.user));
            
            // プロフィール情報を再読み込み
            await loadProfile();
        } catch (error) {
            console.error('プロフィール更新エラー:', error);
            showErrorMessage(error.message);
        }
    });

    // キャンセルボタンの処理
    cancelBtn.addEventListener('click', async () => {
        passwordInput.value = ''; // パスワードフィールドをクリア
        await loadProfile();
        disableForm();
    });

    // フォームの有効化
    function enableForm() {
        displayNameInput.disabled = false;
        summonerNameInput.disabled = false;
        passwordInput.disabled = false;
        submitBtn.style.display = 'block';
        editBtn.style.display = 'none';
        cancelBtn.style.display = 'block';
    }

    // フォームの無効化
    function disableForm() {
        displayNameInput.disabled = true;
        summonerNameInput.disabled = true;
        passwordInput.disabled = true;
        submitBtn.style.display = 'none';
        editBtn.style.display = 'block';
        cancelBtn.style.display = 'none';
    }

    // サモナー情報の表示を更新
    function updateSummonerInfo(summonerInfo) {
        const summonerIcon = document.getElementById('summonerIcon');
        const summonerNameDisplay = document.getElementById('summonerNameDisplay');
        const summonerLevel = document.getElementById('summonerLevel');
        const rankInfo = document.getElementById('rankInfo');

        // アイコンを設定
        if (summonerInfo.iconUrl) {
            summonerIcon.src = summonerInfo.iconUrl;
            
            // アイコンの読み込み状況を監視
            summonerIcon.onload = function() {
                summonerIcon.style.display = 'block';
            };
            
            summonerIcon.onerror = function() {
                console.error('アイコン読み込み失敗:', summonerInfo.iconUrl);
            };
        } else {
            summonerIcon.src = '/default-avatar.png';
            summonerIcon.style.display = 'block';
        }
        
        // アイコンの表示を確実にする
        summonerIcon.style.display = 'block';
        summonerIcon.style.width = '100px';
        summonerIcon.style.height = '100px';
        summonerIcon.style.borderRadius = '50%';
        summonerIcon.style.border = '3px solid #ffd700';

        // サモナー名を設定（画像のように揃えて表示）
        const summonerName = summonerInfo.name || '';
        summonerNameDisplay.textContent = summonerName;
        summonerNameDisplay.style.fontSize = '42px';
        summonerNameDisplay.style.lineHeight = '1.1';
        summonerNameDisplay.style.fontWeight = 'bold';
        summonerNameDisplay.style.color = '#fff';
        summonerNameDisplay.style.textShadow = '2px 2px 4px rgba(0, 0, 0, 0.5)';
        summonerNameDisplay.style.margin = '0';

        // レベルを設定（列に合わせて表示）
        let levelText = `レベル ${summonerInfo.level || 0}`;
        
        // 更新日時があれば表示
        if (summonerInfo.lastUpdated) {
            const updateDate = new Date(summonerInfo.lastUpdated).toLocaleString('ja-JP');
            levelText += ` (更新: ${updateDate})`;
        }
        
        summonerLevel.textContent = levelText;
        summonerLevel.style.fontSize = '20px';
        summonerLevel.style.fontWeight = '600';
        summonerLevel.style.marginTop = '5px';
        summonerLevel.style.marginBottom = '15px';
        summonerLevel.style.color = '#F0E6D2';

        // ランク情報を設定
        if (summonerInfo.ranks && summonerInfo.ranks.length > 0) {
            rankInfo.innerHTML = summonerInfo.ranks.map(rank => {
                const queueType = rank.queueType === 'RANKED_SOLO_5x5' ? 'ソロ/デュオ' : 'フレックス';
                const winRate = ((rank.wins / (rank.wins + rank.losses)) * 100).toFixed(1);
                
                return `
                    <div class="rank-card">
                        <div class="rank-header">
                            <img src="/rankIMG/Rank=${rank.tier.charAt(0).toUpperCase() + rank.tier.slice(1).toLowerCase()}.png" alt="${rank.tier}" class="rank-icon-small">
                            <span class="rank-type">${queueType}</span>
                        </div>
                        <div class="rank-details">
                            <span class="rank-tier">${rank.tier} ${rank.rank}</span>
                            <span class="rank-lp">${rank.leaguePoints} LP</span>
                            <span class="rank-record">${rank.wins}勝 ${rank.losses}敗 (${winRate}%)</span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            rankInfo.innerHTML = `
                <div class="rank-card">
                    <div class="rank-header">
                        <img src="/rankIMG/Rank=Unranked.png" alt="Unranked" class="rank-icon-small">
                        <span class="rank-type">ランク未設定</span>
                    </div>
                    <div class="rank-details">
                        <span class="rank-tier">未ランク</span>
                        <span class="rank-record">0勝 0敗</span>
                    </div>
                </div>
            `;
        }
    }

    // エラーメッセージの表示
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.querySelector('.profile-section').insertBefore(errorDiv, document.querySelector('.profile-header'));
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // 成功メッセージを表示する関数
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

    // エラーメッセージを表示する関数
    function showErrorMessage(message) {
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

    // 更新ボタンの処理
    refreshBtn.addEventListener('click', async () => {
        try {
            await refreshSummonerInfo();
        } catch (error) {
            console.error('サモナー情報更新エラー:', error);
            showErrorMessage(error.message);
        }
    });

    // サモナー情報更新の共通処理
    async function refreshSummonerInfo() {
        // ボタンをローディング状態にする
        refreshBtn.classList.add('loading');
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> 更新中...';

        try {
            const response = await fetch('/api/user/refresh-summoner', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include'
            });

            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'サモナー情報の更新に失敗しました');
            }

            // ローカルストレージのユーザー情報を更新
            if (result.user) {
                localStorage.setItem('user', JSON.stringify(result.user));
            }

            // プロフィール情報を再読み込み
            await loadProfile();
            
            // 更新日時を取得して表示
            const lastUpdated = result.user?.summonerInfo?.lastUpdated;
            let successMessage = 'サモナー情報を更新しました';
            if (lastUpdated) {
                const updateDate = new Date(lastUpdated).toLocaleString('ja-JP');
                successMessage += ` (更新日時: ${updateDate})`;
            }
            
            showSuccessMessage(successMessage);
        } finally {
            // ボタンを元の状態に戻す
            refreshBtn.classList.remove('loading');
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> 更新';
        }
    }

    // 初期表示
    await loadProfile();

    // ログアウトボタンの処理
    navLogoutBtn.addEventListener('click', async () => {
        if (confirm('ログアウトしますか？')) {
            try {
                await fetch('/auth/logout', {
                    method: 'GET',
                    credentials: 'include'
                });
            } catch (error) {
                // エラーが発生しても続行
            }
            localStorage.removeItem('user');
            window.location.href = '/';
        }
    });
}); 
