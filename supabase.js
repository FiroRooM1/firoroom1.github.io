const { createClient } = require('@supabase/supabase-js');
const bcrypt = require('bcryptjs');

// Supabase設定
const supabaseUrl = process.env.SUPABASE_URL;
const supabaseAnonKey = process.env.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
    throw new Error('SUPABASE_URL と SUPABASE_ANON_KEY の環境変数が設定されていません');
}

// Supabaseクライアントの初期化
const supabase = createClient(supabaseUrl, supabaseAnonKey);

// 認証情報を設定
function setAuthToken(token) {
    if (token) {
        supabase.auth.setSession({
            access_token: token,
            refresh_token: null
        });
    }
}

// 接続テスト
async function isConnected() {
    try {
        // 存在しないテーブルにクエリを実行して接続をテスト
        const { error } = await supabase
            .from('_dummy_table_that_does_not_exist')
            .select('*')
            .limit(1);
        
        // テーブルが存在しないエラー（42P01）は接続成功を示す
        if (error && error.code === '42P01') {
            return true;
        }
        
        return !error;
    } catch (error) {
        return false;
    }
}

// ユーザー作成
async function createUser(userData) {
    const hashedPassword = bcrypt.hashSync(userData.password, 10);
    
    const { data, error } = await supabase
        .from('users')
        .insert({
            username: userData.username,
            display_name: userData.displayName,
            summoner_name: userData.summonerName,
            summoner_info: userData.summonerInfo,
            password_hash: hashedPassword
        })
        .select()
        .single();

    if (error) {
        throw error;
    }

    return data;
}

// ユーザー名でユーザーを取得
async function getUserByUsername(username) {
    const { data, error } = await supabase
        .from('users')
        .select('*')
        .eq('username', username)
        .single();

    if (error) {
        return null;
    }

    return data;
}

// ユーザーIDでユーザーを取得
async function getUserById(userId) {
    const { data, error } = await supabase
        .from('users')
        .select('*')
        .eq('id', userId)
        .single();

    if (error) {
        return null;
    }

    return data;
}

// ユーザー情報を更新
async function updateUser(userId, updateData) {
    const { data, error } = await supabase
        .from('users')
        .update(updateData)
        .eq('id', userId)
        .select()
        .single();

    if (error) {
        throw error;
    }

    return data;
}

// 投稿を作成
async function createPost(postData) {
    try {
        // データの検証
        if (!postData.title || !postData.gameMode || !postData.mainLane || !postData.authorId) {
            throw new Error('必須フィールドが不足しています');
        }

        const insertData = {
            title: postData.title,
            game_mode: postData.gameMode,
            main_lane: postData.mainLane,
            description: postData.description || '',
            author_id: postData.authorId
        };

        const { data, error } = await supabase
            .from('posts')
            .insert(insertData)
            .select()
            .single();

        if (error) {
            throw error;
        }

        return data;
    } catch (error) {
        throw error;
    }
}

// 全ての投稿を取得
async function getAllPosts() {
    try {
        // まず投稿を取得
        const { data: posts, error: postsError } = await supabase
            .from('posts')
            .select('*')
            .order('created_at', { ascending: false });

        if (postsError) {
            throw postsError;
        }

        if (!posts || posts.length === 0) {
            return [];
        }

        // 投稿の作者IDを収集
        const authorIds = [...new Set(posts.map(post => post.author_id))];

        // 作者の情報を取得
        const { data: authors, error: authorsError } = await supabase
            .from('users')
            .select('id, username, display_name, summoner_name, summoner_info')
            .in('id', authorIds);

        if (authorsError) {
            throw authorsError;
        }

        // 作者情報をマップ化
        const authorsMap = {};
        if (authors) {
            authors.forEach(author => {
                authorsMap[author.id] = author;
            });
        }

        // 投稿と作者情報を結合
        const postsWithAuthors = posts.map(post => ({
            ...post,
            author: authorsMap[post.author_id] || null
        }));

        return postsWithAuthors;
    } catch (error) {
        throw error;
    }
}

// 投稿IDで投稿を取得
async function getPostById(postId) {
    try {
        // 投稿を取得
        const { data: post, error: postError } = await supabase
            .from('posts')
            .select('*')
            .eq('id', postId)
            .single();

        if (postError) {
            return null;
        }

        if (!post) {
            return null;
        }

        // 作者の情報を取得
        const { data: author, error: authorError } = await supabase
            .from('users')
            .select('id, username, display_name, summoner_name, summoner_info')
            .eq('id', post.author_id)
            .single();

        if (authorError) {
            // 作者情報の取得に失敗しても投稿は返す
            return {
                ...post,
                author: null
            };
        }

        // 投稿と作者情報を結合
        return {
            ...post,
            author: author
        };
    } catch (error) {
        return null;
    }
}

// 投稿を削除
async function deletePost(postId, authorId) {
    try {
        console.log('deletePost呼び出し:', { postId, authorId });
        
        const { error } = await supabase
            .from('posts')
            .delete()
            .eq('id', postId)
            .eq('author_id', authorId);

        if (error) {
            console.error('投稿削除エラー:', error);
            throw error;
        }

        console.log('投稿削除成功:', postId);
        return true;
    } catch (error) {
        console.error('deletePostエラー:', error);
        throw error;
    }
}

// リクエストを作成
async function createRequest(requestData) {
    console.log('createRequest呼び出し - パラメータ:', requestData);
    
    const insertData = {
        post_id: requestData.postId,
        applicant_id: requestData.applicantId,
        preferred_lane: requestData.preferredLane,
        message: requestData.message,
        status: requestData.status
    };
    
    console.log('データベース挿入データ:', insertData);
    
    const { data, error } = await supabase
        .from('requests')
        .insert(insertData)
        .select()
        .single();

    if (error) {
        console.error('申請作成エラー:', error);
        throw error;
    }

    console.log('申請作成成功:', data);
    return data;
}

// ユーザーの申請を取得（受け取った申請と送信した申請）
async function getRequestsByUser(userId) {
    try {
        console.log('申請取得開始 - ユーザーID:', userId);

        // まず、ユーザーが投稿した投稿のIDを取得
        const { data: userPosts, error: postsError } = await supabase
            .from('posts')
            .select('id')
            .eq('author_id', userId);

        if (postsError) {
            console.error('ユーザーの投稿取得エラー:', postsError);
            throw postsError;
        }

        const userPostIds = userPosts ? userPosts.map(post => post.id) : [];
        console.log('ユーザーの投稿ID:', userPostIds);

        // 送信した申請を取得
        const { data: sentRequests, error: sentError } = await supabase
            .from('requests')
            .select('*')
            .eq('applicant_id', userId);

        if (sentError) {
            console.error('送信した申請取得エラー:', sentError);
            throw sentError;
        }

        // 受け取った申請を取得
        const { data: receivedRequests, error: receivedError } = await supabase
            .from('requests')
            .select('*')
            .in('post_id', userPostIds);

        if (receivedError) {
            console.error('受け取った申請取得エラー:', receivedError);
            throw receivedError;
        }

        console.log('送信した申請数:', sentRequests ? sentRequests.length : 0);
        console.log('受け取った申請数:', receivedRequests ? receivedRequests.length : 0);

        // 申請データを結合
        const allRequests = [...(sentRequests || []), ...(receivedRequests || [])];

        // 各申請に関連する投稿とユーザー情報を取得
        const enrichedRequests = await Promise.all(
            allRequests.map(async (request) => {
                try {
                    // 投稿情報を取得
                    const { data: post, error: postError } = await supabase
                        .from('posts')
                        .select('*')
                        .eq('id', request.post_id)
                        .single();

                    if (postError) {
                        console.error('投稿取得エラー:', postError);
                        return request;
                    }

                    // 投稿者の情報を取得
                    const { data: postAuthor, error: authorError } = await supabase
                        .from('users')
                        .select('*')
                        .eq('id', post.author_id)
                        .single();

                    if (authorError) {
                        console.error('投稿者取得エラー:', authorError);
                        return { ...request, post: post };
                    }

                    // 申請者の情報を取得
                    const { data: applicant, error: applicantError } = await supabase
                        .from('users')
                        .select('*')
                        .eq('id', request.applicant_id)
                        .single();

                    if (applicantError) {
                        console.error('申請者取得エラー:', applicantError);
                        return { 
                            ...request, 
                            post: { ...post, author: postAuthor }
                        };
                    }

                    return {
                        ...request,
                        post: { ...post, author: postAuthor },
                        applicant: applicant
                    };
                } catch (error) {
                    console.error('申請データ取得エラー:', error);
                    return request;
                }
            })
        );

        console.log('最終的な申請数:', enrichedRequests.length);
        return enrichedRequests;

    } catch (error) {
        console.error('申請取得エラー:', error);
        throw error;
    }
}

// リクエストIDでリクエストを取得
async function getRequestById(requestId) {
    console.log('getRequestById呼び出し - リクエストID:', requestId);
    
    const { data, error } = await supabase
        .from('requests')
        .select(`
            *,
            post:posts(*),
            applicant:users!requests_applicant_id_fkey(*)
        `)
        .eq('id', requestId)
        .single();

    if (error) {
        console.error('getRequestByIdエラー:', error);
        return null;
    }

    console.log('getRequestById結果:', data);
    return data;
}

// 投稿と申請者でリクエストを取得
async function getRequestByPostAndApplicant(postId, applicantId) {
    const { data, error } = await supabase
        .from('requests')
        .select('*')
        .eq('post_id', postId)
        .eq('applicant_id', applicantId)
        .single();

    if (error) {
        return null;
    }

    return data;
}

// リクエストを更新
async function updateRequest(requestId, updateData) {
    const { data, error } = await supabase
        .from('requests')
        .update(updateData)
        .eq('id', requestId)
        .select()
        .single();

    if (error) {
        throw error;
    }

    return data;
}

// パーティーを作成
async function createParty(partyData) {
    try {
        console.log('createParty呼び出し - パラメータ:', partyData);
        
        // パーティーを作成
        const { data: party, error: partyError } = await supabase
            .from('parties')
            .insert({
                post_id: partyData.postId
            })
            .select()
            .single();

        if (partyError) {
            console.error('パーティー作成エラー:', partyError);
            throw partyError;
        }

        console.log('パーティー作成成功:', party);

        // パーティーメンバーを追加
        if (partyData.members && partyData.members.length > 0) {
            console.log('メンバー追加開始:', partyData.members);
            
            const memberInserts = partyData.members.map(member => ({
                party_id: party.id,
                user_id: member.id
            }));

            console.log('メンバー挿入データ:', memberInserts);

            const { error: membersError } = await supabase
                .from('party_members')
                .insert(memberInserts);

            if (membersError) {
                console.error('メンバー追加エラー:', membersError);
                throw membersError;
            }

            console.log('メンバー追加成功');
        }

        return party;
    } catch (error) {
        console.error('createPartyエラー:', error);
        throw error;
    }
}

// ユーザーのパーティーを取得
async function getPartiesByUser(userId) {
    try {
        // ユーザーが参加しているパーティーIDを取得
        const { data: partyMembers, error: membersError } = await supabase
            .from('party_members')
            .select('party_id')
            .eq('user_id', userId);

        if (membersError) {
            throw membersError;
        }

        if (!partyMembers || partyMembers.length === 0) {
            return [];
        }

        const partyIds = partyMembers.map(pm => pm.party_id);

        // パーティー情報を取得
        const { data: parties, error: partiesError } = await supabase
            .from('parties')
            .select(`
                *,
                post:posts(*)
            `)
            .in('id', partyIds)
            .order('created_at', { ascending: false });

        if (partiesError) {
            throw partiesError;
        }

        // 各パーティーのメンバー情報を取得
        const partiesWithMembers = await Promise.all(
            parties.map(async (party) => {
                const { data: members, error: membersError } = await supabase
                    .from('party_members')
                    .select(`
                        user_id,
                        user:users(*)
                    `)
                    .eq('party_id', party.id);

                if (membersError) {
                    console.error('メンバー取得エラー:', membersError);
                    return { ...party, members: [] };
                }

                return {
                    ...party,
                    members: members.map(m => m.user).filter(Boolean)
                };
            })
        );

        return partiesWithMembers;
    } catch (error) {
        throw error;
    }
}

// パーティーIDでパーティーを取得
async function getPartyById(partyId) {
    try {
        // パーティー基本情報を取得
        const { data: party, error: partyError } = await supabase
            .from('parties')
            .select(`
                *,
                post:posts(*)
            `)
            .eq('id', partyId)
            .single();

        if (partyError || !party) {
            return null;
        }

        // パーティーメンバー情報を取得
        const { data: members, error: membersError } = await supabase
            .from('party_members')
            .select(`
                *,
                user:users(*)
            `)
            .eq('party_id', partyId);

        if (membersError) {
            console.error('メンバー取得エラー:', membersError);
            party.members = [];
        } else {
            // メンバー情報を整形
            party.members = members.map(member => ({
                id: member.user.id,
                username: member.user.username,
                display_name: member.user.display_name,
                summoner_name: member.user.summoner_name,
                summoner_info: member.user.summoner_info,
                joined_at: member.joined_at
            }));
        }

        return party;
    } catch (error) {
        console.error('パーティー取得エラー:', error);
        return null;
    }
}

// パーティーを更新
async function updateParty(partyId, updateData) {
    const { data, error } = await supabase
        .from('parties')
        .update(updateData)
        .eq('id', partyId)
        .select()
        .single();

    if (error) {
        throw error;
    }

    return data;
}

// チャットメッセージを作成
async function createChatMessage(messageData) {
    try {
        console.log('createChatMessage呼び出し:', messageData);
        
        // まず、chat_messagesテーブルの構造を確認
        console.log('chat_messagesテーブルの構造を確認中...');
        const { data: tableInfo, error: tableError } = await supabase
            .from('chat_messages')
            .select('*')
            .limit(0);
        
        if (tableError) {
            console.error('テーブル構造確認エラー:', tableError);
        } else {
            console.log('テーブル構造確認成功');
        }
        
        // 一般的なカラム名のパターンを試す
        const insertData = {
            party_id: messageData.partyId,
            content: messageData.content
        };
        
        // user_id, sender_id, author_id のいずれかを試す
        if (messageData.userId) {
            // まず user_id を試す
            try {
                const testData = { ...insertData, user_id: messageData.userId };
                console.log('user_idで挿入を試行:', testData);
                
                const { data, error } = await supabase
                    .from('chat_messages')
                    .insert(testData)
                    .select()
                    .single();

                if (error) {
                    console.log('user_idで失敗、sender_idを試行');
                    // sender_id を試す
                    const testData2 = { ...insertData, sender_id: messageData.userId };
                    const { data: data2, error: error2 } = await supabase
                        .from('chat_messages')
                        .insert(testData2)
                        .select()
                        .single();
                    
                    if (error2) {
                        console.log('sender_idで失敗、author_idを試行');
                        // author_id を試す
                        const testData3 = { ...insertData, author_id: messageData.userId };
                        const { data: data3, error: error3 } = await supabase
                            .from('chat_messages')
                            .insert(testData3)
                            .select()
                            .single();
                        
                        if (error3) {
                            throw new Error(`カラム名が見つかりません: user_id, sender_id, author_id すべて失敗`);
                        }
                        
                        console.log('author_idで成功:', data3);
                        return data3;
                    }
                    
                    console.log('sender_idで成功:', data2);
                    return data2;
                }

                console.log('user_idで成功:', data);
                return data;
            } catch (error) {
                console.error('createChatMessageエラー:', error);
                throw error;
            }
        } else {
            throw new Error('userIdが提供されていません');
        }
    } catch (error) {
        console.error('createChatMessageエラー:', error);
        throw error;
    }
}

// パーティーのチャットメッセージを取得
async function getChatMessages(partyId) {
    try {
        console.log('getChatMessages呼び出し - パーティーID:', partyId);
        
        // sender_idカラムを使用してメッセージを取得
        const { data, error } = await supabase
            .from('chat_messages')
            .select(`
                *,
                user:users!chat_messages_sender_id_fkey(*)
            `)
            .eq('party_id', partyId)
            .order('created_at', { ascending: true });

        if (error) {
            console.error('チャットメッセージ取得エラー:', error);
            throw error;
        }

        console.log('取得したメッセージ数:', data ? data.length : 0);
        console.log('メッセージ詳細:', data);
        
        return data;
    } catch (error) {
        console.error('getChatMessagesエラー:', error);
        throw error;
    }
}

// Discord IDでユーザーを取得
async function getUserByDiscordId(discordId) {
    const { data, error } = await supabase
        .from('users')
        .select('*')
        .eq('discord_id', discordId)
        .single();

    if (error) {
        return null;
    }

    return data;
}

// Discordユーザーを作成
async function createDiscordUser(discordUser) {
    const { data, error } = await supabase
        .from('users')
        .insert({
            discord_id: discordUser.discord_id,
            username: discordUser.username,
            display_name: discordUser.display_name,
            email: discordUser.email,
            avatar: discordUser.avatar
        })
        .select()
        .single();

    if (error) {
        throw error;
    }

    return data;
}

// ユーザープロフィールを更新
async function updateUserProfile(userId, updateData) {
    const { data, error } = await supabase
        .from('users')
        .update(updateData)
        .eq('id', userId)
        .select()
        .single();

    if (error) {
        throw error;
    }

    return data;
}

module.exports = {
    isConnected,
    createUser,
    getUserByUsername,
    getUserById,
    updateUser,
    createPost,
    getAllPosts,
    getPostById,
    deletePost,
    createRequest,
    getRequestsByUser,
    getRequestById,
    getRequestByPostAndApplicant,
    updateRequest,
    createParty,
    getPartiesByUser,
    getPartyById,
    updateParty,
    createChatMessage,
    getChatMessages,
    setAuthToken,
    getUserByDiscordId,
    createDiscordUser,
    updateUserProfile
}; 