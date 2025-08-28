<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは正しいURLではありません。',
    'after' => ':attributeは:dateより後の日付にしてください。',
    'after_or_equal' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeは英字のみで入力してください。',
    'alpha_dash' => ':attributeは英数字・ハイフン・アンダースコアのみで入力してください。',
    'alpha_num' => ':attributeは英数字のみで入力してください。',
    'array' => ':attributeは配列にしてください。',
    'ascii' => ':attributeは英数字と記号のみで入力してください。',
    'before' => ':attributeは:dateより前の日付にしてください。',
    'before_or_equal' => ':attributeは:date以前の日付にしてください。',
    'between' => [
        'array' => ':attributeは:min個から:max個の間で入力してください。',
        'file' => ':attributeは:minキロバイトから:maxキロバイトの間で入力してください。',
        'numeric' => ':attributeは:minから:maxの間で入力してください。',
        'string' => ':attributeは:min文字から:max文字の間で入力してください。',
    ],
    'boolean' => ':attributeは真偽値を入力してください。',
    'can_login' => '提供された認証情報でログインできません。',
    'confirmed' => ':attributeが確認用と一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは正しい日付ではありません。',
    'date_equals' => ':attributeは:dateと等しい日付にしてください。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'decimal' => ':attributeは:decimal桁の小数で入力してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherは異なる値にしてください。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min桁から:max桁の間で入力してください。',
    'dimensions' => ':attributeの画像サイズが正しくありません。',
    'distinct' => ':attributeの値が重複しています。',
    'doesnt_end_with' => ':attributeは:valuesのいずれかで終わらないようにしてください。',
    'doesnt_start_with' => ':attributeは:valuesのいずれかで始まらないようにしてください。',
    'email' => ':attributeは正しいメールアドレス形式で入力してください。',
    'ends_with' => ':attributeは:valuesのいずれかで終わるようにしてください。',
    'enum' => '選択された:attributeは正しくありません。',
    'exists' => '選択された:attributeは正しくありません。',
    'extensions' => ':attributeは以下の拡張子のいずれかである必要があります: :extensions。',
    'file' => ':attributeはファイルにしてください。',
    'filled' => ':attributeは必須項目です。',
    'gt' => [
        'array' => ':attributeは:value個より多く入力してください。',
        'file' => ':attributeは:valueキロバイトより大きくしてください。',
        'numeric' => ':attributeは:valueより大きくしてください。',
        'string' => ':attributeは:value文字より多く入力してください。',
    ],
    'gte' => [
        'array' => ':attributeは:value個以上入力してください。',
        'file' => ':attributeは:valueキロバイト以上にしてください。',
        'numeric' => ':attributeは:value以上にしてください。',
        'string' => ':attributeは:value文字以上入力してください。',
    ],
    'hex_color' => ':attributeは正しい16進色コードで入力してください。',
    'image' => ':attributeは画像にしてください。',
    'in' => '選択された:attributeは正しくありません。',
    'in_array' => ':attributeは:otherに存在しません。',
    'integer' => ':attributeは整数で入力してください。',
    'ip' => ':attributeは正しいIPアドレスで入力してください。',
    'ipv4' => ':attributeは正しいIPv4アドレスで入力してください。',
    'ipv6' => ':attributeは正しいIPv6アドレスで入力してください。',
    'json' => ':attributeは正しいJSON形式で入力してください。',
    'lowercase' => ':attributeは小文字で入力してください。',
    'lt' => [
        'array' => ':attributeは:value個より少なく入力してください。',
        'file' => ':attributeは:valueキロバイトより小さくしてください。',
        'numeric' => ':attributeは:valueより小さくしてください。',
        'string' => ':attributeは:value文字より少なく入力してください。',
    ],
    'lte' => [
        'array' => ':attributeは:value個以下で入力してください。',
        'file' => ':attributeは:valueキロバイト以下にしてください。',
        'numeric' => ':attributeは:value以下にしてください。',
        'string' => ':attributeは:value文字以下で入力してください。',
    ],
    'mac_address' => ':attributeは正しいMACアドレスで入力してください。',
    'max' => [
        'array' => ':attributeは:max個以下で入力してください。',
        'file' => ':attributeは:maxキロバイト以下で入力してください。',
        'numeric' => ':attributeは:max以下で入力してください。',
        'string' => ':attributeは:max文字以下で入力してください。',
    ],
    'max_digits' => ':attributeは:max桁以下で入力してください。',
    'mimes' => ':attributeは:valuesタイプのファイルにしてください。',
    'mimetypes' => ':attributeは:valuesタイプのファイルにしてください。',
    'min' => [
        'array' => ':attributeは:min個以上で入力してください。',
        'file' => ':attributeは:minキロバイト以上で入力してください。',
        'numeric' => ':attributeは:min以上で入力してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'min_digits' => ':attributeは:min桁以上で入力してください。',
    'missing' => ':attributeが不足しています。',
    'missing_if' => ':otherが:valueの場合、:attributeが不足しています。',
    'missing_unless' => ':otherが:valueでない場合、:attributeが不足しています。',
    'missing_with' => ':valuesが存在する場合、:attributeが不足しています。',
    'missing_with_all' => ':valuesがすべて存在する場合、:attributeが不足しています。',
    'multiple_of' => ':attributeは:valueの倍数で入力してください。',
    'not_in' => '選択された:attributeは正しくありません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeは数値で入力してください。',
    'password' => [
        'letters' => ':attributeには少なくとも1つの文字が含まれている必要があります。',
        'mixed' => ':attributeには少なくとも1つの大文字と1つの小文字が含まれている必要があります。',
        'numbers' => ':attributeには少なくとも1つの数字が含まれている必要があります。',
        'symbols' => ':attributeには少なくとも1つの記号が含まれている必要があります。',
        'uncompromised' => '指定された:attributeがデータ漏洩に表示されています。',
    ],
    'present' => ':attributeが存在する必要があります。',
    'present_if' => ':otherが:valueの場合、:attributeが存在する必要があります。',
    'present_unless' => ':otherが:valueでない場合、:attributeが存在する必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeが存在する必要があります。',
    'present_with_all' => ':valuesがすべて存在する場合、:attributeが存在する必要があります。',
    'prohibited' => ':attributeフィールドは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeフィールドは禁止されています。',
    'prohibited_unless' => ':otherが:valuesに含まれていない場合、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeフィールドは:otherが存在することを禁止しています。',
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeは必須項目です。',
    'required_array_keys' => ':attributeフィールドには以下のエントリが含まれている必要があります: :values。',
    'required_if' => ':otherが:valueの場合、:attributeは必須項目です。',
    'required_if_accepted' => ':otherが承認された場合、:attributeは必須項目です。',
    'required_unless' => ':otherが:valuesに含まれていない場合、:attributeは必須項目です。',
    'required_with' => ':valuesが存在する場合、:attributeは必須項目です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeは必須項目です。',
    'required_without' => ':valuesが存在しない場合、:attributeは必須項目です。',
    'required_without_all' => ':valuesがすべて存在しない場合、:attributeは必須項目です。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'array' => ':attributeは:size個で入力してください。',
        'file' => ':attributeは:sizeキロバイトで入力してください。',
        'numeric' => ':attributeは:sizeで入力してください。',
        'string' => ':attributeは:size文字で入力してください。',
    ],
    'starts_with' => ':attributeは:valuesのいずれかで始まるようにしてください。',
    'string' => ':attributeは文字列で入力してください。',
    'timezone' => ':attributeは正しいタイムゾーンで入力してください。',
    'unique' => 'この:attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字で入力してください。',
    'url' => ':attributeは正しいURL形式で入力してください。',
    'ulid' => ':attributeは正しいULIDで入力してください。',
    'uuid' => ':attributeは正しいUUIDで入力してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
        'summoner_name' => 'サモナー名',
        'summoner_level' => 'サモナーレベル',
        'puuid' => 'PUUID',
        'solo_rank' => 'ソロランク',
        'flex_rank' => 'フレックスランク',
        'summoner_icon' => 'サモナーアイコン',
        'recruitment_id' => '募集ID',
        'applicant_id' => '申請者ID',
        'preferred_lane' => '希望レーン',
        'message' => 'メッセージ',
        'status' => 'ステータス',
        'party_id' => 'パーティーID',
        'user_id' => 'ユーザーID',
        'role' => '役割',
        'content' => '内容',
        'type' => 'タイプ',
        'data' => 'データ',
        'read_at' => '既読日時',
    ],

];
