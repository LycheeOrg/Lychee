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
];
