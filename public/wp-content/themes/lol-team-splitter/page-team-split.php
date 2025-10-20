<?php
/**
 * Template Name: Team Split Page
 */

// ページタイトルを設定
add_filter('wp_title', function($title) {
    $roomId = get_query_var('room') ?: ($_GET['room'] ?? '');
    if (!empty($roomId)) {
        return 'チーム分け - ルーム ' . $roomId . ' | ' . get_bloginfo('name');
    }
    return $title;
});

add_filter('document_title', function($title) {
    $roomId = get_query_var('room') ?: ($_GET['room'] ?? '');
    if (!empty($roomId)) {
        return 'チーム分け - ルーム ' . $roomId . ' | ' . get_bloginfo('name');
    }
    return $title;
});

// template_redirectアクション経由でアクセスされた場合はヘッダーを読み込まない
if (!isset($GLOBALS['template_redirect_called'])) {
get_header(); 
} 

// ルームIDを取得（WordPressのクエリ変数システムを使用）
$roomId = get_query_var('room') ?: ($_GET['room'] ?? '');

if (empty($roomId)) {
    // ルームIDがない場合はホームにリダイレクト
    wp_redirect(home_url('/'));
    exit;
}

// ルーム情報を取得
$posts = get_posts(array(
    'post_type' => 'lol_room',
    'meta_query' => array(
        array(
            'key' => 'room_id',
            'value' => $roomId,
            'compare' => '='
        )
    )
));

if (empty($posts)) {
    // ルームが見つからない場合はホームにリダイレクト
    wp_redirect(home_url('/'));
    exit;
}

$room_post = $posts[0];
$host_name = get_post_meta($room_post->ID, 'host_name', true);
$room_password = get_post_meta($room_post->ID, 'room_password', true);
$participants = json_decode(get_post_meta($room_post->ID, 'participants', true), true) ?: array();
$host_data = json_decode(get_post_meta($room_post->ID, 'host_data', true), true) ?: array();


// 現在のユーザーがホストかどうかを判定
$isHost = false;

// セッションを開始
if (!session_id()) {
    session_start();
}

// 方法1: URLパラメータで判定（最も確実）
if (isset($_GET['host']) && $_GET['host'] === 'true') {
    $isHost = true;
    // セッションにも保存
    $_SESSION['lol_host_rooms'][$roomId] = array(
        'host_name' => $host_name,
        'created_at' => current_time('mysql'),
        'room_id' => $roomId,
        'session_id' => session_id()
    );
} else {
    // 方法2: 現在のURLにhost=trueが含まれているかチェック
    $current_url = $_SERVER['REQUEST_URI'];
    if (strpos($current_url, 'host=true') !== false) {
        $isHost = true;
        // セッションにも保存
        $_SESSION['lol_host_rooms'][$roomId] = array(
            'host_name' => $host_name,
            'created_at' => current_time('mysql'),
            'room_id' => $roomId,
            'session_id' => session_id()
        );
    } else {
        // 方法3: セッションからホスト判定（セッションIDも確認）
        if (isset($_SESSION['lol_host_rooms'][$roomId])) {
            $session_data = $_SESSION['lol_host_rooms'][$roomId];
            // セッションIDが一致するかチェック
            if (isset($session_data['session_id']) && $session_data['session_id'] === session_id()) {
                $isHost = true;
            } else {
                // セッションIDが一致しない場合は無効
                unset($_SESSION['lol_host_rooms'][$roomId]);
            }
        } else {
            // 方法4: データベースからホスト判定（最後の手段）
            $host_session_id = get_post_meta($room_post->ID, 'host_session_id', true);
            if ($host_session_id && $host_session_id === session_id()) {
                $isHost = true;
                // セッションにも保存
                $_SESSION['lol_host_rooms'][$roomId] = array(
                    'host_name' => $host_name,
                    'created_at' => current_time('mysql'),
                    'room_id' => $roomId,
                    'session_id' => session_id()
                );
            } else {
                // 方法5: ホスト名ベースの判定（最も確実な方法）
                // 現在のユーザー名を取得（ローカルストレージから）
                $current_user_name = '';
                if (isset($_COOKIE['lol_current_user'])) {
                    $current_user_name = sanitize_text_field($_COOKIE['lol_current_user']);
                }
                
                // ホスト名と現在のユーザー名が一致するかチェック
                if ($current_user_name && $current_user_name === $host_name) {
                    $isHost = true;
                    // セッションにも保存
                    $_SESSION['lol_host_rooms'][$roomId] = array(
                        'host_name' => $host_name,
                        'created_at' => current_time('mysql'),
                        'room_id' => $roomId,
                        'session_id' => session_id()
                    );
                }
            }
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="display-5 fw-bold text-white mb-3">
                <i class="fas fa-swords me-2"></i>
                <?php _e('カスタムゲーム チーム分け', 'lol-team-splitter'); ?>
            </h1>
            <p class="lead text-light"><?php _e('バランスの取れたチームを作成しましょう', 'lol-team-splitter'); ?></p>
            <div class="alert alert-info d-inline-block">
                <i class="fas fa-door-open me-2"></i>
                <strong><?php _e('ルームID:', 'lol-team-splitter'); ?></strong> <?php echo esc_html($roomId); ?>
                <span class="ms-3">
                    <i class="fas fa-user me-1"></i>
                    <strong><?php _e('ホスト:', 'lol-team-splitter'); ?></strong> <?php echo esc_html($host_name); ?>
                </span>
                <?php if (!empty($room_password)): ?>
                <span class="ms-3">
                    <i class="fas fa-lock me-1"></i>
                    <strong><?php _e('パスワード:', 'lol-team-splitter'); ?></strong> <?php echo esc_html($room_password); ?>
                </span>
                <?php else: ?>
                <span class="ms-3">
                    <i class="fas fa-unlock me-1"></i>
                    <strong><?php _e('パスワード:', 'lol-team-splitter'); ?></strong> <?php _e('なし', 'lol-team-splitter'); ?>
                </span>
                <?php endif; ?>
                <span class="ms-3">
                    <i class="fas fa-link me-1"></i>
                    <strong><?php _e('参加用URL:', 'lol-team-splitter'); ?></strong>
                    <div class="input-group d-inline-flex" style="width: 300px;">
                        <input type="text" class="form-control form-control-sm" value="<?php echo esc_attr(home_url('/join/' . $roomId)); ?>" readonly id="joinUrl">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyJoinUrl()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </span>
                <?php if ($isHost): ?>
                    <span class="ms-3">
                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#closeRoomModal">
                            <i class="fas fa-times me-1"></i>
                            <?php _e('ルームを閉じる', 'lol-team-splitter'); ?>
                        </button>
                    </span>
                <?php endif; ?>
                <?php if (false): // テストデータ読込ボタンは一時的に非表示 ?>
                <span class="ms-3">
                    <button class="btn btn-info btn-sm" onclick="loadTestData()">
                        <i class="fas fa-flask me-1"></i>
                        <?php _e('テストデータ読込', 'lol-team-splitter'); ?>
                    </button>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- チーム1 -->
        <div class="col-md-6">
            <div class="team-card bg-dark border-warning">
                <div class="team-header">
                    <h3 class="team-title text-warning">
                        <i class="fas fa-shield-alt me-2"></i>
                        <?php _e('チーム1 ブルーサイド', 'lol-team-splitter'); ?>
                    </h3>
                    <div class="team-stats">
                        <span class="badge bg-warning text-dark"><?php _e('平均ランク:', 'lol-team-splitter'); ?> <span id="team1AvgRank">-</span></span>
                    </div>
                </div>
                
                <div class="team-players">
                    <!-- プレイヤー1-5 -->
                    <?php 
                    
                    // 参加者データを参加順で並べ替え（表示用のみ、チーム割り当ては手動）
                    $sortedParticipants = $participants;
                    uasort($sortedParticipants, function($a, $b) {
                        $timeA = isset($a['joined_at']) ? strtotime($a['joined_at']) : 0;
                        $timeB = isset($b['joined_at']) ? strtotime($b['joined_at']) : 0;
                        return $timeA - $timeB;
                    });
                    
                    // チーム1とチーム2に手動で割り当てられた参加者を取得
                    $team1Participants = array();
                    $team2Participants = array();
                    
                    foreach ($sortedParticipants as $name => $data) {
                        if (isset($data['team']) && $data['team'] == 1) {
                            $team1Participants[$name] = $data;
                        } elseif (isset($data['team']) && $data['team'] == 2) {
                            $team2Participants[$name] = $data;
                        }
                    }
                    
                    $participantNames = array_keys($sortedParticipants);
                    $slotIndex = 1;
                    
                    for($i = 1; $i <= 5; $i++): 
                        $participantName = null;
                        $participantData = null;
                        
                    // このスロットに対応する参加者を見つける（チーム1用、手動割り当て）
                    $team1Names = array_keys($team1Participants);
                    if (isset($team1Names[$i - 1])) {
                        $participantName = $team1Names[$i - 1];
                        $participantData = $team1Participants[$participantName] ?? null;
                            
                            // 参加者データが配列でない場合は修正
                            if ($participantData && !is_array($participantData)) {
                                $participantData = array(
                                    'name' => $participantName,
                                    'level' => '?',
                                    'rank' => 'Unknown',
                                    'stats' => '-'
                                );
                            }
                        }
                    ?>
                    <div class="player-slot" id="team1-player<?php echo $i; ?>">
                        <?php if ($participantName && $participantData): ?>
                            <!-- 参加者がいる場合 -->
                            <div class="player-main-info">
                                <div class="player-info">
                                    <div class="player-icon">
                                        <?php 
                                        $icon_id = $participantData['icon_id'] ?? 0;
                                        ?>
                                        <?php 
                                        $icon_url = "https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/{$icon_id}.png";
                                        ?>
                                        <img src="<?php echo esc_url($icon_url); ?>" 
                                             alt="<?php echo esc_attr($participantData['name'] ?? $participantName); ?>" 
                                             class="summoner-icon"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <i class="fas fa-user-circle" style="display:none;"></i>
                                    </div>
                                    <div class="player-details">
                                        <div class="player-name"><?php echo esc_html($participantData['name'] ?? $participantName); ?></div>
                                        <div class="player-level"><?php _e('レベル:', 'lol-team-splitter'); ?> <?php echo esc_html($participantData['level'] ?? '?'); ?></div>
                                    </div>
                                </div>
                                <div class="player-rank">
                                    <div class="rank-info"><?php echo esc_html($participantData['rank'] ?? 'Unknown'); ?></div>
                                    <div class="rank-stats"><?php echo esc_html($participantData['stats'] ?? '-'); ?></div>
                                </div>
                            </div>
                            <div class="player-actions">
                                <select class="form-select form-select-sm me-2 lane-select" onchange="changeLane(this, '<?php echo esc_js($participantData['name'] ?? $participantName); ?>')">
                                    <option value=""><?php _e('レーン', 'lol-team-splitter'); ?></option>
                                    <option value="top"><?php _e('トップ', 'lol-team-splitter'); ?></option>
                                    <option value="jungle"><?php _e('ジャングル', 'lol-team-splitter'); ?></option>
                                    <option value="mid"><?php _e('ミッド', 'lol-team-splitter'); ?></option>
                                    <option value="bot"><?php _e('ボット', 'lol-team-splitter'); ?></option>
                                    <option value="support"><?php _e('サポート', 'lol-team-splitter'); ?></option>
                                </select>
                                <button class="btn btn-outline-danger btn-sm" onclick="removePlayer(this)">
                                    <i class="fas fa-times me-1"></i><?php _e('削除', 'lol-team-splitter'); ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <!-- 空きスロット -->
                            <div class="player-main-info">
                                <div class="player-info">
                                    <div class="player-icon">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="player-details">
                                        <div class="player-name"><?php _e('空き', 'lol-team-splitter'); ?></div>
                                        <div class="player-level">-</div>
                                    </div>
                                </div>
                                <div class="player-rank">
                                    <div class="rank-info">-</div>
                                    <div class="rank-stats">-</div>
                                </div>
                            </div>
                            <div class="player-actions">
                                <button class="btn btn-outline-warning btn-sm" onclick="joinTeam(1, <?php echo $i; ?>)">
                                    <i class="fas fa-plus me-1"></i><?php _e('参加', 'lol-team-splitter'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <!-- チーム2 -->
        <div class="col-md-6">
            <div class="team-card bg-dark border-warning">
                <div class="team-header">
                    <h3 class="team-title text-warning">
                        <i class="fas fa-shield-alt me-2"></i>
                        <?php _e('チーム2 レッドサイド', 'lol-team-splitter'); ?>
                    </h3>
                    <div class="team-stats">
                        <span class="badge bg-warning text-dark"><?php _e('平均ランク:', 'lol-team-splitter'); ?> <span id="team2AvgRank">-</span></span>
                    </div>
                </div>
                
                <div class="team-players">
                    <!-- プレイヤー1-5 -->
                    <?php 
                    // チーム2の参加者データを処理（手動割り当て）
                    $team2Names = array_keys($team2Participants);
                    $team2SlotIndex = 0;
                    
                    for($i = 1; $i <= 5; $i++): 
                        $participantName = null;
                        $participantData = null;
                        
                        // このスロットに対応する参加者を見つける（チーム2用、手動割り当て）
                        if (isset($team2Names[$i - 1])) {
                            $participantName = $team2Names[$i - 1];
                            $participantData = $team2Participants[$participantName] ?? null;
                            
                            // 参加者データが配列でない場合は修正
                            if ($participantData && !is_array($participantData)) {
                                $participantData = array(
                                    'name' => $participantName,
                                    'level' => '?',
                                    'rank' => 'Unknown',
                                    'stats' => '-'
                                );
                            }
                        }
                    ?>
                    <div class="player-slot" id="team2-player<?php echo $i; ?>">
                        <?php if ($participantName && $participantData): ?>
                            <!-- 参加者がいる場合 -->
                            <div class="player-main-info">
                                <div class="player-info">
                                    <div class="player-icon">
                                        <?php 
                                        $icon_id = $participantData['icon_id'] ?? 0;
                                        ?>
                                        <?php 
                                        $icon_url = "https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/{$icon_id}.png";
                                        ?>
                                        <img src="<?php echo esc_url($icon_url); ?>" 
                                             alt="<?php echo esc_attr($participantData['name'] ?? $participantName); ?>" 
                                             class="summoner-icon"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <i class="fas fa-user-circle" style="display:none;"></i>
                                    </div>
                                    <div class="player-details">
                                        <div class="player-name"><?php echo esc_html($participantData['name'] ?? $participantName); ?></div>
                                        <div class="player-level"><?php _e('レベル:', 'lol-team-splitter'); ?> <?php echo esc_html($participantData['level'] ?? '?'); ?></div>
                                    </div>
                                </div>
                                <div class="player-rank">
                                    <div class="rank-info"><?php echo esc_html($participantData['rank'] ?? 'Unknown'); ?></div>
                                    <div class="rank-stats"><?php echo esc_html($participantData['stats'] ?? '-'); ?></div>
                                </div>
                            </div>
                            <div class="player-actions">
                                <select class="form-select form-select-sm me-2 lane-select" onchange="changeLane(this, '<?php echo esc_js($participantData['name'] ?? $participantName); ?>')">
                                    <option value=""><?php _e('レーン', 'lol-team-splitter'); ?></option>
                                    <option value="top"><?php _e('トップ', 'lol-team-splitter'); ?></option>
                                    <option value="jungle"><?php _e('ジャングル', 'lol-team-splitter'); ?></option>
                                    <option value="mid"><?php _e('ミッド', 'lol-team-splitter'); ?></option>
                                    <option value="bot"><?php _e('ボット', 'lol-team-splitter'); ?></option>
                                    <option value="support"><?php _e('サポート', 'lol-team-splitter'); ?></option>
                                </select>
                                <button class="btn btn-outline-danger btn-sm" onclick="removePlayer(this)">
                                    <i class="fas fa-times me-1"></i><?php _e('削除', 'lol-team-splitter'); ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <!-- 空きスロット -->
                            <div class="player-main-info">
                                <div class="player-info">
                                    <div class="player-icon">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="player-details">
                                        <div class="player-name"><?php _e('空き', 'lol-team-splitter'); ?></div>
                                        <div class="player-level">-</div>
                                    </div>
                                </div>
                                <div class="player-rank">
                                    <div class="rank-info">-</div>
                                    <div class="rank-stats">-</div>
                                </div>
                            </div>
                            <div class="player-actions">
                                <button class="btn btn-outline-warning btn-sm" onclick="joinTeam(2, <?php echo $i; ?>)">
                                    <i class="fas fa-plus me-1"></i><?php _e('参加', 'lol-team-splitter'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- コントロールパネル -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="control-panel bg-dark p-4 rounded">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="text-white mb-3">
                            <i class="fas fa-cog me-2"></i>
                            <?php _e('チーム分け設定', 'lol-team-splitter'); ?>
                        </h5>
                        <div class="form-check form-check-inline me-3">
                            <input class="form-check-input" type="radio" name="splitMode" id="balanceMode" value="balance" checked>
                            <label class="form-check-label text-light" for="balanceMode">
                                <?php _e('バランス重視', 'lol-team-splitter'); ?>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="splitMode" id="customMode" value="custom">
                            <label class="form-check-label text-light" for="customMode">
                                <?php _e('カスタム試合後', 'lol-team-splitter'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                前提条件: 10人参加 + 全員レーン設定済み
                            </small>
                        </div>
                        <button class="btn btn-warning btn-lg me-2" onclick="splitTeams()" id="splitTeamsBtn">
                            <i class="fas fa-magic me-2"></i>
                            <?php _e('チーム分け実行', 'lol-team-splitter'); ?>
                        </button>
                        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#resetConfirmModal">
                            <i class="fas fa-refresh me-2"></i>
                            <?php _e('リセット', 'lol-team-splitter'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- カスタムKDA入力セクション -->
                <div class="row mt-3" id="customKdaSection" style="display: none;">
                    <div class="col-12">
                        <div class="card bg-secondary">
                            <div class="card-header">
                                <h6 class="text-white mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    <?php _e('KDA入力', 'lol-team-splitter'); ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row" id="playerKdaInputs">
                                    <!-- プレイヤーのKDA入力フィールドがここに動的に生成されます -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- リセット確認モーダル -->
    <div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-labelledby="resetConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="resetConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <?php _e('チーム分けリセット確認', 'lol-team-splitter'); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-refresh text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-3"><?php _e('チーム分けをリセットしますか？', 'lol-team-splitter'); ?></h6>
                    <p class="text-muted mb-0"><?php _e('すべての参加者がチームから外れます。', 'lol-team-splitter'); ?></p>
                </div>
                <div class="modal-footer border-secondary justify-content-center">
                    <button type="button" class="btn btn-danger" id="confirmResetBtn">
                        <i class="fas fa-refresh me-1"></i>
                        <?php _e('リセット実行', 'lol-team-splitter'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- プレイヤー追加モーダル -->
    <div class="modal fade" id="addPlayerModal" tabindex="-1" aria-labelledby="addPlayerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="addPlayerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        <?php _e('プレイヤーを追加', 'lol-team-splitter'); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPlayerForm">
                        <div class="mb-3">
                            <label for="playerName" class="form-label">Riot ID</label>
                            <input type="text" class="form-control bg-secondary text-white border-secondary" id="playerName" 
                                   placeholder="<?php _e('例: サモナー名#JP1', 'lol-team-splitter'); ?>" required>
                        </div>
                        <div class="alert alert-info d-none" id="playerLoadingAlert">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            <?php _e('アカウント情報を取得中...', 'lol-team-splitter'); ?>
                        </div>
                        <div class="alert alert-danger d-none" id="playerErrorAlert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="playerErrorMessage"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('キャンセル', 'lol-team-splitter'); ?></button>
                    <button type="button" class="btn btn-warning" id="addPlayerBtn">
                        <i class="fas fa-plus me-2"></i>
                        <?php _e('追加', 'lol-team-splitter'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <!-- ルーム閉じる確認モーダル -->
    <div class="modal fade" id="closeRoomModal" tabindex="-1" aria-labelledby="closeRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="closeRoomModalLabel">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <?php _e('ルームを閉じる確認', 'lol-team-splitter'); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-3"><?php _e('ルームを閉じますか？', 'lol-team-splitter'); ?></h6>
                    <p class="text-muted mb-0"><?php _e('この操作は取り消せません。ルーム内のすべての参加者が退出します。', 'lol-team-splitter'); ?></p>
                </div>
                <div class="modal-footer border-secondary justify-content-center">
                    <button type="button" class="btn btn-danger" id="confirmCloseRoomBtn">
                        <i class="fas fa-times me-1"></i>
                        <?php _e('ルームを閉じる', 'lol-team-splitter'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
let currentPlayerSlot = null;
let players = [];

// テスト用のサンプルデータ
const testPlayers = [
    {
        name: "TestPlayer1#JP1",
        level: 150,
        rank: "DIAMOND I",
        stats: "120勝85敗",
        recentMatches: [
            { kills: 8, deaths: 3, assists: 12, win: true, champion: "Yasuo" },
            { kills: 12, deaths: 2, assists: 8, win: true, champion: "Zed" },
            { kills: 6, deaths: 4, assists: 15, win: true, champion: "Ahri" },
            { kills: 4, deaths: 6, assists: 10, win: false, champion: "LeBlanc" },
            { kills: 10, deaths: 1, assists: 14, win: true, champion: "Akali" }
        ]
    },
    {
        name: "TestPlayer2#JP1",
        level: 200,
        rank: "PLATINUM II",
        stats: "95勝70敗",
        recentMatches: [
            { kills: 3, deaths: 2, assists: 18, win: true, champion: "Thresh" },
            { kills: 1, deaths: 4, assists: 22, win: true, champion: "Leona" },
            { kills: 2, deaths: 3, assists: 16, win: false, champion: "Nautilus" },
            { kills: 4, deaths: 1, assists: 20, win: true, champion: "Blitzcrank" },
            { kills: 0, deaths: 5, assists: 15, win: false, champion: "Pyke" }
        ]
    },
    {
        name: "TestPlayer3#JP1",
        level: 180,
        rank: "GOLD I",
        stats: "80勝65敗",
        recentMatches: [
            { kills: 15, deaths: 3, assists: 5, win: true, champion: "Jinx" },
            { kills: 12, deaths: 2, assists: 8, win: true, champion: "Caitlyn" },
            { kills: 8, deaths: 4, assists: 12, win: true, champion: "Ezreal" },
            { kills: 6, deaths: 7, assists: 9, win: false, champion: "Vayne" },
            { kills: 18, deaths: 1, assists: 6, win: true, champion: "Tristana" }
        ]
    },
    {
        name: "TestPlayer4#JP1",
        level: 220,
        rank: "MASTER 450LP",
        stats: "150勝90敗",
        recentMatches: [
            { kills: 5, deaths: 1, assists: 8, win: true, champion: "Graves" },
            { kills: 8, deaths: 2, assists: 12, win: true, champion: "Lee Sin" },
            { kills: 3, deaths: 4, assists: 15, win: true, champion: "Elise" },
            { kills: 7, deaths: 3, assists: 10, win: true, champion: "Kha'Zix" },
            { kills: 4, deaths: 2, assists: 14, win: true, champion: "Rengar" }
        ]
    },
    {
        name: "TestPlayer5#JP1",
        level: 160,
        rank: "SILVER III",
        stats: "45勝55敗",
        recentMatches: [
            { kills: 2, deaths: 8, assists: 6, win: false, champion: "Garen" },
            { kills: 4, deaths: 6, assists: 4, win: false, champion: "Darius" },
            { kills: 1, deaths: 9, assists: 3, win: false, champion: "Mundo" },
            { kills: 3, deaths: 5, assists: 7, win: true, champion: "Malphite" },
            { kills: 5, deaths: 4, assists: 8, win: true, champion: "Sion" }
        ]
    },
    {
        name: "TestPlayer6#JP1",
        level: 190,
        rank: "PLATINUM IV",
        stats: "88勝72敗",
        recentMatches: [
            { kills: 7, deaths: 4, assists: 11, win: true, champion: "Orianna" },
            { kills: 9, deaths: 2, assists: 8, win: true, champion: "Syndra" },
            { kills: 5, deaths: 6, assists: 12, win: false, champion: "Azir" },
            { kills: 11, deaths: 3, assists: 6, win: true, champion: "Cassiopeia" },
            { kills: 6, deaths: 5, assists: 9, win: true, champion: "Viktor" }
        ]
    },
    {
        name: "TestPlayer7#JP1",
        level: 140,
        rank: "BRONZE I",
        stats: "30勝45敗",
        recentMatches: [
            { kills: 1, deaths: 12, assists: 2, win: false, champion: "Yuumi" },
            { kills: 0, deaths: 8, assists: 4, win: false, champion: "Soraka" },
            { kills: 2, deaths: 6, assists: 8, win: true, champion: "Lulu" },
            { kills: 1, deaths: 10, assists: 3, win: false, champion: "Janna" },
            { kills: 3, deaths: 7, assists: 6, win: false, champion: "Nami" }
        ]
    },
    {
        name: "TestPlayer8#JP1",
        level: 210,
        rank: "DIAMOND III",
        stats: "110勝75敗",
        recentMatches: [
            { kills: 6, deaths: 2, assists: 9, win: true, champion: "Camille" },
            { kills: 8, deaths: 3, assists: 7, win: true, champion: "Fiora" },
            { kills: 4, deaths: 5, assists: 11, win: true, champion: "Irelia" },
            { kills: 7, deaths: 4, assists: 8, win: true, champion: "Riven" },
            { kills: 5, deaths: 3, assists: 10, win: true, champion: "Jax" }
        ]
    },
    {
        name: "TestPlayer9#JP1",
        level: 170,
        rank: "GOLD IV",
        stats: "65勝58敗",
        recentMatches: [
            { kills: 9, deaths: 4, assists: 7, win: true, champion: "Kai'Sa" },
            { kills: 7, deaths: 5, assists: 9, win: false, champion: "Lucian" },
            { kills: 11, deaths: 2, assists: 6, win: true, champion: "Draven" },
            { kills: 5, deaths: 6, assists: 8, win: false, champion: "Varus" },
            { kills: 8, deaths: 3, assists: 10, win: true, champion: "Sivir" }
        ]
    },
    {
        name: "TestPlayer10#JP1",
        level: 230,
        rank: "CHALLENGER 1200LP",
        stats: "200勝120敗",
        recentMatches: [
            { kills: 12, deaths: 1, assists: 15, win: true, champion: "Riven" },
            { kills: 15, deaths: 2, assists: 8, win: true, champion: "Yasuo" },
            { kills: 9, deaths: 3, assists: 12, win: true, champion: "Zed" },
            { kills: 11, deaths: 4, assists: 9, win: true, champion: "Akali" },
            { kills: 13, deaths: 1, assists: 11, win: true, champion: "Irelia" }
        ]
    }
];

// 直前5試合の勝率を計算する関数
function getWinRate(recentMatches) {
    if (!recentMatches || recentMatches.length === 0) return 0.5; // デフォルト50%
    
    const wins = recentMatches.filter(match => match.win).length;
    return wins / recentMatches.length;
}

// KDAスコアを計算する関数
function getKdaScore(recentMatches) {
    if (!recentMatches || recentMatches.length === 0) return 1.0; // デフォルトKDA 1.0
    
    let totalKills = 0;
    let totalDeaths = 0;
    let totalAssists = 0;
    
    recentMatches.forEach(match => {
        totalKills += match.kills || 0;
        totalDeaths += match.deaths || 0;
        totalAssists += match.assists || 0;
    });
    
    // デスが0の場合は1に設定（KDA無限大を防ぐ）
    if (totalDeaths === 0) totalDeaths = 1;
    
    const kda = (totalKills + totalAssists) / totalDeaths;
    
    // KDAを0-10のスケールに正規化（KDA 3.0 = スコア 5.0）
    return Math.min(10, Math.max(0, kda * 1.67));
}

// 総合スコアを計算する関数
function getOverallScore(player) {
    const rankScore = getRankValue(player.rank);
    const winRate = getWinRate(player.recentMatches);
    const kdaScore = getKdaScore(player.recentMatches);
    
    // スコアの重み付け
    const rankWeight = 0.6;    // ランク 60%
    const winRateWeight = 0.25; // 勝率 25%
    const kdaWeight = 0.15;    // KDA 15%
    
    // ランクスコアを0-10に正規化
    const normalizedRankScore = Math.min(10, rankScore / 1000);
    
    // 勝率を0-10に変換（50% = 5.0）
    const winRateScore = winRate * 10;
    
    // 総合スコア計算
    const overallScore = (normalizedRankScore * rankWeight) + 
                       (winRateScore * winRateWeight) + 
                       (kdaScore * kdaWeight);
    
    return {
        overall: overallScore,
        rank: normalizedRankScore,
        winRate: winRateScore,
        kda: kdaScore,
        details: {
            rankValue: rankScore,
            winRatePercent: (winRate * 100).toFixed(1),
            kdaRaw: kdaScore / 1.67
        }
    };
}

// ランクを数値に変換する関数（バランシング用）
function getRankValue(rankString) {
    if (!rankString || rankString === 'Unknown') return 0;
    
    const rankOrder = {
        'IRON': 1,
        'BRONZE': 2,
        'SILVER': 3,
        'GOLD': 4,
        'PLATINUM': 5,
        'EMERALD': 6,
        'DIAMOND': 7,
        'MASTER': 8,
        'GRANDMASTER': 9,
        'CHALLENGER': 10
    };
    
    // ランク文字列を解析
    const parts = rankString.split(' ');
    const tier = parts[0];
    const tierValue = rankOrder[tier] || 0;
    
    // Master以上の場合はLPを考慮
    if (['MASTER', 'GRANDMASTER', 'CHALLENGER'].includes(tier)) {
        const lpMatch = rankString.match(/(\d+)LP/);
        const lp = lpMatch ? parseInt(lpMatch[1]) : 0;
        return tierValue * 1000 + lp; // LPを細かい調整値として使用
    } else {
        // 通常のランク（I, II, III, IV）
        const rankMatch = rankString.match(/([IV]+)$/);
        const rankRoman = rankMatch ? rankMatch[1] : '';
        const rankValue = {
            'I': 4,
            'II': 3,
            'III': 2,
            'IV': 1
        }[rankRoman] || 0;
        return tierValue * 100 + rankValue;
    }
}

// 精密なバランシングアルゴリズム
function advancedBalanceTeams(players) {
    // 各プレイヤーの総合スコアを計算
    const playersWithScores = players.map(player => ({
        ...player,
        score: getOverallScore(player)
    }));
    
    // 総合スコア順でソート（高い順）
    const sortedPlayers = playersWithScores.sort((a, b) => b.score.overall - a.score.overall);
    
    
    // 最適なチーム分けを探索
    const bestBalance = findOptimalTeamBalance(sortedPlayers);
    
    return bestBalance;
}

// 最適なチームバランスを探索する関数
function findOptimalTeamBalance(sortedPlayers) {
    const totalPlayers = sortedPlayers.length;
    const teamSize = Math.floor(totalPlayers / 2);
    
    let bestBalance = null;
    let minScoreDifference = Infinity;
    
    // 全ての可能な組み合わせを試行（計算量を抑えるため、上位プレイヤーから順次配置）
    function tryTeamCombination(team1, team2, remainingPlayers, index) {
        if (team1.length === teamSize && team2.length === teamSize) {
            // 両チームが満杯になったらスコア差を計算
            const team1Score = team1.reduce((sum, p) => sum + p.score.overall, 0) / team1.length;
            const team2Score = team2.reduce((sum, p) => sum + p.score.overall, 0) / team2.length;
            const scoreDifference = Math.abs(team1Score - team2Score);
            
            if (scoreDifference < minScoreDifference) {
                minScoreDifference = scoreDifference;
                bestBalance = {
                    team1: [...team1],
                    team2: [...team2],
                    team1Score: team1Score,
                    team2Score: team2Score,
                    scoreDifference: scoreDifference
                };
            }
            return;
        }
        
        if (index >= remainingPlayers.length) return;
        
        const currentPlayer = remainingPlayers[index];
        
        // チーム1に追加
        if (team1.length < teamSize) {
            tryTeamCombination([...team1, currentPlayer], team2, remainingPlayers, index + 1);
        }
        
        // チーム2に追加
        if (team2.length < teamSize) {
            tryTeamCombination(team1, [...team2, currentPlayer], remainingPlayers, index + 1);
        }
    }
    
    // 探索開始
    tryTeamCombination([], [], sortedPlayers, 0);
    
    // 結果が見つからない場合は蛇行配置にフォールバック
    if (!bestBalance) {
        const team1 = [];
        const team2 = [];
        
        sortedPlayers.forEach((player, index) => {
            if (index % 2 === 0) {
                team1.push(player);
            } else {
                team2.push(player);
            }
        });
        
        const team1Score = team1.reduce((sum, p) => sum + p.score.overall, 0) / team1.length;
        const team2Score = team2.reduce((sum, p) => sum + p.score.overall, 0) / team2.length;
        
        bestBalance = {
            team1,
            team2,
            team1Score,
            team2Score,
            scoreDifference: Math.abs(team1Score - team2Score)
        };
    }
    
    return bestBalance;
}

// 最適なチーム分けを行う関数
function balanceTeams(players) {
    // プレイヤーをランク順でソート（高い順）
    const sortedPlayers = [...players].sort((a, b) => {
        const rankA = getRankValue(a.rank);
        const rankB = getRankValue(b.rank);
        return rankB - rankA; // 高いランクから順
    });
    
    // 蛇行配置でチーム分け
    const team1 = [];
    const team2 = [];
    
    sortedPlayers.forEach((player, index) => {
        if (index % 2 === 0) {
            team1.push(player);
        } else {
            team2.push(player);
        }
    });
    
    return { team1, team2 };
}

// ランク取得のヘルパー関数
function getHighestRank(ranks) {
    if (!ranks || ranks.length === 0) return 'Unknown';
    
    const rankOrder = {
        'IRON': 1,
        'BRONZE': 2,
        'SILVER': 3,
        'GOLD': 4,
        'PLATINUM': 5,
        'EMERALD': 6,
        'DIAMOND': 7,
        'MASTER': 8,
        'GRANDMASTER': 9,
        'CHALLENGER': 10
    };
    
    let highestOrder = 0;
    let highestRank = null;
    
    for (const rank of ranks) {
        const tier = rank.tier;
        if (rankOrder[tier] && rankOrder[tier] > highestOrder) {
            highestOrder = rankOrder[tier];
            highestRank = rank;
        }
    }
    
    if (!highestRank) return 'Unknown';
    
    // Master以上のランクはLPで表示
    if (['MASTER', 'GRANDMASTER', 'CHALLENGER'].includes(highestRank.tier)) {
        return `${highestRank.tier} ${highestRank.leaguePoints || 0}LP`;
    } else {
        return `${highestRank.tier} ${highestRank.rank}`;
    }
}

function getHighestRankStats(ranks) {
    if (!ranks || ranks.length === 0) return '-';
    
    const rankOrder = {
        'IRON': 1,
        'BRONZE': 2,
        'SILVER': 3,
        'GOLD': 4,
        'PLATINUM': 5,
        'EMERALD': 6,
        'DIAMOND': 7,
        'MASTER': 8,
        'GRANDMASTER': 9,
        'CHALLENGER': 10
    };
    
    let highestOrder = 0;
    let highestRank = null;
    
    for (const rank of ranks) {
        const tier = rank.tier;
        if (rankOrder[tier] && rankOrder[tier] > highestOrder) {
            highestOrder = rankOrder[tier];
            highestRank = rank;
        }
    }
    
    return highestRank ? `${highestRank.wins}勝${highestRank.losses}敗` : '-';
}

// サーバーから参加者データを取得して表示する関数
function loadParticipantsFromServer() {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    fetch(`${ajax_object.ajax_url}?action=get_room_info&room_id=${roomId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const roomData = data.data;
            const participants = roomData.participants || {};
            
            // 参加者リストをクリア
            players = [];
            
            // すべての参加者を参加者リストに追加（チーム情報も含む）
            Object.values(participants).forEach(participant => {
                const player = {
                    name: participant.name,
                    level: participant.level !== undefined && participant.level !== null ? participant.level : '?',
                    rank: participant.rank !== undefined && participant.rank !== null && participant.rank !== '' ? participant.rank : 'Unknown',
                    stats: participant.stats !== undefined && participant.stats !== null && participant.stats !== '' ? participant.stats : '-',
                    lane: participant.lane || '',
                    team: participant.team || null,
                    icon_id: participant.icon_id || 0,
                    icon_url: participant.icon_url || '',
                    recentMatches: participant.recent_matches || [],
                    isAI: false
                };
                
                players.push(player);
                
                // 初回のみRiot APIから取得（既に保存されている場合は取得しない）
                // データが完全に欠けている場合のみ取得
                const hasNoData = (!player.level || player.level === '?') && 
                                  (!player.rank || player.rank === 'Unknown') && 
                                  (!player.stats || player.stats === '-') &&
                                  (!player.icon_id || player.icon_id === 0);
                if (hasNoData) {
                    fetchPlayerDataFromRiotAPI(player.name, null);
                }
            });
            
            // 参加者をスロットに表示（保護モードを無効にして強制的に表示）
            updateAllPlayerSlots(participants, false);
            
            // レーン選択状態を更新
            updateLaneSelectStates(1);
            updateLaneSelectStates(2);
            
            updateTeamStats();
        } else {
            // ルームが存在しない場合（ホストがルームを閉じた場合など）
            // 定期的な更新を停止
            if (window.participantsUpdateInterval) {
                clearInterval(window.participantsUpdateInterval);
            }
            if (window.kdaUpdateInterval) {
                clearInterval(window.kdaUpdateInterval);
            }
            // ホームページにリダイレクト
            window.location.href = '<?php echo home_url('/'); ?>';
        }
    })
    .catch(error => {
        // 通信エラーの場合もルームが閉じられた可能性があるため、確認する
        // 定期的な更新を停止
        if (window.participantsUpdateInterval) {
            clearInterval(window.participantsUpdateInterval);
        }
        if (window.kdaUpdateInterval) {
            clearInterval(window.kdaUpdateInterval);
        }
        // ホームページにリダイレクト
        window.location.href = '<?php echo home_url('/'); ?>';
    });
}

// テストデータ読込機能
function loadTestData() {
    if (!confirm('テストデータを読み込みますか？既存の参加者データは上書きされます。')) {
        return;
    }
    
    // 既存の参加者をクリア
    players = [];
    
    // すべてのスロットをリセット
    for (let team = 1; team <= 2; team++) {
        for (let slot = 1; slot <= 5; slot++) {
            const slotElement = document.getElementById(`team${team}-player${slot}`);
            if (slotElement) {
                resetSlot(slotElement);
            }
        }
    }
    
    // 精密バランシングアルゴリズムを使用してテストデータを配置
    const balancedTeams = advancedBalanceTeams(testPlayers);
    
    // すべてのプレイヤーを順番に参加させる
    const allPlayers = [...balancedTeams.team1, ...balancedTeams.team2];
    let currentIndex = 0;
    
    function addNextTestPlayer() {
        if (currentIndex >= allPlayers.length) {
            // すべてのプレイヤーが追加完了
            setTimeout(() => {
                // サーバーから最新の参加者データを取得して表示を同期
                loadParticipantsFromServer();
                
                showNotification('テストデータ（10人）をバランス重視で読み込みました', 'success');
            }, 1000);
            return;
        }
        
        const testPlayer = allPlayers[currentIndex];
        const isTeam1 = balancedTeams.team1.includes(testPlayer);
        const teamNumber = isTeam1 ? 1 : 2;
        const teamPlayers = isTeam1 ? balancedTeams.team1 : balancedTeams.team2;
        const slotNumber = teamPlayers.indexOf(testPlayer) + 1;
        
        
        // プレイヤーオブジェクトを作成
        const player = {
            name: testPlayer.name,
            level: testPlayer.level,
            rank: testPlayer.rank,
            stats: testPlayer.stats,
            lane: '',
            team: teamNumber,
            slot_position: slotNumber,
            icon_id: 0, // デフォルトアイコン
            icon_url: 'https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/0.png',
            recentMatches: testPlayer.recentMatches,
            isAI: true // テストデータであることを示すフラグ
        };
        
        // プレイヤーリストに追加
        players.push(player);
        
        // スロットを更新
        const slotElement = document.getElementById(`team${teamNumber}-player${slotNumber}`);
        if (slotElement) {
            updatePlayerSlot(slotElement, player);
        }
        
        // サーバーに保存（完了後に次のプレイヤーを追加）
        savePlayerToRoom(player).then(() => {
            currentIndex++;
            setTimeout(addNextTestPlayer, 100); // 100ms待機してから次のプレイヤーを追加
        }).catch((error) => {
            // エラーが発生した場合も次のプレイヤーに進む
            currentIndex++;
            setTimeout(addNextTestPlayer, 100);
        });
    }
    
    // 最初のプレイヤーから開始
    addNextTestPlayer();
}

// カスタム試合後モードの表示/非表示制御
document.addEventListener('DOMContentLoaded', function() {
    const customModeRadio = document.getElementById('customMode');
    const balanceModeRadio = document.getElementById('balanceMode');
    const customKdaSection = document.getElementById('customKdaSection');
    
    if (customModeRadio && balanceModeRadio && customKdaSection) {
        // カスタム試合後が選択された時
        customModeRadio.addEventListener('change', function() {
            if (this.checked) {
                customKdaSection.style.display = 'block';
                generatePlayerKdaInputs();
            }
        });
        
        // バランス重視が選択された時
        balanceModeRadio.addEventListener('change', function() {
            if (this.checked) {
                customKdaSection.style.display = 'none';
            }
        });
    }
});

// プレイヤーのKDA入力フィールドを生成
function generatePlayerKdaInputs() {
    const container = document.getElementById('playerKdaInputs');
    if (!container) return;
    
    container.innerHTML = '';
    
    // 参加者を取得（players配列から）
    const participants = players.filter(p => p.name && p.name !== '');
    
    if (participants.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-light"><p>参加者がいません。まずプレイヤーを参加させてください。</p></div>';
        return;
    }
    
    // サーバーからKDAデータを取得
    const roomId = '<?php echo esc_js($roomId); ?>';
    const formData = new FormData();
    formData.append('action', 'get_kda_data');
    formData.append('room_id', roomId);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const savedKdaData = data.success ? data.data.kda_data : {};
        
        participants.forEach((player, index) => {
            const colDiv = document.createElement('div');
            colDiv.className = 'col-md-6 col-lg-4 mb-3';
            
            // 保存されたKDAデータを取得
            const savedKda = savedKdaData[player.name] || {};
            const killValue = savedKda.kill || '';
            const deathValue = savedKda.death || '';
            const assistValue = savedKda.assist || '';
            
            colDiv.innerHTML = `
                <div class="card bg-dark">
                    <div class="card-body">
                        <h6 class="text-white mb-3">${player.name}</h6>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label text-light small">キル</label>
                                <input type="number" class="form-control form-control-sm bg-secondary text-white border-secondary" 
                                       id="kda_kill_${index}" min="0" placeholder="0" value="${killValue}"
                                       onchange="savePlayerKda(${index})" oninput="savePlayerKda(${index})">
                            </div>
                            <div class="col-4">
                                <label class="form-label text-light small">デス</label>
                                <input type="number" class="form-control form-control-sm bg-secondary text-white border-secondary" 
                                       id="kda_death_${index}" min="0" placeholder="0" value="${deathValue}"
                                       onchange="savePlayerKda(${index})" oninput="savePlayerKda(${index})">
                            </div>
                            <div class="col-4">
                                <label class="form-label text-light small">アシスト</label>
                                <input type="number" class="form-control form-control-sm bg-secondary text-white border-secondary" 
                                       id="kda_assist_${index}" min="0" placeholder="0" value="${assistValue}"
                                       onchange="savePlayerKda(${index})" oninput="savePlayerKda(${index})">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(colDiv);
        });
    })
    .catch(error => {
        console.error('KDAデータ取得エラー:', error);
        // エラー時は空の値で表示
        participants.forEach((player, index) => {
            const colDiv = document.createElement('div');
            colDiv.className = 'col-md-6 col-lg-4 mb-3';
            
            colDiv.innerHTML = `
                <div class="card bg-dark">
                    <div class="card-body">
                        <h6 class="text-white mb-3">${player.name}</h6>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label text-light small">キル</label>
                                <input type="number" class="form-control form-control-sm bg-secondary text-white border-secondary" 
                                       id="kda_kill_${index}" min="0" placeholder="0"
                                       onchange="savePlayerKda(${index})" oninput="savePlayerKda(${index})">
                            </div>
                            <div class="col-4">
                                <label class="form-label text-light small">デス</label>
                                <input type="number" class="form-control form-control-sm bg-secondary text-white border-secondary" 
                                       id="kda_death_${index}" min="0" placeholder="0"
                                       onchange="savePlayerKda(${index})" oninput="savePlayerKda(${index})">
                            </div>
                            <div class="col-4">
                                <label class="form-label text-light small">アシスト</label>
                                <input type="number" class="form-control form-control-sm bg-secondary text-white border-secondary" 
                                       id="kda_assist_${index}" min="0" placeholder="0"
                                       onchange="savePlayerKda(${index})" oninput="savePlayerKda(${index})">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(colDiv);
        });
    });
}

// プレイヤーのKDAを自動保存する関数
function savePlayerKda(playerIndex) {
    const participants = players.filter(p => p.name && p.name !== '');
    
    if (playerIndex >= participants.length) return;
    
    const player = participants[playerIndex];
    const kill = document.getElementById(`kda_kill_${playerIndex}`).value;
    const death = document.getElementById(`kda_death_${playerIndex}`).value;
    const assist = document.getElementById(`kda_assist_${playerIndex}`).value;
    
    // サーバーにKDAデータを保存
    const roomId = '<?php echo esc_js($roomId); ?>';
    const formData = new FormData();
    formData.append('action', 'save_kda_data');
    formData.append('room_id', roomId);
    formData.append('player_name', player.name);
    formData.append('kill', kill || 0);
    formData.append('death', death || 0);
    formData.append('assist', assist || 0);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // すべてのフィールドが入力されている場合のみUIを更新
            if (kill !== '' && death !== '' && assist !== '') {
                const killNum = parseInt(kill);
                const deathNum = parseInt(death);
                const assistNum = parseInt(assist);
                
                // 0以上の数値かチェック
                if (killNum >= 0 && deathNum >= 0 && assistNum >= 0) {
                    // KDA文字列を生成（K/D/A形式）
                    const kdaString = `${killNum}/${deathNum}/${assistNum}`;
                    
                    // プレイヤーにKDAを適用
                    const playerIndexInArray = players.findIndex(p => p.name === player.name);
                    if (playerIndexInArray !== -1) {
                        players[playerIndexInArray].stats = kdaString;
                        players[playerIndexInArray].customKda = {
                            kill: killNum,
                            death: deathNum,
                            assist: assistNum
                        };
                        
                        // UIを更新
                        updateTeamStats();
                        updateAverageRanks();
                        
                        // プレイヤースロットの表示を更新
                        for (let team = 1; team <= 2; team++) {
                            for (let slot = 1; slot <= 5; slot++) {
                                const slotElement = document.getElementById(`team${team}-player${slot}`);
                                if (slotElement) {
                                    const playerInfo = slotElement.querySelector('.player-info');
                                    if (playerInfo) {
                                        const slotPlayer = players.find(p => p.team === team && p.slot === slot);
                                        if (slotPlayer && slotPlayer.name === player.name) {
                                            updatePlayerSlot(slotElement, slotPlayer);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    })
    .catch(error => {
        console.error('KDA保存エラー:', error);
    });
}

// ページ読み込み時に参加者を自動表示
document.addEventListener('DOMContentLoaded', function() {
    const roomData = <?php echo json_encode(array(
        'host' => $host_name,
        'participants' => $sortedParticipants, // 参加順で並べ替え済みのデータを使用
        'room_id' => $roomId
    )); ?>;
    
    // ルーム情報をlocalStorageに保存（ホームページに戻る時の「ルームに戻る」ボタン用）
    const roomInfo = {
        roomId: roomData.room_id,
        hostName: roomData.host,
        createdAt: new Date().toISOString(),
        roomUrl: `${window.location.origin}/team-split/?room=${roomData.room_id}`
    };
    localStorage.setItem('currentRoom', JSON.stringify(roomInfo));
    
    // サーバーから最新の参加者データを取得して表示
    loadParticipantsFromServer();
    
    // レーン選択状態を更新
    updateLaneSelectStates(1);
    updateLaneSelectStates(2);
    
    updateTeamStats();
    updateAverageRanks();
    
    // 定期的に参加者情報を更新（2秒ごと）
    window.participantsUpdateInterval = setInterval(updateParticipants, 2000);
    
    // 定期的にKDAデータを更新（3秒ごと）
    window.kdaUpdateInterval = setInterval(updateKdaData, 3000);
    
    // リセット確認モーダルの確認ボタンにイベントリスナーを追加
    const confirmResetBtn = document.getElementById('confirmResetBtn');
    if (confirmResetBtn) {
        confirmResetBtn.addEventListener('click', function() {
            // モーダルを閉じる
            const modal = bootstrap.Modal.getInstance(document.getElementById('resetConfirmModal'));
            if (modal) {
                modal.hide();
            }
            // リセット実行
            resetTeams();
        });
    }
    
    // ルーム閉じる確認モーダルの確認ボタンにイベントリスナーを追加
    const confirmCloseRoomBtn = document.getElementById('confirmCloseRoomBtn');
    if (confirmCloseRoomBtn) {
        confirmCloseRoomBtn.addEventListener('click', function() {
            // モーダルを閉じる
            const modal = bootstrap.Modal.getInstance(document.getElementById('closeRoomModal'));
            if (modal) {
                modal.hide();
            }
            // ルーム閉じる実行
            closeRoom();
        });
    }
});

// KDAデータを更新する関数
function updateKdaData() {
    // カスタム試合後が選択されていない場合は更新しない
    const customModeRadio = document.getElementById('customMode');
    if (!customModeRadio || !customModeRadio.checked) {
        return;
    }
    
    // KDA入力セクションが表示されていない場合は更新しない
    const customKdaSection = document.getElementById('customKdaSection');
    if (!customKdaSection || customKdaSection.style.display === 'none') {
        return;
    }
    
    const roomId = '<?php echo esc_js($roomId); ?>';
    const formData = new FormData();
    formData.append('action', 'get_kda_data');
    formData.append('room_id', roomId);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const savedKdaData = data.data.kda_data || {};
            
            // 各プレイヤーのKDA入力フィールドを更新
            const participants = players.filter(p => p.name && p.name !== '');
            participants.forEach((player, index) => {
                const savedKda = savedKdaData[player.name] || {};
                const killValue = savedKda.kill || '';
                const deathValue = savedKda.death || '';
                const assistValue = savedKda.assist || '';
                
                // 現在の入力値と比較して、異なる場合のみ更新
                const killInput = document.getElementById(`kda_kill_${index}`);
                const deathInput = document.getElementById(`kda_death_${index}`);
                const assistInput = document.getElementById(`kda_assist_${index}`);
                
                if (killInput && killInput.value !== killValue.toString()) {
                    killInput.value = killValue;
                }
                if (deathInput && deathInput.value !== deathValue.toString()) {
                    deathInput.value = deathValue;
                }
                if (assistInput && assistInput.value !== assistValue.toString()) {
                    assistInput.value = assistValue;
                }
                
                // KDAが完全に入力されている場合はプレイヤーデータも更新
                if (killValue !== '' && deathValue !== '' && assistValue !== '') {
                    const killNum = parseInt(killValue);
                    const deathNum = parseInt(deathValue);
                    const assistNum = parseInt(assistValue);
                    
                    if (killNum >= 0 && deathNum >= 0 && assistNum >= 0) {
                        const kdaString = `${killNum}/${deathNum}/${assistNum}`;
                        
                        const playerIndexInArray = players.findIndex(p => p.name === player.name);
                        if (playerIndexInArray !== -1) {
                            players[playerIndexInArray].stats = kdaString;
                            players[playerIndexInArray].customKda = {
                                kill: killNum,
                                death: deathNum,
                                assist: assistNum
                            };
                        }
                    }
                }
            });
            
            // UIを更新
            updateTeamStats();
            updateAverageRanks();
            
            // プレイヤースロットの表示を更新
            for (let team = 1; team <= 2; team++) {
                for (let slot = 1; slot <= 5; slot++) {
                    const slotElement = document.getElementById(`team${team}-player${slot}`);
                    if (slotElement) {
                        const playerInfo = slotElement.querySelector('.player-info');
                        if (playerInfo) {
                            const slotPlayer = players.find(p => p.team === team && p.slot === slot);
                            if (slotPlayer) {
                                updatePlayerSlot(slotElement, slotPlayer);
                            }
                        }
                    }
                }
            }
        }
    })
    .catch(error => {
        console.error('KDAデータ更新エラー:', error);
    });
}

// 参加者情報を更新する関数
function updateParticipants() {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    fetch(`${ajax_object.ajax_url}?action=get_room_info&room_id=${roomId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const roomData = data.data;
            const participants = roomData.participants || {};
            
            // 参加者を配列に変換
            const participantsArray = Object.values(participants);
            
            // 現在の参加者リストを更新
            const currentParticipants = participantsArray.map(p => p.name);
            const displayedParticipants = players.map(p => p.name);
            
            // 削除された参加者をチェック（すべての参加者を削除）
            displayedParticipants.forEach(playerName => {
                if (!currentParticipants.includes(playerName)) {
                    // 参加者が削除されている場合、スロットを空きに戻す
                    const slot = findPlayerSlot(playerName);
                    if (slot) {
                        resetSlot(slot);
                        // プレイヤーリストからも削除
                        const playerIndex = players.findIndex(p => p.name === playerName);
                        if (playerIndex !== -1) {
                            players.splice(playerIndex, 1);
                        }
                    }
                }
            });
            
            // 新しく参加した参加者をチェック（ホストも含む）
            currentParticipants.forEach(participantName => {
                if (!displayedParticipants.includes(participantName)) {
                    // 新しい参加者を追加（チーム情報も含む）
                    const participant = participants[participantName];
                    if (participant) {
                        const player = {
                            name: participant.name,
                            level: participant.level || '?',
                            rank: participant.rank || 'Unknown',
                            stats: participant.stats || '-',
                            lane: participant.lane || '',
                            team: participant.team || null,
                            icon_id: participant.icon_id || 0,
                            icon_url: participant.icon_url || '',
                            recentMatches: participant.recent_matches || [],
                            isAI: false
                        };
                        players.push(player);
                        
                        // 初回のみRiot APIから取得（既に保存されている場合は取得しない）
                        const hasNoData = (!player.level || player.level === '?') && 
                                          (!player.rank || player.rank === 'Unknown') && 
                                          (!player.stats || player.stats === '-') &&
                                          (!player.icon_id || player.icon_id === 0);
                        if (hasNoData) {
                            fetchPlayerDataFromRiotAPI(player.name, null);
                        }
                    }
                }
            });
            
            // 既存の参加者の情報を更新
            players.forEach(player => {
                const participant = participants[player.name];
                if (participant && player.team !== null) {
                    let needsUpdate = false;
                    
                    // レベル情報が変更されている場合、更新
                    if (player.level !== participant.level && participant.level !== undefined && participant.level !== null) {
                        player.level = participant.level;
                        needsUpdate = true;
                    }
                    
                    // ランク情報が変更されている場合、更新
                    if (player.rank !== participant.rank && participant.rank !== undefined && participant.rank !== null && participant.rank !== '') {
                        player.rank = participant.rank;
                        needsUpdate = true;
                    }
                    
                    // ランク統計が変更されている場合、更新
                    if (player.stats !== participant.stats && participant.stats !== undefined && participant.stats !== null && participant.stats !== '') {
                        player.stats = participant.stats;
                        needsUpdate = true;
                    }
                    
                    // レーン情報が変更されている場合、更新
                    if (player.lane !== participant.lane) {
                        player.lane = participant.lane;
                        // スロットのレーン選択を更新
                        const slot = findPlayerSlot(player.name);
                        if (slot) {
                            const laneSelect = slot.querySelector('.lane-select');
                            if (laneSelect) {
                                laneSelect.value = participant.lane || '';
                            }
                        }
                        needsUpdate = true;
                    }
                    
                    // 戦績データを更新（空の場合も含む）
                    if (participant.hasOwnProperty('recent_matches')) {
                        player.recentMatches = participant.recent_matches || [];
                        needsUpdate = true;
                    }
                    
                    // 何かしらの情報が更新された場合、スロットを更新
                    if (needsUpdate) {
                        const slot = findPlayerSlot(player.name);
                        if (slot) {
                            updatePlayerSlot(slot, player);
                        }
                    }
                    
                    // 既存の参加者はデータが既に保存されているため、Riot APIは呼び出さない
                    // データが必要な場合は、初回参加時のみ取得される
                }
            });
            
            // 全参加者のスロットを更新（レーン情報も含む）
            // ただし、既にチームに参加している参加者は上書きしない
            updateAllPlayerSlots(participants, true);
            
            // レーン選択状態を更新
            updateLaneSelectStates(1);
            updateLaneSelectStates(2);
            
            updateTeamStats();
        } else {
            // ルームが存在しない場合（ホストがルームを閉じた場合など）
            // 定期的な更新を停止
            if (window.participantsUpdateInterval) {
                clearInterval(window.participantsUpdateInterval);
            }
            if (window.kdaUpdateInterval) {
                clearInterval(window.kdaUpdateInterval);
            }
            // ホームページにリダイレクト
            window.location.href = '<?php echo home_url('/'); ?>';
        }
    })
    .catch(error => {
        // 通信エラーの場合もルームが閉じられた可能性があるため、確認する
        // 定期的な更新を停止
        if (window.participantsUpdateInterval) {
            clearInterval(window.participantsUpdateInterval);
        }
        if (window.kdaUpdateInterval) {
            clearInterval(window.kdaUpdateInterval);
        }
        // ホームページにリダイレクト
        window.location.href = '<?php echo home_url('/'); ?>';
    });
}

// 全参加者のスロットを更新する関数
function updateAllPlayerSlots(participants, preserveExisting = false) {
    // チーム1とチーム2に分ける（スロット位置ごとにマップ）
    const team1PlayersMap = {};
    const team2PlayersMap = {};
    
    Object.values(participants).forEach(player => {
        if (player.team === 1 && player.slot_position) {
            team1PlayersMap[player.slot_position] = player;
        } else if (player.team === 2 && player.slot_position) {
            team2PlayersMap[player.slot_position] = player;
        }
    });
    
    // チーム1のスロットを更新
    for (let i = 1; i <= 5; i++) {
        const slotElement = document.getElementById(`team1-player${i}`);
        if (slotElement) {
            const player = team1PlayersMap[i]; // スロット位置に基づいて取得
            if (player) {
                // 既存の参加者を保護する場合、既に表示されている参加者は上書きしない
                if (preserveExisting) {
                    const existingPlayerName = slotElement.querySelector('.player-name');
                    if (existingPlayerName && existingPlayerName.textContent !== '<?php _e('空き', 'lol-team-splitter'); ?>') {
                        // 既に表示されている参加者で、チームに参加している場合はスキップ
                        const existingPlayer = players.find(p => p.name === existingPlayerName.textContent && p.team !== null);
                        if (existingPlayer) {
                            continue; // チームに参加している参加者はスキップ
                        }
                    }
                }
                // サーバーから取得した参加者データをplayers配列の形式に変換
                const playerData = {
                    name: player.name,
                    level: player.level !== undefined && player.level !== null ? player.level : '?',
                    rank: player.rank !== undefined && player.rank !== null && player.rank !== '' ? player.rank : 'Unknown',
                    stats: player.stats !== undefined && player.stats !== null && player.stats !== '' ? player.stats : '-',
                    lane: player.lane || '',
                    team: player.team || null,
                    slot_position: player.slot_position || null,
                    icon_id: player.icon_id || 0,
                    icon_url: player.icon_url || '',
                    recentMatches: player.recent_matches || [],
                    isAI: false
                };
                updatePlayerSlot(slotElement, playerData);
            } else if (!preserveExisting) {
                // 保護モードでない場合のみ空きスロットにリセット
                resetSlot(slotElement);
            }
        }
    }
    
    // チーム2のスロットを更新
    for (let i = 1; i <= 5; i++) {
        const slotElement = document.getElementById(`team2-player${i}`);
        if (slotElement) {
            const player = team2PlayersMap[i]; // スロット位置に基づいて取得
            if (player) {
                // 既存の参加者を保護する場合、既に表示されている参加者は上書きしない
                if (preserveExisting) {
                    const existingPlayerName = slotElement.querySelector('.player-name');
                    if (existingPlayerName && existingPlayerName.textContent !== '<?php _e('空き', 'lol-team-splitter'); ?>') {
                        // 既に表示されている参加者で、チームに参加している場合はスキップ
                        const existingPlayer = players.find(p => p.name === existingPlayerName.textContent && p.team !== null);
                        if (existingPlayer) {
                            continue; // チームに参加している参加者はスキップ
                        }
                    }
                }
                // サーバーから取得した参加者データをplayers配列の形式に変換
                const playerData = {
                    name: player.name,
                    level: player.level !== undefined && player.level !== null ? player.level : '?',
                    rank: player.rank !== undefined && player.rank !== null && player.rank !== '' ? player.rank : 'Unknown',
                    stats: player.stats !== undefined && player.stats !== null && player.stats !== '' ? player.stats : '-',
                    lane: player.lane || '',
                    team: player.team || null,
                    slot_position: player.slot_position || null,
                    icon_id: player.icon_id || 0,
                    icon_url: player.icon_url || '',
                    recentMatches: player.recent_matches || [],
                    isAI: false
                };
                updatePlayerSlot(slotElement, playerData);
            } else if (!preserveExisting) {
                // 保護モードでない場合のみ空きスロットにリセット
                resetSlot(slotElement);
            }
        }
    }
}

// プレイヤーのスロットを見つける関数
function findPlayerSlot(playerName) {
    for (let team = 1; team <= 2; team++) {
        for (let slot = 1; slot <= 5; slot++) {
            const slotElement = document.getElementById(`team${team}-player${slot}`);
            const nameElement = slotElement.querySelector('.player-name');
            if (nameElement && nameElement.textContent === playerName) {
                return slotElement;
            }
        }
    }
    return null;
}

// スロットをリセットする関数
function resetSlot(slot) {
    const teamNumber = slot.id.split('-')[0].replace('team', '');
    const slotNumber = slot.id.split('-')[1].replace('player', '');
    
    slot.innerHTML = `
        <div class="player-main-info">
            <div class="player-info">
                <div class="player-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="player-details">
                    <div class="player-name"><?php _e('空き', 'lol-team-splitter'); ?></div>
                    <div class="player-level">-</div>
                </div>
            </div>
            <div class="player-rank">
                <div class="rank-info">-</div>
                <div class="rank-stats">-</div>
            </div>
        </div>
        <div class="player-actions">
            <button class="btn btn-outline-warning btn-sm" onclick="joinTeam(${teamNumber}, ${slotNumber})">
                <i class="fas fa-plus me-1"></i><?php _e('参加', 'lol-team-splitter'); ?>
            </button>
        </div>
    `;
}

// 新しい参加者を追加する関数
function addNewParticipant(participantName) {
    // 最大参加者数チェック（合計10人まで）
    if (players.length >= 10) {
        showNotification('ルームは満員です！（最大10人まで）', 'error');
        return;
    }
    
    // 参加者を追加（チーム情報は手動で設定されるまで未設定）
    const participantPlayer = {
        name: participantName,
        level: '?',
        rank: 'Unknown',
        stats: '-',
        lane: '',
        isAI: false,
        team: null // チームは未設定
    };
    
    // プレイヤーリストに追加（スロットには表示しない）
    players.push(participantPlayer);
}

// プレイヤー参加
function joinTeam(teamNumber, slotNumber) {
    // 最大参加者数チェック（合計10人まで）
    if (players.length >= 10) {
        showNotification('ルームは満員です！（最大10人まで）', 'error');
        return;
    }
    
    // スロットが既に埋まっているかチェック
    const slot = document.getElementById(`team${teamNumber}-player${slotNumber}`);
    const playerNameElement = slot.querySelector('.player-name');
    if (playerNameElement && playerNameElement.textContent !== '<?php _e('空き', 'lol-team-splitter'); ?>') {
        showNotification('このスロットは既に埋まっています！', 'warning');
        return;
    }
    
    // 保存されたサモナー情報を取得
    const playerName = localStorage.getItem('currentPlayerName');
    
    if (!playerName) {
        showNotification('プレイヤー情報が見つかりません。ルームに参加し直してください。', 'error');
        return;
    }
    
    // 既存のプレイヤーをチェック（サーバー側のデータを確認）
    const existingPlayer = players.find(p => p.name === playerName && p.team !== null);
    if (existingPlayer) {
        showNotification('既に参加済みです！', 'warning');
        return;
    }
    
    // ホストの場合は特別な処理（既に参加者データに含まれている可能性がある）
    const isHost = playerName === '<?php echo esc_js($host_name); ?>';
    if (isHost) {
        // ホストが既に参加者データに含まれているかチェック
        const hostInParticipants = players.find(p => p.name === playerName);
        if (hostInParticipants && hostInParticipants.team !== null) {
            showNotification('ホストは既に参加済みです！', 'warning');
            return;
        }
    }
    
    // プレイヤー情報を作成（一時的にデフォルト値を設定）
    const player = {
        name: playerName,
        level: '?',
        rank: 'Unknown',
        stats: '-',
        lane: '',
        icon_id: 0,
        icon_url: '',
        recentMatches: [],
        isAI: false
    };
    
    // プレイヤーにチーム情報とスロット位置を追加
    player.team = parseInt(teamNumber);
    player.slot_position = parseInt(slotNumber);
    
    // スロットを更新
    updatePlayerSlot(slot, player);
    
    // 既存のプレイヤーを更新または新規追加
    const existingPlayerIndex = players.findIndex(p => p.name === playerName);
    if (existingPlayerIndex !== -1) {
        // 既存のプレイヤーを更新
        players[existingPlayerIndex] = player;
    } else {
        // 新規プレイヤーを追加
        players.push(player);
    }
    
    // サーバーに参加者情報を保存（チーム情報も含む）
    savePlayerToRoom(player);
    
    // レーン選択状態を更新
    updateLaneSelectStates(teamNumber);
    
    updateTeamStats();
    updateAverageRanks();
    
    // Riot APIからプレイヤー情報を取得して更新
    fetchPlayerDataFromRiotAPI(playerName, slot);
    
    // 即座に他の参加者にも反映させるため、短時間で更新
    setTimeout(() => {
        updateParticipants();
    }, 500);
}

// Riot APIからプレイヤーデータを取得する関数（初回登録時のみ使用）
function fetchPlayerDataFromRiotAPI(playerName, slot) {
    const formData = new FormData();
    formData.append('action', 'get_summoner_info');
    formData.append('nonce', ajax_object.nonce);
    formData.append('summoner_name', playerName);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // プレイヤー情報を更新
            const playerIndex = players.findIndex(p => p.name === playerName);
            if (playerIndex !== -1) {
                players[playerIndex].level = data.data.level || '?';
                players[playerIndex].rank = getHighestRank(data.data.ranks || []);
                players[playerIndex].stats = getHighestRankStats(data.data.ranks || []);
                players[playerIndex].icon_id = data.data.icon_id || 0;
                players[playerIndex].icon_url = data.data.icon_url || '';
                players[playerIndex].recentMatches = data.data.recent_matches || [];
                
                
                // スロットを更新（スロットが指定されている場合のみ）
                if (slot) {
                    updatePlayerSlot(slot, players[playerIndex]);
                } else {
                    // スロットが指定されていない場合は、該当するスロットを探して更新
                    const player = players[playerIndex];
                    if (player.team) {
                        // チームが設定されている場合は、そのチームのスロットを探す
                        for (let slotNum = 1; slotNum <= 5; slotNum++) {
                            const slotElement = document.getElementById(`team${player.team}-player${slotNum}`);
                            if (slotElement) {
                                const playerNameElement = slotElement.querySelector('.player-name');
                                if (playerNameElement && playerNameElement.textContent === playerName) {
                                    updatePlayerSlot(slotElement, player);
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // サーバー側に更新された情報を保存（戦績データを含む）
                savePlayerToRoom(players[playerIndex]);
                
                updateTeamStats();
            }
        } else {
            // デフォルト値のままサーバーに保存
            const playerIndex = players.findIndex(p => p.name === playerName);
            if (playerIndex !== -1) {
                savePlayerToRoom(players[playerIndex]);
            }
        }
    })
    .catch(error => {
        // デフォルト値のままサーバーに保存
        const playerIndex = players.findIndex(p => p.name === playerName);
        if (playerIndex !== -1) {
            savePlayerToRoom(players[playerIndex]);
        }
    });
}

// サーバー側に参加状態を保存する関数
function savePlayerToRoom(player) {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    // サーバー側で期待される形式に変換
    const playerData = {
        level: player.level,
        rank: player.rank,
        stats: player.stats,
        team: player.team,
        lane: player.lane,
        slot_position: player.slot_position || null,
        icon_id: player.icon_id || 0,
        icon_url: player.icon_url || '',
        recent_matches: player.recentMatches || []
    };
    
    const formData = new FormData();
    formData.append('action', 'save_participant');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', roomId);
    formData.append('player_name', player.name); // エンコードせずに直接送信
    formData.append('player_data', JSON.stringify(playerData));
    
    return fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 保存成功
        } else {
            // 保存失敗
        }
        return data;
    })
    .catch(error => {
        return { success: false, error: error };
    });
}

// サーバー側から参加者を削除する関数
function removePlayerFromRoom(playerName) {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    const formData = new FormData();
    formData.append('action', 'remove_participant');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', roomId);
    formData.append('player_name', playerName); // デコードせずに直接送信
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // サーバーから最新の参加者データを取得して表示
            loadParticipantsFromServer();
        }
    })
    .catch(error => {
        // エラーを無視
    });
}

// プレイヤースロット更新
function updatePlayerSlot(slot, player) {
    // プレイヤー情報とランク情報をまとめるコンテナを作成
    const playerMainInfo = document.createElement('div');
    playerMainInfo.className = 'player-main-info';
    
    playerMainInfo.innerHTML = `
        <div class="player-info">
            <div class="player-icon">
                <img src="https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/${player.icon_id || 0}.png" 
                     alt="${player.name}" 
                     class="summoner-icon"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <i class="fas fa-user-circle" style="display:none;"></i>
            </div>
            <div class="player-details">
                <div class="player-name-row">
                    <div class="player-name">${player.name}</div>
                    <div class="player-level"><?php _e('レベル:', 'lol-team-splitter'); ?> ${player.level}</div>
                    <div class="player-rank">${player.rank}</div>
                </div>
                <div class="player-stats-row">
                    <div class="player-matches">${getRecentMatchesHtml(player.recentMatches || [])}</div>
                </div>
            </div>
        </div>
    `;
    
    const playerActions = slot.querySelector('.player-actions');
    
    // レーン選択と削除ボタンの表示制御
    const currentPlayerName = localStorage.getItem('currentPlayerName');
    const isHost = <?php echo $isHost ? 'true' : 'false'; ?>;
    
    let actionsHtml = '';
    
    // レーン選択
    actionsHtml += `
        <select class="form-select form-select-sm me-2 lane-select" onchange="changeLane(this, '${player.name}')">
            <option value=""><?php _e('レーン', 'lol-team-splitter'); ?></option>
            <option value="top" ${player.lane === 'top' ? 'selected' : ''}><?php _e('トップ', 'lol-team-splitter'); ?></option>
            <option value="jungle" ${player.lane === 'jungle' ? 'selected' : ''}><?php _e('ジャングル', 'lol-team-splitter'); ?></option>
            <option value="mid" ${player.lane === 'mid' ? 'selected' : ''}><?php _e('ミッド', 'lol-team-splitter'); ?></option>
            <option value="bot" ${player.lane === 'bot' ? 'selected' : ''}><?php _e('ボット', 'lol-team-splitter'); ?></option>
            <option value="support" ${player.lane === 'support' ? 'selected' : ''}><?php _e('サポート', 'lol-team-splitter'); ?></option>
        </select>
    `;
    
    // 削除ボタン
    if (isHost) {
        // ホストは全員を削除可能
        actionsHtml += `
            <button class="btn btn-outline-danger btn-sm" onclick="removePlayer(this)">
                <i class="fas fa-times me-1"></i><?php _e('削除', 'lol-team-splitter'); ?>
            </button>
        `;
    } else if (currentPlayerName && player.name === currentPlayerName) {
        // 非ホストは自分だけ削除可能
        actionsHtml += `
            <button class="btn btn-outline-danger btn-sm" onclick="removePlayer(this)">
                <i class="fas fa-times me-1"></i><?php _e('削除', 'lol-team-splitter'); ?>
            </button>
        `;
    } else {
        // 他人は削除不可
        actionsHtml += `
            <span class="text-muted">
                <i class="fas fa-lock me-1"></i><?php _e('削除不可', 'lol-team-splitter'); ?>
            </span>
        `;
    }
    
    playerActions.innerHTML = actionsHtml;
    
    // スロットの内容を更新
    slot.innerHTML = '';
    slot.appendChild(playerMainInfo);
    slot.appendChild(playerActions);
}

// サーバーからレーン情報を取得して復元
function loadPlayerLanes() {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    fetch(`${ajax_object.ajax_url}?action=get_room_info&room_id=${roomId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const roomData = data.data;
            const participants = roomData.participants || [];
            
            // 各プレイヤーのレーン情報を復元
            Object.values(participants).forEach(participant => {
                if (participant.lane) {
                    // プレイヤーリストを更新
                    const playerIndex = players.findIndex(p => p.name === participant.name);
                    if (playerIndex !== -1) {
                        players[playerIndex].lane = participant.lane;
                    }
                    
                    // スロットのレーン選択を更新
                    const slot = findPlayerSlot(participant.name);
                    if (slot) {
                        const laneSelect = slot.querySelector('.lane-select');
                        if (laneSelect) {
                            laneSelect.value = participant.lane;
                        }
                    }
                }
            });
        }
    })
    .catch(error => {
        // エラーを無視
    });
}

// レーン情報をサーバーに保存
function savePlayerLaneToRoom(playerName, lane) {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    const formData = new FormData();
    formData.append('action', 'save_player_lane');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', roomId);
    formData.append('player_name', playerName);
    formData.append('lane', lane);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // サーバーから最新の参加者データを取得して表示
            loadParticipantsFromServer();
        }
    })
    .catch(error => {
        // エラーを無視
    });
}

// チーム情報をサーバーに保存
function savePlayerTeamToRoom(playerName, teamNumber) {
    const roomId = '<?php echo esc_js($roomId); ?>';
    
    const formData = new FormData();
    formData.append('action', 'save_player_team');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', roomId);
    formData.append('player_name', playerName);
    formData.append('team', teamNumber || '');
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 参加者リストを手動で更新（サーバー側のデータで上書きしない）
            showNotification('チームに参加しました！', 'success');
        } else {
            showNotification('チーム参加に失敗しました: ' + (data.data.message || '不明なエラー'), 'error');
        }
    })
    .catch(error => {
        showNotification('通信エラーが発生しました', 'error');
    });
}

// チーム内でレーンが既に使用されているかチェック
function isLaneAlreadyTaken(teamNumber, lane, currentPlayerName) {
    for (let slot = 1; slot <= 5; slot++) {
        const slotElement = document.getElementById(`team${teamNumber}-player${slot}`);
        if (slotElement) {
            const laneSelect = slotElement.querySelector('.lane-select');
            const playerNameElement = slotElement.querySelector('.player-name');
            
            if (laneSelect && playerNameElement && 
                laneSelect.value === lane && 
                playerNameElement.textContent !== currentPlayerName &&
                playerNameElement.textContent !== '<?php _e('空き', 'lol-team-splitter'); ?>') {
                return true;
            }
        }
    }
    return false;
}

// チーム内のレーン選択状態を更新（使用済みレーンを非表示）
function updateLaneSelectStates(teamNumber) {
    const usedLanes = new Set();
    
    // 現在使用されているレーンを収集
    for (let slot = 1; slot <= 5; slot++) {
        const slotElement = document.getElementById(`team${teamNumber}-player${slot}`);
        if (slotElement) {
            const laneSelect = slotElement.querySelector('.lane-select');
            const playerNameElement = slotElement.querySelector('.player-name');
            
            if (laneSelect && playerNameElement && 
                playerNameElement.textContent !== '<?php _e('空き', 'lol-team-splitter'); ?>' &&
                laneSelect.value) {
                usedLanes.add(laneSelect.value);
            }
        }
    }
    
    // 各スロットのレーン選択を更新
    for (let slot = 1; slot <= 5; slot++) {
        const slotElement = document.getElementById(`team${teamNumber}-player${slot}`);
        if (slotElement) {
            const laneSelect = slotElement.querySelector('.lane-select');
            const playerNameElement = slotElement.querySelector('.player-name');
            
            if (laneSelect && playerNameElement) {
                const currentValue = laneSelect.value;
                const currentPlayerName = playerNameElement.textContent;
                
                // 各オプションを更新
                Array.from(laneSelect.options).forEach(option => {
                    if (option.value === '') return; // 空のオプションはスキップ
                    
                    const isUsedByOthers = usedLanes.has(option.value) && 
                                         option.value !== currentValue;
                    
                    if (isUsedByOthers) {
                        option.style.display = 'none'; // 非表示にする
                    } else {
                        option.style.display = 'block'; // 表示する
                    }
                });
            }
        }
    }
}

// レーン変更
function changeLane(selectElement, playerName) {
    const selectedLane = selectElement.value;
    
    // プレイヤーがどのチームにいるかを取得
    const slot = selectElement.closest('.player-slot');
    const teamNumber = slot.id.split('-')[0].replace('team', '');
    
    // レーンが選択された場合、チーム内での重複チェック
    if (selectedLane) {
        if (isLaneAlreadyTaken(teamNumber, selectedLane, playerName)) {
            // 重複している場合は選択を元に戻す
            selectElement.value = '';
            showNotification(`${selectedLane}は既に他のプレイヤーが選択しています！`, 'error');
            return;
        }
    }
    
    // プレイヤーリストを更新
    const playerIndex = players.findIndex(p => p.name === playerName);
    if (playerIndex !== -1) {
        players[playerIndex].lane = selectedLane;
    }
    
    // サーバーに保存
    savePlayerLaneToRoom(playerName, selectedLane);
    
    // レーン選択の状態を更新
    updateLaneSelectStates(teamNumber);
    
    // レーン名を日本語に変換
    const laneNames = {
        'top': 'トップ',
        'jungle': 'ジャングル',
        'mid': 'ミッド',
        'bot': 'ボット',
        'support': 'サポート'
    };
    
    // 通知表示
    if (selectedLane) {
        const laneName = laneNames[selectedLane] || selectedLane;
        showNotification(`${playerName}のレーンを${laneName}に設定しました`, 'success');
    }
}

// プレイヤー削除
function removePlayer(button) {
    const slot = button.closest('.player-slot');
    const playerNameElement = slot.querySelector('.player-name');
    const playerName = playerNameElement.textContent;
    
    // プレイヤー名をそのまま使用
    
    // 削除権限チェック
    const currentPlayerName = localStorage.getItem('currentPlayerName');
    const isHost = <?php echo $isHost ? 'true' : 'false'; ?>;
    const hostName = '<?php echo esc_js($host_name); ?>';
    
    // ホストでない場合、自分のプレイヤーのみ削除可能
    if (!isHost) {
        if (playerName !== currentPlayerName) {
            showNotification('他のプレイヤーを削除する権限がありません！', 'error');
            return;
        }
        // ホストを削除しようとした場合も拒否
        if (playerName === hostName) {
            showNotification('ホストを削除することはできません！', 'error');
            return;
        }
    }
    
    // プレイヤーリストから削除
    const playerIndex = players.findIndex(p => p.name === playerName);
    if (playerIndex !== -1) {
        players.splice(playerIndex, 1);
    }
    
    // サーバー側からも削除（直接名前を送信）
    removePlayerFromRoom(playerName);
    
    // スロットを空き状態に戻す
    resetSlot(slot);
    
    // レーン選択状態を更新
    const teamNumber = slot.id.split('-')[0].replace('team', '');
    updateLaneSelectStates(teamNumber);
    
    updateTeamStats();
    updateAverageRanks();
    
    // 即座に他の参加者にも反映させるため、短時間で更新
    setTimeout(() => {
        updateParticipants();
    }, 500);
}

// チーム統計更新
function updateTeamStats() {
    // 実装予定
}

// 平均ランクを更新
function updateAverageRanks() {
    const team1Players = players.filter(player => player.team === 1);
    const team2Players = players.filter(player => player.team === 2);
    
    const team1AvgRank = calculateAverageRank(team1Players);
    const team2AvgRank = calculateAverageRank(team2Players);
    
    
    // チーム1の平均ランクを更新
    const team1AvgElement = document.querySelector('#team1AvgRank');
    if (team1AvgElement) {
        team1AvgElement.textContent = team1AvgRank;
    }
    
    // チーム2の平均ランクを更新
    const team2AvgElement = document.querySelector('#team2AvgRank');
    if (team2AvgElement) {
        team2AvgElement.textContent = team2AvgRank;
    }
}

// チーム名を更新
function updateTeamNames(blueSide, redSide) {
    // チーム1のタイトルを更新
    const team1Title = document.querySelector('#team1 .team-title');
    if (team1Title) {
        // アイコンを保持してテキストのみ更新
        team1Title.innerHTML = `<i class="fas fa-shield-alt me-2"></i>${blueSide}`;
    }
    
    // チーム2のタイトルを更新
    const team2Title = document.querySelector('#team2 .team-title');
    if (team2Title) {
        // アイコンを保持してテキストのみ更新
        team2Title.innerHTML = `<i class="fas fa-shield-alt me-2"></i>${redSide}`;
    }
}

// チーム分け実行
function splitTeams() {
    // バランス重視がチェックされているか確認
    const balanceModeRadio = document.getElementById('balanceMode');
    const customModeRadio = document.getElementById('customMode');
    const isBalanceMode = balanceModeRadio && balanceModeRadio.checked;
    const isCustomMode = customModeRadio && customModeRadio.checked;
    
    // カスタムモードの場合はKDA入力チェック
    if (isCustomMode) {
        if (!validateCustomKdaInputs()) {
            return;
        }
    } else {
        // バランスモードの場合は通常の前提条件チェック
        if (!validateTeamSplitConditions()) {
            return;
        }
    }
    
    // ボタンを無効化
    const splitBtn = document.getElementById('splitTeamsBtn');
    splitBtn.disabled = true;
    splitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>チーム分け中...';
    
    // チーム分けを実行
    setTimeout(() => {
        if (isBalanceMode) {
            performAdvancedBalancedTeamSplit();
        } else if (isCustomMode) {
            performCustomTeamSplit();
        } else {
            performBalancedTeamSplit();
        }
        
        // ボタンを元に戻す
        splitBtn.disabled = false;
        splitBtn.innerHTML = '<i class="fas fa-magic me-2"></i><?php _e('チーム分け実行', 'lol-team-splitter'); ?>';
    }, 1000);
}

// チーム分けの前提条件をチェック
function validateTeamSplitConditions() {
    const currentParticipants = Object.values(players).filter(player => player.team !== null);
    
    // 1. 人数が10人参加していること
    if (currentParticipants.length !== 10) {
        showNotification(`参加者が10人ではありません。現在の参加者数: ${currentParticipants.length}人`, 'error');
        return false;
    }
    
    // 2. 全員がレーンを設定していること
    const playersWithoutLane = currentParticipants.filter(player => !player.lane || player.lane === '');
    if (playersWithoutLane.length > 0) {
        const playerNames = playersWithoutLane.map(p => p.name).join(', ');
        showNotification(`レーンが設定されていない参加者がいます: ${playerNames}`, 'error');
        return false;
    }
    
    // 3. チーム1とチーム2それぞれでレーンの重複チェック
    const team1Players = currentParticipants.filter(player => player.team === 1);
    const team2Players = currentParticipants.filter(player => player.team === 2);
    
    // チーム1のレーン重複チェック
    const team1LaneCount = {
        'top': 0,
        'jungle': 0,
        'mid': 0,
        'bot': 0,
        'support': 0
    };
    
    team1Players.forEach(player => {
        if (player.lane && team1LaneCount[player.lane] !== undefined) {
            team1LaneCount[player.lane]++;
        }
    });
    
    const team1DuplicateLanes = Object.keys(team1LaneCount).filter(lane => team1LaneCount[lane] > 1);
    if (team1DuplicateLanes.length > 0) {
        const laneNames = {
            'top': 'トップ',
            'jungle': 'ジャングル',
            'mid': 'ミッド',
            'bot': 'ボット',
            'support': 'サポート'
        };
        const duplicateLaneNames = team1DuplicateLanes.map(lane => laneNames[lane]).join(', ');
        showNotification(`チーム1でレーンの重複があります: ${duplicateLaneNames}`, 'error');
        return false;
    }
    
    // チーム2のレーン重複チェック
    const team2LaneCount = {
        'top': 0,
        'jungle': 0,
        'mid': 0,
        'bot': 0,
        'support': 0
    };
    
    team2Players.forEach(player => {
        if (player.lane && team2LaneCount[player.lane] !== undefined) {
            team2LaneCount[player.lane]++;
        }
    });
    
    const team2DuplicateLanes = Object.keys(team2LaneCount).filter(lane => team2LaneCount[lane] > 1);
    if (team2DuplicateLanes.length > 0) {
        const laneNames = {
            'top': 'トップ',
            'jungle': 'ジャングル',
            'mid': 'ミッド',
            'bot': 'ボット',
            'support': 'サポート'
        };
        const duplicateLaneNames = team2DuplicateLanes.map(lane => laneNames[lane]).join(', ');
        showNotification(`チーム2でレーンの重複があります: ${duplicateLaneNames}`, 'error');
        return false;
    }
    
    // 4. 各チームで各レーンに1人ずついること
    const team1MissingLanes = Object.keys(team1LaneCount).filter(lane => team1LaneCount[lane] === 0);
    const team2MissingLanes = Object.keys(team2LaneCount).filter(lane => team2LaneCount[lane] === 0);
    
    if (team1MissingLanes.length > 0 || team2MissingLanes.length > 0) {
        const laneNames = {
            'top': 'トップ',
            'jungle': 'ジャングル',
            'mid': 'ミッド',
            'bot': 'ボット',
            'support': 'サポート'
        };
        
        let missingMessage = '';
        if (team1MissingLanes.length > 0) {
            const missingLaneNames = team1MissingLanes.map(lane => laneNames[lane]).join(', ');
            missingMessage += `チーム1で設定されていないレーン: ${missingLaneNames}`;
        }
        if (team2MissingLanes.length > 0) {
            const missingLaneNames = team2MissingLanes.map(lane => laneNames[lane]).join(', ');
            if (missingMessage) missingMessage += '\n';
            missingMessage += `チーム2で設定されていないレーン: ${missingLaneNames}`;
        }
        
        showNotification(missingMessage, 'error');
        return false;
    }
    
    return true;
}

// 精密バランシングによるチーム分け実行
function performAdvancedBalancedTeamSplit() {
    const currentParticipants = Object.values(players).filter(player => player.team !== null);
    
    // 精密バランシングアルゴリズムを使用
    const balancedTeams = advancedBalanceTeams(currentParticipants);
    
    // チーム分け結果を表示・適用
    applyTeamSplitResult(balancedTeams.team1, balancedTeams.team2);
}

// チーム分け結果を適用する関数
function applyTeamSplitResult(team1, team2) {
    // チーム1のスコア計算
    const team1TotalScore = team1.reduce((sum, player) => {
        return sum + (player.score ? player.score.overall : getOverallScore(player).overall);
    }, 0);
    
    // チーム2のスコア計算
    const team2TotalScore = team2.reduce((sum, player) => {
        return sum + (player.score ? player.score.overall : getOverallScore(player).overall);
    }, 0);
    
    // スコアの低いチームをブルーサイドに
    let blueTeam, redTeam;
    if (team1TotalScore < team2TotalScore) {
        // チーム1のスコアが低い場合
        blueTeam = { team: team1, name: 'チーム1', side: 'ブルーサイド' };
        redTeam = { team: team2, name: 'チーム2', side: 'レッドサイド' };
    } else {
        // チーム2のスコアが低い場合
        blueTeam = { team: team2, name: 'チーム2', side: 'ブルーサイド' };
        redTeam = { team: team1, name: 'チーム1', side: 'レッドサイド' };
    }
    
    // チーム分け結果を表示
    showTeamSplitResult(blueTeam.team, redTeam.team, blueTeam.side, redTeam.side);
    
    // 自動でチームに参加させる
    assignPlayersToTeams(blueTeam.team, redTeam.team, blueTeam.side, redTeam.side);
}

// カスタムKDA入力値を取得する関数
function getCustomKdaInputs() {
    const kdaInputs = {};
    
    // 参加者を取得（players配列から）
    const participants = players.filter(p => p.name && p.name !== '');
    
    // 各プレイヤーのKDA入力フィールドから値を取得
    participants.forEach((player, index) => {
        const killsInput = document.getElementById(`kda_kill_${index}`);
        const deathsInput = document.getElementById(`kda_death_${index}`);
        const assistsInput = document.getElementById(`kda_assist_${index}`);
        
        if (killsInput && deathsInput && assistsInput) {
            const kills = parseFloat(killsInput.value) || 0;
            const deaths = parseFloat(deathsInput.value) || 0;
            const assists = parseFloat(assistsInput.value) || 0;
            
            kdaInputs[player.name] = {
                kills: kills,
                deaths: deaths,
                assists: assists,
                kda: deaths > 0 ? (kills + assists) / deaths : kills + assists
            };
        }
    });
    
    return kdaInputs;
}

// カスタムKDA入力の検証
function validateCustomKdaInputs() {
    const currentParticipants = Object.values(players).filter(player => player.team !== null);
    
    if (currentParticipants.length !== 10) {
        showNotification(`参加者が10人ではありません。現在の参加者数: ${currentParticipants.length}人`, 'error');
        return false;
    }
    
    // 参加者を取得（players配列から）
    const participants = players.filter(p => p.name && p.name !== '');
    
    if (participants.length !== 10) {
        showNotification(`プレイヤーリストが10人ではありません。現在のプレイヤー数: ${participants.length}人`, 'error');
        return false;
    }
    
    // 各プレイヤーのKDA入力チェック
    const missingInputs = [];
    
    participants.forEach((player, index) => {
        const killsInput = document.getElementById(`kda_kill_${index}`);
        const deathsInput = document.getElementById(`kda_death_${index}`);
        const assistsInput = document.getElementById(`kda_assist_${index}`);
        
        if (!killsInput || !deathsInput || !assistsInput) {
            missingInputs.push(player.name);
            return;
        }
        
        const kills = parseFloat(killsInput.value);
        const deaths = parseFloat(deathsInput.value);
        const assists = parseFloat(assistsInput.value);
        
        if (isNaN(kills) || isNaN(deaths) || isNaN(assists)) {
            missingInputs.push(player.name);
        }
    });
    
    if (missingInputs.length > 0) {
        showNotification(`KDA入力が不完全です: ${missingInputs.join(', ')}`, 'error');
        return false;
    }
    
    return true;
}

// カスタム試合後のチーム分けを実行
function performCustomTeamSplit() {
    const currentParticipants = Object.values(players).filter(player => player.team !== null);
    
    // カスタムKDA入力値を取得
    const customKdas = getCustomKdaInputs();
    
    // 各参加者のスコアを計算（レベル + ランク + カスタムKDA）
    const participantsWithScore = currentParticipants.map(player => {
        // ランクスコア
        const rankScore = getRankValue(player.rank);
        
        // レベルスコア（レベルが高いほど強い）
        const levelScore = parseInt(player.level) || 1;
        
        // カスタムKDAスコア
        const customKda = customKdas[player.name];
        let kdaScore = 0;
        if (customKda) {
            kdaScore = customKda.kda;
        }
        
        // 総合スコア（数値が高いほど強い）
        const totalScore = (rankScore / 1000) + (levelScore / 100) + kdaScore;
        
        return {
            ...player,
            score: totalScore,
            rankScore: rankScore,
            levelScore: levelScore,
            kdaScore: kdaScore,
            customKda: customKda
        };
    });
    
    // スコア順でソート（高い順）
    participantsWithScore.sort((a, b) => b.score - a.score);
    
    // 最適なチーム分けを探索
    const bestBalance = findOptimalCustomTeamBalance(participantsWithScore);
    
    // チーム分け結果を適用
    applyTeamSplitResult(bestBalance.team1, bestBalance.team2);
}

// ロール制約をチェックする関数
function validateRoleConstraints(team) {
    const roles = team.map(player => player.lane);
    const uniqueRoles = [...new Set(roles)];
    
    // 5人で5つの異なるロールが必要
    return uniqueRoles.length === 5;
}

// カスタム試合後の最適なチームバランスを探索（ロール制約付き）
function findOptimalCustomTeamBalance(sortedPlayers) {
    const totalPlayers = sortedPlayers.length;
    const teamSize = Math.floor(totalPlayers / 2);
    
    let bestBalance = null;
    let minScoreDifference = Infinity;
    
    // 全ての可能な組み合わせを試行（ロール制約付き）
    function tryTeamCombination(team1, team2, remainingPlayers, index) {
        if (team1.length === teamSize && team2.length === teamSize) {
            // ロール制約をチェック
            if (!validateRoleConstraints(team1) || !validateRoleConstraints(team2)) {
                return;
            }
            
            // 両チームが満杯になったらスコア差を計算
            const team1Score = team1.reduce((sum, p) => sum + p.score, 0) / team1.length;
            const team2Score = team2.reduce((sum, p) => sum + p.score, 0) / team2.length;
            const scoreDifference = Math.abs(team1Score - team2Score);
            
            if (scoreDifference < minScoreDifference) {
                minScoreDifference = scoreDifference;
                bestBalance = {
                    team1: [...team1],
                    team2: [...team2],
                    team1Score: team1Score,
                    team2Score: team2Score,
                    scoreDifference: scoreDifference
                };
            }
            return;
        }
        
        if (index >= remainingPlayers.length) return;
        
        const currentPlayer = remainingPlayers[index];
        
        // チーム1に追加（ロール制約チェック）
        if (team1.length < teamSize) {
            const newTeam1 = [...team1, currentPlayer];
            if (team1.length === teamSize - 1) {
                // 最後のプレイヤーを追加する場合はロール制約をチェック
                if (validateRoleConstraints(newTeam1)) {
                    tryTeamCombination(newTeam1, team2, remainingPlayers, index + 1);
                }
            } else {
                tryTeamCombination(newTeam1, team2, remainingPlayers, index + 1);
            }
        }
        
        // チーム2に追加（ロール制約チェック）
        if (team2.length < teamSize) {
            const newTeam2 = [...team2, currentPlayer];
            if (team2.length === teamSize - 1) {
                // 最後のプレイヤーを追加する場合はロール制約をチェック
                if (validateRoleConstraints(newTeam2)) {
                    tryTeamCombination(team1, newTeam2, remainingPlayers, index + 1);
                }
            } else {
                tryTeamCombination(team1, newTeam2, remainingPlayers, index + 1);
            }
        }
    }
    
    // 探索開始
    tryTeamCombination([], [], sortedPlayers, 0);
    
    // 結果が見つからない場合は蛇行配置にフォールバック（ロール制約付き）
    if (!bestBalance) {
        const team1 = [];
        const team2 = [];
        
        // ロールごとにプレイヤーをグループ化
        const playersByRole = {};
        sortedPlayers.forEach(player => {
            const role = player.lane;
            if (!playersByRole[role]) {
                playersByRole[role] = [];
            }
            playersByRole[role].push(player);
        });
        
        // 各ロールから1人ずつ交互にチームに割り当て
        const roles = Object.keys(playersByRole);
        roles.forEach(role => {
            const rolePlayers = playersByRole[role];
            if (rolePlayers.length >= 2) {
                team1.push(rolePlayers[0]);
                team2.push(rolePlayers[1]);
            } else if (rolePlayers.length === 1) {
                // 1人しかいない場合はスコアの低いチームに追加
                const team1Score = team1.reduce((sum, p) => sum + p.score, 0) / (team1.length || 1);
                const team2Score = team2.reduce((sum, p) => sum + p.score, 0) / (team2.length || 1);
                
                if (team1Score <= team2Score) {
                    team1.push(rolePlayers[0]);
                } else {
                    team2.push(rolePlayers[0]);
                }
            }
        });
        
        const team1Score = team1.reduce((sum, p) => sum + p.score, 0) / team1.length;
        const team2Score = team2.reduce((sum, p) => sum + p.score, 0) / team2.length;
        
        bestBalance = {
            team1,
            team2,
            team1Score,
            team2Score,
            scoreDifference: Math.abs(team1Score - team2Score)
        };
    }
    
    return bestBalance;
}

// バランス重視のチーム分けを実行
function performBalancedTeamSplit() {
    const currentParticipants = Object.values(players).filter(player => player.team !== null);
    
    // ランクの重み付け（数値が小さいほど強い）
    const rankWeights = {
        'Iron': 1,
        'Bronze': 2,
        'Silver': 3,
        'Gold': 4,
        'Platinum': 5,
        'Diamond': 6,
        'Master': 7,
        'Grandmaster': 8,
        'Challenger': 9,
        'Unknown': 5 // 不明なランクは中間値
    };
    
    // ティアの重み付け（数値が小さいほど強い）
    const tierWeights = {
        'I': 0.1,
        'II': 0.2,
        'III': 0.3,
        'IV': 0.4,
        'V': 0.5
    };
    
    // 各参加者のスコアを計算（ランク + ティア + レベル + KDA）
    const participantsWithScore = currentParticipants.map(player => {
        // ランクスコア
        const rankScore = rankWeights[player.rank] || 5;
        
        // ティアスコア（同じランク内での強さ）
        let tierScore = 0;
        if (player.rank && player.rank !== 'Unknown') {
            const rankParts = player.rank.split(' ');
            if (rankParts.length >= 2) {
                const tier = rankParts[1];
                // Master以上の場合はLPを考慮
                if (['MASTER', 'GRANDMASTER', 'CHALLENGER'].includes(rankParts[0])) {
                    const lp = parseInt(tier.replace('LP', '')) || 0;
                    // LPが高いほど強い（スコアを低くする）
                    tierScore = Math.max(0, 0.5 - (lp / 2000)); // LP2000で0になるように調整
                } else {
                    tierScore = tierWeights[tier] || 0.5;
                }
            }
        }
        
        // レベルスコア
        const levelScore = parseInt(player.level) || 1;
        
        // KDAスコア（直近5試合の平均KDA）
        let kdaScore = 0;
        if (player.recentMatches && player.recentMatches.length > 0) {
            const totalKills = player.recentMatches.reduce((sum, match) => sum + match.kills, 0);
            const totalDeaths = player.recentMatches.reduce((sum, match) => sum + match.deaths, 0);
            const totalAssists = player.recentMatches.reduce((sum, match) => sum + match.assists, 0);
            
            if (totalDeaths > 0) {
                kdaScore = (totalKills + totalAssists) / totalDeaths;
            } else {
                kdaScore = (totalKills + totalAssists) / 1; // デス0の場合は1で割る
            }
        }
        
        // 総合スコア（数値が小さいほど強い）
        const totalScore = rankScore + tierScore + (levelScore / 100) - (kdaScore / 10);
        
        return {
            ...player,
            score: totalScore,
            rankScore: rankScore,
            tierScore: tierScore,
            levelScore: levelScore,
            kdaScore: kdaScore
        };
    });
    
    // スコア順でソート（強い順）
    participantsWithScore.sort((a, b) => a.score - b.score);
    
    // バランス重視のチーム分けアルゴリズム
    const team1 = [];
    const team2 = [];
    
    // 最適なチーム分けを探索
    let bestTeam1 = [];
    let bestTeam2 = [];
    let minScoreDifference = Infinity;
    
    // 全プレイヤーを2つのチームに分ける組み合わせを探索
    function findBestSplit(players, team1, team2, index) {
        if (index === players.length) {
            // レーン制約をチェック
            if (validateLaneConstraints(team1) && validateLaneConstraints(team2)) {
                const team1Score = team1.reduce((sum, p) => sum + p.score, 0);
                const team2Score = team2.reduce((sum, p) => sum + p.score, 0);
                const scoreDifference = Math.abs(team1Score - team2Score);
                
                if (scoreDifference < minScoreDifference) {
                    minScoreDifference = scoreDifference;
                    bestTeam1 = [...team1];
                    bestTeam2 = [...team2];
                }
            }
            return;
        }
        
        const player = players[index];
        
        // チーム1に追加
        if (team1.length < 5) {
            team1.push(player);
            findBestSplit(players, team1, team2, index + 1);
            team1.pop();
        }
        
        // チーム2に追加
        if (team2.length < 5) {
            team2.push(player);
            findBestSplit(players, team1, team2, index + 1);
            team2.pop();
        }
    }
    
    // レーン制約をチェックする関数
    function validateLaneConstraints(team) {
        const laneCount = {
            'top': 0,
            'jungle': 0,
            'mid': 0,
            'bot': 0,
            'support': 0
        };
        
        team.forEach(player => {
            if (player.lane && laneCount[player.lane] !== undefined) {
                laneCount[player.lane]++;
            }
        });
        
        // 各レーンに最大1人まで
        return Object.values(laneCount).every(count => count <= 1);
    }
    
    // 最適解を探索（10人なので全探索は重いので、貪欲法を使用）
    const sortedPlayers = [...participantsWithScore];
    const team1Score = [];
    const team2Score = [];
    
    // よりバランスの良い分け方を探索（ランク分散も考慮）
    for (let i = 0; i < sortedPlayers.length; i++) {
        const player = sortedPlayers[i];
        
        // 各チームの現在のスコアを計算
        const currentTeam1Score = team1Score.reduce((sum, p) => sum + p.score, 0);
        const currentTeam2Score = team2Score.reduce((sum, p) => sum + p.score, 0);
        
        // レーン制約を考慮してチームを選択
        let canGoToTeam1 = team1Score.length < 5 && 
            (!player.lane || team1Score.filter(p => p.lane === player.lane).length === 0);
        let canGoToTeam2 = team2Score.length < 5 && 
            (!player.lane || team2Score.filter(p => p.lane === player.lane).length === 0);
        
        if (canGoToTeam1 && canGoToTeam2) {
            // 両方に追加可能な場合、よりバランスの良い方を選択
            const team1Variance = calculateTeamVariance([...team1Score, player]);
            const team2Variance = calculateTeamVariance([...team2Score, player]);
            const team1ScoreDiff = Math.abs(currentTeam1Score + player.score - currentTeam2Score);
            const team2ScoreDiff = Math.abs(currentTeam2Score + player.score - currentTeam1Score);
            
            // スコア差とランク分散の両方を考慮
            const team1TotalScore = team1ScoreDiff + team1Variance * 0.3; // 分散に重み付け
            const team2TotalScore = team2ScoreDiff + team2Variance * 0.3;
            
            if (team1TotalScore < team2TotalScore) {
                team1Score.push(player);
            } else {
                team2Score.push(player);
            }
        } else if (canGoToTeam1) {
            team1Score.push(player);
        } else if (canGoToTeam2) {
            team2Score.push(player);
        } else {
            // どちらにも追加できない場合、レーン制約を無視してバランスの良い方に追加
            if (team1Score.length < 5 && team2Score.length < 5) {
                const team1Variance = calculateTeamVariance([...team1Score, player]);
                const team2Variance = calculateTeamVariance([...team2Score, player]);
                const team1ScoreDiff = Math.abs(currentTeam1Score + player.score - currentTeam2Score);
                const team2ScoreDiff = Math.abs(currentTeam2Score + player.score - currentTeam1Score);
                
                const team1TotalScore = team1ScoreDiff + team1Variance * 0.3;
                const team2TotalScore = team2ScoreDiff + team2Variance * 0.3;
                
                if (team1TotalScore < team2TotalScore) {
                    team1Score.push(player);
                } else {
                    team2Score.push(player);
                }
            } else if (team1Score.length < 5) {
                team1Score.push(player);
            } else {
                team2Score.push(player);
            }
        }
    }
    
    // チーム内のランク分散を計算する関数
    function calculateTeamVariance(team) {
        if (team.length === 0) return 0;
        
        const scores = team.map(p => p.score);
        const mean = scores.reduce((sum, score) => sum + score, 0) / scores.length;
        const variance = scores.reduce((sum, score) => sum + Math.pow(score - mean, 2), 0) / scores.length;
        
        return variance;
    }
    
    // 最終的なチーム分け
    team1.push(...team1Score);
    team2.push(...team2Score);
    
    // スコアを計算してブルーサイド/レッドサイドを決定
    const team1TotalScore = team1.reduce((sum, p) => sum + p.score, 0);
    const team2TotalScore = team2.reduce((sum, p) => sum + p.score, 0);
    
    let blueTeam, redTeam;
    if (team1TotalScore < team2TotalScore) {
        // チーム1のスコアが低い場合
        blueTeam = { team: team1, name: 'チーム1', side: 'ブルーサイド' };
        redTeam = { team: team2, name: 'チーム2', side: 'レッドサイド' };
    } else {
        // チーム2のスコアが低い場合
        blueTeam = { team: team2, name: 'チーム2', side: 'ブルーサイド' };
        redTeam = { team: team1, name: 'チーム1', side: 'レッドサイド' };
    }
    
    // チーム分け結果を表示
    showTeamSplitResult(blueTeam.team, redTeam.team, blueTeam.side, redTeam.side);
    
    // 自動でチームに参加させる
    assignPlayersToTeams(blueTeam.team, redTeam.team, blueTeam.side, redTeam.side);
}

// 平均ランクを計算する関数
function calculateAverageRank(team) {
    if (team.length === 0) return '-';
    
    // ランクの重み付け（数値が小さいほど強い）
    const rankWeights = {
        'Iron': 1,
        'Bronze': 2,
        'Silver': 3,
        'Gold': 4,
        'Platinum': 5,
        'Diamond': 6,
        'Master': 7,
        'Grandmaster': 8,
        'Challenger': 9,
        'Unknown': 5
    };
    
    // ティアの重み付け
    const tierWeights = {
        'I': 0.1,
        'II': 0.2,
        'III': 0.3,
        'IV': 0.4,
        'V': 0.5
    };
    
    let totalScore = 0;
    let validPlayers = 0;
    
    team.forEach(player => {
        if (player.rank && player.rank !== 'Unknown') {
            const rankParts = player.rank.split(' ');
            const rankName = rankParts[0].toUpperCase();
            const tier = rankParts[1] || '';
            
            // ランク名を正規化
            const normalizedRankName = rankName === 'MASTER' ? 'Master' :
                                     rankName === 'GRANDMASTER' ? 'Grandmaster' :
                                     rankName === 'CHALLENGER' ? 'Challenger' :
                                     rankName === 'DIAMOND' ? 'Diamond' :
                                     rankName === 'PLATINUM' ? 'Platinum' :
                                     rankName === 'GOLD' ? 'Gold' :
                                     rankName === 'SILVER' ? 'Silver' :
                                     rankName === 'BRONZE' ? 'Bronze' :
                                     rankName === 'IRON' ? 'Iron' : rankName;
            
            let rankScore = rankWeights[normalizedRankName] || 5;
            
            // ティアスコアを追加
            if (['Master', 'Grandmaster', 'Challenger'].includes(normalizedRankName)) {
                const lp = parseInt(tier.replace('LP', '')) || 0;
                rankScore += Math.max(0, 0.5 - (lp / 2000));
            } else if (tier) {
                rankScore += tierWeights[tier] || 0.5;
            }
            
            totalScore += rankScore;
            validPlayers++;
        }
    });
    
    if (validPlayers === 0) return '-';
    
    const averageScore = totalScore / validPlayers;
    
    // スコアからランク名を逆算
    const rankNames = ['Iron', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Master', 'Grandmaster', 'Challenger'];
    const rankIndex = Math.floor(averageScore) - 1;
    
    if (rankIndex >= 0 && rankIndex < rankNames.length) {
        const rankName = rankNames[rankIndex];
        const tierScore = averageScore - Math.floor(averageScore);
        
        if (['Master', 'Grandmaster', 'Challenger'].includes(rankName)) {
            const lp = Math.round(tierScore * 2000);
            return `${rankName} ${lp}LP`;
        } else {
            // ティアはI、II、III、IVの4つまで
            const tierNames = ['IV', 'III', 'II', 'I'];
            const tierIndex = Math.floor(tierScore * 4);
            const tier = tierNames[Math.min(tierIndex, 3)];
            return `${rankName} ${tier}`;
        }
    }
    
    return 'Unknown';
}

// チーム分け結果を表示
function showTeamSplitResult(blueTeam, redTeam, blueSide, redSide) {
    // スコア計算（精密バランシングと従来の両方に対応）
    const blueScore = blueTeam.reduce((sum, p) => {
        if (p.score && typeof p.score === 'number') {
            return sum + p.score; // 従来の形式
        } else if (p.score && p.score.overall) {
            return sum + p.score.overall; // 精密バランシング形式
        }
        return sum;
    }, 0);
    
    const redScore = redTeam.reduce((sum, p) => {
        if (p.score && typeof p.score === 'number') {
            return sum + p.score; // 従来の形式
        } else if (p.score && p.score.overall) {
            return sum + p.score.overall; // 精密バランシング形式
        }
        return sum;
    }, 0);
    
    // 平均ランクを計算
    const blueAvgRank = calculateAverageRank(blueTeam);
    const redAvgRank = calculateAverageRank(redTeam);
    
    // ブルーサイドの詳細情報（スコア詳細付き）
    const blueDetails = blueTeam.map(p => {
        let kdaText = 'KDA:なし';
        if (p.recentMatches && p.recentMatches.length > 0) {
            if (p.kdaScore !== undefined) {
                kdaText = `KDA:${p.kdaScore.toFixed(1)}`;
            } else if (p.score && p.score.details) {
                kdaText = `KDA:${p.score.details.kdaRaw.toFixed(1)}`;
            }
        }
        return `${p.name} (${p.rank} Lv.${p.level} ${kdaText})`;
    }).join('\n');
    
    // レッドサイドの詳細情報（スコア詳細付き）
    const redDetails = redTeam.map(p => {
        let kdaText = 'KDA:なし';
        if (p.recentMatches && p.recentMatches.length > 0) {
            if (p.kdaScore !== undefined) {
                kdaText = `KDA:${p.kdaScore.toFixed(1)}`;
            } else if (p.score && p.score.details) {
                kdaText = `KDA:${p.score.details.kdaRaw.toFixed(1)}`;
            }
        }
        return `${p.name} (${p.rank} Lv.${p.level} ${kdaText})`;
    }).join('\n');
    
    const message = 'チーム分け完了！';
    
    showNotification(message, 'success');
}

// 参加者をチームに自動参加させる
function assignPlayersToTeams(blueTeam, redTeam, blueSide, redSide) {
    
    // まずすべてのスロットをリセット
    for (let team = 1; team <= 2; team++) {
        for (let slot = 1; slot <= 5; slot++) {
            const slotElement = document.getElementById(`team${team}-player${slot}`);
            if (slotElement) {
                resetSlot(slotElement);
            }
        }
    }
    
    // プレイヤーリストをクリア
    players = [];
    
    // ブルーサイドの参加者を割り当て（チーム1のスロットに）
    blueTeam.forEach((player, index) => {
        const slotNumber = index + 1;
        if (slotNumber <= 5) {
            // プレイヤーオブジェクトを更新
            const updatedPlayer = {
                ...player,
                team: 1
            };
            
            // プレイヤーリストに追加
            players.push(updatedPlayer);
            
            // スロットを更新
            const slotElement = document.getElementById(`team1-player${slotNumber}`);
            if (slotElement) {
                updatePlayerSlot(slotElement, updatedPlayer);
            }
        }
    });
    
    // レッドサイドの参加者を割り当て（チーム2のスロットに）
    redTeam.forEach((player, index) => {
        const slotNumber = index + 1;
        if (slotNumber <= 5) {
            // プレイヤーオブジェクトを更新
            const updatedPlayer = {
                ...player,
                team: 2
            };
            
            // プレイヤーリストに追加
            players.push(updatedPlayer);
            
            // スロットを更新
            const slotElement = document.getElementById(`team2-player${slotNumber}`);
            if (slotElement) {
                updatePlayerSlot(slotElement, updatedPlayer);
            }
        }
    });
    
    
    // レーン選択状態を更新
    updateLaneSelectStates(1);
    updateLaneSelectStates(2);
    
    updateTeamStats();
    
    // 平均ランクを更新
    updateAverageRanks();
    
    // チーム名を更新
    updateTeamNames(blueSide, redSide);
    
    // サーバーに全プレイヤーを保存
    setTimeout(() => {
        players.forEach(player => {
            savePlayerToRoom(player);
        });
    }, 100);
}

// 直近5試合の戦績をHTMLで表示する関数
function getRecentMatchesHtml(matches) {
    if (!matches || matches.length === 0) {
        return '<span class="matches-info">戦績データなし</span>';
    }
    
    // 勝率を計算
    const wins = matches.filter(match => match.win).length;
    const totalMatches = matches.length;
    const winRate = totalMatches > 0 ? Math.round((wins / totalMatches) * 100) : 0;
    
    // 合計KDAを計算
    const totalKills = matches.reduce((sum, match) => sum + match.kills, 0);
    const totalDeaths = matches.reduce((sum, match) => sum + match.deaths, 0);
    const totalAssists = matches.reduce((sum, match) => sum + match.assists, 0);
    
    return `<span class="win-rate ${winRate >= 60 ? 'high' : winRate >= 40 ? 'medium' : 'low'}">直近5試合 勝率${winRate}% KDA${totalKills}/${totalDeaths}/${totalAssists}</span>`;
}

// 参加者を指定されたチームのスロットに参加させる
function joinTeamToSlot(playerName, teamNumber) {
    // 空いているスロットを探す
    for (let slot = 1; slot <= 5; slot++) {
        const slotElement = document.getElementById(`team${teamNumber}-player${slot}`);
        if (slotElement) {
            const playerNameElement = slotElement.querySelector('.player-name');
            if (playerNameElement && playerNameElement.textContent === '<?php _e('空き', 'lol-team-splitter'); ?>') {
                // 空きスロットに参加（既存の戦績データを使用）
                const player = players.find(p => p.name === playerName);
                if (player) {
                    player.team = teamNumber;
                    // 既存の戦績データを使用してスロットを更新
                    updatePlayerSlot(slotElement, player);
                    
                    // サーバーに保存
                    savePlayerTeamToRoom(playerName, teamNumber);
                }
                break;
            }
        }
    }
}

// チームリセット
function resetTeams() {
    
    // すべてのスロットをリセット
    for (let team = 1; team <= 2; team++) {
        for (let slot = 1; slot <= 5; slot++) {
            const slotElement = document.getElementById(`team${team}-player${slot}`);
            if (slotElement) {
                resetSlot(slotElement);
            }
        }
    }
    
    // プレイヤーリストをクリア
    players = [];
    
    // サーバーから全プレイヤーを削除
    const roomId = '<?php echo esc_js($roomId); ?>';
    const formData = new FormData();
    formData.append('action', 'reset_all_teams');
    formData.append('room_id', roomId);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // サーバーから参加者情報を再読み込み
            loadParticipantsFromServer();
        }
    })
    .catch(error => {
        // エラーを無視
    });
    
    // チーム名をデフォルトに戻す
    updateTeamNames('チーム1 ブルーサイド', 'チーム2 レッドサイド');
    
    updateTeamStats();
    updateLaneSelectStates(1);
    updateLaneSelectStates(2);
    updateAverageRanks();
    
    showNotification('チーム分けをリセットしました', 'info');
}

// ルームを閉じる
function closeRoom() {
    // ホスト権限チェック
    const isHost = <?php echo $isHost ? 'true' : 'false'; ?>;
    if (!isHost) {
        showNotification('ルームを閉じる権限がありません！', 'error');
        return;
    }
    
    const roomId = '<?php echo esc_js($roomId); ?>';
    const hostName = '<?php echo esc_js($host_name); ?>';
    
    const formData = new FormData();
    formData.append('action', 'close_room');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', roomId);
    formData.append('host_name', hostName);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('ルームが閉じられました', 'success');
            // localStorageからルーム情報を削除
            localStorage.removeItem('currentRoom');
            // ホームページにリダイレクト
            setTimeout(() => {
                window.location.href = '<?php echo home_url('/'); ?>';
            }, 1500);
        } else {
            showNotification('エラー: ' + data.data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('通信エラーが発生しました', 'error');
    });
}

// URLコピー機能
function copyJoinUrl() {
    const joinUrlInput = document.getElementById('joinUrl');
    joinUrlInput.select();
    joinUrlInput.setSelectionRange(0, 99999); // モバイル対応
    document.execCommand('copy');
    
    // コピー完了のフィードバック
    const copyBtn = document.querySelector('[onclick="copyJoinUrl()"]');
    const originalIcon = copyBtn.innerHTML;
    copyBtn.innerHTML = '<i class="fas fa-check"></i>';
    copyBtn.classList.remove('btn-outline-secondary');
    copyBtn.classList.add('btn-success');
    
    setTimeout(() => {
        copyBtn.innerHTML = originalIcon;
        copyBtn.classList.remove('btn-success');
        copyBtn.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>

<style>
.team-card {
    border: 2px solid;
    border-radius: 15px;
    padding: 20px;
    min-height: 600px;
}

.team-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #444;
}

.team-title {
    font-size: 1.5rem;
    font-weight: bold;
}

.player-slot {
    display: flex;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    flex-wrap: nowrap;
    justify-content: space-between;
}

.player-slot:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: #ffc107;
}

.player-main-info {
    display: flex;
    align-items: center;
    flex: 1;
    gap: 20px;
}

.player-name-row {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 4px;
}

.player-stats-row {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 4px;
}


.player-name {
    font-weight: bold;
    font-size: 0.9rem;
    color: #fff;
}

.player-rank {
    font-weight: bold;
    font-size: 0.8rem;
    color: #ffc107;
}

.player-level {
    font-size: 0.8rem;
    color: #ccc;
}

.player-matches {
    font-size: 0.8rem;
}

.matches-info {
    color: #ccc;
}

.win-rate {
    font-weight: bold;
    font-size: 0.8rem;
    padding: 2px 6px;
    border-radius: 4px;
}

.win-rate.high {
    background-color: rgba(40, 167, 69, 0.2);
    border: 1px solid #28a745;
    color: #28a745;
}

.win-rate.medium {
    background-color: rgba(255, 193, 7, 0.2);
    border: 1px solid #ffc107;
    color: #ffc107;
}

.win-rate.low {
    background-color: rgba(220, 53, 69, 0.2);
    border: 1px solid #dc3545;
    color: #dc3545;
}

.rank-stats {
    font-size: 0.8rem;
    color: #ccc;
}

.player-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.player-icon {
    font-size: 2rem;
    color: #ffc107;
    margin-right: 15px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.summoner-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid #ffc107;
    object-fit: cover;
}

.player-details {
    flex: 1;
}

.player-name {
    font-weight: bold;
    color: white;
    font-size: 1.1rem;
}

.player-level {
    color: #ffc107;
    font-size: 0.9rem;
}

.player-rank {
    flex: 1;
    text-align: center;
}

.rank-info {
    font-weight: bold;
    color: #ffc107;
    font-size: 1rem;
}

.rank-stats {
    color: #ccc;
    font-size: 0.8rem;
}

.player-actions {
    display: flex;
    gap: 5px;
    align-items: center;
    flex-wrap: nowrap;
    min-width: 200px;
}

.lane-select {
    min-width: 80px;
    max-width: 120px;
    background-color: #343a40 !important;
    border-color: #6c757d !important;
    color: white !important;
    font-size: 0.875rem;
}

.lane-select:focus {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
}

.lane-select option {
    background-color: #343a40;
    color: white;
}

.control-panel {
    border: 1px solid #444;
}

@media (max-width: 768px) {
    .player-slot {
        flex-direction: column;
        text-align: center;
    }
    
    .player-info {
        margin-bottom: 10px;
    }
    
    .player-rank {
        margin-bottom: 10px;
    }
    
    .player-actions {
        flex-direction: row;
        justify-content: center;
        gap: 10px;
        min-width: auto;
    }
    
    .lane-select {
        min-width: 70px;
        max-width: 100px;
    }
}

/* 通知システムのスタイル */
.notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
}

.notification {
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 15px 20px;
    margin-bottom: 10px;
    border-radius: 8px;
    border-left: 4px solid;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transform: translateX(100%);
    transition: transform 0.3s ease;
    position: relative;
    overflow: hidden;
}

.notification.show {
    transform: translateX(0);
}

.notification.success {
    border-left-color: #28a745;
}

.notification.warning {
    border-left-color: #ffc107;
}

.notification.error {
    border-left-color: #dc3545;
}

.notification.info {
    border-left-color: #17a2b8;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-icon {
    font-size: 1.2rem;
}

.notification-message {
    flex: 1;
    font-weight: 500;
}

.notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.notification-close:hover {
    opacity: 1;
}

.notification-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.3);
    transition: width 0.1s linear;
}

@media (max-width: 768px) {
    .notification-container {
        top: 10px;
        right: 10px;
        left: 10px;
        max-width: none;
    }
}
</style>

<script>
// 通知システム
function showNotification(message, type = 'info', duration = 4000) {
    // 通知コンテナを作成（まだ存在しない場合）
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    // 通知要素を作成
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // アイコンを設定
    const icons = {
        success: 'fas fa-check-circle',
        warning: 'fas fa-exclamation-triangle',
        error: 'fas fa-times-circle',
        info: 'fas fa-info-circle'
    };
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="notification-icon ${icons[type] || icons.info}"></i>
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="closeNotification(this)">×</button>
        </div>
        <div class="notification-progress"></div>
    `;
    
    // コンテナに追加
    container.appendChild(notification);
    
    // アニメーション開始
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // プログレスバーのアニメーション
    const progressBar = notification.querySelector('.notification-progress');
    progressBar.style.width = '100%';
    progressBar.style.transition = `width ${duration}ms linear`;
    
    // 自動削除
    setTimeout(() => {
        closeNotification(notification.querySelector('.notification-close'));
    }, duration);
}

function closeNotification(closeButton) {
    const notification = closeButton.closest('.notification');
    notification.classList.remove('show');
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

// リアルタイム更新機能
let eventSource = null;
let reconnectTimeout = null;

function startRealtimeUpdates() {
    const roomId = '<?php echo esc_js($roomId); ?>';
    const eventUrl = `${ajax_object.ajax_url}?action=room_events&room_id=${roomId}`;
    
    if (eventSource) {
        eventSource.close();
    }
    
    eventSource = new EventSource(eventUrl);
    
    eventSource.onopen = function(event) {
        if (reconnectTimeout) {
            clearTimeout(reconnectTimeout);
            reconnectTimeout = null;
        }
    };
    
    eventSource.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            
            if (data.type === 'connected') {
                // 接続確認
            } else if (data.type === 'room_update') {
                updateRoomDisplay(data.participants);
            } else if (data.type === 'timeout') {
                reconnectSSE();
            } else if (data.error) {
                reconnectSSE();
            }
        } catch (error) {
            // エラーを無視
        }
    };
    
    eventSource.onerror = function(event) {
        if (eventSource.readyState === EventSource.CLOSED) {
            reconnectSSE();
        }
    };
}

function reconnectSSE() {
    if (reconnectTimeout) {
        clearTimeout(reconnectTimeout);
    }
    
    reconnectTimeout = setTimeout(() => {
        startRealtimeUpdates();
    }, 3000);
}

function updateRoomDisplay(participants) {
    // プレイヤーリストを更新
    players = [];
    Object.values(participants).forEach(participant => {
        const player = {
            name: participant.name,
            level: participant.level || '?',
            rank: participant.rank || 'Unknown',
            stats: participant.stats || '-',
            lane: participant.lane || '',
            team: participant.team || null,
            icon_id: participant.icon_id || 0,
            icon_url: participant.icon_url || '',
            recentMatches: participant.recent_matches || [],
            isAI: false
        };
        players.push(player);
    });
    
    // チーム1とチーム2のスロットを更新（既存の参加者を保護）
    updateTeamSlots(1, participants, true);
    updateTeamSlots(2, participants, true);
}

function updateTeamSlots(teamNumber, participants, preserveExisting = false) {
    // チーム情報に基づいて参加者を分ける（スロット位置ごとにマップ）
    const teamPlayersMap = {};
    Object.values(participants).forEach(player => {
        if (player.team === teamNumber && player.slot_position) {
            teamPlayersMap[player.slot_position] = player;
        }
    });
    
    // 各スロットを更新
    for (let i = 1; i <= 5; i++) {
        const slotElement = document.getElementById(`team${teamNumber}-player${i}`);
        if (slotElement) {
            const player = teamPlayersMap[i]; // スロット位置に基づいて取得
            if (player) {
                // 既存の参加者を保護する場合、既に表示されている参加者は上書きしない
                if (preserveExisting) {
                    const existingPlayerName = slotElement.querySelector('.player-name');
                    if (existingPlayerName && existingPlayerName.textContent !== '<?php _e('空き', 'lol-team-splitter'); ?>') {
                        continue; // 既に表示されている参加者はスキップ
                    }
                }
                // プレイヤーがいる場合
                updatePlayerSlot(slotElement, player);
            } else if (!preserveExisting) {
                // 保護モードでない場合のみ空きスロットにリセット
                resetSlot(slotElement);
            }
        }
    }
}

function createPlayerSlot(player, teamNumber, slotIndex) {
    const slot = document.createElement('div');
    slot.className = 'player-slot';
    
    if (player) {
        const currentLane = player.lane || '未設定';
        const laneClass = getLaneClass(currentLane);
        
        slot.innerHTML = `
            <div class="player-info">
                <div class="player-name">${player.name}</div>
                <div class="player-level">レベル: ${player.level}</div>
                <div class="player-rank">${player.rank}</div>
                <div class="rank-stats">${player.stats}</div>
            </div>
            <div class="player-actions">
                <button class="btn btn-outline-danger btn-sm" onclick="removePlayer('${player.name}')">
                    <i class="fas fa-times"></i> 削除
                </button>
            </div>
        `;
    } else {
        slot.innerHTML = `
            <div class="player-info">
                <i class="fas fa-user-plus text-warning"></i>
                <div class="text-warning">空き</div>
                <div class="text-muted">-</div>
            </div>
            <div class="player-actions">
                <button class="btn btn-outline-warning btn-sm" onclick="joinTeam(${teamNumber}, ${slotIndex})">
                    <i class="fas fa-plus"></i> 参加
                </button>
            </div>
        `;
    }
    
    return slot;
}


// ページ読み込み時にリアルタイム更新を開始
document.addEventListener('DOMContentLoaded', function() {
    startRealtimeUpdates();
});

// ページを離れる時にSSE接続を閉じる
window.addEventListener('beforeunload', function() {
    if (eventSource) {
        eventSource.close();
    }
});

</script>

<?php 
// template_redirectアクション経由でアクセスされた場合はフッターを読み込まない
if (!isset($GLOBALS['template_redirect_called'])) {
    get_footer();
}
?>
