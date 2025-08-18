const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const cors = require('cors');
const axios = require('axios');
const Pusher = require('pusher');
const cookieParser = require('cookie-parser');
const supabase = require('./supabase');
const app = express();
const http = require('http').createServer(app);
require('dotenv').config();

// プロキシ設定（Render環境でのレート制限のため）
if (process.env.NODE_ENV === 'production') {
    app.set('trust proxy', 1);
    console.log('✅ プロキシ設定: 有効（本番環境）');
} else {
    console.log('✅ プロキシ設定: 無効（開発環境）');
}
const { v4: uuidv4 } = require('uuid'); // uuidモジュールを追加
const rateLimit = require('express-rate-limit');
const path = require('path');
const helmet = require('helmet');
const passport = require('passport');
const DiscordStrategy = require('passport-discord').Strategy;
const session = require('express-session');

// Discord OAuth設定
const DISCORD_CLIENT_ID = process.env.DISCORD_CLIENT_ID;
const DISCORD_CLIENT_SECRET = process.env.DISCORD_CLIENT_SECRET;
const DISCORD_CALLBACK_URL = process.env.DISCORD_CALLBACK_URL || 'http://localhost:3000/auth/discord/callback';

// Passport設定
passport.use(new DiscordStrategy({
    clientID: DISCORD_CLIENT_ID,
    clientSecret: DISCORD_CLIENT_SECRET,
    callbackURL: DISCORD_CALLBACK_URL,
    scope: ['identify', 'email']
}, async (accessToken, refreshToken, profile, done) => {
    try {
        // Discordユーザー情報を取得
        const discordUser = {
            discord_id: profile.id,
            username: profile.username,
            display_name: profile.global_name || profile.username,
            email: profile.email,
            avatar: profile.avatar
        };

        // 既存ユーザーをチェック
        let user = await supabase.getUserByDiscordId(discordUser.discord_id);
        
        if (!user) {
            // 新規ユーザーの場合、一時的にDiscord情報を保存
            user = await supabase.createDiscordUser(discordUser);
        }

        return done(null, user);
    } catch (error) {
        return done(error, null);
    }
}));

passport.serializeUser((user, done) => {
    done(null, user.id);
});

passport.deserializeUser(async (id, done) => {
    try {
        const user = await supabase.getUserById(id);
        done(null, user);
    } catch (error) {
        done(error, null);
    }
});

// セッション設定（本番環境対応）
const sessionConfig = {
    secret: process.env.SESSION_SECRET || 'your-secret-key',
    resave: true, // セッションが変更されていなくても保存
    saveUninitialized: true, // 初期化されていないセッションも保存
    cookie: {
        secure: process.env.NODE_ENV === 'production',
        httpOnly: true,
        maxAge: 90 * 24 * 60 * 60 * 1000, // 90日（3ヶ月）
        sameSite: 'lax', // CSRF対策
        expires: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000) // 明示的な有効期限
    },
    name: 'sessionId', // セッションクッキーの名前を明示的に設定
    rolling: true, // アクセスするたびにセッションの有効期限を延長
    unset: 'destroy' // セッション削除時の動作
};

// 開発環境でもセッションを永続化するための設定
if (process.env.NODE_ENV === 'production') {
    console.log('✅ 本番環境用セッション設定を適用');
} else {
    console.log('✅ 開発環境用セッション設定を適用（セッション永続化）');
}

app.use(session(sessionConfig));

app.use(passport.initialize());
app.use(passport.session());

// Helmetでセキュリティヘッダーを設定
const cspConfig = process.env.NODE_ENV === 'production' ? {
    contentSecurityPolicy: {
        directives: {
            defaultSrc: ["'self'"],
            styleSrc: ["'self'", "'unsafe-inline'", "https://cdnjs.cloudflare.com"],
            scriptSrc: ["'self'", "'unsafe-inline'", "https://js.pusher.com", "https://cdnjs.cloudflare.com"],
            scriptSrcAttr: ["'unsafe-inline'"],
            imgSrc: ["'self'", "data:", "https:", "http:"],
            connectSrc: [
                "'self'",
                "https://*.pusher.com",
                "wss://*.pusher.com",
                "https://*.pusherapp.com",
                "wss://*.pusherapp.com",
                "https://sockjs-ap3.pusher.com",
                "wss://ws-ap3.pusher.com"
            ],
            fontSrc: ["'self'", "https://cdnjs.cloudflare.com"],
            objectSrc: ["'none'"],
            mediaSrc: ["'self'"],
            frameSrc: ["'none'"]
        }
    }
} : {
    contentSecurityPolicy: false, // 開発環境ではCSPを無効化
    crossOriginEmbedderPolicy: false,
    crossOriginResourcePolicy: false
};

app.use(helmet({
    ...cspConfig,
    crossOriginEmbedderPolicy: false
}));

// レート制限の設定
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15分
    max: 200, // 15分間に200リクエストまで（増加）
    message: {
        error: 'リクエストが多すぎます。しばらく待ってから再度お試しください。'
    },
    standardHeaders: true,
    legacyHeaders: false,
});

// より厳しいレート制限（ログイン・登録用）
const authLimiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15分
    max: 10, // 15分間に10回まで（増加）
    message: {
        error: '認証リクエストが多すぎます。しばらく待ってから再度お試しください。'
    },
    standardHeaders: true,
    legacyHeaders: false,
});

// チャット用のレート制限
const chatLimiter = rateLimit({
    windowMs: 1 * 60 * 1000, // 1分
    max: 50, // 1分間に50メッセージまで（増加）
    message: {
        error: 'メッセージの送信が多すぎます。しばらく待ってから再度お試しください。'
    },
    standardHeaders: true,
    legacyHeaders: false,
});

// 全体的なレート制限を適用
app.use(limiter);

// リクエストサイズ制限
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ limit: '10mb', extended: true }));

// ヘルスチェックエンドポイント（デプロイタイムアウト対策）
app.get('/health', (req, res) => {
    res.status(200).json({ 
        status: 'OK', 
        timestamp: new Date().toISOString(),
        environment: process.env.NODE_ENV || 'development'
    });
});

// 静的ファイルの提供（キャッシュ設定付き）
app.use(express.static('public', {
    maxAge: '1h', // 1時間キャッシュ
    etag: true,
    lastModified: true
}));

// ユーザー登録完了ページのルート
app.get('/complete-registration', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'complete-registration.html'));
});

// プロフィールページ用のクリーンなURLルーティング
app.get('/profile', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'profile.html'));
});

// 募集ページ用のクリーンなURLルーティング
app.get('/matching', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'matching.html'));
});

// 投稿ページ用のクリーンなURLルーティング
app.get('/posts', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'posts.html'));
});

// 申請ページ用のクリーンなURLルーティング
app.get('/requests', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'requests.html'));
});

// パーティーページ用のクリーンなURLルーティング
app.get('/parties', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'parties.html'));
});

// パーティー詳細ページ用のクリーンなURLルーティング
app.get('/party', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'party.html'));
});

// ログインページは削除済み（Discord OAuthに移行）
// app.get('/login', (req, res) => {
//     res.sendFile(path.join(__dirname, 'public', 'login.html'));
// });

// CSSファイルのMIMEタイプを正しく設定
app.use((req, res, next) => {
    if (req.path.endsWith('.css')) {
        res.setHeader('Content-Type', 'text/css');
    } else if (req.path.endsWith('.js')) {
        res.setHeader('Content-Type', 'application/javascript');
    } else if (req.path.endsWith('.html')) {
        res.setHeader('Content-Type', 'text/html');
    } else if (req.path.endsWith('.png')) {
        res.setHeader('Content-Type', 'image/png');
    } else if (req.path.endsWith('.jpg') || req.path.endsWith('.jpeg')) {
        res.setHeader('Content-Type', 'image/jpeg');
    } else if (req.path.endsWith('.gif')) {
        res.setHeader('Content-Type', 'image/gif');
    } else if (req.path.endsWith('.svg')) {
        res.setHeader('Content-Type', 'image/svg+xml');
    } else if (req.path.endsWith('.ico')) {
        res.setHeader('Content-Type', 'image/x-icon');
    }
    next();
});

// レスポンス時間の最適化
app.use((req, res, next) => {
    res.setHeader('X-Response-Time', '0');
    next();
});

// IP制限は無効化（世界中のユーザーがアクセス可能）
// 必要に応じて特定のエンドポイントでのみ制限を適用可能
// const trustedIPs = process.env.TRUSTED_IPS ? process.env.TRUSTED_IPS.split(',') : [];
// app.use((req, res, next) => {
//     const clientIP = req.ip || req.connection.remoteAddress;
//     if (trustedIPs.length > 0 && !trustedIPs.includes(clientIP)) {
//         return res.status(403).json({ error: 'アクセスが拒否されました' });
//     }
//     next();
// });

// CORS設定
app.use(cors({
    origin: process.env.NODE_ENV === 'production' 
        ? ['https://rallyleague.onrender.com', 'https://your-domain.com'] // 実際のドメインを追加
        : ['http://localhost:3000', 'http://127.0.0.1:3000'],
    credentials: true,  // Cookie送信を許可
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization']
}));
app.use(cookieParser()); // Cookieパーサーを追加

// JWT認証は削除済み（Discord OAuthに移行）
// const JWT_SECRET = process.env.JWT_SECRET;
// if (!JWT_SECRET) {
//     throw new Error('JWT_SECRET環境変数が設定されていません');
// }

// 認証ミドルウェア（Passport.jsセッション認証を使用）
function isAuthenticated(req, res, next) {
    if (req.isAuthenticated()) {
        return next();
    }
    res.status(401).json({ message: '認証が必要です' });
}

// Pusherの設定
const pusherAppId = process.env.PUSHER_APP_ID;
const pusherKey = process.env.PUSHER_KEY;
const pusherSecret = process.env.PUSHER_SECRET;
const pusherCluster = process.env.PUSHER_CLUSTER;

if (!pusherAppId || !pusherKey || !pusherSecret || !pusherCluster) {
    throw new Error('Pusher設定の環境変数が不足しています');
}

const pusher = new Pusher({
    appId: pusherAppId,
    key: pusherKey,
    secret: pusherSecret,
    cluster: pusherCluster,
    useTLS: true
});

// Riot API設定
const RIOT_API_KEY = process.env.RIOT_API_KEY;
if (!RIOT_API_KEY) {
    console.error('警告: RIOT_API_KEYが設定されていません。.envファイルを確認してください。');
}
const RIOT_API_BASE_URL = 'https://jp1.api.riotgames.com';
const RIOT_API_ASIA_URL = 'https://asia.api.riotgames.com';

// サモナー情報を取得する関数
async function getSummonerInfo(riotId) {
    try {
        const riotApiKey = process.env.RIOT_API_KEY;
        if (!riotApiKey) {
            throw new Error('Riot API Keyが設定されていません');
        }

        // Riot IDの形式を確認
        if (!riotId.includes('#')) {
            throw new Error('Riot IDの形式が正しくありません（例: beginner#330）');
        }

        // Riot IDを分解
        const [gameName, tagLine] = riotId.split('#', 2);

        // 1. Riot IDからアカウント情報を取得（PUUIDを取得）
        const accountUrl = `https://asia.api.riotgames.com/riot/account/v1/accounts/by-riot-id/${gameName}/${tagLine}`;
        
        const accountResponse = await axios.get(accountUrl, {
            headers: {
                'X-Riot-Token': riotApiKey
            },
            timeout: 10000
        });

        const accountData = accountResponse.data;
        const puuid = accountData.puuid;

        // 2. PUUIDからサモナー情報を取得（JP1サーバー）
        const summonerUrl = `https://jp1.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/${puuid}`;
        
        const summonerResponse = await axios.get(summonerUrl, {
            headers: {
                'X-Riot-Token': riotApiKey
            },
            timeout: 10000
        });

        const summonerData = summonerResponse.data;

        // 3. PUUIDからランク情報を取得（JP1サーバー）
        const rankUrl = `https://jp1.api.riotgames.com/lol/league/v4/entries/by-puuid/${puuid}`;
        
        const rankResponse = await axios.get(rankUrl, {
            headers: {
                'X-Riot-Token': riotApiKey
            },
            timeout: 10000
        });

        const rankData = rankResponse.data;

        // アイコンURLを生成（最新バージョンを使用）
        const iconUrl = `https://ddragon.leagueoflegends.com/cdn/15.15.1/img/profileicon/${summonerData.profileIconId}.png`;

        return {
            name: summonerData.name,
            level: summonerData.summonerLevel,
            iconUrl: iconUrl,
            ranks: rankData
        };
    } catch (error) {
        console.error('getSummonerInfo詳細エラー:', {
            message: error.message,
            status: error.response?.status,
            statusText: error.response?.statusText,
            data: error.response?.data,
            url: error.config?.url,
            riotId: riotId,
            headers: error.config?.headers ? 'API Key設定済み' : 'API Key未設定'
        });
        
        // より具体的なエラーメッセージ
        if (error.response?.status === 403) {
            throw new Error(`Riot APIアクセス拒否 (403): APIキーの確認が必要です。Riot ID: ${riotId}`);
        } else if (error.response?.status === 404) {
            throw new Error(`Riot IDが見つかりません (404): ${riotId} が存在しないか、地域が異なります`);
        } else if (error.response?.status === 429) {
            throw new Error(`APIレート制限 (429): しばらく待ってから再試行してください`);
        } else {
            throw new Error(`サモナー情報の取得に失敗しました: ${error.message}`);
        }
    }
}

// 最近送信されたメッセージを追跡（重複送信防止用）
const recentMessages = new Map(); // partyId -> { userId -> { message, timestamp } }

// チャットメッセージを処理する関数
const handleChatMessage = async (partyId, message, user) => {
    try {
        // 重複メッセージチェック（5秒以内の同じ内容のメッセージを防ぐ）
        const now = Date.now();
        const recentMessageKey = `${partyId}-${user.username}`;
        const recentMessage = recentMessages.get(recentMessageKey);
        
        if (recentMessage && 
            recentMessage.message === message && 
            (now - recentMessage.timestamp) < 5000) {
            return false;
        }

        // 最近送信されたメッセージを記録
        recentMessages.set(recentMessageKey, {
            message: message,
            timestamp: now
        });

        // 古いメッセージ記録をクリーンアップ（1分以上前のもの）
        for (const [key, value] of recentMessages.entries()) {
            if (now - value.timestamp > 60000) {
                recentMessages.delete(key);
            }
        }

        // チャットメッセージを作成
        const chatMessage = await supabase.createChatMessage({
            party_id: partyId,
            sender_id: user.id,
            content: message
        });

        // Pusherでメッセージをブロードキャスト
        await pusher.trigger(`party-${partyId}`, 'chat-message', chatMessage);
        return true;
    } catch (error) {
        console.error('チャットメッセージ処理エラー:', error);
        return false;
    }
};

// チャットメッセージ送信
app.post('/api/chat/send', chatLimiter, isAuthenticated, async (req, res) => {
    try {
        const { partyId, content } = req.body;
        const userId = req.user.id;

        if (!partyId || !content) {
            return res.status(400).json({ message: 'パーティーIDとメッセージ内容が必要です' });
        }

        // 重複メッセージチェック
        const messageKey = `${userId}-${content}-${Date.now()}`;
        if (recentMessages.has(messageKey)) {
            return res.status(400).json({ message: '重複メッセージです' });
        }

        // データベースにメッセージを保存
        const savedMessage = await supabase.createChatMessage({
            partyId: partyId,
            userId: userId,
            content: content
        });

        // 重複チェック用に保存
        recentMessages.set(messageKey, true);
        setTimeout(() => recentMessages.delete(messageKey), 5000);

        // Pusherでブロードキャスト
        pusher.trigger(`party-${partyId}`, 'chat-message', savedMessage);

        res.json({ message: 'メッセージが送信されました', data: savedMessage });
    } catch (error) {
        console.error('チャットメッセージ送信エラー詳細:', error);
        res.status(500).json({ 
            message: 'メッセージの送信に失敗しました',
            error: error.message
        });
    }
});

// チャットメッセージ取得API
app.get('/api/chat/:partyId', isAuthenticated, async (req, res) => {
    try {
        const partyId = req.params.partyId;
        const userId = req.user.id;

        // パーティーメンバーかどうかを確認
        const party = await supabase.getPartyById(partyId);
        if (!party) {
            return res.status(404).json({ message: 'パーティーが見つかりません' });
        }

        if (!party.members.some(member => member.id === userId)) {
            return res.status(403).json({ message: 'パーティーへのアクセスが拒否されました' });
        }

        // チャットメッセージを取得
        const messages = await supabase.getChatMessages(partyId);
        
        res.json(messages || []);
    } catch (error) {
        console.error('チャットメッセージ取得エラー:', error);
        res.status(500).json({ message: 'メッセージの取得に失敗しました' });
    }
});

// パーティー参加時の認証トークン生成
app.post('/api/party/auth', isAuthenticated, (req, res) => {
    const { partyId } = req.body;
    const user = req.user; // req.userには認証済みユーザー情報が含まれている
    
    if (!user || !partyId) {
        return res.status(400).json({ message: '無効なリクエストです' });
    }

    // Supabaseからパーティー情報を取得
    supabase.getPartyById(partyId)
        .then(party => {
            if (!party) {
                return res.status(404).json({ message: 'パーティーが見つかりません' });
            }

            // パーティーメンバーかどうかを確認
            if (!party.members.some(member => member.id === user.id)) {
                return res.status(403).json({ message: 'パーティーへのアクセスが拒否されました' });
            }

            const socketId = req.body.socket_id;
            const auth = pusher.authorizeChannel(socketId, `private-party-${partyId}`);
            res.send(auth);
        })
        .catch(err => {
            console.error('パーティー認証エラー:', err);
            res.status(500).json({ message: 'サーバーエラーが発生しました' });
        });
});

// Supabase接続テストエンドポイント
app.get('/api/debug/supabase-test', async (req, res) => {
    try {
        const isConnected = await supabase.isConnected();
        res.json({
            connected: isConnected,
            url: process.env.SUPABASE_URL,
            hasKey: !!process.env.SUPABASE_ANON_KEY
        });
    } catch (error) {
        res.status(500).json({
            connected: false,
            error: error.message
        });
    }
});

// データベース接続テストエンドポイント
app.get('/api/debug/db-test', async (req, res) => {
    try {
        // 接続テスト
        const isConnected = await supabase.isConnected();
        
        // テーブル存在確認
        const { data: users, error: usersError } = await supabase
            .from('users')
            .select('count')
            .limit(1);
            
        const { data: posts, error: postsError } = await supabase
            .from('posts')
            .select('count')
            .limit(1);

        // テーブル構造確認
        const { data: postsStructure, error: structureError } = await supabase
            .from('posts')
            .select('*')
            .limit(0);

        res.json({
            connected: isConnected,
            users_table: !usersError,
            posts_table: !postsError,
            users_error: usersError?.message,
            posts_error: postsError?.message,
            structure_error: structureError?.message,
            supabase_url: process.env.SUPABASE_URL,
            has_supabase_key: !!process.env.SUPABASE_ANON_KEY
        });
    } catch (error) {
        res.status(500).json({
            connected: false,
            error: error.message,
            stack: error.stack
        });
    }
});

// デバッグ用：登録済みユーザー一覧を表示
app.get('/api/debug/users', (req, res) => {
    supabase.getAllUsers()
        .then(users => {
            const userList = users.map(user => ({
                username: user.username,
                displayName: user.display_name,
                summonerName: user.summoner_name,
                avatar: user.avatar_url
            }));
            res.json(userList);
        })
        .catch(err => {
            console.error('デバッグユーザー一覧取得エラー:', err);
            res.status(500).json({ message: 'ユーザー一覧の取得に失敗しました' });
        });
});

// ユーザー名・パスワード認証は廃止されました
// Discord OAuthを使用してください

// ユーザー名・パスワード認証は廃止されました
// Discord OAuthを使用してください

// ログアウトエンドポイントを追加
app.post('/api/logout', (req, res) => {
    // クッキーをクリア
    res.clearCookie('token');
    res.clearCookie('jwt');
    
    res.json({ message: 'ログアウト成功' });
});

// 認証チェック専用API（軽量版）
app.get('/api/auth/check', isAuthenticated, async (req, res) => {
    try {
        const user = await supabase.getUserById(req.user.id);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        // 基本的なユーザー情報のみを返す（サモナー情報更新は行わない）
        res.json({
            id: user.id,
            username: user.username,
            displayName: user.display_name,
            summonerName: user.summoner_name,
            authenticated: true
        });
    } catch (error) {
        console.error('認証チェックエラー:', error);
        res.status(500).json({ 
            message: '認証チェックに失敗しました',
            error: error.message
        });
    }
});

// プロフィール情報取得API
app.get('/api/user/profile', isAuthenticated, async (req, res) => {
    try {
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        // サモナー情報を更新
        if (user.summoner_name) {
            try {
                const summonerInfo = await getSummonerInfo(user.summoner_name);
                user.summoner_info = summonerInfo;
                await supabase.updateUser(user.id, { summoner_info: summonerInfo });
                
                // 更新されたユーザー情報を再取得
                const updatedUser = await supabase.getUserById(user.id);
                if (updatedUser) {
                    res.json(updatedUser);
                    return;
                }
            } catch (error) {
                console.error('サモナー情報更新エラー:', error);
                // サモナー情報の更新に失敗してもプロフィールは返す
            }
        }

        res.json(user);
    } catch (error) {
        res.status(500).json({ 
            message: 'プロフィールの取得に失敗しました',
            error: error.message
        });
    }
});

// プロフィール更新
app.post('/api/user/profile', isAuthenticated, async (req, res) => {
    try {
        const { displayName, summonerName, password } = req.body;
        const userId = req.user.id;

        const updateData = {};
        if (displayName) updateData.display_name = displayName;
        if (summonerName) updateData.summoner_name = summonerName;
        if (password) updateData.password_hash = bcrypt.hashSync(password, 10);

        // サモナー名が変更された場合、サモナー情報も更新
        if (summonerName) {
            try {
                const summonerInfo = await getSummonerInfo(summonerName);
                updateData.summoner_info = summonerInfo;
            } catch (error) {
                return res.status(400).json({ 
                    message: 'サモナー情報の取得に失敗しました',
                    details: error.message
                });
            }
        }

        const updatedUser = await supabase.updateUser(userId, updateData);

        res.json({
            message: 'プロフィールが更新されました',
            user: {
                id: updatedUser.id,
                username: updatedUser.username,
                displayName: updatedUser.display_name,
                summonerName: updatedUser.summoner_name,
                summonerInfo: updatedUser.summoner_info
            }
        });
    } catch (error) {
        console.error('プロフィール更新エラー:', error);
        res.status(500).json({ message: 'プロフィールの更新に失敗しました' });
    }
});

// サモナー情報取得API
app.get('/api/summoner/:summonerName', isAuthenticated, async (req, res) => {
    try {
        const summonerName = decodeURIComponent(req.params.summonerName);
        const summonerInfo = await getSummonerInfo(summonerName);
        res.json(summonerInfo);
    } catch (error) {
        res.status(400).json({ message: error.message });
    }
});

// サモナー情報更新API
app.post('/api/user/refresh-summoner', isAuthenticated, async (req, res) => {
    try {
        const userId = req.user.id;
        
        // ユーザー情報を取得
        const user = await supabase.getUserById(userId);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        if (!user.summoner_name) {
            return res.status(400).json({ message: 'サモナー名が設定されていません' });
        }

        // 最新のサモナー情報を取得
        const newSummonerInfo = await getSummonerInfo(user.summoner_name);
        
        // 既存のサモナー情報とマージ
        const existingSummonerInfo = user.summoner_info || {};
        const updatedSummonerInfo = {
            ...existingSummonerInfo,
            level: newSummonerInfo.level,
            ranks: newSummonerInfo.ranks,
            iconUrl: newSummonerInfo.iconUrl,
            lastUpdated: new Date().toISOString()
        };
        
        // ユーザー情報を更新
        const updatedUser = await supabase.updateUser(userId, { 
            summoner_info: updatedSummonerInfo 
        });

        res.json({
            message: 'サモナー情報を更新しました',
            user: {
                id: updatedUser.id,
                username: updatedUser.username,
                displayName: updatedUser.display_name,
                summonerName: updatedUser.summoner_name,
                summonerInfo: updatedUser.summoner_info
            }
        });
    } catch (error) {
        console.error('サモナー情報更新エラー詳細:', {
            message: error.message,
            stack: error.stack,
            userId: req.user.id
        });
        res.status(500).json({ 
            message: 'サモナー情報の更新に失敗しました',
            error: error.message
        });
    }
});

// 投稿一覧取得API
app.get('/api/posts', isAuthenticated, async (req, res) => {
    try {
        const { gameMode, mainLane, rank } = req.query;
        
        const posts = await supabase.getAllPosts();
        
        // 投稿を日付の降順でソート
        posts.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        
        res.json(posts);
    } catch (error) {
        res.status(500).json({ 
            message: '投稿の取得に失敗しました',
            error: error.message
        });
    }
});

// 投稿作成
app.post('/api/posts', isAuthenticated, async (req, res) => {
    try {
        const { title, gameMode, mainLane, description } = req.body;
        const userId = req.user.id;

        // ユーザー情報を取得
        const user = await supabase.getUserById(userId);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        // 入力検証
        if (!title || !gameMode || !mainLane) {
            return res.status(400).json({ message: 'タイトル、ゲームモード、メインレーンは必須です' });
        }

        const postData = {
            title,
            gameMode,
            mainLane,
            description: description || '',
            authorId: userId
        };

        // 投稿を作成
        const post = await supabase.createPost(postData);

        res.status(201).json({
            message: '投稿が作成されました',
            post: {
                id: post.id,
                title: post.title,
                gameMode: post.game_mode,
                mainLane: post.main_lane,
                description: post.description,
                author: {
                    id: user.id,
                    username: user.username,
                    displayName: user.display_name,
                    summonerName: user.summoner_name,
                    summonerInfo: user.summoner_info
                },
                createdAt: post.created_at
            }
        });
    } catch (error) {
        // エラーの種類に応じて適切なレスポンスを返す
        if (error.code === '23503') {
            return res.status(400).json({ 
                message: '無効なユーザーIDです',
                error: error.message,
                code: error.code
            });
        }
        
        if (error.code === '23505') {
            return res.status(400).json({ 
                message: '重複したデータです',
                error: error.message,
                code: error.code
            });
        }
        
        if (error.code === '42P01') {
            return res.status(500).json({ 
                message: 'テーブルが存在しません',
                error: error.message,
                code: error.code
            });
        }
        
        res.status(500).json({ 
            message: '投稿の作成に失敗しました',
            error: error.message
        });
    }
});

// 投稿を削除
app.delete('/api/posts/:id', isAuthenticated, async (req, res) => {
    try {
        const postId = req.params.id;
        
        // ユーザー情報を取得
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        console.log('投稿削除リクエスト:', { postId, userId: user.id, username: req.user.username });

        // 投稿を取得して所有者を確認
        const post = await supabase.getPostById(postId);
        if (!post) {
            console.log('投稿が見つかりません:', postId);
            return res.status(404).json({ message: '投稿が見つかりません' });
        }

        console.log('投稿情報:', { postId: post.id, authorId: post.author_id, userId: user.id });

        // 投稿の所有者かどうかを確認
        if (post.author_id !== user.id) {
            console.log('権限がありません:', { postAuthorId: post.author_id, userId: user.id });
            return res.status(403).json({ message: 'この投稿を削除する権限がありません' });
        }

        // 投稿を削除
        await supabase.deletePost(postId, user.id);

        console.log('投稿削除成功:', postId);
        res.json({ message: '投稿を削除しました' });
    } catch (error) {
        console.error('投稿削除エラー詳細:', error);
        res.status(500).json({ 
            message: 'サーバーエラーが発生しました',
            error: error.message
        });
    }
});

// 申請一覧を取得するエンドポイント
app.get('/api/requests', isAuthenticated, async (req, res) => {
    try {
        console.log('申請一覧取得リクエスト:', req.user.username);
        
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            console.log('ユーザーが見つかりません:', req.user.username);
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        console.log('ユーザーID:', user.id);

        // 受け取った申請と送信した申請を取得
        const requests = await supabase.getRequestsByUser(user.id);
        console.log('取得した申請数:', requests ? requests.length : 0);
        
        res.json(requests || []);
    } catch (error) {
        console.error('申請一覧取得エラー詳細:', error);
        console.error('エラースタック:', error.stack);
        res.status(500).json({ 
            message: 'サーバーエラーが発生しました',
            error: error.message,
            details: error.details || ''
        });
    }
});

// 申請を作成するエンドポイント
app.post('/api/requests', isAuthenticated, async (req, res) => {
    try {
        const { postId, preferredLane, message } = req.body;
        const applicant = await supabase.getUserByUsername(req.user.username);

        if (!applicant) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        const post = await supabase.getPostById(postId);
        if (!post) {
            return res.status(404).json({ message: '投稿が見つかりません' });
        }

        // 自分の投稿への申請を防止
        if (post.author_id === applicant.id) {
            return res.status(400).json({ message: '自分の投稿には申請できません' });
        }

        // 重複申請の防止
        const existingRequest = await supabase.getRequestByPostAndApplicant(postId, applicant.id);

        if (existingRequest) {
            return res.status(400).json({ message: 'すでに申請済みです' });
        }

        // 新しい申請を作成
        console.log('申請作成パラメータ:', {
            postId: postId,
            applicantId: applicant.id,
            preferredLane: preferredLane,
            message: message,
            status: 'pending'
        });
        
        const newRequest = await supabase.createRequest({
            postId: postId,
            applicantId: applicant.id,
            preferredLane: preferredLane,
            message: message,
            status: 'pending'
        });

        console.log('作成された申請:', newRequest);

        res.status(201).json(newRequest);
    } catch (error) {
        console.error('申請作成エラー:', error);
        res.status(500).json({ message: 'サーバーエラーが発生しました' });
    }
});

// パーティー情報取得API
app.get('/api/party', isAuthenticated, async (req, res) => {
    try {
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        const parties = await supabase.getPartiesByUser(user.id);
        if (!parties || parties.length === 0) {
            return res.json(null);
        }
        res.json(parties[0]); // 最初のパーティーを返す
    } catch (error) {
        console.error('パーティー情報取得エラー:', error);
        res.status(500).json({ message: 'パーティー情報の取得に失敗しました' });
    }
});

// パーティー一覧取得API（ユーザーが参加しているパーティー）
app.get('/api/parties', isAuthenticated, async (req, res) => {
    try {
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        const parties = await supabase.getPartiesByUser(user.id);
        // 作成日時の降順でソート
        parties.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        res.json(parties);
    } catch (error) {
        console.error('パーティー一覧取得エラー:', error);
        res.status(500).json({ message: 'パーティー一覧の取得に失敗しました' });
    }
});

// 特定のパーティー情報取得API
app.get('/api/parties/:partyId', isAuthenticated, (req, res) => {
    try {
        const userId = req.user.id; // usernameではなくidを使用
        const partyId = req.params.partyId;
        
        supabase.getPartyById(partyId)
            .then(party => {
                if (!party) {
                    return res.status(404).json({ message: 'パーティーが見つかりません' });
                }

                // パーティーメンバーかどうかを確認
                if (!party.members.some(member => member.id === userId)) {
                    return res.status(403).json({ message: 'パーティーへのアクセスが拒否されました' });
                }

                res.json(party);
            })
            .catch(err => {
                console.error('パーティー情報取得エラー:', err);
                res.status(500).json({ message: 'パーティー情報の取得に失敗しました' });
            });
    } catch (error) {
        console.error('パーティー情報取得エラー:', error);
        res.status(500).json({ message: 'パーティー情報の取得に失敗しました' });
    }
});

// パーティー退出API
app.post('/api/party/leave', isAuthenticated, (req, res) => {
    try {
        const username = req.user.username;
        supabase.getPartyByMemberId(username)
            .then(party => {
                if (!party) {
                    return res.status(404).json({ message: 'パーティーが見つかりません' });
                }

                // メンバーから削除
                party.members = party.members.filter(member => member.id !== username);

                // メンバーが0人になった場合はパーティーを削除
                if (party.members.length === 0) {
                    supabase.deleteParty(party.id)
                        .then(() => {
                            res.json({ message: 'パーティーを削除しました' });
                        })
                        .catch(err => {
                            console.error('パーティー削除エラー:', err);
                            res.status(500).json({ message: 'パーティーからの退出に失敗しました' });
                        });
                } else {
                    supabase.updateParty(party.id, {
                        members: party.members,
                        updated_at: new Date()
                    })
                        .then(() => {
                            res.json({ message: 'パーティーから退出しました' });
                        })
                        .catch(err => {
                            console.error('パーティー退出エラー:', err);
                            res.status(500).json({ message: 'パーティーからの退出に失敗しました' });
                        });
                }
            })
            .catch(err => {
                console.error('パーティー退出エラー:', err);
                res.status(500).json({ message: 'パーティーからの退出に失敗しました' });
            });
    } catch (error) {
        console.error('パーティー退出エラー:', error);
        res.status(500).json({ message: 'パーティーからの退出に失敗しました' });
    }
});

// パーティー解散API
app.post('/api/party/disband', isAuthenticated, (req, res) => {
    try {
        const username = req.user.username;
        supabase.getPartyByMemberId(username)
            .then(party => {
                if (!party) {
                    return res.status(404).json({ message: 'パーティーが見つかりません' });
                }

                // 投稿主かどうかを確認
                if (party.post.author_id !== username) {
                    return res.status(403).json({ message: 'パーティーを解散する権限がありません' });
                }

                // パーティーを削除
                supabase.deleteParty(party.id)
                    .then(() => {
                        res.json({ message: 'パーティーを解散しました' });
                    })
                    .catch(err => {
                        console.error('パーティー解散エラー:', err);
                        res.status(500).json({ message: 'パーティーの解散に失敗しました' });
                    });
            })
            .catch(err => {
                console.error('パーティー解散エラー:', err);
                res.status(500).json({ message: 'パーティーの解散に失敗しました' });
            });
    } catch (error) {
        console.error('パーティー解散エラー:', error);
        res.status(500).json({ message: 'パーティーの解散に失敗しました' });
    }
});

// 申請を承認するエンドポイント
app.post('/api/requests/:requestId/accept', isAuthenticated, async (req, res) => {
    try {
        console.log('申請承認開始 - リクエストID:', req.params.requestId);
        
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            console.log('ユーザーが見つかりません:', req.user.username);
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        console.log('ユーザー情報:', { id: user.id, username: user.username });

        const request = await supabase.getRequestById(req.params.requestId);
        if (!request) {
            console.log('申請が見つかりません:', req.params.requestId);
            return res.status(404).json({ message: '申請が見つかりません' });
        }

        console.log('申請情報:', {
            id: request.id,
            postId: request.post_id,
            applicantId: request.applicant_id,
            postAuthorId: request.post?.author_id
        });

        // 投稿者本人かどうかを確認
        if (request.post.author_id !== user.id) {
            console.log('権限がありません:', {
                postAuthorId: request.post.author_id,
                userId: user.id
            });
            return res.status(403).json({ message: '申請を承認する権限がありません' });
        }

        console.log('申請ステータスを更新中...');
        // 申請のステータスを更新
        await supabase.updateRequest(request.id, { status: 'accepted' });
        console.log('申請ステータス更新完了');

        console.log('パーティー作成開始...');
        // パーティーを作成
        const party = await supabase.createParty({
            postId: request.post_id,
            members: [
                {
                    id: request.post.author_id
                },
                {
                    id: request.applicant_id
                }
            ]
        });

        console.log('パーティー作成完了:', party);

        // Pusherを使用してリアルタイム通知
        await pusher.trigger(`user-${request.applicant_id}`, 'request-accepted', {
            message: '申請が承認されました',
            party: party
        });

        console.log('申請承認処理完了');
        res.json({ 
            message: '申請を承認しました',
            party: party
        });
    } catch (error) {
        console.error('申請承認エラー詳細:', error);
        console.error('エラースタック:', error.stack);
        res.status(500).json({ message: 'サーバーエラーが発生しました' });
    }
});

// 申請を拒否するエンドポイント
app.post('/api/requests/:requestId/reject', isAuthenticated, async (req, res) => {
    try {
        const user = await supabase.getUserByUsername(req.user.username);
        if (!user) {
            return res.status(404).json({ message: 'ユーザーが見つかりません' });
        }

        const request = await supabase.getRequestById(req.params.requestId);
        if (!request) {
            return res.status(404).json({ message: '申請が見つかりません' });
        }

        // 投稿者本人かどうかを確認
        if (request.post.author_id !== user.id) {
            return res.status(403).json({ message: '申請を拒否する権限がありません' });
        }

        // 申請のステータスを更新
        await supabase.updateRequest(request.id, { status: 'rejected' });

        // Pusherを使用してリアルタイム通知
        await pusher.trigger(`user-${request.applicant_id}`, 'request-rejected', {
            message: '申請が拒否されました'
        });

        res.json({ message: '申請を拒否しました' });
    } catch (error) {
        console.error('申請拒否エラー:', error);
        res.status(500).json({ message: 'サーバーエラーが発生しました' });
    }
});

// Riot APIキーテストエンドポイント
app.get('/api/debug/riot-api-test', async (req, res) => {
    try {
        const riotApiKey = process.env.RIOT_API_KEY;
        
        if (!riotApiKey) {
            return res.status(400).json({
                success: false,
                message: 'RIOT_API_KEYが設定されていません',
                hasKey: false
            });
        }

        // 簡単なAPIテスト（韓国サーバーのステータス確認）
        const testResponse = await axios.get(
            'https://kr.api.riotgames.com/lol/status/v4/platform-data',
            {
                headers: {
                    'X-Riot-Token': riotApiKey
                },
                timeout: 5000
            }
        );

        res.json({
            success: true,
            message: 'Riot APIキーは有効です',
            hasKey: true,
            status: testResponse.status,
            data: testResponse.data
        });
    } catch (error) {
        console.error('Riot APIテストエラー:', error.response?.status, error.response?.data);
        
        res.status(400).json({
            success: false,
            message: 'Riot APIキーが無効です',
            hasKey: !!process.env.RIOT_API_KEY,
            error: {
                status: error.response?.status,
                statusText: error.response?.statusText,
                data: error.response?.data
            }
        });
    }
});

// 入力バリデーション関数
function validateInput(input, type) {
    if (!input || typeof input !== 'string') {
        return false;
    }
    
    // 基本的な長さ制限
    if (input.length < 1 || input.length > 100) {
        return false;
    }
    
    // 特殊文字の制限
    const dangerousChars = /[<>\"'&]/;
    if (dangerousChars.test(input)) {
        return false;
    }
    
    switch (type) {
        case 'username':
            // ユーザー名は英数字とアンダースコアのみ
            return /^[a-zA-Z0-9_]{3,20}$/.test(input);
        case 'password':
            // パスワードは8文字以上
            return input.length >= 8;
        case 'displayName':
            // 表示名は1-50文字
            return input.length >= 1 && input.length <= 50;
        case 'summonerName':
            // サモナー名は1-20文字
            return input.length >= 1 && input.length <= 20;
        default:
            return true;
    }
}

// HTMLエスケープ関数
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// エラーハンドリングミドルウェア
app.use((err, req, res, next) => {
    console.error('エラー:', err);
    
    // レート制限エラーの場合
    if (err.status === 429) {
        return res.status(429).json({
            error: 'リクエストが多すぎます。しばらく待ってから再度お試しください。',
            retryAfter: err.headers?.['retry-after'] || 60
        });
    }
    
    // その他のエラー
    res.status(err.status || 500).json({
        error: 'サーバーエラーが発生しました',
        message: process.env.NODE_ENV === 'development' ? err.message : '内部サーバーエラー'
    });
});

// Discord OAuthルート
app.get('/auth/discord', passport.authenticate('discord'));

app.get('/auth/discord/callback', 
    passport.authenticate('discord', { failureRedirect: '/' }),
    (req, res) => {
        // セッションを確実に保存
        req.session.save((err) => {
            if (err) {
                console.error('セッション保存エラー:', err);
                return res.redirect('/?error=session_error');
            }
            
            // ログイン成功後のリダイレクト
            if (req.user.summoner_name) {
                // 既存ユーザー：トップページにリダイレクト
                console.log('既存ユーザーログイン成功:', req.user.username);
                res.redirect('/');
            } else {
                // 新規ユーザー：ユーザー登録完了ページにリダイレクト
                console.log('新規ユーザーログイン成功:', req.user.username);
                res.redirect('/complete-registration');
            }
        });
    }
);

// ユーザー登録完了エンドポイント
app.post('/api/auth/complete-registration', async (req, res) => {
    try {
        if (!req.isAuthenticated()) {
            return res.status(401).json({ message: '認証が必要です' });
        }

        const { displayName, summonerName } = req.body;

        // 入力検証
        if (!displayName || !summonerName) {
            return res.status(400).json({ 
                message: '表示名とサモナー名を入力してください' 
            });
        }

        // 詳細なバリデーション
        if (!validateInput(displayName, 'displayName')) {
            return res.status(400).json({ 
                message: '表示名は1-50文字で入力してください' 
            });
        }
        
        if (!validateInput(summonerName, 'summonerName')) {
            return res.status(400).json({ 
                message: 'サモナー名は1-20文字で入力してください' 
            });
        }

        // サモナー情報を取得
        let summonerInfo;
        try {
            summonerInfo = await getSummonerInfo(summonerName);
        } catch (error) {
            return res.status(400).json({ 
                message: 'サモナー情報の取得に失敗しました',
                details: error.message
            });
        }

        // ユーザー情報を更新
        const updatedUser = await supabase.updateUserProfile(req.user.id, {
            display_name: displayName,
            summoner_name: summonerName,
            summoner_info: summonerInfo
        });

        res.json({
            message: 'ユーザー登録が完了しました',
            user: updatedUser
        });
    } catch (error) {
        console.error('ユーザー登録完了エラー:', error);
        
        const errorMessage = process.env.NODE_ENV === 'production' 
            ? '登録に失敗しました。しばらく時間をおいて再度お試しください。'
            : '登録エラー: ' + error.message;
            
        res.status(500).json({ 
            message: errorMessage
        });
    }
});

// ログアウト
app.get('/auth/logout', (req, res) => {
    req.logout((err) => {
        if (err) {
            console.error('ログアウトエラー:', err);
        }
        res.redirect('/');
    });
});

// 認証状態チェック
app.get('/api/auth/status', (req, res) => {
    console.log('認証状態チェック - セッションID:', req.sessionID);
    console.log('認証状態:', req.isAuthenticated());
    
    if (req.isAuthenticated()) {
        // セッションを確実に保存
        req.session.save((err) => {
            if (err) {
                console.error('セッション保存エラー:', err);
            }
            
            res.json({
                authenticated: true,
                user: {
                    id: req.user.id,
                    username: req.user.username,
                    displayName: req.user.display_name,
                    summonerName: req.user.summoner_name,
                    summonerInfo: req.user.summoner_info,
                    isComplete: !!req.user.summoner_name
                },
                sessionId: req.sessionID
            });
        });
    } else {
        res.json({ 
            authenticated: false,
            sessionId: req.sessionID
        });
    }
});

// 404エラーハンドリング（最後に配置）
app.use((req, res) => {
    res.status(404).json({ error: 'ページが見つかりません' });
});

// サーバーを起動
const PORT = process.env.PORT || 3000;
const server = http.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 サーバーがポート${PORT}で起動しました`);
    console.log(`📊 環境: ${process.env.NODE_ENV || 'development'}`);
    console.log(`🔗 URL: http://localhost:${PORT}`);
    
    // ヘルスチェックの準備完了を通知
    console.log('✅ ヘルスチェックエンドポイント: /health');
    console.log('✅ Discord OAuth: /auth/discord');
    console.log('✅ 静的ファイル配信: 準備完了');
});

// グレースフルシャットダウン
process.on('SIGTERM', () => {
    console.log('SIGTERM received, shutting down gracefully');
    server.close(() => {
        console.log('Process terminated');
    });
});

process.on('SIGINT', () => {
    console.log('SIGINT received, shutting down gracefully');
    server.close(() => {
        console.log('Process terminated');
    });
}); 