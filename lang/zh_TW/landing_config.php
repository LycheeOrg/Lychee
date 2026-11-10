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

    'title' => '登陸頁面',
    'tab_settings' => '設定',
    'tab_links' => '連結',
    'tab_featured' => '精選',

    'section_layout' => '版面配置與結構',
    'section_hero' => '主視覺',
    'section_background_landscape' => '背景（橫向）',
    'section_background_portrait' => '背景（直向）',
    'section_cta_position' => '行動呼籲按鈕位置',
    'section_login_position' => '登入面板',
    'section_meridian' => 'Meridian 直線',
    'section_content' => '內容',

    'field_layout' => '版面配置',
    'field_intro_screen_enabled' => '開場畫面',
    'field_hero_text_position' => '主視覺文字位置',
    'field_hero_text_color' => '主視覺文字顏色',
    'field_hero_text_opacity' => '主視覺文字不透明度',
    'field_animation_preset' => '動畫預設',
    'field_backdrop_opacity' => '背景遮罩不透明度（:value%）',
    'field_cta_text' => '文字',
    'field_cta_text_placeholder' => '留空則使用版面配置預設值',
    'field_about_enabled' => '關於區塊',
    'field_about_text' => '關於文字',

    'field_background_mode' => '來源',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => '照片 ID',
        'random' => '隨機公開照片',
        'latest_album_cover' => '最新相簿封面',
        'random_from_album' => '相簿中的隨機照片',
    ],
    'field_background_url' => '圖片 URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => '照片 ID',
    'field_background_photo_id_placeholder' => '24 個字元的照片 ID',
    'field_background_photo_id_hint' => '要使用之圖片的照片 ID。開啟照片並複製 URL 的最後 24 個字元。',
    'field_background_album_id' => '相簿 ID',
    'field_background_album_id_placeholder' => '24 個字元的相簿 ID',
    'background_load_error' => '無法載入照片。請確認 ID 正確且您有存取權限。',
    'background_mode_hint' => [
        'random' => '每次載入頁面時都會隨機挑選一張公開照片。',
        'latest_album_cover' => '使用最近發布之公開相簿的封面。',
        'random_from_album' => '每次載入頁面時都會從此相簿隨機挑選一張照片。預覽僅會在儲存後更新。',
    ],

    'preview_orientation_landscape' => '預覽橫向背景',
    'preview_orientation_portrait' => '預覽直向背景',

    'field_cta_position' => '位置',
    'cta_position_options' => [
        'top-left' => '左上',
        'top' => '上方置中',
        'top-right' => '右上',
        'left' => '左側中間',
        'center' => '置中',
        'right' => '右側中間',
        'bottom-left' => '左下',
        'bottom' => '下方置中',
        'bottom-right' => '右下',
    ],
    'field_cta_shift_type' => '位移單位',
    'cta_shift_type_options' => [
        'relative' => '相對（%）',
        'absolute' => '絕對（px）',
    ],
    'cta_shift_type_hint' => '相對位移為檢視區大小的百分比；絕對位移為固定的像素數。',
    'field_cta_shift_x' => '水平位移（:value）',
    'cta_shift_x_direction_options' => [
        'left' => '左',
        'right' => '右',
    ],
    'field_cta_shift_y' => '垂直位移（:value）',
    'cta_shift_y_direction_options' => [
        'up' => '上',
        'down' => '下',
    ],
    'reset_to_zero' => '重設為 0',

    'field_login_position' => '位置',
    'login_position_options' => [
        'side' => '側邊',
        'center' => '置中',
    ],
    'field_login_position_hint' => '在窄螢幕上，無論此設定為何，登入面板一律置中顯示於主視覺下方。當動畫預設為 "Slide reveal" 或 "Parallax scroll" 時，面板會從閱讀方向的邊緣滑入。',

    'field_meridian_explore_offset' => '探索標籤高度（:value%）',
    'field_meridian_contact_offset' => '聯絡標籤高度（:value%）',
    'field_meridian_offset_hint' => '各條全高直線上標籤所在的垂直位置：0% 為頂端，100% 為底端。',
    'field_meridian_explore_line_position' => '探索直線位置（:value%）',
    'field_meridian_contact_line_position' => '聯絡直線位置（:value%）',
    'field_meridian_line_position_hint' => '各條直線與左側邊緣的距離：0% 為左側邊緣，100% 為右側邊緣。關閉聯絡直線時，探索直線一律自動置中。',

    'preview_title' => '即時預覽',
    'preview_hint' => '編輯時即時更新 — 點擊「儲存」前不會儲存任何內容。',
    'flat_list_hint' => '這些設定也可以在一般的設定清單中編輯。',

    'save' => '儲存',
    'saved' => '已儲存登陸頁面設定。',
    'save_error' => '無法儲存登陸頁面設定。',

    'se_required' => '此版面配置／預設需要 Lychee SE。',
];
