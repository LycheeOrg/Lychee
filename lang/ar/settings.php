<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
    'title' => 'الإعدادات',
    'small_screen' => 'لتحقيق تجربة أفضل على صفحة الإعدادات،<br />نوصي باستخدام شاشة أكبر.',
    'tabs' => [
        'basic' => 'أساسي',
        'all_settings' => 'جميع الإعدادات',
    ],
    'toasts' => [
        'change_saved' => 'تم حفظ التغيير!',
        'details' => 'تم تعديل الإعدادات حسب الطلب',
        'error' => 'خطأ!',
        'error_load_css' => 'تعذر تحميل dist/user.css',
        'error_load_js' => 'تعذر تحميل dist/custom.js',
        'error_save_css' => 'تعذر حفظ CSS',
        'error_save_js' => 'تعذر حفظ JS',
        'thank_you' => 'شكرًا لدعمك.',
        'reload' => 'أعد تحميل الصفحة للحصول على جميع الوظائف.',
    ],
    'system' => [
        'header' => 'النظام',
        'use_dark_mode' => 'استخدام الوضع الداكن لـ Lychee',
        'language' => 'اللغة المستخدمة بواسطة Lychee',
        'nsfw_album_visibility' => 'جعل الألبومات الحساسة مرئية افتراضيًا.',
        'nsfw_album_explanation' => 'إذا كان الألبوم عامًا، فإنه لا يزال متاحًا، ولكنه مخفي عن العرض ويمكن <b>إظهاره بالضغط على <kbd>H</kbd></b>.',
        'cache_enabled' => 'تمكين التخزين المؤقت للاستجابات.',
        'cache_enabled_details' => 'سيؤدي ذلك إلى تسريع وقت استجابة Lychee بشكل كبير.<br> <i class="pi pi-exclamation-triangle text-warning-600 ml-2"></i>إذا كنت تستخدم ألبومات محمية بكلمة مرور، فلا يجب تمكين هذا.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'احصل على ميزات حصرية وادعم تطوير Lychee. افتح <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">إصدار SE</a>.',
        'preview' => 'تمكين معاينة ميزات Lychee SE',
        'hide_call4action' => 'إخفاء نموذج تسجيل Lychee SE هذا. أنا سعيد بـ Lychee كما هو. :)',
        'hide_warning' => 'إذا تم التمكين، فإن الطريقة الوحيدة لتسجيل مفتاح الترخيص الخاص بك ستكون عبر علامة التبويب المزيد أعلاه. يتم تطبيق التغييرات عند إعادة تحميل الصفحة.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'لاستيراد الصور من Dropbox الخاص بك، تحتاج إلى مفتاح تطبيق صالح من موقعهم.',
        'api_key' => 'مفتاح API لـ Dropbox',
        'set_key' => 'تعيين مفتاح Dropbox',
    ],
    'gallery' => [
        'header' => 'المعرض',
        'photo_order_column' => 'العمود الافتراضي المستخدم لترتيب الصور',
        'photo_order_direction' => 'الترتيب الافتراضي المستخدم لترتيب الصور',
        'album_order_column' => 'العمود الافتراضي المستخدم لترتيب الألبومات',
        'album_order_direction' => 'الترتيب الافتراضي المستخدم لترتيب الألبومات',
        'aspect_ratio' => 'نسبة العرض إلى الارتفاع الافتراضية لمصغرات الألبوم',
        'photo_layout' => 'تخطيط الصور',
        'album_decoration' => 'عرض الزخارف على غلاف الألبوم (الألبوم الفرعي و/أو عدد الصور)',
        'album_decoration_direction' => 'محاذاة زخارف الألبوم أفقيًا أو عموديًا',
        'photo_overlay' => 'معلومات التراكب الافتراضية للصورة',
        'license_default' => 'الترخيص الافتراضي المستخدم للألبومات',
        'license_help' => 'تحتاج مساعدة في الاختيار؟',
    ],
    'geolocation' => [
        'header' => 'الموقع الجغرافي',
        'map_display' => 'عرض الخريطة بناءً على إحداثيات GPS',
        'map_display_public' => 'السماح للمستخدمين المجهولين بالوصول إلى الخريطة',
        'map_provider' => 'تحديد مزود الخريطة',
        'map_include_subalbums' => 'تضمين صور الألبومات الفرعية على الخريطة',
        'location_decoding' => 'استخدام فك تشفير موقع GPS',
        'location_show' => 'عرض الموقع المستخرج من إحداثيات GPS',
        'location_show_public' => 'يمكن للمستخدمين المجهولين الوصول إلى الموقع المستخرج من إحداثيات GPS',
    ],
    'cssjs' => [
        'header' => 'CSS وJs مخصص',
        'change_css' => 'تغيير CSS',
        'change_js' => 'تغيير JS',
    ],
    'all' => [
        'old_setting_style' => 'نمط الإعدادات القديم',
        'expert_settings' => 'وضع الخبير',
        'change_detected' => 'تم تغيير بعض الإعدادات.',
        'save' => 'حفظ',
        'back_to_settings' => 'العودة إلى الإعدادات المجمعة',
    ],
    'tool_option' => [
        'disabled' => 'معطل',
        'enabled' => 'مفعل',
        'discover' => 'اكتشاف',
    ],
    'groups' => [
        'general' => 'عام',
        'system' => 'النظام',
        'modules' => 'الوحدات',
        'advanced' => 'متقدم',
    ],
];
