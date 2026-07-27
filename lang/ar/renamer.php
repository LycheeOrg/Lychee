<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'قواعد إعادة التسمية',

    // Modal titles
    'create_rule' => 'إنشاء قاعدة إعادة تسمية',
    'edit_rule' => 'تعديل قاعدة إعادة التسمية',

    // Form fields
    'rule_name' => 'اسم القاعدة',
    'description' => 'الوصف',
    'pattern' => 'النمط',
    'replacement' => 'الاستبدال',
    'mode' => 'الوضع',
    'order' => 'الترتيب',
    'enabled' => 'مفعّل',
    'photo_rule' => 'القاعدة مطبّقة على الصور',
    'album_rule' => 'القاعدة مطبّقة على الألبومات',

    // Form placeholders and help text
    'description_placeholder' => 'وصف اختياري لما تقوم به هذه القاعدة',
    'pattern_help' => 'النمط المطلوب مطابقته (مثال: IMG_، DSC_)',
    'replacement_help' => 'نص الاستبدال (مثال: Photo_، Camera_)',
    'order_help' => 'تتم معالجة الأرقام الأصغر أولاً (1 = أعلى أولوية)',
    'enabled_help' => '(سيتم تطبيق القواعد المفعّلة فقط أثناء إعادة التسمية)',

    // Mode options
    'mode_first' => 'أول ظهور',
    'mode_all' => 'كل مرات الظهور',
    'mode_regex' => 'تعبير نمطي (Regex)',
    'mode_trim' => 'إزالة المسافات الزائدة',
    'mode_strtolower' => 'أحرف صغيرة',
    'mode_strtoupper' => 'أحرف كبيرة',
    'mode_ucwords' => 'تكبير أول حرف من كل كلمة',
    'mode_ucfirst' => 'تكبير الحرف الأول',

    'mode_first_description' => 'استبدال أول ظهور فقط',
    'mode_all_description' => 'استبدال جميع مرات الظهور',
    'mode_regex_description' => 'استخدام مطابقة الأنماط بالتعبيرات النمطية',
    'mode_trim_description' => 'إزالة المسافات الزائدة',
    'mode_strtolower_description' => 'تحويل النص إلى أحرف صغيرة',
    'mode_strtoupper_description' => 'تحويل النص إلى أحرف كبيرة',
    'mode_ucwords_description' => 'تكبير أول حرف من كل كلمة',
    'mode_ucfirst_description' => 'تكبير الحرف الأول فقط',

    'regex_help' => 'استخدم التعبيرات النمطية لمطابقة الأنماط. على سبيل المثال، لاستبدال <code>IMG_1234.jpeg</code> بـ <code>1234_JPG.jpeg</code>، يمكنك استخدام <code>/IMG_(\d+)/</code> كنمط بحث و <code>$1_JPG</code> كنص استبدال. يمكنك الاطلاع على مزيد من الشروحات والأمثلة في الروابط التالية.',

    // Buttons
    'cancel' => 'إلغاء',
    'create' => 'إنشاء',
    'update' => 'تحديث',
    'create_first_rule' => 'أنشئ قاعدتك الأولى',

    // Validation messages
    'rule_name_required' => 'اسم القاعدة مطلوب',
    'pattern_required' => 'النمط مطلوب',
    'replacement_required' => 'الاستبدال مطلوب',
    'mode_required' => 'الوضع مطلوب',
    'order_positive' => 'يجب أن يكون الترتيب رقمًا موجبًا',

    // Success messages
    'rule_created' => 'تم إنشاء قاعدة إعادة التسمية بنجاح',
    'rule_updated' => 'تم تحديث قاعدة إعادة التسمية بنجاح',
    'rule_deleted' => 'تم حذف قاعدة إعادة التسمية بنجاح',

    // Error messages
    'failed_to_create' => 'فشل إنشاء قاعدة إعادة التسمية',
    'failed_to_update' => 'فشل تحديث قاعدة إعادة التسمية',
    'failed_to_delete' => 'فشل حذف قاعدة إعادة التسمية',
    'failed_to_load' => 'فشل تحميل قواعد إعادة التسمية',

    // List view
    'rules_count' => ':count قاعدة',
    'no_rules' => 'لم يتم العثور على قواعد إعادة تسمية',
    'loading' => 'جارٍ تحميل قواعد إعادة التسمية...',
    'pattern_label' => 'النمط',
    'replace_with_label' => 'استبدال بـ',
    'photo' => 'صورة',
    'album' => 'ألبوم',

    // Delete confirmation
    'confirm_delete_header' => 'تأكيد الحذف',
    'confirm_delete_message' => 'هل أنت متأكد أنك تريد حذف القاعدة ":rule"؟',
    'delete' => 'حذف',

    // Status messages
    'success' => 'نجاح',
    'error' => 'خطأ',

    // Placeholders
    'select_mode' => 'اختر وضع إعادة التسمية',
    'execution_order' => 'ترتيب التنفيذ',

    // Test functionality
    'test_input_placeholder' => 'أدخل اسم ملف لاختبار قواعد إعادة التسمية الخاصة بك (مثال: IMG_1234.jpg)',
    'test_original' => 'الأصلي',
    'test_result' => 'النتيجة',
    'test_failed' => 'فشل اختبار قواعد إعادة التسمية',
    'apply_photo_rules' => 'تطبيق قواعد الصور',
    'apply_album_rules' => 'تطبيق قواعد الألبومات',
];
