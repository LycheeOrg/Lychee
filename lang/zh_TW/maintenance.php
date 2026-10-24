<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => '維護',
    'description' => '在此頁面上，您可以找到讓 Lychee 安裝保持順暢運作所需的所有操作。',
    'cleaning' => [
        'title' => '清理 %s',
        'result' => '已刪除 %s。',
        'description' => '移除 <span class="font-mono">%s</span> 中的所有內容',
        'button' => '清理',
    ],
    'duplicate-finder' => [
        'title' => '重複項目',
        'description' => '此模組會統計照片之間可能的重複項目。',
        'duplicates-all' => '所有相簿中的重複項目',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => '各相簿的重複項目',
        'show' => '顯示重複項目',
        'load' => '載入統計',
    ],
    'fix-jobs' => [
        'title' => '修復工作記錄',
        'description' => '將狀態為 <span class="text-ready-400">%s</span> 或 <span class="text-primary-500">%s</span> 的工作標記為 <span class="text-danger-700">%s</span>。',
        'button' => '修復工作記錄',
    ],
    'gen-sizevariants' => [
        'title' => '缺少 %s',
        'description' => '找到 %d 個可產生的 %s。',
        'button' => '產生！',
        'success' => '已成功產生 %d 個 %s。',
    ],
    'fill-filesize-sizevariants' => [
        'title' => '缺少檔案大小',
        'description' => '找到 %d 個沒有檔案大小的小尺寸版本。',
        'button' => '擷取資料！',
        'success' => '已成功計算 %d 個小尺寸版本的大小。',
    ],
    'fix-tree' => [
        'title' => '樹狀結構統計',
        'Oddness' => '異常',
        'Duplicates' => '重複項目',
        'Wrong parents' => '錯誤的上層',
        'Missing parents' => '缺少上層',
        'button' => '修復樹狀結構',
    ],
    'optimize' => [
        'title' => '最佳化資料庫',
        'description' => '如果您發現系統變慢，可能是因為資料庫缺少部分必要的索引。',
        'button' => '最佳化資料庫',
    ],
    'missing-palettes' => [
        'title' => '缺少調色盤',
        'description' => '找到 %d 個缺少的調色盤。',
        'button' => '建立缺少項目',
    ],
    'statistics-check' => [
        'title' => '統計資料完整性檢查',
        'missing_photos' => '缺少 %d 筆照片統計資料。',
        'missing_albums' => '缺少 %d 筆相簿統計資料。',
        'button' => '建立缺少項目',
    ],
    'flush-cache' => [
        'title' => '清除快取',
        'description' => '清除所有使用者的快取，以解決快取失效問題。',
        'button' => '清除',
    ],
    'old-orders' => [
        'title' => '舊訂單',
        'description' => '找到 %d 筆舊訂單。<br/>舊訂單是指超過 14 天、沒有關聯使用者，且仍在等待付款或不含任何項目的訂單。',
        'button' => '刪除舊訂單',
    ],
    'fulfill-orders' => [
        'title' => '待履行訂單',
        'description' => '找到 %d 筆內容尚未提供的訂單。<br/>點擊按鈕即可在可行時指派內容。',
        'button' => '履行訂單',
    ],
    'fulfill-precompute' => [
        'title' => '相簿預先計算欄位',
        'description' => '找到 %d 個缺少預先計算欄位的相簿。<br/>等同於執行：php artisan lychee:recompute-album-stats',
        'button' => '計算欄位',
    ],
    'flush-queue' => [
        'title' => '清空佇列',
        'description' => '佇列中有 %d 個待處理工作。<br/>注意：清空佇列將永久刪除所有待處理工作，且無法復原。',
        'button' => '清空佇列',
    ],
    'backfill-album-sizes' => [
        'title' => '相簿大小統計',
        'description' => '找到 %d 個沒有大小統計的相簿。<br/>等同於執行：php artisan lychee:recompute-album-sizes',
        'description_queue' => '在背景重新計算所有相簿的大小統計。<br/>等同於執行：php artisan lychee:recompute-album-sizes',
        'button' => '計算大小',
    ],
    'recompute-buckets' => [
        'title' => '時間軸分組',
        'description' => '重新計算每個相簿和每張相片的時間軸分組，然後重新整理相簿和相片清單。<br/>等同於執行：php artisan lychee:recompute-buckets',
        'button' => '重新計算',
    ],

    'face_quality' => [
        'title' => '臉孔品質審查',
        'description' => '依品質分數審查偵測到的臉孔，並忽略低品質或錯誤的臉孔。',
        'sort_by' => '排序方式：',
        'sort_confidence' => '信賴度',
        'sort_blur' => '模糊度（Laplacian）',
        'no_faces' => '沒有符合條件的臉孔，一切正常！',
        'col_face' => '臉孔',
        'col_person' => '人物',
        'col_cluster' => '群組',
        'col_confidence' => '信賴度',
        'col_blur' => '模糊分數',
        'col_actions' => '操作',
        'unassigned' => '未指派',
        'dismiss' => '忽略臉孔',
        'readd' => '重新加入臉孔',
        'load_error' => '無法載入臉孔。',
        'dismissed' => '已忽略臉孔。',
        'readded' => '已重新加入臉孔。',
        'dismiss_error' => '無法忽略臉孔。',
        'readd_error' => '無法重新加入臉孔。',
        'batch_dismiss' => '忽略所選項目',
        'batch_dismissed' => '已忽略 :count 張臉孔。',
        'batch_dismiss_error' => '無法忽略所選臉孔。',
        'batch_reactivate' => '重新啟用所選項目',
        'batch_reactivated' => '已重新啟用 :count 張臉孔。',
        'batch_reactivate_error' => '無法重新啟用所選臉孔。',
        'show_dismissed' => '顯示已忽略',
        'show_active' => '顯示啟用中',
        'show_unassigned' => '僅顯示未指派',
        'select_all' => '全選',
        'deselect_all' => '取消全選',
        'selected_count' => '已選取 :count 個',
    ],
    'bulk-scan-faces' => [
        'description' => '找到 %d 張尚未進行臉部辨識掃描的照片。<br/>需要 AI Vision 服務正在執行。',
    ],
    'run-clustering' => [
        'description' => '在 AI Vision 服務中啟動臉孔分群。依相似度將偵測到的臉孔分組，方便您將其指派給人物。',
        'success' => '已成功開始分群。',
    ],
    'destroy-dismissed-faces' => [
        'title' => '銷毀已忽略的臉孔',
        'description' => '找到 %d 張已忽略的臉孔。銷毀後將永久刪除其裁切檔案與特徵向量。',
        'action' => '全部銷毀',
        'success' => '已成功銷毀已忽略的臉孔。',
    ],
    'sync-face-embeddings' => [
        'title' => '同步臉孔特徵向量',
        'description' => '偵測到臉孔數量不一致（相差 %d）。同步將從 AI Vision 服務擷取最新的臉孔資料至 Lychee。',
        'action' => '立即同步',
        'success' => '已成功同步臉孔特徵向量。',
    ],
    'reset-face-scan-status' => [
        'title' => '重設臉孔掃描狀態',
        'description' => '找到 %d 張臉孔掃描狀態卡在待處理或失敗的照片。重設後即可重新掃描。',
        'action' => '全部重設',
        'success' => '已成功重設臉孔掃描狀態。',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => '清除孤立的臉孔特徵向量',
            'description' => 'AI Vision 服務可能仍保有最多 %d 筆在 Lychee 中已不存在的特徵向量。清除時會將目前的臉孔清單推送至 AI Vision，並永久刪除所有剩餘項目。',
            'action' => '立即清除',
            'success' => '已成功清除孤立的臉孔特徵向量。',
        ],
        'bulk-scan-nsfw' => [
        'title' => '批次 NSFW 掃描',
        'description' => '使用已設定的預設組合，掃描所有尚未掃描的照片是否含有 NSFW 內容。需要 NSFW 分類服務正在執行。',
        'button' => '掃描所有未掃描項目',
        'success' => '已成功派送 NSFW 掃描。',
    ],
];
