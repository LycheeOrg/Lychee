<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'v8 前端实际所用令牌和组件的实时参考。本页的每个色板和控件都读取当前实际使用的 CSS 自定义属性和 Nuxt UI 组件，因此不会像静态样稿那样过时。',

	'sections' => [
		'foundations' => '基础',
		'colors' => '调色板',
		'typography' => '排版',
		'buttons' => '按钮',
		'badges' => '徽章与标签',
		'forms' => '表单控件',
		'feedback' => '反馈与状态',
		'radius' => '圆角与层级',
	],

	'loading' => [
		'full_page' => '全页加载器',
		'full_page_description' => '缓慢的交错显现——在视图加载时显示于全屏遮罩之后。',
		'mini_spinner' => '迷你加载指示器',
		'mini_spinner_description' => '快速变体，立即可见——用于按钮、列表和小型加载状态的内联显示。',
	],

	'foundations' => [
		'system' => '组件库',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => '正文字体',
		'body_font_value' => 'Libre Franklin，自托管（阿拉伯语/波斯语使用 Vazirmatn）',
		'mono_font' => '等宽字体',
		'mono_font_value' => '系统等宽字体栈（ui-monospace）',
		'icons' => '图标集',
		'icons_value' => 'Lucide，通过 Iconify（UIcon）',
		'theme' => '主题',
		'theme_value' => '单一深色/浅色主题，在设置中切换——无按区域覆盖',
	],

	'colors' => [
		'primary' => '主色——品牌色、主要操作、链接、激活状态',
		'secondary' => '次色——备选操作和选中项',
		'neutral' => '中性色——文本、边框、表面（浅色模式为 Slate，深色模式为 Zinc）',
		'success' => '成功——确认、正面状态',
		'warning' => '警告——提醒、非阻塞问题',
		'error' => '错误——破坏性操作、失败',
		'info' => '信息——提示性消息',
		'resolved_from' => '实时解析自 :token',
	],

	'typography' => [
		'display' => '展示',
		'heading' => '标题',
		'body' => '正文',
		'body_sample' => '天地玄黄，宇宙洪荒——相册标题、表单标签和日常文案都以此字体栈呈现。',
		'mono' => '等宽',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'emphasis' => '文本强调层级',
		'emphasis_sample' => '往年今日拍摄的照片',
	],

	'buttons' => [
		'intro' => '按钮系统支持的所有颜色，以及应用中实际使用的各种变体。',
	],

	'forms' => [
		'input_label' => '相册标题',
		'input_placeholder' => '京都之夏',
		'select_label' => '排序方式',
		'checkbox_label' => '显示敏感相册',
		'switch_label' => '公开相册',
		'switch_description' => '任何拥有链接的人都可以查看此相册',
		'textarea_label' => '描述',
		'textarea_placeholder' => '为此相册添加描述…',
		'radio_legend' => '标签匹配逻辑',
	],

	'feedback' => [
		'alert_info' => '此相册中添加了新照片。',
		'alert_success' => '设置已保存。',
		'alert_warning' => '任何拥有链接的人都可以看到此相册。',
		'alert_error' => '无法连接服务器——更改未保存。',
		'empty_title' => '暂无共享',
		'empty_description' => '共享第一个相册后，共享的相册及其权限将显示在这里。',
	],
];
