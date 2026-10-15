<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'ランディングページ',
    'tab_settings' => '設定',
    'tab_links' => 'リンク',
    'tab_featured' => '注目',

    'section_layout' => 'レイアウトと構成',
    'section_hero' => 'ヒーロー',
    'section_background_landscape' => '背景（横向き）',
    'section_background_portrait' => '背景（縦向き）',
    'section_cta_position' => 'CTA ボタンの位置',
    'section_login_position' => 'ログインパネル',
    'section_meridian' => 'Meridian のレール',
    'section_content' => 'コンテンツ',

    'field_layout' => 'レイアウト',
    'field_intro_screen_enabled' => 'イントロのスプラッシュ画面',
    'field_hero_text_position' => 'ヒーローテキストの位置',
    'field_hero_text_color' => 'ヒーローテキストの色',
    'field_hero_text_opacity' => 'ヒーローテキストの不透明度',
    'field_animation_preset' => 'アニメーションプリセット',
    'field_backdrop_opacity' => '背景の不透明度（:value%）',
    'field_cta_text' => 'テキスト',
    'field_cta_text_placeholder' => '空欄の場合はレイアウトの既定値を使用',
    'field_about_enabled' => '概要セクション',
    'field_about_text' => '概要テキスト',

    'field_background_mode' => 'ソース',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => '写真 ID',
        'random' => 'ランダムな公開写真',
        'latest_album_cover' => '最新アルバムのカバー',
        'random_from_album' => 'アルバムからランダムな写真',
    ],
    'field_background_url' => '画像 URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => '写真 ID',
    'field_background_photo_id_placeholder' => '24文字の写真 ID',
    'field_background_photo_id_hint' => '使用する画像の写真 ID です。写真を開き、URL の最後の24文字をコピーしてください。',
    'field_background_album_id' => 'アルバム ID',
    'field_background_album_id_placeholder' => '24文字のアルバム ID',
    'background_load_error' => '写真を読み込めませんでした。ID が正しく、アクセス権があることを確認してください。',
    'background_mode_hint' => [
        'random' => 'ページを読み込むたびにランダムな公開写真が選ばれます。',
        'latest_album_cover' => '最も新しく公開された公開アルバムのカバーが使用されます。',
        'random_from_album' => 'ページを読み込むたびにこのアルバムからランダムな写真が選ばれます。プレビューは保存後にのみ更新されます。',
    ],

    'preview_orientation_landscape' => '横向き背景をプレビュー',
    'preview_orientation_portrait' => '縦向き背景をプレビュー',

    'field_cta_position' => '位置',
    'cta_position_options' => [
        'top-left' => '左上',
        'top' => '中央上',
        'top-right' => '右上',
        'left' => '左中央',
        'center' => '中央',
        'right' => '右中央',
        'bottom-left' => '左下',
        'bottom' => '中央下',
        'bottom-right' => '右下',
    ],
    'field_cta_shift_type' => '移動の単位',
    'cta_shift_type_options' => [
        'relative' => '相対（%）',
        'absolute' => '絶対（px）',
    ],
    'cta_shift_type_hint' => '相対移動はビューポートサイズに対する割合、絶対移動は固定のピクセル数です。',
    'field_cta_shift_x' => '水平方向の移動（:value）',
    'cta_shift_x_direction_options' => [
        'left' => '左',
        'right' => '右',
    ],
    'field_cta_shift_y' => '垂直方向の移動（:value）',
    'cta_shift_y_direction_options' => [
        'up' => '上',
        'down' => '下',
    ],
    'reset_to_zero' => '0 にリセット',

    'field_login_position' => '位置',
    'login_position_options' => [
        'side' => '横',
        'center' => '中央',
    ],
    'field_login_position_hint' => '狭い画面では、この設定にかかわらず、ログインパネルは常にヒーローの下に中央揃えで表示されます。アニメーションプリセットが "Slide reveal" または "Parallax scroll" の場合、読み方向の端からスライドインします。',

    'field_meridian_explore_offset' => 'Explore ラベルの高さ（:value%）',
    'field_meridian_contact_offset' => 'Contact ラベルの高さ（:value%）',
    'field_meridian_offset_hint' => '全高の各レール上でラベルをどの位置に置くか：0% が上端、100% が下端です。',
    'field_meridian_explore_line_position' => 'Explore レールの位置（:value%）',
    'field_meridian_contact_line_position' => 'Contact レールの位置（:value%）',
    'field_meridian_line_position_hint' => '各レールの左端からの位置：0% が左端、100% が右端です。Contact レールがオフの場合、Explore レールは常に中央に配置されます。',

    'preview_title' => 'ライブプレビュー',
    'preview_hint' => '編集内容は即座に反映されます。「保存」をクリックするまで何も保存されません。',
    'flat_list_hint' => 'これらの設定は、一般的な設定一覧からも編集できます。',

    'save' => '保存',
    'saved' => 'ランディングページの設定を保存しました。',
    'save_error' => 'ランディングページの設定の保存に失敗しました。',

    'se_required' => 'このレイアウト/プリセットには Lychee SE が必要です。',
];
