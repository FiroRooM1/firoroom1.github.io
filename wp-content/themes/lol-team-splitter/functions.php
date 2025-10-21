<?php
/**
 * LoL Team Splitter Theme Functions
 * Text Domain: lol-team-splitter
 */

// テーマのセットアップ
function lol_team_splitter_setup() {
    // 言語ファイルの読み込み
    load_theme_textdomain('lol-team-splitter', get_template_directory() . '/languages');
    
    // テーマサポート
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
}
add_action('after_setup_theme', 'lol_team_splitter_setup');

// スタイルとスクリプトの読み込み
function lol_team_splitter_scripts() {
    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    
    // テーマのスタイル
    wp_enqueue_style('lol-team-splitter-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array(), '5.3.0', true);
    
    // カスタムJS
    wp_enqueue_script('lol-team-splitter-js', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true);
    
    // AJAX用のnonceと翻訳文字列
    wp_localize_script('lol-team-splitter-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lol_team_splitter_nonce'),
        'translations' => array(
            'riot_id_required' => __('Riot IDを入力してください', 'lol-team-splitter'),
            'room_id_required' => __('ルームIDを入力してください', 'lol-team-splitter'),
            'room_id_format' => __('ルームIDは6桁の数字で入力してください', 'lol-team-splitter'),
            'room_already_exists' => __('既にルームを作成しています。既存のルームを閉じてから新しいルームを作成してください。', 'lol-team-splitter'),
            'room_created' => __('ルーム作成完了！ページを移動中...', 'lol-team-splitter'),
            'room_creation_failed' => __('ルーム作成に失敗しました', 'lol-team-splitter'),
            'join_failed' => __('ルーム参加に失敗しました', 'lol-team-splitter'),
            'communication_error' => __('通信エラーが発生しました', 'lol-team-splitter'),
            'return_to_room' => __('ルームに戻る', 'lol-team-splitter'),
            'slot_already_taken' => __('このスロットは既に埋まっています！', 'lol-team-splitter'),
            'player_info_not_found' => __('プレイヤー情報が見つかりません。ルームに参加し直してください。', 'lol-team-splitter'),
            'already_joined' => __('既に参加済みです！', 'lol-team-splitter'),
            'no_permission_to_delete' => __('他のプレイヤーを削除する権限がありません！', 'lol-team-splitter'),
            'confirm_close_room' => __('本当にルームを閉じますか？この操作は取り消せません。', 'lol-team-splitter'),
            'room_closed' => __('ルームが閉じられました。', 'lol-team-splitter'),
            'error' => __('エラー:', 'lol-team-splitter'),
            'communication_error_occurred' => __('通信エラーが発生しました。', 'lol-team-splitter')
        )
    ));
}
add_action('wp_enqueue_scripts', 'lol_team_splitter_scripts');

// カスタムメニューの登録
function lol_team_splitter_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'lol-team-splitter'),
    ));
}
add_action('init', 'lol_team_splitter_menus');

// フォールバックメニュー
function lol_team_splitter_fallback_menu() {
    echo '<ul class="navbar-nav ms-auto">';
    echo '<li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('/')) . '">ホーム</a></li>';
    echo '<li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#howToUseModal">使い方</a></li>';
    echo '</ul>';
}

// ウィジェットエリアの登録
function lol_team_splitter_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'lol-team-splitter'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'lol-team-splitter'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'lol_team_splitter_widgets_init');

// カスタム投稿タイプの登録（ルーム管理用）
function lol_team_splitter_custom_post_types() {
    register_post_type('lol_room', array(
        'labels' => array(
            'name' => 'LoLルーム',
            'singular_name' => 'LoLルーム',
            'add_new' => '新しいルーム',
            'add_new_item' => '新しいルームを追加',
            'edit_item' => 'ルームを編集',
            'new_item' => '新しいルーム',
            'view_item' => 'ルームを表示',
            'search_items' => 'ルームを検索',
            'not_found' => 'ルームが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にルームはありません',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_icon' => 'dashicons-groups',
    ));
}
add_action('init', 'lol_team_splitter_custom_post_types');

// .envファイルを読み込む関数
function load_env_file($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
        if (!array_key_exists($name, $_SERVER)) {
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// Riot APIからサモナー情報を取得する関数
function get_summoner_info_from_riot_api($summoner_name) {
    // .envファイルからAPIキーを読み込み
    $env_path = ABSPATH . '../.env';
    if (file_exists($env_path)) {
        load_env_file($env_path);
    }
    
    $api_key = $_ENV['RIOT_API_KEY'] ?? get_option('riot_api_key', '');
    
    if (empty($api_key)) {
        return array(
            'success' => false,
            'message' => __('Riot APIキーが設定されていません', 'lol-team-splitter')
        );
    }
    
    try {
        // Riot IDを分解
        if (strpos($summoner_name, '#') === false) {
            throw new Exception(__('Riot IDの形式が正しくありません（例: サモナー名#JP1）', 'lol-team-splitter'));
        }
        
        list($game_name, $tag_line) = explode('#', $summoner_name, 2);
        
        // 1. アカウント情報を取得（PUUIDを取得）
        $account_url = "https://asia.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{$game_name}/{$tag_line}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $account_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Riot-Token: ' . $api_key
        ));
        
        $account_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $account_data = json_decode($account_response, true);
            
            // 2. サモナー情報を取得（PUUIDを使用）
            $summoner_url = "https://jp1.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{$account_data['puuid']}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $summoner_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Riot-Token: ' . $api_key
            ));
            
            $summoner_response = curl_exec($ch);
            $summoner_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($summoner_http_code === 200) {
                $summoner_data = json_decode($summoner_response, true);
                
                // 3. リーグ情報を取得（PUUIDを直接使用）
                $league_data = array();
                if (isset($summoner_data['puuid'])) {
                    $league_url = "https://jp1.api.riotgames.com/lol/league/v4/entries/by-puuid/{$summoner_data['puuid']}?api_key={$api_key}";
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $league_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'X-Riot-Token: ' . $api_key
                    ));
                    
                    $league_response = curl_exec($ch);
                    $league_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($league_http_code === 200) {
                        $league_data = json_decode($league_response, true);
                    }
                }
                
                // 4. 直近5試合の戦績を取得
                $match_history = array();
                if (isset($account_data['puuid'])) {
                    $match_history = get_recent_matches($account_data['puuid'], $api_key);
                }
                
                // サモナーアイコンをダウンロードして保存（非同期で実行）
                $icon_url = "https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/" . ($summoner_data['profileIconId'] ?? 0) . ".png";
                
                // アイコンのダウンロードを非同期で実行（バックグラウンドで処理）
                wp_schedule_single_event(time(), 'download_summoner_icon_async', array(
                    'icon_id' => $summoner_data['profileIconId'] ?? 0,
                    'summoner_name' => $summoner_name
                ));
                
                // レスポンスを構築
                $icon_id = $summoner_data['profileIconId'] ?? 0;
                
                return array(
                    'success' => true,
                    'data' => array(
                        'summoner_name' => $summoner_name,
                        'level' => $summoner_data['summonerLevel'] ?? 0,
                        'icon_id' => $icon_id,
                        'icon_url' => $icon_url, // 直接RiotのURLを使用
                        'ranks' => $league_data,
                        'puuid' => $account_data['puuid'],
                        'recent_matches' => $match_history
                    )
                );
            } else {
                throw new Exception(sprintf(__('サモナー情報の取得に失敗しました: HTTP %d', 'lol-team-splitter'), $summoner_http_code));
            }
        } else {
            if ($http_code === 403) {
                throw new Exception(__('Riot APIアクセス拒否 (403): APIキーの確認が必要です', 'lol-team-splitter'));
            } elseif ($http_code === 404) {
                throw new Exception(__('Riot IDが見つかりません (404): プレイヤーが存在しないか、地域が異なります', 'lol-team-splitter'));
            } elseif ($http_code === 429) {
                throw new Exception(__('APIレート制限 (429): しばらく待ってから再試行してください', 'lol-team-splitter'));
            } else {
                throw new Exception(sprintf(__('アカウント情報の取得に失敗しました: HTTP %d', 'lol-team-splitter'), $http_code));
            }
        }
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => $e->getMessage()
        );
    }
}

// 非同期でアイコンをダウンロードするハンドラー
function handle_download_summoner_icon_async($icon_id, $summoner_name) {
    download_summoner_icon($icon_id, $summoner_name);
}
add_action('download_summoner_icon_async', 'handle_download_summoner_icon_async', 10, 2);

// サモナーアイコンをダウンロードしてサーバーに保存する関数
function download_summoner_icon($icon_id, $summoner_name) {
    // アイコンIDが無効な場合はデフォルトアイコンを使用
    if (empty($icon_id) || $icon_id < 0 || $icon_id > 5000) {
        $icon_id = 0; // デフォルトアイコン
    }
    
    // アップロードディレクトリのパスを取得
    $upload_dir = wp_upload_dir();
    $icons_dir = $upload_dir['basedir'] . '/summoner-icons';
    
    // ディレクトリが存在しない場合は作成
    if (!file_exists($icons_dir)) {
        wp_mkdir_p($icons_dir);
    }
    
    // ファイル名を生成（サモナー名をサニタイズ）
    $sanitized_name = sanitize_file_name($summoner_name);
    $filename = "icon_{$icon_id}_{$sanitized_name}.png";
    $filepath = $icons_dir . '/' . $filename;
    
    // 既にファイルが存在する場合はそのパスを返す
    if (file_exists($filepath)) {
        return $upload_dir['baseurl'] . '/summoner-icons/' . $filename;
    }
    
    // Riot APIからアイコン画像をダウンロード
    $icon_url = "https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/{$icon_id}.png";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $icon_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // タイムアウトを短縮
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 接続タイムアウトを追加
    curl_setopt($ch, CURLOPT_USERAGENT, 'LoL Team Splitter/1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL検証を無効化
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $icon_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $icon_data !== false) {
        // ファイルに保存
        if (file_put_contents($filepath, $icon_data) !== false) {
            return $upload_dir['baseurl'] . '/summoner-icons/' . $filename;
        }
    }
    
    // ダウンロードに失敗した場合はデフォルトアイコンのURLを返す
    return "https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/0.png";
}

// 直近5試合の戦績を取得する関数
function get_recent_matches($puuid, $api_key) {
    try {
        // マッチID一覧を取得（直近5試合）
        $match_list_url = "https://asia.api.riotgames.com/lol/match/v5/matches/by-puuid/{$puuid}/ids?start=0&count=5";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $match_list_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Riot-Token: ' . $api_key
        ));
        
        $match_list_response = curl_exec($ch);
        $match_list_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($match_list_http_code !== 200) {
            return array();
        }
        
        $match_ids = json_decode($match_list_response, true);
        if (empty($match_ids)) {
            return array();
        }
        
        $matches = array();
        
        // 各マッチの詳細情報を取得
        foreach ($match_ids as $match_id) {
            $match_url = "https://asia.api.riotgames.com/lol/match/v5/matches/{$match_id}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $match_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Riot-Token: ' . $api_key
            ));
            
            $match_response = curl_exec($ch);
            $match_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($match_http_code === 200) {
                $match_data = json_decode($match_response, true);
                
                // プレイヤーの情報を抽出
                $player_info = null;
                foreach ($match_data['info']['participants'] as $participant) {
                    if ($participant['puuid'] === $puuid) {
                        $player_info = $participant;
                        break;
                    }
                }
                
                if ($player_info) {
                    $matches[] = array(
                        'match_id' => $match_id,
                        'game_mode' => $match_data['info']['gameMode'],
                        'game_type' => $match_data['info']['gameType'],
                        'champion' => $player_info['championName'],
                        'kills' => $player_info['kills'],
                        'deaths' => $player_info['deaths'],
                        'assists' => $player_info['assists'],
                        'win' => $player_info['win'],
                        'cs' => $player_info['totalMinionsKilled'] + $player_info['neutralMinionsKilled'],
                        'duration' => $match_data['info']['gameDuration'],
                        'timestamp' => $match_data['info']['gameCreation']
                    );
                }
            }
        }
        
        return $matches;
        
    } catch (Exception $e) {
        return array();
    }
}

// 最高ランクを取得する関数
function get_highest_rank($ranks) {
    if (empty($ranks) || !is_array($ranks)) {
        return 'Unknown';
    }
    
    $rank_order = array(
        'IRON' => 1, 'BRONZE' => 2, 'SILVER' => 3, 'GOLD' => 4, 'PLATINUM' => 5,
        'DIAMOND' => 6, 'MASTER' => 7, 'GRANDMASTER' => 8, 'CHALLENGER' => 9
    );
    
    $highest_rank = 'IRON';
    $highest_tier = 5;
    $highest_lp = 0;
    
    foreach ($ranks as $rank) {
        $tier = $rank['tier'] ?? 'IRON';
        $rank_num = $rank['rank'] ?? 'V';
        $lp = $rank['leaguePoints'] ?? 0;
        $rank_value = $rank_order[$tier] ?? 1;
        
        if ($rank_value > $rank_order[$highest_rank]) {
            $highest_rank = $tier;
            $highest_tier = $rank_num;
            $highest_lp = $lp;
        } elseif ($rank_value == $rank_order[$highest_rank]) {
            $tier_order = array('I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5);
            if (($tier_order[$rank_num] ?? 5) < ($tier_order[$highest_tier] ?? 5)) {
                $highest_tier = $rank_num;
                $highest_lp = $lp;
            }
        }
    }
    
    // Master以上のランクはLPで表示
    if (in_array($highest_rank, ['MASTER', 'GRANDMASTER', 'CHALLENGER'])) {
        return $highest_rank . ' ' . $highest_lp . 'LP';
    } else {
        return $highest_rank . ' ' . $highest_tier;
    }
}

// 最高ランクの統計を取得する関数
function get_highest_rank_stats($ranks) {
    if (empty($ranks) || !is_array($ranks)) {
        return '-';
    }
    
    $rank_order = array(
        'IRON' => 1, 'BRONZE' => 2, 'SILVER' => 3, 'GOLD' => 4, 'PLATINUM' => 5,
        'DIAMOND' => 6, 'MASTER' => 7, 'GRANDMASTER' => 8, 'CHALLENGER' => 9
    );
    
    $highest_rank = 'IRON';
    $highest_tier = 5;
    $wins = 0;
    $losses = 0;
    
    foreach ($ranks as $rank) {
        $tier = $rank['tier'] ?? 'IRON';
        $rank_num = $rank['rank'] ?? 'V';
        $rank_value = $rank_order[$tier] ?? 1;
        
        if ($rank_value > $rank_order[$highest_rank]) {
            $highest_rank = $tier;
            $highest_tier = $rank_num;
            $wins = $rank['wins'] ?? 0;
            $losses = $rank['losses'] ?? 0;
        } elseif ($rank_value == $rank_order[$highest_rank]) {
            $tier_order = array('I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5);
            if (($tier_order[$rank_num] ?? 5) < ($tier_order[$highest_tier] ?? 5)) {
                $highest_tier = $rank_num;
                $wins = $rank['wins'] ?? 0;
                $losses = $rank['losses'] ?? 0;
            }
        }
    }
    
    return $wins . '勝' . $losses . '敗';
}

// AJAX処理（ルーム作成）
function handle_create_room() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $summoner_name = sanitize_text_field($_POST['summoner_name']);
    $room_password = sanitize_text_field($_POST['room_password']);
    
    if (empty($summoner_name)) {
        wp_send_json_error(array('message' => __('Riot IDを入力してください', 'lol-team-splitter')));
    }
    
    // Riot APIからサモナー情報を取得
    $summoner_data = get_summoner_info_from_riot_api($summoner_name);
    if (!$summoner_data['success']) {
        wp_send_json_error(array('message' => $summoner_data['message']));
    }
    
    // ルームIDを生成（6桁の数字）
    $room_id = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    
    // ホストをparticipantsに追加（チームには未参加状態）
    $host_participant = array(
        'name' => $summoner_name,
        'level' => $summoner_data['data']['level'],
        'icon_id' => $summoner_data['data']['icon_id'],
        'icon_url' => $summoner_data['data']['icon_url'],
        'rank' => !empty($summoner_data['data']['ranks']) ? $summoner_data['data']['ranks'][0]['tier'] . ' ' . $summoner_data['data']['ranks'][0]['leaguePoints'] . 'LP' : 'UNRANKED',
        'stats' => !empty($summoner_data['data']['ranks']) ? $summoner_data['data']['ranks'][0]['wins'] . '勝' . $summoner_data['data']['ranks'][0]['losses'] . '敗' : '',
        'lane' => '',
        'team' => null,
        'recent_matches' => $summoner_data['data']['recent_matches'] ?? []
    );
    
    $participants = array($summoner_name => $host_participant);
    
    // ルーム情報をカスタムフィールドとして保存
    $post_id = wp_insert_post(array(
        'post_title' => 'Room ' . $room_id,
        'post_type' => 'lol_room',
        'post_status' => 'publish',
        'meta_input' => array(
            'room_id' => $room_id,
            'host_name' => $summoner_name,
            'room_password' => $room_password,
            'created_at' => current_time('mysql'),
            'participants' => json_encode($participants, JSON_UNESCAPED_UNICODE),
            'host_data' => json_encode($summoner_data['data'], JSON_UNESCAPED_UNICODE),
            'host_session_id' => session_id() ?: uniqid('host_', true)
        )
    ));
    
    if ($post_id) {
        // セッションにホスト情報を保存
        if (!session_id()) {
            session_start();
        }
        $_SESSION['lol_host_rooms'][$room_id] = array(
            'host_name' => $summoner_name,
            'created_at' => current_time('mysql'),
            'room_id' => $room_id
        );
        
        // クッキーにユーザー名を保存（ホスト判定用）
        setcookie('lol_current_user', $summoner_name, time() + (86400 * 30), '/'); // 30日間有効
        
        wp_send_json_success(array(
            'room_id' => $room_id,
            'message' => __('ルーム作成完了！ページを移動中...', 'lol-team-splitter'),
            'host_data' => $summoner_data['data'],
            'debug' => $summoner_data['debug'] ?? ''
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('ルーム作成に失敗しました', 'lol-team-splitter')
        ));
    }
}
add_action('wp_ajax_create_room', 'handle_create_room');
add_action('wp_ajax_nopriv_create_room', 'handle_create_room');

// AJAX処理（サモナー情報取得）
function handle_get_summoner_info() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $summoner_name = sanitize_text_field($_POST['summoner_name']);
    
    if (empty($summoner_name)) {
        wp_send_json_error(array('message' => __('Riot IDを入力してください', 'lol-team-splitter')));
    }
    
    // Riot APIからサモナー情報を取得
    $summoner_data = get_summoner_info_from_riot_api($summoner_name);
    
    if ($summoner_data['success']) {
        wp_send_json_success($summoner_data['data']);
    } else {
        wp_send_json_error(array('message' => $summoner_data['message']));
    }
}
add_action('wp_ajax_get_summoner_info', 'handle_get_summoner_info');
add_action('wp_ajax_nopriv_get_summoner_info', 'handle_get_summoner_info');

// ページタイトルの修正
function lol_team_splitter_custom_title($title) {
    // team-splitページの場合
    if (get_query_var('pagename') === 'team-split' || (isset($_GET['pagename']) && $_GET['pagename'] === 'team-split')) {
        $roomId = get_query_var('room') ?: ($_GET['room'] ?? '');
        if (!empty($roomId)) {
            return 'チーム分け - ルーム ' . $roomId . ' | ' . get_bloginfo('name');
        }
        return 'チーム分け | ' . get_bloginfo('name');
    }
    
    // joinページの場合
    if (get_query_var('pagename') === 'join' || (isset($_GET['pagename']) && $_GET['pagename'] === 'join')) {
        $roomId = get_query_var('room') ?: ($_GET['room'] ?? '');
        if (!empty($roomId)) {
            return 'ルーム参加 - ' . $roomId . ' | ' . get_bloginfo('name');
        }
        return 'ルーム参加 | ' . get_bloginfo('name');
    }
    
    return $title;
}
add_filter('wp_title', 'lol_team_splitter_custom_title');
add_filter('document_title', 'lol_team_splitter_custom_title');

// 管理画面にRiot APIキー設定を追加
function lol_team_splitter_admin_menu() {
    add_options_page(
        __('LoL Team Splitter Settings', 'lol-team-splitter'),
        __('LoL Team Splitter', 'lol-team-splitter'),
        'manage_options',
        'lol-team-splitter-settings',
        'lol_team_splitter_settings_page'
    );
}
add_action('admin_menu', 'lol_team_splitter_admin_menu');

function lol_team_splitter_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('riot_api_key', sanitize_text_field($_POST['riot_api_key']));
        echo '<div class="notice notice-success"><p>' . __('設定が保存されました', 'lol-team-splitter') . '</p></div>';
    }
    
    // .envファイルからAPIキーを読み込み
    $env_path = ABSPATH . '../.env';
    $env_api_key = '';
    if (file_exists($env_path)) {
        load_env_file($env_path);
        $env_api_key = $_ENV['RIOT_API_KEY'] ?? '';
    }
    
    $wp_api_key = get_option('riot_api_key', '');
    $current_api_key = $env_api_key ?: $wp_api_key;
    ?>
    <div class="wrap">
        <h1><?php _e('LoL Team Splitter Settings', 'lol-team-splitter'); ?></h1>
        
        <?php if ($env_api_key): ?>
        <div class="notice notice-info">
            <p>
                <strong><?php _e('現在のAPIキー:', 'lol-team-splitter'); ?></strong> 
                <?php _e('.envファイルから読み込まれています', 'lol-team-splitter'); ?>
                <br>
                <code><?php echo esc_html(substr($env_api_key, 0, 8) . '...'); ?></code>
            </p>
        </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Riot API Key (WordPress設定)', 'lol-team-splitter'); ?></th>
                    <td>
                        <input type="text" name="riot_api_key" value="<?php echo esc_attr($wp_api_key); ?>" class="regular-text" />
                        <p class="description">
                            <?php _e('Riot Games Developer Portalから取得したAPIキーを入力してください。', 'lol-team-splitter'); ?>
                            <br>
                            <a href="https://developer.riotgames.com/" target="_blank"><?php _e('Riot Games Developer Portal', 'lol-team-splitter'); ?></a>
                            <br>
                            <strong><?php _e('注意:', 'lol-team-splitter'); ?></strong> 
                            <?php if ($env_api_key): ?>
                                <?php _e('.envファイルにRIOT_API_KEYが設定されている場合、そちらが優先されます。', 'lol-team-splitter'); ?>
                            <?php else: ?>
                                <?php _e('.envファイルにRIOT_API_KEYが設定されていない場合、この設定が使用されます。', 'lol-team-splitter'); ?>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <h2><?php _e('APIキーの設定方法', 'lol-team-splitter'); ?></h2>
        <div class="card">
            <h3><?php _e('方法1: .envファイルを使用（推奨）', 'lol-team-splitter'); ?></h3>
            <p><?php _e('プロジェクトルートの.envファイルに以下を追加:', 'lol-team-splitter'); ?></p>
            <code>RIOT_API_KEY=your_riot_api_key_here</code>
            <p><?php _e('この方法では、APIキーがWordPressのデータベースに保存されません。', 'lol-team-splitter'); ?></p>
        </div>
        
        <div class="card">
            <h3><?php _e('方法2: WordPress管理画面で設定', 'lol-team-splitter'); ?></h3>
            <p><?php _e('上記のフォームでAPIキーを入力して保存してください。', 'lol-team-splitter'); ?></p>
        </div>
    </div>
    <?php
}

// AJAX処理（ルーム参加）
function handle_join_room() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $summoner_name = sanitize_text_field($_POST['summoner_name']);
    $room_password = sanitize_text_field($_POST['room_password']);
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $stored_password = get_post_meta($room_post->ID, 'room_password', true);
    
    // パスワードチェック
    if (!empty($stored_password) && $stored_password !== $room_password) {
        wp_send_json_error(array(
            'message' => 'パスワードが正しくありません'
        ));
    }
    
    // Riot APIからサモナー情報を取得
    $summoner_data = get_summoner_info_from_riot_api($summoner_name);
    if (!$summoner_data['success']) {
        wp_send_json_error(array('message' => $summoner_data['message']));
    }
    
    // 参加者を追加
    $participants = json_decode(get_post_meta($room_post->ID, 'participants', true), true);
    if (!is_array($participants)) {
        $participants = array();
    }
    
    // 参加者データを配列形式で保存
    $participants[$summoner_name] = array(
        'name' => $summoner_name,
        'level' => $summoner_data['data']['level'] ?? '?',
        'rank' => get_highest_rank($summoner_data['data']['ranks'] ?? []),
        'stats' => get_highest_rank_stats($summoner_data['data']['ranks'] ?? []),
        'team' => null,
        'lane' => '',
        'icon_id' => $summoner_data['data']['icon_id'] ?? 0,
        'icon_url' => $summoner_data['data']['icon_url'] ?? '',
        'recent_matches' => $summoner_data['data']['recent_matches'] ?? array(),
        'joined_at' => current_time('mysql')
    );
    
    update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
    update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
    
    wp_send_json_success(array(
        'message' => 'ルームに参加しました',
        'room_id' => $room_id
    ));
}
add_action('wp_ajax_join_room', 'handle_join_room');
add_action('wp_ajax_nopriv_join_room', 'handle_join_room');

// AJAX処理（ルーム情報取得）
function handle_get_room_info() {
    $room_id = sanitize_text_field($_GET['room_id']);
    
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    // 参加者データが不完全な場合はRiot APIから再取得
    $updated = false;
    foreach ($participants as $name => $data) {
        // レベル、ランク、戦績、アイコンのいずれかが不完全な場合は再取得
        $needs_update = false;
        $reasons = [];
        if (!isset($data['level']) || $data['level'] === '?') {
            $needs_update = true;
            $reasons[] = 'level';
        }
        if (!isset($data['rank']) || $data['rank'] === 'Unknown') {
            $needs_update = true;
            $reasons[] = 'rank';
        }
        if (!isset($data['stats']) || $data['stats'] === '-') {
            $needs_update = true;
            $reasons[] = 'stats';
        }
        if (!isset($data['icon_id']) || !isset($data['icon_url']) || $data['icon_id'] == 0) {
            $needs_update = true;
            $reasons[] = 'icon';
        }
        
        if ($needs_update) {
            // Riot APIからサモナー情報を再取得
            $summoner_info = get_summoner_info_from_riot_api($name);
            if ($summoner_info['success']) {
                $participants[$name]['level'] = $summoner_info['data']['level'] ?? $data['level'] ?? '?';
                $participants[$name]['rank'] = get_highest_rank($summoner_info['data']['ranks'] ?? []) ?: ($data['rank'] ?? 'Unknown');
                $participants[$name]['stats'] = get_highest_rank_stats($summoner_info['data']['ranks'] ?? []) ?: ($data['stats'] ?? '-');
                $participants[$name]['icon_id'] = $summoner_info['data']['icon_id'] ?? $data['icon_id'] ?? 0;
                $participants[$name]['icon_url'] = $summoner_info['data']['icon_url'] ?? $data['icon_url'] ?? '';
                $participants[$name]['recent_matches'] = $summoner_info['data']['recent_matches'] ?? $data['recent_matches'] ?? [];
                $updated = true;
            } else {
                // API取得に失敗した場合はデフォルト値を設定
                $participants[$name]['icon_id'] = $data['icon_id'] ?? 0;
                $participants[$name]['icon_url'] = $data['icon_url'] ?? "https://ddragon.leagueoflegends.com/cdn/15.20.1/img/profileicon/0.png";
            }
        }
    }
    
    // 更新があった場合は保存
    if ($updated) {
        update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
        update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
    }
    
    wp_send_json_success(array(
        'room_id' => $room_id,
        'host_name' => get_post_meta($room_post->ID, 'host_name', true),
        'participants' => $participants,
        'created_at' => get_post_meta($room_post->ID, 'created_at', true)
    ));
}
add_action('wp_ajax_get_room_info', 'handle_get_room_info');
add_action('wp_ajax_nopriv_get_room_info', 'handle_get_room_info');

// AJAX処理（参加者保存）
function handle_save_participant() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $player_name = $_POST['player_name']; // sanitize_text_fieldを使わない（#文字を保持）
    $player_data = json_decode(stripslashes($_POST['player_data']), true);
    
    // プレイヤー名の文字エンコーディングを修正
    $player_name = mb_convert_encoding($player_name, 'UTF-8', 'auto');
    $player_name = trim($player_name); // 前後の空白を削除
    
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    // 参加者を追加または更新
    if (isset($participants[$player_name])) {
        // 既存の参加者を更新（joined_atは保持）
        $participants[$player_name]['level'] = $player_data['level'] ?? $participants[$player_name]['level'] ?? '?';
        $participants[$player_name]['rank'] = $player_data['rank'] ?? $participants[$player_name]['rank'] ?? 'Unknown';
        $participants[$player_name]['stats'] = $player_data['stats'] ?? $participants[$player_name]['stats'] ?? '-';
        $participants[$player_name]['team'] = $player_data['team'] ?? $participants[$player_name]['team'] ?? null;
        $participants[$player_name]['lane'] = $player_data['lane'] ?? $participants[$player_name]['lane'] ?? '';
        $participants[$player_name]['slot_position'] = $player_data['slot_position'] ?? $participants[$player_name]['slot_position'] ?? null;
        $participants[$player_name]['icon_id'] = $player_data['icon_id'] ?? $participants[$player_name]['icon_id'] ?? 0;
        $participants[$player_name]['icon_url'] = $player_data['icon_url'] ?? $participants[$player_name]['icon_url'] ?? '';
        $participants[$player_name]['recent_matches'] = $player_data['recent_matches'] ?? $participants[$player_name]['recent_matches'] ?? array();
        
    } else {
        // 新規参加者を追加
        $participants[$player_name] = array(
            'name' => $player_name,
            'level' => $player_data['level'] ?? '?',
            'rank' => $player_data['rank'] ?? 'Unknown',
            'stats' => $player_data['stats'] ?? '-',
            'team' => $player_data['team'] ?? null,
            'lane' => $player_data['lane'] ?? '',
            'slot_position' => $player_data['slot_position'] ?? null,
            'icon_id' => $player_data['icon_id'] ?? 0,
            'icon_url' => $player_data['icon_url'] ?? '',
            'recent_matches' => $player_data['recent_matches'] ?? array(),
            'joined_at' => current_time('mysql')
        );
    }
    
    // 最大参加者数チェック（10人まで）
    if (count($participants) > 10) {
        wp_send_json_error(array(
            'message' => 'ルームは満員です！（最大10人まで）'
        ));
        return;
    }
    
    // JSONエンコーディング時にUTF-8フラグを設定
    update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
    update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
    
    wp_send_json_success(array(
        'message' => '参加者が追加されました'
    ));
}
add_action('wp_ajax_save_participant', 'handle_save_participant');
add_action('wp_ajax_nopriv_save_participant', 'handle_save_participant');

// AJAX処理（参加者削除）
function handle_remove_participant() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $player_name = $_POST['player_name']; // sanitize_text_fieldを使わない（#文字を保持）
    
    // プレイヤー名の文字エンコーディングを修正
    $player_name = mb_convert_encoding($player_name, 'UTF-8', 'auto');
    $player_name = trim($player_name); // 前後の空白を削除
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    // 参加者を削除
    if (isset($participants[$player_name])) {
        unset($participants[$player_name]);
        update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
        update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
        
        wp_send_json_success(array(
            'message' => '参加者が削除されました'
        ));
    } else {
        wp_send_json_error(array(
            'message' => '参加者が見つかりません'
        ));
    }
}
add_action('wp_ajax_remove_participant', 'handle_remove_participant');
add_action('wp_ajax_nopriv_remove_participant', 'handle_remove_participant');

// AJAX処理（ルーム閉じる）
function handle_close_room() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $host_name = sanitize_text_field($_POST['host_name']);
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $stored_host_name = get_post_meta($room_post->ID, 'host_name', true);
    
    // ホスト名をチェック
    if ($host_name !== $stored_host_name) {
        wp_send_json_error(array(
            'message' => 'ルームを閉じる権限がありません'
        ));
    }
    
    // ルームを削除
    wp_delete_post($room_post->ID, true);
    
    wp_send_json_success(array(
        'message' => 'ルームが閉じられました'
    ));
}
add_action('wp_ajax_close_room', 'handle_close_room');
add_action('wp_ajax_nopriv_close_room', 'handle_close_room');

// 文字化けしたJSONデータを修正する関数
function fix_garbled_json($json_string) {
    // Unicodeエスケープシーケンスを正しい文字に変換
    $fixed = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
        return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
    }, $json_string);
    return $fixed;
}

// AJAX処理（直接参加）
function handle_join_room_direct() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $summoner_name = sanitize_text_field($_POST['summoner_name']);
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    // 参加者を追加（既存の参加者を上書きしない）
    if (!isset($participants[$summoner_name])) {
        // Riot APIからサモナー情報を取得
        $summoner_data = get_summoner_info_from_riot_api($summoner_name);
        
        if ($summoner_data['success'] && isset($summoner_data['data'])) {
            $participants[$summoner_name] = array(
                'name' => $summoner_name,
                'level' => $summoner_data['data']['level'] ?? '?',
                'rank' => get_highest_rank($summoner_data['data']['ranks'] ?? []),
                'stats' => get_highest_rank_stats($summoner_data['data']['ranks'] ?? []),
                'icon_id' => $summoner_data['data']['icon_id'] ?? 0,
                'icon_url' => $summoner_data['data']['icon_url'] ?? '',
                'recent_matches' => $summoner_data['data']['recent_matches'] ?? [],
                'lane' => '',
                'team' => null,
                'joined_at' => current_time('mysql')
            );
        } else {
            // APIから情報が取得できない場合はデフォルト値を使用
            // 注：handle_get_room_info関数で後ほど自動的に再取得されます
            $participants[$summoner_name] = array(
                'name' => $summoner_name,
                'level' => '?',
                'rank' => 'Unknown',
                'stats' => '-',
                'icon_id' => 0,
                'icon_url' => '',
                'recent_matches' => [],
                'lane' => '',
                'team' => null,
                'joined_at' => current_time('mysql')
            );
        }
    }
    
    update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
    update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
    
    wp_send_json_success(array(
        'message' => 'ルームに参加しました',
        'room_id' => $room_id
    ));
}
add_action('wp_ajax_join_room_direct', 'handle_join_room_direct');
add_action('wp_ajax_nopriv_join_room_direct', 'handle_join_room_direct');

// Server-Sent Events エンドポイント
function handle_room_events() {
    $room_id = sanitize_text_field($_GET['room_id'] ?? '');
    
    if (empty($room_id)) {
        http_response_code(400);
        exit;
    }
    
    // SSE用のヘッダーを設定
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Cache-Control');
    
    // ルーム情報を取得
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        echo "data: " . json_encode(array('error' => 'Room not found')) . "\n\n";
        exit;
    }
    
    $room_post = $posts[0];
    $last_modified = get_post_meta($room_post->ID, 'last_modified', true) ?: '0';
    
    // クライアントに接続確認を送信
    echo "data: " . json_encode(array('type' => 'connected', 'room_id' => $room_id)) . "\n\n";
    flush();
    
    // ループで変更を監視
    $timeout = 0;
    while ($timeout < 30) { // 30秒でタイムアウト
        $current_modified = get_post_meta($room_post->ID, 'last_modified', true) ?: '0';
        
        if ($current_modified !== $last_modified) {
            // ルーム情報を取得して送信
            $participants_json = get_post_meta($room_post->ID, 'participants', true);
            $participants_json = fix_garbled_json($participants_json);
            $participants = json_decode($participants_json, true) ?: array();
            
            $room_data = array(
                'type' => 'room_update',
                'room_id' => $room_id,
                'participants' => $participants,
                'timestamp' => $current_modified
            );
            
            echo "data: " . json_encode($room_data, JSON_UNESCAPED_UNICODE) . "\n\n";
            flush();
            
            $last_modified = $current_modified;
        }
        
        sleep(1);
        $timeout++;
    }
    
    echo "data: " . json_encode(array('type' => 'timeout')) . "\n\n";
    exit;
}
add_action('wp_ajax_room_events', 'handle_room_events');
add_action('wp_ajax_nopriv_room_events', 'handle_room_events');

// AJAX処理（プレイヤーレーン保存）
function handle_save_player_lane() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $player_name = $_POST['player_name']; // sanitize_text_fieldを使わない（#文字を保持）
    $lane = sanitize_text_field($_POST['lane']);
    
    // プレイヤー名の文字エンコーディングを修正
    $player_name = mb_convert_encoding($player_name, 'UTF-8', 'auto');
    $player_name = trim($player_name);
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    // プレイヤーのレーン情報を更新
    if (isset($participants[$player_name])) {
        $participants[$player_name]['lane'] = $lane;
        update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
        update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
        
        wp_send_json_success(array(
            'message' => 'レーン情報が保存されました'
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'プレイヤーが見つかりません'
        ));
    }
}
add_action('wp_ajax_save_player_lane', 'handle_save_player_lane');
add_action('wp_ajax_nopriv_save_player_lane', 'handle_save_player_lane');

// AJAX処理（プレイヤーのチーム情報を保存）
function handle_save_player_team() {
    check_ajax_referer('lol_team_splitter_nonce', 'nonce');
    
    $room_id = sanitize_text_field($_POST['room_id']);
    $player_name = sanitize_text_field($_POST['player_name']);
    $team = $_POST['team'] === '' ? null : intval($_POST['team']);
    
    // ルームを検索
    $posts = get_posts(array(
        'post_type' => 'lol_room',
        'meta_query' => array(
            array(
                'key' => 'room_id',
                'value' => $room_id,
                'compare' => '='
            )
        )
    ));
    
    if (empty($posts)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
    }
    
    $room_post = $posts[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    // プレイヤーのチーム情報を更新
    if (isset($participants[$player_name])) {
        $participants[$player_name]['team'] = $team;
        
        update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
        update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
        
        wp_send_json_success(array(
            'message' => 'チーム情報を保存しました'
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'プレイヤーが見つかりません'
        ));
    }
}
add_action('wp_ajax_save_player_team', 'handle_save_player_team');
add_action('wp_ajax_nopriv_save_player_team', 'handle_save_player_team');

// 全プレイヤーのチーム情報をリセットするハンドラー
function handle_reset_all_teams() {
    $room_id = sanitize_text_field($_POST['room_id']);
    
    if (empty($room_id)) {
        wp_send_json_error(array(
            'message' => 'ルームIDが指定されていません'
        ));
        return;
    }
    
    // ルームの参加者情報を取得
    $room = get_posts(array(
        'post_type' => 'lol_room',
        'meta_key' => 'room_id',
        'meta_value' => $room_id,
        'posts_per_page' => 1
    ));
    
    if (empty($room)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
        return;
    }
    
    $room_post = $room[0];
    $participants_json = get_post_meta($room_post->ID, 'participants', true);
    $participants_json = fix_garbled_json($participants_json);
    $participants = json_decode($participants_json, true) ?: array();
    
    if (is_array($participants)) {
        // 参加者リストを完全にクリア
        $participants = array();
        
        // 空の参加者リストをJSON形式で保存（削除処理と同じ形式）
        update_post_meta($room_post->ID, 'participants', json_encode($participants, JSON_UNESCAPED_UNICODE));
        update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
        
        wp_send_json_success(array(
            'message' => '全プレイヤーをルームから削除しました'
        ));
    } else {
        wp_send_json_error(array(
            'message' => '参加者情報が見つかりません'
        ));
    }
}
add_action('wp_ajax_reset_all_teams', 'handle_reset_all_teams');
add_action('wp_ajax_nopriv_reset_all_teams', 'handle_reset_all_teams');

// KDAデータを保存するハンドラー
function handle_save_kda_data() {
    $room_id = sanitize_text_field($_POST['room_id']);
    $player_name = sanitize_text_field($_POST['player_name']);
    $kill = intval($_POST['kill']);
    $death = intval($_POST['death']);
    $assist = intval($_POST['assist']);
    
    if (empty($room_id) || empty($player_name)) {
        wp_send_json_error(array(
            'message' => 'ルームIDまたはプレイヤー名が指定されていません'
        ));
        return;
    }
    
    // ルームを取得
    $room = get_posts(array(
        'post_type' => 'lol_room',
        'meta_key' => 'room_id',
        'meta_value' => $room_id,
        'posts_per_page' => 1
    ));
    
    if (empty($room)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
        return;
    }
    
    $room_post = $room[0];
    
    // 既存のKDAデータを取得
    $kda_data_json = get_post_meta($room_post->ID, 'kda_data', true);
    $kda_data_json = fix_garbled_json($kda_data_json);
    $kda_data = json_decode($kda_data_json, true) ?: array();
    
    // プレイヤーのKDAデータを更新
    $kda_data[$player_name] = array(
        'kill' => $kill,
        'death' => $death,
        'assist' => $assist
    );
    
    // KDAデータを保存
    update_post_meta($room_post->ID, 'kda_data', json_encode($kda_data, JSON_UNESCAPED_UNICODE));
    update_post_meta($room_post->ID, 'last_modified', current_time('timestamp'));
    
    wp_send_json_success(array(
        'message' => 'KDAデータを保存しました',
        'kda_data' => $kda_data
    ));
}
add_action('wp_ajax_save_kda_data', 'handle_save_kda_data');
add_action('wp_ajax_nopriv_save_kda_data', 'handle_save_kda_data');

// KDAデータを取得するハンドラー
function handle_get_kda_data() {
    $room_id = sanitize_text_field($_POST['room_id']);
    
    if (empty($room_id)) {
        wp_send_json_error(array(
            'message' => 'ルームIDが指定されていません'
        ));
        return;
    }
    
    // ルームを取得
    $room = get_posts(array(
        'post_type' => 'lol_room',
        'meta_key' => 'room_id',
        'meta_value' => $room_id,
        'posts_per_page' => 1
    ));
    
    if (empty($room)) {
        wp_send_json_error(array(
            'message' => 'ルームが見つかりません'
        ));
        return;
    }
    
    $room_post = $room[0];
    
    // KDAデータを取得
    $kda_data_json = get_post_meta($room_post->ID, 'kda_data', true);
    $kda_data_json = fix_garbled_json($kda_data_json);
    $kda_data = json_decode($kda_data_json, true) ?: array();
    
    wp_send_json_success(array(
        'kda_data' => $kda_data
    ));
}
add_action('wp_ajax_get_kda_data', 'handle_get_kda_data');
add_action('wp_ajax_nopriv_get_kda_data', 'handle_get_kda_data');

// カスタムページテンプレートの追加
function lol_team_splitter_add_page_templates($templates) {
    $templates['page-team-split.php'] = 'Team Split Page';
    $templates['page-join.php'] = 'Join Room Page';
    return $templates;
}
add_filter('theme_page_templates', 'lol_team_splitter_add_page_templates');

// ページテンプレートの読み込み（既存のページテンプレートを上書きしない）
function lol_team_splitter_load_page_template($template) {
    global $post;
    
    // 既存のページテンプレートがある場合はそれを優先
    if ($post) {
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($page_template === 'page-team-split.php') {
            $template = get_template_directory() . '/page-team-split.php';
            return $template;
        } elseif ($page_template === 'page-join.php') {
            $template = get_template_directory() . '/page-join.php';
            return $template;
        }
    }
    
    // クエリ変数でteam-splitが指定されている場合（リライトルール経由）
    if (get_query_var('pagename') === 'team-split') {
        $template = get_template_directory() . '/page-team-split.php';
        return $template;
    }
    
    // クエリ変数でjoinが指定されている場合（リライトルール経由）
    if (get_query_var('pagename') === 'join') {
        $template = get_template_directory() . '/page-join.php';
        return $template;
    }
    
    return $template;
}
add_filter('page_template', 'lol_team_splitter_load_page_template');

// より確実なテンプレート読み込み
add_action('template_redirect', function() {
    $pagename = get_query_var('pagename');
    $room = get_query_var('room');
    $host = get_query_var('host');
    
    // デバッグ情報（一時的）
    echo "<!-- Debug: pagename=$pagename, room=$room, host=$host -->";
    echo "<!-- Debug: template_redirect action triggered -->";
    
    // team-splitページの場合（先にチェック）
    if ($pagename === 'team-split') {
        echo "<!-- Debug: team-split condition matched -->";
        $custom_template = get_template_directory() . '/page-team-split.php';
        echo "<!-- Debug: template path: $custom_template -->";
        if (file_exists($custom_template)) {
            echo "<!-- Debug: template file exists, loading... -->";
            // ヘッダーとフッターの呼び出しを無効化
            add_filter('wp_title', '__return_empty_string');
            add_filter('document_title', '__return_empty_string');
            
            // template_redirect経由であることを示すフラグを設定
            $GLOBALS['template_redirect_called'] = true;
            
            // ヘッダーを読み込み
            get_header();
            // テンプレートを読み込み
            include $custom_template;
            // フッターを読み込み
            get_footer();
            exit;
        } else {
            echo "<!-- Debug: template file does not exist -->";
        }
    }
    
    // joinページの場合
    if ($pagename === 'join') {
        echo "<!-- Debug: join condition matched -->";
        $custom_template = get_template_directory() . '/page-join.php';
        echo "<!-- Debug: template path: $custom_template -->";
        if (file_exists($custom_template)) {
            echo "<!-- Debug: template file exists, loading... -->";
            // ヘッダーとフッターの呼び出しを無効化
            add_filter('wp_title', '__return_empty_string');
            add_filter('document_title', '__return_empty_string');
            
            // template_redirect経由であることを示すフラグを設定
            $GLOBALS['template_redirect_called'] = true;
            
            // ヘッダーを読み込み
            get_header();
            // テンプレートを読み込み
            include $custom_template;
            // フッターを読み込み
            get_footer();
            exit;
        } else {
            echo "<!-- Debug: template file does not exist -->";
        }
    }
    
    
    echo "<!-- Debug: no conditions matched, continuing... -->";
});

add_filter('template_include', function($template) {
    $pagename = get_query_var('pagename');
    $room = get_query_var('room');
    $host = get_query_var('host');
    
    // team-splitページの場合（先にチェック）
    if ($pagename === 'team-split') {
        $custom_template = get_template_directory() . '/page-team-split.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // joinページの場合
    if ($pagename === 'join') {
        $custom_template = get_template_directory() . '/page-join.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template;
});

// URLリライトルールの追加
function lol_team_splitter_add_rewrite_rules() {
    // より具体的なルールを先に設定
    add_rewrite_rule('^team-split/?$', 'index.php?pagename=team-split', 'top');
    add_rewrite_rule('^team-split/room/([0-9]+)/?$', 'index.php?pagename=team-split&room=$matches[1]', 'top');
    add_rewrite_rule('^team-split/room/([0-9]+)/host/?$', 'index.php?pagename=team-split&room=$matches[1]&host=true', 'top');
    add_rewrite_rule('^join/([0-9]+)/?$', 'index.php?pagename=join&room=$matches[1]', 'top');
}
add_action('init', 'lol_team_splitter_add_rewrite_rules');

// リライトルールを強制的にフラッシュ
function lol_team_splitter_flush_rewrite_rules() {
    lol_team_splitter_add_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'lol_team_splitter_flush_rewrite_rules');
add_action('after_switch_theme', 'lol_team_splitter_create_pages');

// テーマが更新された時にもリライトルールをフラッシュ
add_action('init', function() {
    if (get_option('lol_team_splitter_flush_rewrite_rules') !== '2') {
        lol_team_splitter_flush_rewrite_rules();
        update_option('lol_team_splitter_flush_rewrite_rules', '2');
    }
});

// 管理画面でリライトルールをフラッシュするためのアクション
add_action('admin_init', function() {
    if (isset($_GET['flush_rewrite_rules']) && $_GET['flush_rewrite_rules'] === '1') {
        lol_team_splitter_flush_rewrite_rules();
        wp_redirect(admin_url('themes.php?page=theme-options&rewrite_flushed=1'));
        exit;
    }
});

// 管理画面でページを作成するためのアクション
add_action('admin_init', function() {
    if (isset($_GET['create_pages']) && $_GET['create_pages'] === '1') {
        lol_team_splitter_create_pages();
        wp_redirect(admin_url('edit.php?post_type=page&pages_created=1'));
        exit;
    }
});

// フロントエンドでページを作成するためのアクション（デバッグ用）
add_action('wp', function() {
    if (isset($_GET['create_pages']) && $_GET['create_pages'] === '1') {
        lol_team_splitter_create_pages();
        echo '<script>alert("Pages created successfully!"); window.location.href = "' . home_url('/') . '";</script>';
        exit;
    }
    
    // リライトルールをフラッシュするアクション
    if (isset($_GET['flush_rules']) && $_GET['flush_rules'] === '1') {
        lol_team_splitter_flush_rewrite_rules();
        echo '<script>alert("Rewrite rules flushed successfully!"); window.location.href = "' . home_url('/') . '";</script>';
        exit;
    }
    
});

// クエリ変数の追加
function lol_team_splitter_add_query_vars($vars) {
    $vars[] = 'room';
    $vars[] = 'host';
    return $vars;
}
add_filter('query_vars', 'lol_team_splitter_add_query_vars');

// ページ作成の自動化
function lol_team_splitter_create_pages() {
    // Team Split ページ
    $team_split_page = get_page_by_path('team-split');
    if (!$team_split_page) {
        $page_id = wp_insert_post(array(
            'post_title' => 'チーム分け',
            'post_name' => 'team-split',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1
        ));
        
        if ($page_id) {
            update_post_meta($page_id, '_wp_page_template', 'page-team-split.php');
            echo "Team Split page created with ID: " . $page_id;
        }
    } else {
        // 既存のページのタイトルを更新
        wp_update_post(array(
            'ID' => $team_split_page->ID,
            'post_title' => 'チーム分け'
        ));
        echo "Team Split page already exists with ID: " . $team_split_page->ID;
    }
    
    // Join ページ
    $join_page = get_page_by_path('join');
    if (!$join_page) {
        $page_id = wp_insert_post(array(
            'post_title' => 'ルーム参加',
            'post_name' => 'join',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1
        ));
        
        if ($page_id) {
            update_post_meta($page_id, '_wp_page_template', 'page-join.php');
        }
    } else {
        // 既存のページのタイトルを更新
        wp_update_post(array(
            'ID' => $join_page->ID,
            'post_title' => 'ルーム参加'
        ));
    }
    
    
    // リライトルールをフラッシュ
    lol_team_splitter_flush_rewrite_rules();
}
add_action('after_switch_theme', 'lol_team_splitter_create_pages');

