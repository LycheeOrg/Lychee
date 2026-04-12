<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Maintenance Page
    |--------------------------------------------------------------------------
    */
    'title' => 'الصيانة',
    'description' => 'في هذه الصفحة ستجد جميع الإجراءات المطلوبة للحفاظ على تشغيل تثبيت Lychee بسلاسة وجمال.',
    'cleaning' => [
        'title' => 'تنظيف %s',
        'result' => '%s تم حذفه.',
        'description' => 'إزالة جميع المحتويات من <span class="font-mono">%s</span>',
        'button' => 'تنظيف',
    ],
    'duplicate-finder' => [
        'title' => 'التكرارات',
        'description' => 'يحسب هذا الموديل التكرارات المحتملة بين الصور.',
        'duplicates-all' => 'التكرارات عبر جميع الألبومات',
        'duplicates-title' => 'تكرارات العنوان لكل ألبوم',
        'duplicates-per-album' => 'التكرارات لكل ألبوم',
        'show' => 'عرض التكرارات',
        'load' => 'Load counts',
    ],
    'fix-jobs' => [
        'title' => 'إصلاح سجل الوظائف',
        'description' => 'تحديد الوظائف بالحالة <span class="text-ready-400">%s</span> أو <span class="text-primary-500">%s</span> كـ <span class="text-danger-700">%s</span>.',
        'button' => 'إصلاح سجل الوظائف',
    ],
    'gen-sizevariants' => [
        'title' => 'الناقص %s',
        'description' => 'تم العثور على %d %s يمكن إنشاؤها.',
        'button' => 'إنشاء!',
        'success' => 'تم إنشاء %d %s بنجاح.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'أحجام الملفات المفقودة',
        'description' => 'تم العثور على %d متغيرات صغيرة بدون حجم ملف.',
        'button' => 'جلب البيانات!',
        'success' => 'تم حساب أحجام %d متغيرات صغيرة بنجاح.',
    ],
    'fix-tree' => [
        'title' => 'إحصائيات الشجرة',
        'Oddness' => 'الشذوذ',
        'Duplicates' => 'التكرارات',
        'Wrong parents' => 'الآباء الخاطئون',
        'Missing parents' => 'الآباء المفقودون',
        'button' => 'إصلاح الشجرة',
    ],
    'optimize' => [
        'title' => 'تحسين قاعدة البيانات',
        'description' => 'إذا لاحظت تباطؤًا في التثبيت الخاص بك، فقد يكون ذلك بسبب عدم وجود جميع الفهارس المطلوبة في قاعدة البيانات.',
        'button' => 'تحسين قاعدة البيانات',
    ],
    'update' => [
        'title' => 'التحديثات',
        'check-button' => 'التحقق من التحديثات',
        'update-button' => 'تحديث',
        'no-pending-updates' => 'لا توجد تحديثات معلقة.',
    ],
    'missing-palettes' => [
        'title' => 'لوحات الألوان المفقودة',
        'description' => 'تم العثور على %d لوحة ألوان مفقودة.',
        'button' => 'إنشاء المفقود',
    ],
    'statistics-check' => [
        'title' => 'فحص سلامة الإحصائيات',
        'missing_photos' => 'إحصائيات %d صورة مفقودة.',
        'missing_albums' => 'إحصائيات %d ألبوم مفقودة.',
        'button' => 'إنشاء المفقود',
    ],
    'flush-cache' => [
        'title' => 'مسح ذاكرة التخزين المؤقت',
        'description' => 'مسح ذاكرة التخزين المؤقت لكل مستخدم لحل مشاكل الإبطال.',
        'button' => 'مسح',
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
