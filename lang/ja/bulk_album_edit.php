<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'アルバム一括編集',
    'description' => '複数のアルバムのメタデータと公開設定を一度に編集します。',
    'warning' => 'ここでの変更は即座に適用され、元に戻すことはできません。タグアルバムは表示されません。',

    // Table columns
    'col_title' => 'タイトル',
    'col_owner' => '所有者',
    'col_license' => 'ライセンス',
    'col_is_nsfw' => 'センシティブ',
    'col_is_public' => '公開',
    'col_is_link_required' => 'リンク',
    'col_grants_full_photo_access' => 'フル写真',
    'col_grants_download' => 'ダウンロード',
    'col_grants_upload' => 'アップロード',
    'col_photo_sorting' => '写真の並び順',
    'col_album_sorting' => 'アルバムの並び順',
    'col_created_at' => '作成日',

    // Filter
    'filter_placeholder' => 'タイトルで検索...',

    // Pagination
    'per_page' => '1ページあたり',
    'total_selected' => ':n 件のアルバムを選択中|:n 件のアルバムを選択中',
    'select_all_page' => 'このページをすべて選択',
    'select_all_matching' => '一致するものをすべて選択',
    'cap_warning' => '最初の1,000件のアルバムのみが選択されました。',

    // Mode toggle
    'mode_paginated' => 'ページ分割',
    'mode_infinite' => '無限スクロール',

    // Action buttons
    'action_delete' => '削除',
    'action_set_owner' => '所有者を設定',
    'action_edit_fields' => '項目を編集',

    // Edit Fields modal
    'edit_fields_title' => '項目を編集',
    'edit_fields_description' => 'チェックした項目のみが更新されます。空の値は項目をクリアします。',
    'section_metadata' => 'メタデータ',
    'section_visibility' => '公開設定',
    'field_description' => '説明',
    'field_copyright' => '著作権',
    'field_license' => 'ライセンス',
    'field_photo_layout' => '写真レイアウト',
    'field_photo_sorting_col' => '写真の並べ替え列',
    'field_photo_sorting_order' => '写真の並べ替え順',
    'field_album_sorting_col' => 'アルバムの並べ替え列',
    'field_album_sorting_order' => 'アルバムの並べ替え順',
    'field_album_thumb_aspect_ratio' => 'サムネイルのアスペクト比',
    'field_album_timeline' => 'アルバムのタイムライン',
    'field_photo_timeline' => '写真のタイムライン',
    'field_published_at' => 'Flow 公開日',
    'field_is_nsfw' => 'センシティブ',
    'field_is_public' => '公開',
    'field_is_link_required' => 'リンク必須',
    'field_grants_full_photo_access' => 'フル写真アクセス',
    'field_grants_download' => 'ダウンロード',
    'field_grants_upload' => 'アップロード (SE)',
    'apply' => '適用',
    'cancel' => 'キャンセル',

    // Set Owner modal
    'set_owner_title' => '所有者を設定',
    'set_owner_description' => '選択したすべてのアルバムはルートレベルに移動され、その下位のアルバムもあわせて移譲されます。',
    'set_owner_select_user' => '新しい所有者を選択',
    'transfer' => '移譲',

    // Delete confirmation modal
    'delete_title' => 'アルバムを削除',
    'delete_confirm' => ':count 件のアルバムと、そのすべてのサブアルバムおよび写真を完全に削除しようとしています。この操作は元に戻せません。|:count 件のアルバムと、そのすべてのサブアルバムおよび写真を完全に削除しようとしています。この操作は元に戻せません。',
    'confirm_delete' => '削除を確認',

    // Toasts
    'success_patch' => 'アルバムを更新しました。',
    'success_set_owner' => '所有権を移譲しました。',
    'success_delete' => 'アルバムを削除しました。',
    'error_load' => 'アルバムの読み込みに失敗しました。',
    'error_load_ids' => 'アルバムIDの読み込みに失敗しました。',
    'error_patch' => 'アルバムの更新に失敗しました。',
    'error_missing_timezone' => '公開日のタイムゾーンを選択してください。',
    'error_set_owner' => '所有権の移譲に失敗しました。',
    'error_delete' => 'アルバムの削除に失敗しました。',
    'error_load_users' => 'ユーザーの読み込みに失敗しました。',
];
