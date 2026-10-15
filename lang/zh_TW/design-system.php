<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'v8 前端實際使用的設計權杖與元件的即時參考。此頁面上的每個色票與控制項都讀取目前實際使用的 CSS 自訂屬性與 Nuxt UI 元件，因此不會像靜態模型那樣過時。',

    'sections' => [
        'foundations' => '基礎',
        'colors' => '調色盤',
        'typography' => '字體排印',
        'buttons' => '按鈕',
        'badges' => '徽章與標籤',
        'forms' => '表單控制項',
        'feedback' => '回饋與狀態',
        'radius' => '圓角與陰影層級',
    ],

    'loading' => [
        'full_page' => '全頁載入動畫',
        'full_page_description' => '緩慢、交錯的顯示效果——在檢視載入時用於全螢幕遮罩後方。',
        'mini_spinner' => '迷你載入圖示',
        'mini_spinner_description' => '快速版本，立即顯示——用於按鈕、清單及小型載入狀態中。',
    ],

    'foundations' => [
        'system' => '元件套件',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => '內文字型',
        'body_font_value' => 'Libre Franklin，自行託管（阿拉伯文／波斯文使用 Vazirmatn）',
        'mono_font' => '等寬字型',
        'mono_font_value' => '系統等寬字型堆疊（ui-monospace）',
        'icons' => '圖示集',
        'icons_value' => 'Lucide，透過 Iconify（UIcon）',
        'theme' => '主題',
        'theme_value' => '單一深色／淺色主題，在設定中切換——無個別區塊覆寫',
    ],

    'colors' => [
        'primary' => '主要色——品牌色、主要動作、連結、啟用狀態',
        'secondary' => '次要色——替代動作與選取',
        'neutral' => '中性色——文字、邊框、表面（淺色模式為 Slate，深色模式為 Zinc）',
        'success' => '成功——確認訊息、正面狀態',
        'warning' => '警告——注意事項、不阻擋操作的問題',
        'error' => '錯誤——破壞性動作、失敗',
        'info' => '資訊——提示性訊息',
        'resolved_from' => '即時解析自 :token',
    ],

    'typography' => [
        'display' => '展示',
        'heading' => '標題',
        'body' => '內文',
        'body_sample' => '天地玄黃，宇宙洪荒，日月盈昃，辰宿列張——相簿標題、表單標籤與日常文字都以此字型堆疊呈現。',
        'mono' => '等寬',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => '文字強調層級',
        'emphasis_sample' => '往年今日拍攝的照片',
    ],

    'buttons' => [
        'intro' => '按鈕系統支援的所有顏色，以及應用程式中實際使用的各種樣式。',
    ],

    'forms' => [
        'input_label' => '相簿標題',
        'input_placeholder' => '京都的夏天',
        'select_label' => '排序方式',
        'checkbox_label' => '顯示敏感相簿',
        'switch_label' => '公開相簿',
        'switch_description' => '任何擁有連結的人都能檢視此相簿',
        'textarea_label' => '描述',
        'textarea_placeholder' => '為此相簿新增描述…',
        'radio_legend' => '標籤比對邏輯',
    ],

    'feedback' => [
        'alert_info' => '此相簿已新增照片。',
        'alert_success' => '設定已儲存。',
        'alert_warning' => '任何擁有連結的人都能看到此相簿。',
        'alert_error' => '無法連線至伺服器——變更未儲存。',
        'empty_title' => '尚無共享',
        'empty_description' => '當您共享第一個相簿後，共享的相簿及其權限會顯示在這裡。',
    ],
];
