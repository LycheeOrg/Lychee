<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => '水印预览',
        'se_required' => '水印模块需要启用 Lychee 支持者版（SE）或 SE 预览版。',

        'section_settings' => '水印设置',
        'section_preview' => '实时预览',
        'disclaimer' => '此预览可让您大致了解水印的效果。实际照片上的最终效果可能略有不同。',

        'watermark_photo_id' => '水印图片 ID',
        'watermark_photo_id_placeholder' => '24 位照片 ID',
        'watermark_photo_id_hint' => '用作水印的图片的照片 ID。打开一张照片并复制 URL 中的最后 24 个字符。',

        'preview_photo_id' => '背景照片 ID',
        'preview_photo_id_placeholder' => '24 位照片 ID',
        'preview_photo_id_hint' => '输入一个照片 ID 作为预览背景。',

        'size' => '大小（:value%）',
        'opacity' => '不透明度（:value%）',
        'position' => '位置',
        'position_options' => [
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

        'section_shift' => '偏移',
        'shift_type' => '偏移单位',
        'shift_type_options' => [
            'relative' => '相对（%）',
            'absolute' => '绝对（px）',
        ],
        'shift_type_hint' => '相对偏移为图片尺寸的百分比；绝对偏移为固定的像素数。',
        'shift_mode_use_slider' => '使用滑块',
        'shift_mode_use_classic' => '使用数字输入',
        'shift_x' => '水平偏移（:value）',
        'shift_x_direction_options' => [
            'left' => '左',
            'right' => '右',
        ],
        'shift_y' => '垂直偏移（:value）',
        'shift_y_direction_options' => [
            'up' => '上',
            'down' => '下',
        ],
        'reset_to_zero' => '重置为 0',

        'save' => '保存设置',
        'saved' => '水印设置已保存。',
        'save_error' => '保存水印设置失败。',
        'save_requires_se' => '保存水印设置需要完整的支持者版（SE）许可证。SE 预览版仅允许预览效果。',

        'no_watermark_image' => '未配置水印图片。请输入水印照片 ID 并点击 "Load" 进行预览。',
        'no_preview_photo' => '请在上方输入背景照片 ID 以预览水印叠加效果。',
        'photo_load_error' => '无法加载照片。请确认 ID 正确且您有访问权限。',
        'watermark_load_error' => '无法加载水印图片。请确认照片 ID 正确。',
    ],
];
