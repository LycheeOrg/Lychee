<?php
return [
    /*
    |--------------------------------------------------------------------------
    | صفحه نگهداری
    |--------------------------------------------------------------------------
    */
    'title' => 'نگهداری',
    'description' => 'در این صفحه تمام اقدامات موذد نیاز برای حفظ عملکرد بی نقص و مناسب نسخه لیچی نصب شده خود را خواهید یافت.',
    'cleaning' => [
        'title' => 'پاک‌سازی %s',
        'result' => '%s حذف شد.',
        'description' => 'تمام محتویات <span class="font-mono">%s</span> را حذف کنید',
        'button' => 'پاک‌سازی',
    ],
    'duplicate-finder' => [
        'title' => 'موارد تکراری',
        'description' => 'این ماژول موارد تکراری احتمالی بین تصاویر را شمارش می‌کند.',
        'duplicates-all' => 'موارد تکراری در تمام آلبوم‌ها',
        'duplicates-title' => 'موارد تکراری عنوان در هر آلبوم',
        'duplicates-per-album' => 'موارد تکراری در هر آلبوم',
        'show' => 'نمایش موارد تکراری',
        'load' => 'Load counts',
    ],
    'fix-jobs' => [
        'title' => 'اصلاح تاریخچه وظایف',
        'description' => 'وظایف با وضعیت <span class="text-ready-400">%s</span> یا <span class="text-primary-500">%s</span> را به عنوان <span class="text-danger-700">%s</span> علامت‌گذاری کنید.',
        'button' => 'اصلاح تاریخچه وظایف',
    ],
    'gen-sizevariants' => [
        'title' => '%s گمشده',
        'description' => '%d %s یافت شد که می‌تواند تولید شود.',
        'button' => 'تولید!',
        'success' => '%d %s با موفقیت تولید شد.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'اندازه فایل‌ها گمشده است',
        'description' => '%d واریانت کوچک بدون اندازه فایل یافت شد.',
        'button' => 'دریافت داده!',
        'success' => 'اندازه %d واریانت کوچک با موفقیت محاسبه شد.',
    ],
    'fix-tree' => [
        'title' => 'آمار درخت',
        'Oddness' => 'ناهنجاری',
        'Duplicates' => 'موارد تکراری',
        'Wrong parents' => 'والدین اشتباه',
        'Missing parents' => 'والدین گمشده',
        'button' => 'اصلاح درخت',
    ],
    'optimize' => [
        'title' => 'بهینه سازی پایگاه داده',
        'description' => 'اگر کندی در نصب خود مشاهده می‌کنید، ممکن است پایگاه داده شما همه ایندکس های لازم را نداشته باشد.',
        'button' => 'بهینه سازی پایگاه داده',
    ],
    'update' => [
        'title' => 'به‌روزرسانی‌ها',
        'check-button' => 'بررسی به‌روزرسانی',
        'update-button' => 'به‌روزرسانی',
        'no-pending-updates' => 'به‌روزرسانی معوقه‌ای وجود ندارد.',
    ],
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'بررسی صحت آمار',
        'missing_photos' => '%d آمار عکس گمشده است.',
        'missing_albums' => '%d آمار آلبوم گمشده است.',
        'button' => 'ایجاد موارد گمشده',
    ],
    'flush-cache' => [
        'title' => 'پاک سازی کش',
        'description' => 'کش همه کاربران را برای حل مشکلات اعتبارسنجی پاک کنید.',
        'button' => 'پاک سازی',
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
