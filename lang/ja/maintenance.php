<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'メンテナンス',
	'description' => 'このページには、Lychee のインストールをスムーズかつ適切に実行するために必要なすべてのアクションが記載されています。',
	'cleaning' => [
		'title' => '%s を削除',
		'result' => '%s が削除されました。',
		'description' => '<span class="font-mono">%s</span> からすべてのコンテンツを削除します',
		'button' => '削除',
	],
	'duplicate-finder' => [
		'title' => 'Duplicates',
		'description' => 'This module counts potential duplicates betwen pictures.',
		'duplicates-all' => 'Duplicates over all albums',
		'duplicates-title' => 'Title duplicates per album',
		'duplicates-per-album' => 'Duplicates per album',
		'show' => 'Show duplicates',
	],
	'fix-jobs' => [
		'title' => 'ジョブ履歴の修正',
		'description' => 'ステータスが <span class="text-ready-400">%s</span> または <span class="text-primary-500">%s</span> のジョブを <span class="text-danger-700">%s</span> としてマークします。',
		'button' => 'ジョブ履歴を修正',
	],
	'gen-sizevariants' => [
		'title' => '存在しない %s',
		'description' => '生成可能な %d 個の %s が見つかりました。',
		'button' => '生成',
		'success' => '%d 個の %s が正常に生成されました。',
	],
	'fill-filesize-sizevariants' => [
		'title' => 'ファイルサイズが見つかりません',
		'description' => 'ファイルサイズのない小さなバリアントが %d 個見つかりました。',
		'button' => 'データを取得',
		'success' => '%d 個の小さなバリアントのサイズを正常に計算しました。',
	],
	'fix-tree' => [
		'title' => 'ツリー統計',
		'Oddness' => 'Oddness',
		'Duplicates' => '重複',
		'Wrong parents' => '間違った親要素',
		'Missing parents' => '存在しない親要素',
		'button' => 'ツリーを修正',
	],
	'optimize' => [
		'title' => 'データベースを最適化',
		'description' => 'インストールの速度低下に気付いた場合、データベースに必要なインデックスがすべて揃っていないことが原因の可能性があります。',
		'button' => 'データベースを最適化',
	],
	'update' => [
		'title' => '更新',
		'check-button' => '更新を確認',
		'update-button' => '更新',
		'no-pending-updates' => '保留中の更新はありません',
	],
	'flush-cache' => [
		'title' => 'Flush Cache',
		'description' => 'Flush the cache of every user to solve invalidation problems.',
		'button' => 'Flush',
	],
];
