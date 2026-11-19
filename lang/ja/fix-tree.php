<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'メンテナンス',
    'intro' => 'このページでは、アルバムを手動で並べ替えたり修正したりできます。<br />変更を行う前に、Nested Set ツリー構造について理解しておくことを強くお勧めします。',
    'warning' => 'ここでは Lychee のインストールを実際に破損させる可能性があります。値の変更は自己責任で行ってください。',
    'help' => [
        'header' => 'ヘルプ',
        'hover' => 'ID またはタイトルにカーソルを合わせると、関連するアルバムがハイライトされます。',
        'left' => '<span class="text-muted-color-emphasis font-bold">左</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">右</span>',
        'convenience' => '便宜上、<i class="pi pi-angle-up" ></i> ボタンと <i class="pi pi-angle-down" ></i> ボタンを使うと、%s と %s の値をそれぞれ +1 と -1 だけ伝播付きで変更できます。',
        'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left" ></i> と <i class="text-warning-600 pi pi-chevron-circle-right" ></i> は、%s（およびそれぞれ %s）の値がどこかで重複していることを示します。',
        'parent-marked' => 'マークされた <span class="font-bold text-danger-600">親 ID</span> は、%s と %s が Nested Set ツリー構造を満たしていないことを示します。<span class="font-bold text-danger-600">親 ID</span> または %s/%s の値のいずれかを編集してください。',
        'slowness' => 'アルバム数が多い場合、このページの動作は遅くなります。',
    ],
    'buttons' => [
        'reset' => 'リセット',
        'check' => 'チェック',
        'apply' => '適用',
    ],
    'no-changes' => '適用する変更はありません。',
    'table' => [
        'title' => 'タイトル',
        'left' => '左',
        'right' => '右',
        'id' => 'Id',
        'parent' => '親 ID',
    ],
    'errors' => [
        'invalid' => '無効なツリーです！',
        'invalid_details' => '確実に破損した状態になるため、この変更は適用しません。',
        'invalid_left' => 'アルバム %s の left 値が無効です。',
        'invalid_right' => 'アルバム %s の right 値が無効です。',
        'invalid_left_right' => 'アルバム %s の left/right 値が無効です。left は right より厳密に小さくなければなりません: %s < %s。',
        'duplicate_left' => 'アルバム %s の left 値 %s が重複しています。',
        'duplicate_right' => 'アルバム %s の right 値 %s が重複しています。',
        'parent' => 'アルバム %s に予期しない親 ID %s があります。',
        'unknown' => 'アルバム %s で不明なエラーが発生しました。',
    ],
];
