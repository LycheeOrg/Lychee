<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => '透かしのプレビュー',
        'se_required' => '透かしモジュールを使用するには、Lychee Supporter Edition (SE) または SE Preview を有効にする必要があります。',

        'section_settings' => '透かしの設定',
        'section_preview' => 'ライブプレビュー',
        'disclaimer' => 'このプレビューは透かしの見た目の目安です。実際の写真での最終的な仕上がりは若干異なる場合があります。',

        'watermark_photo_id' => '透かし画像 ID',
        'watermark_photo_id_placeholder' => '24文字の写真 ID',
        'watermark_photo_id_hint' => '透かしとして使用する画像の写真 ID です。写真を開き、URL の末尾24文字をコピーしてください。',

        'preview_photo_id' => '背景写真 ID',
        'preview_photo_id_placeholder' => '24文字の写真 ID',
        'preview_photo_id_hint' => 'プレビューの背景として使用する写真 ID を入力してください。',

        'size' => 'サイズ (:value%)',
        'opacity' => '不透明度 (:value%)',
        'position' => '位置',
        'position_options' => [
            'top-left' => '左上',
            'top' => '上中央',
            'top-right' => '右上',
            'left' => '左中央',
            'center' => '中央',
            'right' => '右中央',
            'bottom-left' => '左下',
            'bottom' => '下中央',
            'bottom-right' => '右下',
        ],

        'section_shift' => 'シフト / オフセット',
        'shift_type' => 'シフト単位',
        'shift_type_options' => [
            'relative' => '相対 (%)',
            'absolute' => '絶対 (px)',
        ],
        'shift_type_hint' => '相対シフトは画像サイズに対する割合、絶対シフトは固定のピクセル数です。',
        'shift_mode_use_slider' => 'スライダーを使用',
        'shift_mode_use_classic' => '数値入力を使用',
        'shift_x' => '水平シフト (:value)',
        'shift_x_direction_options' => [
            'left' => '左',
            'right' => '右',
        ],
        'shift_y' => '垂直シフト (:value)',
        'shift_y_direction_options' => [
            'up' => '上',
            'down' => '下',
        ],
        'reset_to_zero' => '0 にリセット',

        'save' => '設定を保存',
        'saved' => '透かしの設定を保存しました。',
        'save_error' => '透かしの設定を保存できませんでした。',
        'save_requires_se' => '透かしの設定を保存するには、Supporter Edition (SE) の正式ライセンスが必要です。SE Preview では効果のプレビューのみ可能です。',

        'no_watermark_image' => '透かし画像が設定されていません。透かしの写真 ID を入力し、「Load」をクリックしてプレビューしてください。',
        'no_preview_photo' => '透かしのオーバーレイをプレビューするには、上に背景写真 ID を入力してください。',
        'photo_load_error' => '写真を読み込めませんでした。ID が正しく、アクセス権があることを確認してください。',
        'watermark_load_error' => '透かし画像を読み込めませんでした。写真 ID が正しいことを確認してください。',
    ],
];
