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
        'title' => '重複',
        'description' => 'このモジュールは写真間の重複の可能性がある数をカウントします。',
        'duplicates-all' => '全アルバムでの重複',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'アルバムごとの重複',
        'show' => '重複を表示',
        'load' => '件数を読み込み',
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
        'Oddness' => '不整合',
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
    'missing-palettes' => [
        'title' => '存在しないパレット',
        'description' => '存在しないパレットが %d 個見つかりました。',
        'button' => '不足分を作成',
    ],
    'statistics-check' => [
        'title' => '統計の整合性チェック',
        'missing_photos' => '%d 件の写真統計が欠落しています。',
        'missing_albums' => '%d 件のアルバム統計が欠落しています。',
        'button' => '不足分を作成',
    ],
    'flush-cache' => [
        'title' => 'キャッシュをクリア',
        'description' => '無効化の問題を解決するため、すべてのユーザーのキャッシュをクリアします。',
        'button' => 'クリア',
    ],
    'old-orders' => [
        'title' => '古い注文',
        'description' => '古い注文が %d 件見つかりました。<br/><br/>古い注文とは、14日以上経過し、関連付けられたユーザーがおらず、支払い待ちのままか商品が含まれていない注文です。',
        'button' => '古い注文を削除',
    ],
    'fulfill-orders' => [
        'title' => '処理待ちの注文',
        'description' => 'コンテンツがまだ提供されていない注文が %d 件見つかりました。<br/><br/>ボタンをクリックすると、可能な場合にコンテンツを割り当てます。',
        'button' => '注文を処理',
    ],
    'fulfill-precompute' => [
        'title' => 'アルバムの事前計算フィールド',
        'description' => '事前計算フィールドが欠落しているアルバムが %d 件見つかりました。<br/><br/>次のコマンドの実行と同等です：php artisan lychee:recompute-album-fields',
        'button' => 'フィールドを計算',
    ],
    'flush-queue' => [
        'title' => 'キューをクリア',
        'description' => 'キューに保留中のジョブが %d 件見つかりました。<br/><br/>注意：キューをクリアすると、保留中のすべてのジョブが完全に削除されます。この操作は元に戻せません。',
        'button' => 'キューをクリア',
    ],
    'backfill-album-sizes' => [
        'title' => 'アルバムサイズ統計',
        'description' => 'サイズ統計のないアルバムが %d 件見つかりました。<br/><br/>次のコマンドの実行と同等です：php artisan lychee:recompute-album-sizes',
        'button' => 'サイズを計算',
    ],

    'face_quality' => [
        'title' => '顔の品質レビュー',
        'description' => '品質スコアで顔検出をレビューし、低品質または誤った顔を却下します。',
        'sort_by' => '並べ替え：',
        'sort_confidence' => '信頼度',
        'sort_blur' => 'ぼやけ（ラプラシアン）',
        'no_faces' => '該当する顔はありません。すべて問題ありません！',
        'col_face' => '顔',
        'col_person' => '人物',
        'col_cluster' => 'クラスター',
        'col_confidence' => '信頼度',
        'col_blur' => 'ぼやけスコア',
        'col_actions' => '操作',
        'unassigned' => '未割り当て',
        'dismiss' => '顔を却下',
        'readd' => '顔を再追加',
        'load_error' => '顔の読み込みに失敗しました。',
        'dismissed' => '顔を却下しました。',
        'readded' => '顔を再追加しました。',
        'dismiss_error' => '顔の却下に失敗しました。',
        'readd_error' => '顔の再追加に失敗しました。',
        'batch_dismiss' => '選択項目を却下',
        'batch_dismissed' => ':count 件の顔を却下しました。',
        'batch_dismiss_error' => '選択した顔の却下に失敗しました。',
        'batch_reactivate' => '選択項目を再有効化',
        'batch_reactivated' => ':count 件の顔を再有効化しました。',
        'batch_reactivate_error' => '選択した顔の再有効化に失敗しました。',
        'show_dismissed' => '却下済みを表示',
        'show_active' => '有効なものを表示',
        'show_unassigned' => '未割り当てのみ',
        'select_all' => 'すべて選択',
        'deselect_all' => 'すべて選択解除',
        'selected_count' => ':count 件選択中',
    ],
    'bulk-scan-faces' => [
        'description' => '顔認識のスキャンがまだ行われていない写真が %d 枚見つかりました。<br/><br/>AI Vision サービスが稼働している必要があります。',
    ],
    'run-clustering' => [
        'description' => 'AI Vision サービスで顔のクラスタリングを実行します。検出された顔を類似度でグループ化し、人物に割り当てられるようにします。',
        'success' => 'クラスタリングを開始しました。',
    ],
    'destroy-dismissed-faces' => [
        'title' => '却下済みの顔を破棄',
        'description' => '却下済みの顔が %d 件見つかりました。破棄すると、切り抜きファイルと埋め込みが完全に削除されます。',
        'action' => 'すべて破棄',
        'success' => '却下済みの顔を破棄しました。',
    ],
    'sync-face-embeddings' => [
        'title' => '顔の埋め込みを同期',
        'description' => '顔の数の不一致が検出されました（差分 %d）。同期すると、AI Vision サービスから最新の顔データを Lychee に取り込みます。',
        'action' => '今すぐ同期',
        'success' => '顔の埋め込みを同期しました。',
    ],
    'reset-face-scan-status' => [
        'title' => '顔スキャンのステータスをリセット',
        'description' => '顔スキャンのステータスが保留中のまま停止しているか失敗している写真が %d 枚見つかりました。リセットすると再スキャンできるようになります。',
        'action' => 'すべてリセット',
        'success' => '顔スキャンのステータスをリセットしました。',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => '孤立した顔の埋め込みを消去',
            'description' => 'AI Vision サービスには、Lychee にもう存在しない埋め込みが最大 %d 件残っている可能性があります。消去すると、現在の顔のリストを AI Vision に送信し、残ったものを完全に削除します。',
            'action' => '今すぐ消去',
            'success' => '孤立した顔の埋め込みを消去しました。',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'NSFW 一括スキャン',
        'description' => '設定されたプリセットを使用して、未スキャンのすべての写真の NSFW コンテンツをスキャンします。NSFW 分類サービスが稼働している必要があります。',
        'button' => '未スキャンをすべてスキャン',
        'success' => 'NSFW スキャンを開始しました。',
    ],
];
