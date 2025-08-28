<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ダッシュボード - League of Legends フレンド募集</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url('/images/Teemo_47.jpg') no-repeat center center;
            background-size: cover;
            background-attachment: scroll;
            color: white;
            margin: 0;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(0, 0, 0, 0.8);
            border-bottom: 2px solid #c89b3c;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #f0c040;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid transparent;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            border-color: #c89b3c;
            background: rgba(200, 155, 60, 0.2);
        }
        
        .main-content {
            margin-top: 100px;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .welcome-section {
            text-align: center;
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid #f0c040;
            backdrop-filter: blur(5px);
        }
        
        .welcome-title {
            color: #f0c040;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.9);
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .menu-card {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #f0c040;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            backdrop-filter: blur(3px);
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(240, 192, 64, 0.4);
            background: rgba(0, 0, 0, 0.9);
            border-color: #ffda4a;
        }
        
        .menu-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #f0c040;
            background: rgba(0, 0, 0, 0.3);
        }
        
        .menu-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            filter: drop-shadow(0 0 5px rgba(200, 155, 60, 0.3));
        }
        
        .menu-title {
            color: #f0c040;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }
        
        .menu-description {
            color: #e6f3ff;
            font-size: 0.9rem;
            line-height: 1.4;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
        }
        
        .logout-btn {
            background: #dc3545;
            color: #ffffff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">LoL フレンド募集</div>
            <div class="nav-links">
                <span class="welcome-text">{{ Auth::user()->name ?? 'サモナー' }}</span>
                <div class="notification-bell" id="notification-bell">
                    <span class="bell-icon">🔔</span>
                    <span class="notification-text">お知らせ</span>
                    <div class="notification-badge" id="notification-badge" 
                         style="display: {{ $unreadNotificationsCount > 0 ? 'flex' : 'none' }};">
                        {{ $unreadNotificationsCount ?? 0 }}
                    </div>
                </div>
                <a href="{{ route('friends.index') }}" class="nav-link">ダッシュボード</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <section class="welcome-section">
            <h1 class="welcome-title">ダッシュボード</h1>
            <p style="color: #e6f3ff; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);">サモナーズ・リフトで最高のチームメイトを見つけよう！</p>
        </section>

        <section class="menu-grid">
                <a href="{{ route('profile.show') }}" class="menu-card">
        <div class="menu-icon">
            <img src="/images/ArcaneComet.png" alt="プロフィール">
        </div>
        <h3 class="menu-title">プロフィール</h3>
        <p class="menu-description">あなたのサモナープロフィールを管理・編集</p>
    </a>
            
            <a href="{{ route('recruitment.index') }}" class="menu-card">
                <div class="menu-icon">
                    <img src="/images/SummonAery.png" alt="募集">
                </div>
                <h3 class="menu-title">募集</h3>
                <p class="menu-description">ランクやノーマルの募集一覧・投稿</p>
            </a>
            
            <a href="{{ route('recruitment.create') }}" class="menu-card">
                <div class="menu-icon">
                    <img src="/images/GlacialAugment.png" alt="新規募集">
                </div>
                <h3 class="menu-title">新規募集</h3>
                <p class="menu-description">新しい募集を作成する</p>
            </a>
            
            <a href="{{ route('applications.index') }}" class="menu-card">
                <div class="menu-icon">
                    <img src="/images/UnsealedSpellbook.png" alt="申請">
                </div>
                <h3 class="menu-title">申請</h3>
                <p class="menu-description">募集への申請を管理</p>
            </a>
            
            <a href="{{ route('parties.index') }}" class="menu-card">
                <div class="menu-icon">
                    <img src="/images/FleetFootwork.png" alt="パーティー">
                </div>
                <h3 class="menu-title">パーティー</h3>
                <p class="menu-description">参加しているパーティー一覧</p>
            </a>
        </section>
    </main>

    <!-- トースト通知エリア -->
    <div id="toast-container" class="toast-container"></div>

    <!-- 通知モーダル -->
    <div id="notification-modal" class="notification-modal">
        <div class="notification-modal-content">
            <div class="notification-modal-header">
                <h2>お知らせ一覧</h2>
                <span class="close-notification-modal">&times;</span>
            </div>
            <div class="notification-modal-body">
                <div id="notification-list">
                    <!-- 通知がここに表示されます -->
                </div>
                <div id="no-notifications" class="no-notifications">
                    現在、新しいお知らせはありません。
                </div>
            </div>
        </div>
    </div>

    <style>
        /* 既存のスタイル... */
        
        /* ヘッダー内お知らせアイコンスタイル */
        .welcome-text {
            color: #ffffff;
            font-weight: bold;
            margin-right: 1rem;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(240, 192, 64, 0.1);
            border: 1px solid #f0c040;
            border-radius: 8px;
            margin-right: 1rem;
        }

        .notification-bell:hover {
            background: rgba(240, 192, 64, 0.2);
            transform: scale(1.05);
        }

        .bell-icon {
            font-size: 1.2rem;
            color: #f0c040;
        }

        .notification-text {
            color: #f0c040;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            border: 2px solid #000;
            animation: pulse 2s infinite;
            z-index: 1001;
            min-width: 22px;
            min-height: 22px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .notification-tooltip {
            position: absolute;
            top: 50px;
            right: 0;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            border: 1px solid #f0c040;
            z-index: 1001;
        }

        .notification-bell:hover .notification-tooltip {
            opacity: 1;
            visibility: visible;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }
            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        /* トースト通知スタイル */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        }

        .toast {
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid #f0c040;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 10px;
            color: white;
            font-size: 0.9rem;
            max-width: 350px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            pointer-events: auto;
            cursor: pointer;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.hide {
            transform: translateX(400px);
        }

        .toast-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .toast-icon {
            font-size: 1.2rem;
        }

        .toast-title {
            font-weight: bold;
            color: #f0c040;
            font-size: 1rem;
        }

        .toast-message {
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        .toast-time {
            font-size: 0.8rem;
            color: #888;
            text-align: right;
        }

        .toast.application_received {
            border-color: #00bfff;
        }

        .toast.application_approved {
            border-color: #28a745;
        }

        .toast.application_rejected {
            border-color: #dc3545;
        }

        @media (max-width: 768px) {
            .toast-container {
                right: 10px;
                left: 10px;
            }
            
            .toast {
                max-width: none;
                transform: translateY(-100px);
            }
            
            .toast.show {
                transform: translateY(0);
            }
            
            .toast.hide {
                transform: translateY(-100px);
            }
        }

        /* 通知モーダルスタイル */
        .notification-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .notification-modal-content {
            background: rgba(0, 0, 0, 0.95);
            border: 2px solid #f0c040;
            border-radius: 15px;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 600px;
            backdrop-filter: blur(10px);
            max-height: 80vh;
            overflow: hidden;
        }

        .notification-modal-header {
            background: rgba(240, 192, 64, 0.1);
            padding: 1.5rem;
            border-bottom: 1px solid #f0c040;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-modal-header h2 {
            color: #f0c040;
            margin: 0;
            font-size: 1.5rem;
        }

        .close-notification-modal {
            color: #f0c040;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-notification-modal:hover {
            color: #d4af37;
        }

        .notification-modal-body {
            padding: 1.5rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .notification-item {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #00bfff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            background: rgba(0, 0, 0, 0.7);
            border-color: #f0c040;
        }

        .notification-item.application_received {
            border-color: #00bfff;
        }

        .notification-item.application_approved {
            border-color: #28a745;
        }

        .notification-item.application_rejected {
            border-color: #dc3545;
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .notification-icon {
            font-size: 1.2rem;
        }

        .notification-title {
            font-weight: bold;
            color: #f0c040;
            font-size: 1rem;
        }

        .notification-message {
            color: #ffffff;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        .notification-time {
            font-size: 0.8rem;
            color: #888;
            text-align: right;
        }

        .no-notifications {
            text-align: center;
            color: #888;
            padding: 2rem;
            font-style: italic;
        }
    </style>

    <script>
        // トースト通知システム
        class ToastNotification {
            constructor() {
                this.container = document.getElementById('toast-container');
                this.notifications = [];
                this.maxNotifications = 5;
            }

            // 通知を表示
            show(type, message, data = {}) {
                const toast = this.createToast(type, message, data);
                this.container.appendChild(toast);
                
                // 表示アニメーション
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);

                // 自動非表示（5秒後）
                setTimeout(() => {
                    this.hide(toast);
                }, 5000);

                // 通知を配列に追加
                this.notifications.push(toast);
                
                // 最大表示数を超えた場合、古い通知を削除
                if (this.notifications.length > this.maxNotifications) {
                    const oldToast = this.notifications.shift();
                    if (oldToast && oldToast.parentNode) {
                        oldToast.parentNode.removeChild(oldToast);
                    }
                }
            }

            // トースト要素を作成
            createToast(type, message, data) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                const icon = this.getIcon(type);
                const title = this.getTitle(type);
                
                toast.innerHTML = `
                    <div class="toast-header">
                        <span class="toast-icon">${icon}</span>
                        <span class="toast-title">${title}</span>
                    </div>
                    <div class="toast-message">${message}</div>
                    <div class="toast-time">${new Date().toLocaleTimeString('ja-JP', {hour: '2-digit', minute: '2-digit'})}</div>
                `;

                // クリックで非表示
                toast.addEventListener('click', () => {
                    this.hide(toast);
                });

                return toast;
            }

            // 通知タイプに応じたアイコンを取得
            getIcon(type) {
                switch (type) {
                    case 'application_received': return '📝';
                    case 'application_approved': return '✅';
                    case 'application_rejected': return '❌';
                    default: return '📢';
                }
            }

            // 通知タイプに応じたタイトルを取得
            getTitle(type) {
                switch (type) {
                    case 'application_received': return '申請受信';
                    case 'application_approved': return '申請承認';
                    case 'application_rejected': return '申請拒否';
                    default: return '通知';
                }
            }

            // 通知を非表示
            hide(toast) {
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                        // 配列からも削除
                        const index = this.notifications.indexOf(toast);
                        if (index > -1) {
                            this.notifications.splice(index, 1);
                        }
                    }
                }, 300);
            }

            // すべての通知を非表示
            hideAll() {
                this.notifications.forEach(toast => {
                    this.hide(toast);
                });
            }
        }

        // グローバルなトースト通知インスタンス
        window.toastNotification = new ToastNotification();

        // お知らせカウンター管理
        class NotificationCounter {
            constructor() {
                this.badge = document.getElementById('notification-badge');
                this.count = {{ $unreadNotificationsCount ?? 0 }};
                this.updateDisplay();
            }

            // カウントを増やす
            increment() {
                this.count++;
                this.updateDisplay();
            }

            // カウントをリセット（実際の通知数に基づいて）
            reset() {
                // データベースの実際の通知数に基づいてリセット
                this.count = {{ $unreadNotificationsCount ?? 0 }};
                this.updateDisplay();
            }

            // カウントを0にリセット（通知を見た時）
            clearCount() {
                this.count = 0;
                this.updateDisplay();
            }

            // 表示を更新
            updateDisplay() {
                if (this.badge) {
                    if (this.count > 0) {
                        this.badge.style.display = 'flex';
                        this.badge.textContent = this.count > 99 ? '99+' : this.count;
                    } else {
                        this.badge.style.display = 'none';
                    }
                }
            }

            // 実際の通知数で更新
            updateFromServer() {
                this.count = {{ $unreadNotificationsCount ?? 0 }};
                this.updateDisplay();
            }
        }

        // グローバルなお知らせカウンターインスタンス
        window.notificationCounter = new NotificationCounter();

        // セッションからトースト通知を表示（ページ読み込み時）
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('toast_notification'))
                const toastData = @json(session('toast_notification'));
                if (toastData && window.toastNotification) {
                    // お知らせカウンターを増やす
                    if (window.notificationCounter) {
                        window.notificationCounter.increment();
                    }
                    
                    setTimeout(() => {
                        window.toastNotification.show(toastData.type, toastData.message);
                    }, 1000); // 1秒後に表示
                }
            @endif

            // 通知カウンターの初期化
            if (window.notificationCounter) {
                window.notificationCounter.updateFromServer();
            }
        });

        // お知らせアイコンクリック時の処理
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notification-bell');
            if (notificationBell) {
                notificationBell.addEventListener('click', function() {
                    // 通知カウンターをリセットしない（既読状態のみ管理）
                    // if (window.notificationCounter) {
                    //     window.notificationCounter.reset();
                    // }
                    
                    // 通知モーダルを表示
                    showNotificationModal();
                });
            }
        });

        // 通知モーダルの表示・非表示
        function showNotificationModal() {
            console.log('showNotificationModalが呼び出されました');
            const modal = document.getElementById('notification-modal');
            if (modal) {
                console.log('通知モーダルが見つかりました');
                modal.style.display = 'block';
                loadNotifications();
            } else {
                console.error('通知モーダルが見つかりません');
            }
        }

        function hideNotificationModal() {
            const modal = document.getElementById('notification-modal');
            if (modal) {
                modal.style.display = 'none';
                
                // 通知モーダルを閉じた後に通知を既読状態にする
                markNotificationsAsRead();
            }
        }

        // 通知を既読状態にする
        async function markNotificationsAsRead() {
            try {
                const response = await fetch('{{ route("notifications.markAllAsRead") }}', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // 通知カウンターを0にリセット
                    if (window.notificationCounter) {
                        window.notificationCounter.count = 0;
                        window.notificationCounter.updateDisplay();
                    }
                } else {
                    console.error('通知の既読処理に失敗しました');
                }
            } catch (error) {
                console.error('通知の既読処理中にエラーが発生しました:', error);
            }
        }

        // 通知の読み込み
        function loadNotifications() {
            const notificationList = document.getElementById('notification-list');
            const noNotifications = document.getElementById('no-notifications');
            
            if (!notificationList || !noNotifications) {
                console.error('通知要素が見つかりません');
                return;
            }
            
            // データベースの通知数を取得して表示
            const unreadCount = {{ $unreadNotificationsCount ?? 0 }};
            
            if (unreadCount === 0) {
                notificationList.style.display = 'none';
                noNotifications.style.display = 'block';
                noNotifications.innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <p style="color: #888; margin-bottom: 1rem;">現在、新しいお知らせはありません。</p>
                    </div>
                `;
            } else {
                notificationList.style.display = 'block';
                noNotifications.style.display = 'none';
                
                // 個別通知の表示（実際の通知データを使用）
                // ここでは例として表示。実際の実装では、データベースから通知データを取得
                notificationList.innerHTML = `
                    <div class="notification-item">
                        <div class="notification-header">
                            <span class="notification-icon">📝</span>
                            <span class="notification-title">申請受信</span>
                        </div>
                        <div class="notification-message">
                            未読の通知が<strong style="color: #f0c040;">${unreadCount}</strong>件あります。
                        </div>
                        <div class="notification-actions" style="text-align: center; margin-top: 1rem;">
                            <p style="color: #888; font-size: 0.9rem;">
                                申請や承認などの通知が届いています。<br>
                                詳細は申請一覧ページで確認してください。
                            </p>
                            <a href="{{ route('applications.index') }}" class="nav-link" style="margin-top: 1rem; display: inline-block; padding: 0.5rem 1rem; background: #f0c040; color: #000; border-radius: 5px; text-decoration: none;">
                                申請一覧を見る
                            </a>
                        </div>
                    </div>
                `;
            }
        }

        // 通知アイテムの作成
        function createNotificationItem(notification) {
            const item = document.createElement('div');
            item.className = `notification-item ${notification.type}`;
            
            const icon = getNotificationIcon(notification.type);
            const title = getNotificationTitle(notification.type);
            
            item.innerHTML = `
                <div class="notification-header">
                    <span class="notification-icon">${icon}</span>
                    <span class="notification-title">${title}</span>
                </div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
            `;
            
            return item;
        }

        // 通知タイプに応じたアイコンを取得
        function getNotificationIcon(type) {
            switch (type) {
                case 'application_received': return '📝';
                case 'application_approved': return '✅';
                case 'application_rejected': return '❌';
                default: return '📢';
            }
        }

        // 通知タイプに応じたタイトルを取得
        function getNotificationTitle(type) {
            switch (type) {
                case 'application_received': return '申請受信';
                case 'application_approved': return '申請承認';
                case 'application_rejected': return '申請拒否';
                default: return '通知';
            }
        }

        // 通知履歴に追加
        function addToNotificationHistory(type, message) {
            const notifications = JSON.parse(localStorage.getItem('notification_history') || '[]');
            const newNotification = {
                type: type,
                message: message,
                time: new Date().toLocaleString('ja-JP')
            };
            
            notifications.unshift(newNotification);
            
            // 最大50件まで保持
            if (notifications.length > 50) {
                notifications.splice(50);
            }
            
            localStorage.setItem('notification_history', JSON.stringify(notifications));
        }

        // 既存のトースト通知システムを拡張
        const originalShow = window.toastNotification.show;
        window.toastNotification.show = function(type, message, data = {}) {
            // 元の機能を実行
            originalShow.call(this, type, message, data);
            
            // 通知履歴に追加
            addToNotificationHistory(type, message);
        };

        // モーダルを閉じる処理
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.querySelector('.close-notification-modal');
            const modal = document.getElementById('notification-modal');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', hideNotificationModal);
            }
            
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        hideNotificationModal();
                    }
                });
            }
        });

        // 通知の受信をシミュレート（テスト用）
        // 実際の実装では、WebSocketやServer-Sent Eventsを使用
        function simulateNotification() {
            const types = ['application_received', 'application_approved', 'application_rejected'];
            const messages = [
                '「テスト募集」に「Godzilla」さんから申請が来ました。',
                '「テスト募集」への申請が承認されました！',
                '「テスト募集」への申請が拒否されました。'
            ];
            
            const randomIndex = Math.floor(Math.random() * types.length);
            window.toastNotification.show(types[randomIndex], messages[randomIndex]);
        }

        // テスト用ボタン（開発完了後に削除可能）
        // document.addEventListener('DOMContentLoaded', function() {
        //     const testBtn = document.createElement('button');
        //     testBtn.textContent = 'テスト通知';
        //     testBtn.style.cssText = 'position: fixed; top: 10px; left: 10px; z-index: 10000; padding: 10px;';
        //     testBtn.onclick = simulateNotification;
        //     document.body.appendChild(testBtn);
        // });
    </script>
</body>
</html>
