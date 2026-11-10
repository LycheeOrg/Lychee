<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => '同步您的服务器文件',
    'description' => '将您的服务器文件与 Lychee 同步。这将从某个目录及其所有子目录导入照片。此过程非常缓慢，建议使用 worker 和队列以避免超时。',
    'sync' => '同步',
    'loading' => '加载中…',
    'selected_directory' => '当前选定的目录：',
    'resync_metadata' => '重新同步现有文件的元数据。',
    'delete_imported' => '删除原始文件。',
    'import_via_symlink' => '通过符号链接导入照片，而不是复制文件。',
    'skip_duplicates' => '如果照片和相册已存在于图库中，则跳过。',
    'delete_missing_photos' => '删除相册中不存在于同步目录中的照片。',
    'delete_missing_albums' => '删除父相册中不存在于同步目录中的相册。',
    'importing_please_be_patient' => '正在导入，请耐心等待…',
];

