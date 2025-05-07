<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => '维护',
	'description' => '在此页面中，您可以找到保持 Lychee 安装运行顺畅所需的所有操作。',
	'cleaning' => [
		'title' => '清理 %s',
		'result' => '已删除 %s。',
		'description' => '删除 <span class="font-mono">%s</span> 中的所有内容',
		'button' => '清理',
	],
	'duplicate-finder' => [
		'title' => '重复项',
		'description' => '此模块统计图片之间的潜在重复项。',
		'duplicates-all' => '所有相册中的重复项',
		'duplicates-title' => '每个相册中的标题重复项',
		'duplicates-per-album' => '每个相册中的重复项',
		'show' => '显示重复项',
	],
	'fix-jobs' => [
		'title' => '修复任务历史',
		'description' => '将状态为 <span class="text-ready-400">%s</span> 或 <span class="text-primary-500">%s</span> 的任务标记为 <span class="text-danger-700">%s</span>。',
		'button' => '修复任务历史',
	],
	'gen-sizevariants' => [
		'title' => '缺失的 %s',
		'description' => '发现 %d 个可以生成的 %s。',
		'button' => '生成！',
		'success' => '已成功生成 %d 个 %s。',
	],
	'fill-filesize-sizevariants' => [
		'title' => '缺失文件大小',
		'description' => '发现 %d 个缺少文件大小的小型变体。',
		'button' => '获取数据！',
		'success' => '已成功计算 %d 个小型变体的大小。',
	],
	'fix-tree' => [
		'title' => '树结构统计',
		'Oddness' => '异常项',
		'Duplicates' => '重复项',
		'Wrong parents' => '错误的父级',
		'Missing parents' => '缺失的父级',
		'button' => '修复树结构',
	],
	'optimize' => [
		'title' => '优化数据库',
		'description' => '如果您注意到安装运行变慢，可能是因为您的数据库缺少必要的索引。',
		'button' => '优化数据库',
	],
	'update' => [
		'title' => '更新',
		'check-button' => '检查更新',
		'update-button' => '更新',
		'no-pending-updates' => '没有待处理的更新。',
	],
	'flush-cache' => [
		'title' => '清除缓存',
		'description' => '清除所有用户的缓存以解决失效问题。',
		'button' => '清除',
	],
];