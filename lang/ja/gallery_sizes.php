<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Gallery sizes: preview page and live drawer (Feature 110)
    |--------------------------------------------------------------------------
    */

    'title' => 'ギャラリーのサイズ',
    'live_title' => 'ギャラリーのサイズ (ライブ)',
    'live_hint' => '変更はこのパネルの後ろのギャラリーに反映されます。保存せずに閉じると破棄されます。',
    'spotlight_action' => 'ギャラリーのサイズを調整 (ライブ)',
    'not_allowed' => 'ギャラリーのサイズを変更できるのは管理者のみです。',
    'section_albums' => 'アルバム',
    'section_photos' => '写真',
    'section_placeholders' => 'プレースホルダー',
    'album_min_width' => 'タイルの最小幅',
    'album_aspect_ratio' => 'アスペクト比',
    'albums_per_row_mobile' => 'スマートフォンでの 1 行あたり',
    'layout' => 'レイアウト',
    'layouts' => [
        'justified' => '両端揃え',
        'masonry' => 'メイソンリー',
        'square' => '正方形',
        'grid' => 'グリッド',
    ],
    'gap' => '間隔',
    'justified_row_height' => '行の高さ (両端揃え)',
    'masonry_column_width' => '列の幅 (メイソンリー)',
    'grid_column_width' => '列の幅 (グリッド)',
    'square_column_width' => '列の幅 (正方形)',
    'px' => ':value px',
    'album_count' => 'アルバム',
    'photo_count' => '写真',
    'shuffle' => 'シャッフル',
    'album_title' => 'アルバム :n',
    'width' => '幅',
    'presets' => [
        'phone' => 'スマートフォン',
        'tablet' => 'タブレット',
        'laptop' => 'ノート PC',
        'desktop' => 'デスクトップ',
        'wide' => 'ワイド',
        'window' => 'このウィンドウ',
    ],
    'shown_at' => ':width px · :scale % で表示',
    'summary' => '1 行あたり :albums アルバム · 写真 :rows 行',
    'unsaved' => '未保存の変更',
    'save' => '保存',
    'revert' => '元に戻す',
    'close' => '閉じる',
    'saved' => 'ギャラリーのサイズを保存しました',
    'save_error' => 'ギャラリーのサイズを保存できませんでした',
    'load_error' => '設定を読み込めませんでした',
    'reset_hint' => 'ダブルクリックで保存済みの値に戻します',
    'live_empty' => 'このページにはこれらのサイズを使う要素がありません。',
];
