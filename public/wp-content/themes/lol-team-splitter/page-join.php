<?php
/**
 * Template Name: Join Room Page
 */

// template_redirectアクション経由でアクセスされた場合はヘッダーを読み込まない
if (!isset($GLOBALS['template_redirect_called'])) {
    get_header();
} 

// ルームIDを取得
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
?>

<div class="hero-section" style="background: linear-gradient(135deg, #0F1419 0%, #0A0E13 100%); min-height: 100vh;">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6 mx-auto">
                <div class="card bg-dark text-white">
                    <div class="card-header border-secondary">
                        <h3 class="card-title text-center mb-0">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            <?php _e('ルームに参加', 'lol-team-splitter'); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h5 class="text-warning"><?php _e('ルームID:', 'lol-team-splitter'); ?> <?php echo esc_html($roomId); ?></h5>
                            <p class="text-light">
                                <i class="fas fa-unlock me-1"></i>
                                <?php _e('URL経由での参加（パスワード不要）', 'lol-team-splitter'); ?>
                            </p>
                        </div>
                        
                        <form id="joinForm">
                            <div class="mb-3">
                                <label for="summonerName" class="form-label">Riot ID</label>
                                <input type="text" class="form-control bg-dark text-white custom-border" id="summonerName" 
                                       placeholder="<?php _e('例: サモナー名#JP1', 'lol-team-splitter'); ?>" required>
                                <div class="form-text text-light"><?php _e('Riot IDの形式で入力してください（例: プレイヤー名#タグ）', 'lol-team-splitter'); ?></div>
                            </div>
                            
                            <div class="alert alert-info d-none" id="loadingAlert">
                                <i class="fas fa-spinner fa-spin me-2"></i>
                                <?php _e('ルームに参加中...', 'lol-team-splitter'); ?>
                            </div>
                            <div class="alert alert-danger d-none" id="errorAlert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="errorMessage"></span>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-warning btn-lg" id="joinBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <?php _e('参加', 'lol-team-splitter'); ?>
                                </button>
                                <a href="<?php echo home_url('/'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-home me-2"></i>
                                    <?php _e('ホームに戻る', 'lol-team-splitter'); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('joinBtn').addEventListener('click', function() {
    const summonerName = document.getElementById('summonerName').value.trim();
    const loadingAlert = document.getElementById('loadingAlert');
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    
    if (!summonerName) {
        errorMessage.textContent = 'Riot IDを入力してください';
        errorAlert.classList.remove('d-none');
        return;
    }
    
    // ローディング表示
    loadingAlert.classList.remove('d-none');
    errorAlert.classList.add('d-none');
    
    // ルームに参加（URL経由なのでパスワードは空文字列）
    const formData = new FormData();
    formData.append('action', 'join_room_direct');
    formData.append('nonce', ajax_object.nonce);
    formData.append('room_id', '<?php echo esc_js($roomId); ?>');
    formData.append('summoner_name', summonerName);
    
    fetch(ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // サモナー情報を保存
            localStorage.setItem('currentPlayerName', summonerName);
            
            // ホストかどうかを判定
            const hostName = '<?php echo esc_js($host_name); ?>';
            const isHost = summonerName === hostName;
            
            // チーム分けページに遷移（ホストの場合はhost=trueパラメータを追加）
            setTimeout(() => {
                const url = isHost 
                    ? `${window.location.origin}/team-split/?room=<?php echo esc_js($roomId); ?>&host=true`
                    : `${window.location.origin}/team-split/?room=<?php echo esc_js($roomId); ?>`;
                window.location.href = url;
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
});
</script>

<?php 
// template_redirectアクション経由でアクセスされた場合はフッターを読み込まない
if (!isset($GLOBALS['template_redirect_called'])) {
    get_footer();
}
?>
