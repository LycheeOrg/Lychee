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

    'title' => '首页',
    'tab_settings' => '设置',
    'tab_links' => '链接',
    'tab_featured' => '精选',

    'section_layout' => '布局与结构',
    'section_hero' => '主视觉区',
    'section_background_landscape' => '背景（横向）',
    'section_background_portrait' => '背景（纵向）',
    'section_cta_position' => '行动号召按钮位置',
    'section_login_position' => '登录面板',
    'section_meridian' => 'Meridian 竖线',
    'section_content' => '内容',

    'field_layout' => '布局',
    'field_intro_screen_enabled' => '开场启动画面',
    'field_hero_text_position' => '主视觉文字位置',
    'field_hero_text_color' => '主视觉文字颜色',
    'field_hero_text_opacity' => '主视觉文字不透明度',
    'field_animation_preset' => '动画预设',
    'field_backdrop_opacity' => '背景遮罩不透明度（:value%）',
    'field_cta_text' => '文字',
    'field_cta_text_placeholder' => '留空则使用布局默认值',
    'field_about_enabled' => '关于部分',
    'field_about_text' => '关于文字',

    'field_background_mode' => '来源',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => '照片 ID',
        'random' => '随机公开照片',
        'latest_album_cover' => '最新相册封面',
        'random_from_album' => '相册中的随机照片',
    ],
    'field_background_url' => '图片 URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => '照片 ID',
    'field_background_photo_id_placeholder' => '24 个字符的照片 ID',
    'field_background_photo_id_hint' => '要使用的图片的照片 ID。打开一张照片，并复制 URL 中的最后 24 个字符。',
    'field_background_album_id' => '相册 ID',
    'field_background_album_id_placeholder' => '24 个字符的相册 ID',
    'background_load_error' => '无法加载照片。请确保 ID 正确且您有访问权限。',
    'background_mode_hint' => [
        'random' => '每次加载页面时随机选取一张公开照片。',
        'latest_album_cover' => '使用最近发布的公开相册的封面。',
        'random_from_album' => '每次加载页面时从此相册中随机选取一张照片。预览仅在保存后更新。',
    ],

    'preview_orientation_landscape' => '预览横向背景',
    'preview_orientation_portrait' => '预览纵向背景',

    'field_cta_position' => '位置',
    'cta_position_options' => [
        'top-left' => '左上',
        'top' => '顶部居中',
        'top-right' => '右上',
        'left' => '左侧居中',
        'center' => '居中',
        'right' => '右侧居中',
        'bottom-left' => '左下',
        'bottom' => '底部居中',
        'bottom-right' => '右下',
    ],
    'field_cta_shift_type' => '偏移单位',
    'cta_shift_type_options' => [
        'relative' => '相对（%）',
        'absolute' => '绝对（px）',
    ],
    'cta_shift_type_hint' => '相对偏移为视口尺寸的百分比；绝对偏移为固定的像素数。',
    'field_cta_shift_x' => '水平偏移（:value）',
    'cta_shift_x_direction_options' => [
        'left' => '左',
        'right' => '右',
    ],
    'field_cta_shift_y' => '垂直偏移（:value）',
    'cta_shift_y_direction_options' => [
        'up' => '上',
        'down' => '下',
    ],
    'reset_to_zero' => '重置为 0',

    'field_login_position' => '位置',
    'login_position_options' => [
        'side' => '侧边',
        'center' => '居中',
    ],
    'field_login_position_hint' => '在窄屏幕上，无论此设置如何，登录面板始终居中显示在主视觉区下方。当动画预设为 "Slide reveal" 或 "Parallax scroll" 时，它会从阅读方向的边缘滑入。',

    'field_meridian_explore_offset' => '探索标签高度（:value%）',
    'field_meridian_contact_offset' => '联系标签高度（:value%）',
    'field_meridian_offset_hint' => '每条全高竖线上标签所处的位置：0% 为顶部，100% 为底部。',
    'field_meridian_explore_line_position' => '探索竖线位置（:value%）',
    'field_meridian_contact_line_position' => '联系竖线位置（:value%）',
    'field_meridian_line_position_hint' => '每条竖线距左边缘的位置：0% 为左边缘，100% 为右边缘。当联系竖线关闭时，探索竖线始终自动居中。',

    'preview_title' => '实时预览',
    'preview_hint' => '编辑时即时更新 — 点击保存之前不会保存任何内容。',
    'flat_list_hint' => '这些设置也可以在通用的平铺设置列表中编辑。',

    'save' => '保存',
    'saved' => '首页设置已保存。',
    'save_error' => '保存首页设置失败。',

    'se_required' => '此布局/预设需要 Lychee SE。',
];
