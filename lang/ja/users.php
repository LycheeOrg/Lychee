<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'ユーザー',
    'description' => 'ここでは Lychee インストールのユーザーを管理できます。ユーザーの作成、編集、削除が可能です。',
    'create' => '新しいユーザーを作成',
    'username' => 'ユーザー名',
    'password' => 'パスワード',
    'legend' => '凡例',
    'upload_rights' => '選択すると、ユーザーはコンテンツをアップロードできます。',
    'edit_rights' => '選択すると、ユーザーは自分のプロフィール（ユーザー名、パスワード）を変更できます。',
    'upload_trust_level' => 'アップロード信頼レベル — アップロードを直ちに公開するかどうかを制御します。',

    'quota' => '設定すると、ユーザーには写真用の容量クォータ（kB 単位）が適用されます。',
    'user_deleted' => 'ユーザーを削除しました',
    'user_created' => 'ユーザーを作成しました',
    'user_updated' => 'ユーザーを更新しました',
    'change_saved' => '変更を保存しました！',
    'create_edit' => [
        'upload_rights' => 'ユーザーはコンテンツをアップロードできます。',
        'edit_rights' => 'ユーザーは自分のプロフィール（ユーザー名、パスワード）を変更できます。',
        'admin_rights' => 'ユーザーに管理者権限があります。',
        'upload_trust_level' => 'アップロード信頼レベル',
        'upload_trust_level_check' => '確認 – アップロードは公開前に管理者の承認が必要です。',
        'upload_trust_level_monitor' => '監視 – コンテンツでフラグが立てられない限り、アップロードは公開されます。',
        'upload_trust_level_trust_but_verify' => '信頼しつつ検証 – アップロードは公開されます。要確認の検出結果は自動承認され、ブロックの検出結果の扱いは設定可能です。',
        'upload_trust_level_trusted' => '信頼済み – アップロードは直ちに公開されます。',
        'trust_level_options' => [
            'trusted' => '信頼済み',
            'trust_but_verify' => '信頼しつつ検証',
            'monitor' => '監視',
            'check' => '確認',
        ],
        'quota' => 'ユーザーにクォータ制限があります。',
        'quota_kb' => 'クォータ（kB、0 でデフォルト）',
        'note' => '管理者メモ（公開されません）',
        'create' => '作成',
        'edit' => '編集',
    ],
    'invite' => [
        'button' => 'ユーザーを招待',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'このリンクは %d 日間有効です。',
    ],
    'line' => [
        'owner' => '所有者',
        'admin' => '管理者ユーザー',
        'edit' => '編集',
        'delete' => '削除',
    ],
];
