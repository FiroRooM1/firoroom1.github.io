<?php get_header(); ?>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-white mb-4">
                    League of Legends<br>
                    <span class="text-warning"><?php _e('カスタムゲーム', 'lol-team-splitter'); ?></span><br>
                    <?php _e('チーム分けツール', 'lol-team-splitter'); ?>
                </h1>
                <p class="lead text-light mb-4">
                    <?php _e('プレイヤーを公平にチーム分けして、バランスの取れたカスタムゲームを楽しもう！', 'lol-team-splitter'); ?>
                </p>
                <div class="d-flex gap-3 justify-content-start flex-wrap">
                    <button class="btn btn-warning btn-lg px-5 py-3" type="button" data-bs-toggle="modal" data-bs-target="#summonerModal">
                        <i class="fas fa-play me-2"></i>
                        <?php _e('チーム分けを開始', 'lol-team-splitter'); ?>
                    </button>
                    <button class="btn btn-outline-light btn-lg px-5 py-3" type="button" data-bs-toggle="modal" data-bs-target="#joinRoomModal">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <?php _e('ルーム参加', 'lol-team-splitter'); ?>
                    </button>
                    <button class="btn btn-success btn-lg px-5 py-3 d-none" type="button" id="returnToRoomBtn">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?php _e('ルームに戻る', 'lol-team-splitter'); ?>
                        <span id="roomIdDisplay"></span>
                    </button>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <div class="champion-showcase">
                        <div class="champion-card">
                            <div class="champion-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5><?php _e('バランス重視', 'lol-team-splitter'); ?></h5>
                            <p><?php _e('プレイヤーのスキルレベルを考慮した公平なチーム分け', 'lol-team-splitter'); ?></p>
                        </div>
                        <div class="champion-card">
                            <div class="champion-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5><?php _e('プライベート', 'lol-team-splitter'); ?></h5>
                            <p><?php _e('ルームIDとパスワードで安全な環境を提供', 'lol-team-splitter'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="features-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold text-white"><?php _e('主な機能', 'lol-team-splitter'); ?></h2>
                <p class="lead text-light"><?php _e('リーグ・オブ・レジェンドのカスタムゲームに最適化された機能', 'lol-team-splitter'); ?></p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4><?php _e('スキルバランス', 'lol-team-splitter'); ?></h4>
                    <p><?php _e('各プレイヤーのランクやスキルレベルを考慮して、バランスの取れたチームを作成します。', 'lol-team-splitter'); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4><?php _e('プライベート', 'lol-team-splitter'); ?></h4>
                    <p><?php _e('各ルームには固有のルームIDとパスワードが設定され、許可されたプレイヤーのみが参加できます。', 'lol-team-splitter'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cta-section py-5">
    <div class="container text-center">
        <h2 class="display-5 fw-bold text-white mb-4"><?php _e('今すぐ始めよう！', 'lol-team-splitter'); ?></h2>
        <p class="lead text-light mb-4"><?php _e('友達と一緒にバランスの取れたカスタムゲームを楽しもう', 'lol-team-splitter'); ?></p>
        <button class="btn btn-warning btn-lg px-5" data-bs-toggle="modal" data-bs-target="#summonerModal">
            <i class="fas fa-rocket me-2"></i>
            <?php _e('チーム分けを開始', 'lol-team-splitter'); ?>
        </button>
    </div>
</div>

<!-- サモナー名入力モーダル -->
<div class="modal fade" id="summonerModal" tabindex="-1" aria-labelledby="summonerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="summonerModalLabel">
                    <i class="fas fa-users me-2"></i>
                    サモナー名を入力してください
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="summonerForm">
                    <div class="mb-3">
                        <label for="summonerName" class="form-label">Riot ID</label>
                        <input type="text" class="form-control bg-dark text-white custom-border" id="summonerName" 
                               placeholder="例: サモナー名#JP1" required>
                        <div class="form-text text-light">Riot IDの形式で入力してください（例: プレイヤー名#タグ）</div>
                    </div>
                    <div class="mb-3">
                        <label for="roomPassword" class="form-label">ルームパスワード（任意）</label>
                        <input type="password" class="form-control bg-dark text-white custom-border" id="roomPassword" 
                               placeholder="空欄の場合はパスワードなしでルーム作成">
                        <div class="form-text text-light">空欄の場合はパスワードなしでルームが作成されます</div>
                    </div>
                    <div class="alert alert-info d-none" id="loadingAlert">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        アカウント情報を取得中...
                    </div>
                    <div class="alert alert-danger d-none" id="errorAlert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="errorMessage"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-warning" id="searchSummoner">
                    <i class="fas fa-play me-2"></i>
                    開始
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ルーム参加モーダル -->
<div class="modal fade" id="joinRoomModal" tabindex="-1" aria-labelledby="joinRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="joinRoomModalLabel">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    ルームに参加
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="joinRoomForm">
                    <div class="mb-3">
                        <label for="roomId" class="form-label">ルームID</label>
                        <input type="text" class="form-control bg-dark text-white custom-border" id="roomId" 
                               placeholder="例: 123456" required>
                        <div class="form-text text-light">6桁のルームIDを入力してください</div>
                    </div>
                    <div class="mb-3">
                        <label for="joinSummonerName" class="form-label">Riot ID</label>
                        <input type="text" class="form-control bg-dark text-white custom-border" id="joinSummonerName" 
                               placeholder="例: サモナー名#JP1" required>
                        <div class="form-text text-light">Riot IDの形式で入力してください（例: プレイヤー名#タグ）</div>
                    </div>
                    <div class="mb-3">
                        <label for="joinRoomPassword" class="form-label">ルームパスワード（任意）</label>
                        <input type="password" class="form-control bg-dark text-white custom-border" id="joinRoomPassword" 
                               placeholder="パスワードが設定されている場合のみ入力">
                        <div class="form-text text-light">パスワードが設定されているルームの場合のみ入力してください</div>
                    </div>
                    <div class="alert alert-info d-none" id="joinLoadingAlert">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        ルームに参加中...
                    </div>
                    <div class="alert alert-danger d-none" id="joinErrorAlert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="joinErrorMessage"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-warning" id="joinRoomBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    参加
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 使い方モーダル -->
<div class="modal fade" id="howToUseModal" tabindex="-1" aria-labelledby="howToUseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="howToUseModalLabel">
                    <i class="fas fa-question-circle me-2"></i>
                    使い方
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-play me-2"></i>
                            基本的な使い方
                        </h6>
                        <ol class="text-light">
                            <li class="mb-2">
                                <strong>ルーム作成</strong><br>
                                「チーム分けを開始」ボタンをクリックして、Riot IDを入力してルームを作成します。
                            </li>
                            <li class="mb-2">
                                <strong>参加者招待</strong><br>
                                作成されたルームIDとパスワードを友達に共有して、ルームに参加してもらいます。<br>
                                <small class="text-info">※参加用URLを友達に共有すると、ルームIDとパスワードの入力は省略できます。</small>
                            </li>
                            <li class="mb-2">
                                <strong>参加ボタンを押して参加</strong><br>
                                ルームページの空いているスロットの「参加」ボタンを押すと参加できます。
                            </li>
                            <li class="mb-2">
                                <strong>レーン設定</strong><br>
                                各参加者が自分がプレイするレーン（トップ、ジャングル、ミッド、ボット、サポート）を設定します。
                            </li>
                            <li class="mb-2">
                                <strong>チーム分け実行</strong><br>
                                10人全員が参加し、全員がレーンを設定したら「チーム分け実行」ボタンを押します。
                            </li>
                        </ol>
                        
                        <h6 class="text-warning mb-3 mt-4">
                            <i class="fas fa-cogs me-2"></i>
                            高度な機能
                        </h6>
                        <ul class="text-light">
                            <li class="mb-2">
                                <strong>バランス重視のチーム分け</strong><br>
                                プレイヤーのランク、レベル、KDA、勝率を総合的に考慮して公平なチームを作成します。
                            </li>
                            <li class="mb-2">
                                <strong>ブルーサイド/レッドサイド</strong><br>
                                チーム分け後、スコアの低いチームがブルーサイド、高いチームがレッドサイドに自動で割り当てられます。
                            </li>
                            <li class="mb-2">
                                <strong>平均ランク表示</strong><br>
                                各チームの平均ランクが自動で計算・表示されます。
                            </li>
                        </ul>
                        
                        <h6 class="text-warning mb-3 mt-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            注意事項
                        </h6>
                        <ul class="text-light">
                            <li class="mb-2">チーム分けには必ず10人の参加者が必要です</li>
                            <li class="mb-2">各チームでレーンの重複はできません（例：チーム1にトップが2人など）</li>
                            <li class="mb-2">全員がレーンを設定する必要があります</li>
                            <li class="mb-2">ルーム作成者は「ルームを閉じる」ボタンでルームを終了できます</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
