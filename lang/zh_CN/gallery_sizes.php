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

    'title' => '图库尺寸',
    'live_title' => '图库尺寸(实时)',
    'live_hint' => '更改会应用到此面板后面的图库。不保存就关闭会放弃更改。',
    'spotlight_action' => '调整图库尺寸(实时)',
    'not_allowed' => '只有管理员可以更改图库尺寸。',
    'section_albums' => '相册',
    'section_photos' => '照片',
    'section_placeholders' => '占位块',
    'album_min_width' => '最小图块宽度',
    'album_aspect_ratio' => '宽高比',
    'albums_per_row_mobile' => '手机上每行数量',
    'layout' => '布局',
    'layouts' => [
        'justified' => '对齐',
        'masonry' => '瀑布流',
        'square' => '方形',
        'grid' => '网格',
    ],
    'gap' => '间距',
    'justified_row_height' => '行高(对齐)',
    'masonry_column_width' => '列宽(瀑布流)',
    'grid_column_width' => '列宽(网格)',
    'square_column_width' => '列宽(方形)',
    'px' => ':value px',
    'album_count' => '相册',
    'photo_count' => '照片',
    'shuffle' => '打乱',
    'album_title' => '相册 :n',
    'width' => '宽度',
    'presets' => [
        'phone' => '手机',
        'tablet' => '平板',
        'laptop' => '笔记本',
        'desktop' => '台式机',
        'wide' => '宽屏',
        'window' => '此窗口',
    ],
    'shown_at' => ':width px · 按 :scale % 显示',
    'summary' => '每行 :albums 个相册 · :rows 行照片',
    'unsaved' => '未保存的更改',
    'save' => '保存',
    'revert' => '还原',
    'close' => '关闭',
    'saved' => '已保存图库尺寸',
    'save_error' => '无法保存图库尺寸',
    'load_error' => '无法加载设置',
    'reset_hint' => '双击恢复已保存的值',
    'live_empty' => '此页面上没有使用这些尺寸的内容。',
];
