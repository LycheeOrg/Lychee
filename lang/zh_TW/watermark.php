<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => '浮水印預覽',
        'se_required' => '浮水印模組需要啟用 Lychee 贊助者版本 (SE) 或 SE 預覽。',

        'section_settings' => '浮水印設定',
        'section_preview' => '即時預覽',
        'disclaimer' => '此預覽可讓您大致了解浮水印的外觀，實際相片上的最終效果可能略有不同。',

        'watermark_photo_id' => '浮水印圖片 ID',
        'watermark_photo_id_placeholder' => '24 個字元的相片 ID',
        'watermark_photo_id_hint' => '作為浮水印的圖片之相片 ID。開啟相片後，從網址複製最後 24 個字元。',

        'preview_photo_id' => '背景相片 ID',
        'preview_photo_id_placeholder' => '24 個字元的相片 ID',
        'preview_photo_id_hint' => '輸入要作為預覽背景的相片 ID。',

        'size' => '大小（:value%）',
        'opacity' => '不透明度（:value%）',
        'position' => '位置',
        'position_options' => [
            'top-left' => '左上',
            'top' => '上方置中',
            'top-right' => '右上',
            'left' => '左側置中',
            'center' => '置中',
            'right' => '右側置中',
            'bottom-left' => '左下',
            'bottom' => '下方置中',
            'bottom-right' => '右下',
        ],

        'section_shift' => '位移／偏移',
        'shift_type' => '位移單位',
        'shift_type_options' => [
            'relative' => '相對（%）',
            'absolute' => '絕對（px）',
        ],
        'shift_type_hint' => '相對位移以圖片尺寸的百分比計算；絕對位移則為固定的像素數。',
        'shift_mode_use_slider' => '使用滑桿',
        'shift_mode_use_classic' => '使用數字輸入',
        'shift_x' => '水平位移（:value）',
        'shift_x_direction_options' => [
            'left' => '左',
            'right' => '右',
        ],
        'shift_y' => '垂直位移（:value）',
        'shift_y_direction_options' => [
            'up' => '上',
            'down' => '下',
        ],
        'reset_to_zero' => '重設為 0',

        'save' => '儲存設定',
        'saved' => '浮水印設定已儲存。',
        'save_error' => '儲存浮水印設定失敗。',
        'save_requires_se' => '儲存浮水印設定需要完整的贊助者版本 (SE) 授權。SE 預覽僅能預覽效果。',

        'no_watermark_image' => '尚未設定浮水印圖片。請輸入浮水印相片 ID 並點擊 "Load" 進行預覽。',
        'no_preview_photo' => '請在上方輸入背景相片 ID 以預覽浮水印疊加效果。',
        'photo_load_error' => '無法載入相片。請確認 ID 正確且您有存取權限。',
        'watermark_load_error' => '無法載入浮水印圖片。請確認相片 ID 正確。',
    ],
];
