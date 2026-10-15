<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => '追加リンク',
    'description' => 'ランディングページのナビゲーションやフッターに表示する追加リンクを、任意の順序で管理します。',

    // Empty state
    'no_links' => '追加リンクはまだ設定されていません。',
    'create_first' => '最初のリンクを作成',

    // Table columns
    'col_label' => 'ラベル',
    'col_url' => 'URL',
    'col_placement' => '配置',
    'col_enabled' => '有効',
    'col_actions' => '操作',

    // Placement labels
    'placement_nav' => 'ナビゲーション',
    'placement_footer' => 'フッター',
    'placement_both' => 'ナビゲーションとフッター',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => '組み込み',
    'built_in_cannot_delete' => 'この組み込みリンクは削除できません。代わりに無効にするか、他のリンクと同様に並べ替えてください。',
    'built_in_url_hint' => 'これは組み込みリンクのため、リンク先は変更できません。',

    // Buttons
    'create' => 'リンクを作成',
    'edit' => '編集',
    'delete' => '削除',
    'cancel' => 'キャンセル',
    'save' => '保存',

    // Form fields
    'field_label' => 'ラベル',
    'field_label_placeholder' => '例: Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => '配置',
    'field_open_in_new_tab' => '新しいタブで開く',
    'field_enabled' => '有効',

    // Modal titles
    'modal_create_title' => 'リンクを作成',
    'modal_edit_title' => 'リンクを編集',

    // Delete confirmation
    'confirm_delete_header' => 'リンクを削除',
    'confirm_delete_message' => 'リンク ":label" を削除してもよろしいですか？この操作は元に戻せません。',

    // Toasts
    'created' => 'リンクを作成しました。',
    'updated' => 'リンクを更新しました。',
    'deleted' => 'リンクを削除しました。',
    'reordered' => 'リンクを並べ替えました。',
    'error_load' => 'リンクを読み込めませんでした。',
    'error_save' => 'リンクを保存できませんでした。',
    'error_delete' => 'リンクを削除できませんでした。',
    'error_reorder' => 'リンクを並べ替えできませんでした。',
];
