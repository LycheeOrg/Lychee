<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => '写真の追加、移動、削除時にトリガーされる送信 Webhook を設定します。',

    // Empty state
    'no_webhooks' => 'Webhook はまだ設定されていません。',
    'create_first' => '最初の Webhook を作成',

    // Table columns
    'col_name' => '名前',
    'col_event' => 'イベント',
    'col_method' => 'メソッド',
    'col_url' => 'URL',
    'col_format' => '形式',
    'col_enabled' => '有効',
    'col_actions' => '操作',

    // Event labels
    'event_photo_add' => '写真の追加',
    'event_photo_move' => '写真の移動',
    'event_photo_delete' => '写真の削除',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'クエリ文字列',

    // Buttons
    'create' => 'Webhook を作成',
    'edit' => '編集',
    'delete' => '削除',
    'cancel' => 'キャンセル',
    'save' => '保存',

    // Form fields
    'field_name' => '名前',
    'field_name_placeholder' => '例: My Webhook',
    'field_event' => 'イベント',
    'field_method' => 'HTTP メソッド',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'ペイロード形式',
    'field_enabled' => '有効',
    'field_secret' => 'シークレット',
    'field_secret_placeholder' => '既存のシークレットを維持する場合は空欄のままにしてください',
    'field_secret_header' => 'シークレットヘッダー',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => '写真 ID を送信',
    'field_send_album_id' => 'アルバム ID を送信',
    'field_send_title' => 'タイトルを送信',
    'field_send_size_variants' => 'サイズバリアントを送信',

    // Modal titles
    'modal_create_title' => 'Webhook を作成',
    'modal_edit_title' => 'Webhook を編集',

    // Delete confirmation
    'confirm_delete_header' => 'Webhook を削除',
    'confirm_delete_message' => 'Webhook ":name" を削除してもよろしいですか？この操作は元に戻せません。',
    'delete_warning' => 'この操作は元に戻せません。',

    // Toasts
    'created' => 'Webhook を作成しました。',
    'updated' => 'Webhook を更新しました。',
    'deleted' => 'Webhook を削除しました。',
    'error_load' => 'Webhook を読み込めませんでした。',
    'error_save' => 'Webhook を保存できませんでした。',
    'error_delete' => 'Webhook を削除できませんでした。',

    // Secret badge
    'has_secret' => 'シークレット設定済み',
    'no_secret' => 'シークレットなし',
];
