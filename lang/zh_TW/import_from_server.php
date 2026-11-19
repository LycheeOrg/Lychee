<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => '同步伺服器檔案',
    'description' => '將您的伺服器檔案與 Lychee 同步。這會從一個目錄及其所有子目錄匯入相片。此過程非常緩慢，建議使用 worker 與佇列以避免逾時。',
    'sync' => '同步',
    'loading' => '載入中...',
    'selected_directory' => '目前選擇的目錄：',
    'resync_metadata' => '重新同步現有檔案的中繼資料。',
    'delete_imported' => '刪除原始檔案。',
    'import_via_symlink' => '透過符號連結匯入相片，而非複製檔案。',
    'skip_duplicates' => '若相片和相簿已存在於相簿庫中則略過。',
    'delete_missing_photos' => '刪除相簿中不存在於同步目錄的相片。',
    'delete_missing_albums' => '刪除上層相簿中不存在於同步目錄的相簿。',
    'importing_please_be_patient' => '匯入中，請耐心等候...',
];

