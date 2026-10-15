<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statistics page
    |--------------------------------------------------------------------------
    */
    'title' => '統計',
    'preview_text' => 'これは Lychee <span class="text-primary-emphasis font-bold">SE</span> で利用できる統計ページのプレビューです。<br />ここに表示されているデータはランダムに生成されたもので、お使いのサーバーを反映していません。',
    'no_data' => 'ユーザーのデータはサーバー上にありません。',
    'collapse' => 'アルバムサイズを折りたたむ',
    'total' => [
        'total' => '合計',
        'albums' => 'アルバム',
        'photos' => '写真',
        'size' => 'サイズ',
    ],
    'table' => [
        'username' => '所有者',
        'title' => 'タイトル',
        'photos' => '写真',
        'descendants' => '子アルバム',
        'size' => 'サイズ',
    ],
    'punch_card' => [
        'title' => 'アクティビティ',
        'photo-taken' => '%d 枚の写真を撮影',
        'photo-taken-in' => '%d 枚の写真を撮影（%d 年）',
        'photo-uploaded' => '%d 枚の写真をアップロード',
        'photo-uploaded-in' => '%d 枚の写真をアップロード（%d 年）',
        'with-exif' => 'EXIF データあり',
        'less' => '少',
        'more' => '多',
        'tooltip' => '%d 枚の写真（%s）',
        'created_at' => 'アップロード日',
        'taken_at' => 'EXIF 日付',
        'caption' => '各列は1週間を表します。',
    ],
    'metrics' => [
        'header' => 'ライブ指標',
        'preview_text' => 'これは Lychee <span class="text-primary-emphasis font-bold">SE</span> で利用できるライブ指標のプレビューです。ここに表示されているデータはランダムに生成されたもので、お使いのサーバーを反映していません。',
        'a_visitor' => '訪問者',
        'visitors' => '%d 人の訪問者',
        'visit_singular' => '%1$s が %2$s を閲覧しました',
        'favourite_singular' => '%1$s が %2$s をお気に入りに追加しました',
        'download_singular' => '%1$s が %2$s をダウンロードしました',
        'shared_singular' => '%1$s が %2$s を共有しました',
        'visit_plural' => '%1$s が %2$s を閲覧しました',
        'favourite_plural' => '%1$s が %2$s をお気に入りに追加しました',
        'download_plural' => '%1$s が %2$s をダウンロードしました',
        'shared_plural' => '%1$s が %2$s を共有しました',
        'truncated' => '古いアクティビティは表示されません。',
        'ago' => [
            'days' => '%d 日前',
            'day' => '1日前',
            'hours' => '%d 時間前',
            'hour' => '1時間前',
            'minutes' => '%d 分前',
            'few_minutes' => '数分前',
            'seconds' => '数秒前',
        ],
    ],
];
