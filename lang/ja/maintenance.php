<?php
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
        'load' => 'Load counts',
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
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'Statistics integrity Check',
        'missing_photos' => '%d photo statistics missing.',
        'missing_albums' => '%d album statistics missing.',
        'button' => 'Create missing',
    ],
    'flush-cache' => [
        'title' => 'Flush Cache',
        'description' => 'Flush the cache of every user to solve invalidation problems.',
        'button' => 'Flush',
    ],
    'old-orders' => [
        'title' => 'Old Orders',
        'description' => 'Found %d old orders.<br/><br/>An old order is older than 14 days, that have no associated user and are either still pending payment or have no items in them.',
        'button' => 'Delete old orders',
    ],
    'fulfill-orders' => [
        'title' => 'Orders to fulfill',
        'description' => 'Found %d orders with content that has not been made available.<br/><br/>Click on the button to assign content when possible.',
        'button' => 'Fulfill orders',
    ],
    'fulfill-precompute' => [
        'title' => 'Album Precomputed Fields',
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-fields',
        'button' => 'Compute fields',
    ],
    'flush-queue' => [
        'title' => 'Flush Queue',
        'description' => 'Found %d pending jobs in the queue.<br/><br/>CAUTION: Clearing the queue will permanently delete all pending jobs. This cannot be undone.',
        'button' => 'Clear queue',
    ],
    'backfill-album-sizes' => [
        'title' => 'Album Size Statistics',
        'description' => 'Found %d albums without size statistics.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-sizes',
        'button' => 'Compute sizes',
    ],

    'face_quality' => [
        'title' => 'Face Quality Review',
        'description' => 'Review face detections by quality score and dismiss low-quality or erroneous faces.',
        'sort_by' => 'Sort by:',
        'sort_confidence' => 'Confidence',
        'sort_blur' => 'Blur (Laplacian)',
        'no_faces' => 'No qualifying faces. Everything looks good!',
        'col_face' => 'Face',
        'col_person' => 'Person',
        'col_cluster' => 'Cluster',
        'col_confidence' => 'Confidence',
        'col_blur' => 'Blur Score',
        'col_actions' => 'Actions',
        'unassigned' => 'Unassigned',
        'dismiss' => 'Dismiss face',
        'load_error' => 'Failed to load faces.',
        'dismissed' => 'Face dismissed.',
        'dismiss_error' => 'Failed to dismiss face.',
        'batch_dismiss' => 'Dismiss selected',
        'batch_dismissed' => ':count face(s) dismissed.',
        'batch_dismiss_error' => 'Failed to dismiss selected faces.',
        'select_all' => 'Select all',
        'deselect_all' => 'Deselect all',
        'selected_count' => ':count selected',
    ],
    'bulk-scan-faces' => [
        'description' => 'Found %d photos that have not yet been scanned for facial recognition.<br/><br/>Requires the AI Vision service to be running.',
    ],
    'run-clustering' => [
        'description' => 'Trigger face clustering in the AI Vision service. Groups detected faces by similarity so you can assign them to people.',
        'success' => 'Clustering started successfully.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Destroy Dismissed Faces',
        'description' => 'Found %d dismissed faces. Destroying them will permanently delete their crop files and embeddings.',
        'action' => 'Destroy All',
        'success' => 'Dismissed faces destroyed successfully.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Sync Face Embeddings',
        'description' => 'Face count mismatch detected (%d difference). Syncing will pull latest face data from AI Vision service to Lychee.',
        'action' => 'Sync Now',
        'success' => 'Face embeddings synchronized successfully.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Reset Face Scan Status',
        'description' => 'Found %d photos with a stuck-pending or failed face scan status. Resetting them will allow them to be re-scanned.',
        'action' => 'Reset All',
        'success' => 'Face scan statuses reset successfully.',
    ],

    ];
