<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Fix-tree Page
	|--------------------------------------------------------------------------
	*/
	'title' => '维护',
	'intro' => '此页面允许您手动重新排序和修复相册。<br />在进行任何修改之前，我们强烈建议您了解嵌套集合树结构的相关知识。',
	'warning' => '在此页面的操作可能会严重影响您的 Lychee 安装，请自行承担修改值的风险。',

	'help' => [
		'header' => '帮助',
		'hover' => '将鼠标悬停在 ID 或标题上可以高亮显示相关相册。',
		'left' => '<span class="text-muted-color-emphasis font-bold">左值</span>',
		'right' => '<span class="text-muted-color-emphasis font-bold">右值</span>',
		'convenience' => '为了方便起见，<i class="pi pi-angle-up"></i> 和 <i class="pi pi-angle-down"></i> 按钮允许您分别将 %s 和 %s 的值增加和减少 1，并进行传播。',
		'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left"></i> 和 <i class="text-warning-600 pi pi-chevron-circle-right"></i> 表示 %s（以及 %s）的值在其他位置重复。',
		'parent-marked' => '标记为 <span class="font-bold text-danger-600">父级 ID</span> 表示 %s 和 %s 不符合嵌套集合树结构。请编辑 <span class="font-bold text-danger-600">父级 ID</span> 或 %s/%s 的值。',
		'slowness' => '当相册数量较多时，此页面的加载速度会较慢。',
	],

	'buttons' => [
		'reset' => '重置',
		'check' => '检查',
		'apply' => '应用',
	],

	'table' => [
		'title' => '标题',
		'left' => '左值',
		'right' => '右值',
		'id' => 'ID',
		'parent' => '父级 ID',
	],

	'errors' => [
		'invalid' => '无效的树结构！',
		'invalid_details' => '我们不会应用这些更改，因为这会导致系统进入错误状态。',
		'invalid_left' => '相册 %s 的左值无效。',
		'invalid_right' => '相册 %s 的右值无效。',
		'invalid_left_right' => '相册 %s 的左值/右值无效。左值必须严格小于右值：%s < %s。',
		'duplicate_left' => '相册 %s 的左值 %s 重复。',
		'duplicate_right' => '相册 %s 的右值 %s 重复。',
		'parent' => '相册 %s 的父级 ID %s 异常。',
		'unknown' => '相册 %s 出现未知错误。',
	],
];