<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => '維護',
    'intro' => '本頁可讓您手動重新排序並修復相簿。<br />在進行任何修改之前，強烈建議您先閱讀巢狀集合（Nested Set）樹狀結構的相關資料。',
    'warning' => '您在這裡真的可能弄壞您的 Lychee 安裝，修改數值的風險請自行承擔。',
    'help' => [
        'header' => '說明',
        'hover' => '將滑鼠移到 ID 或標題上，即可標示相關的相簿。',
        'left' => '<span class="text-muted-color-emphasis font-bold">左值</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">右值</span>',
        'convenience' => '為了方便起見，<i class="pi pi-angle-up" ></i> 和 <i class="pi pi-angle-down" ></i> 按鈕可將 %s 和 %s 的值分別 +1 和 -1，並連帶傳遞。',
        'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left" ></i> 和 <i class="text-warning-600 pi pi-chevron-circle-right" ></i> 表示 %s（以及 %s）的值在別處重複。',
        'parent-marked' => '標記的 <span class="font-bold text-danger-600">父項 ID</span> 表示 %s 和 %s 不符合巢狀集合樹狀結構。請編輯 <span class="font-bold text-danger-600">父項 ID</span> 或 %s/%s 的值。',
        'slowness' => '相簿數量很多時，此頁面會變慢。',
    ],
    'buttons' => [
        'reset' => '重設',
        'check' => '檢查',
        'apply' => '套用',
    ],
    'no-changes' => '沒有需要套用的變更。',
    'table' => [
        'title' => '標題',
        'left' => '左值',
        'right' => '右值',
        'id' => 'Id',
        'parent' => '父項 ID',
    ],
    'errors' => [
        'invalid' => '無效的樹狀結構！',
        'invalid_details' => '由於這必定會造成損壞的狀態，我們不會套用此變更。',
        'invalid_left' => '相簿 %s 的左值無效。',
        'invalid_right' => '相簿 %s 的右值無效。',
        'invalid_left_right' => '相簿 %s 的左值／右值無效。左值必須嚴格小於右值：%s < %s。',
        'duplicate_left' => '相簿 %s 的左值 %s 重複。',
        'duplicate_right' => '相簿 %s 的右值 %s 重複。',
        'parent' => '相簿 %s 的父項 ID %s 不符預期。',
        'unknown' => '相簿 %s 發生未知錯誤。',
    ],
];
