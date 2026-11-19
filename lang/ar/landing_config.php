<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'الصفحة الرئيسية',
    'tab_settings' => 'الإعدادات',
    'tab_links' => 'الروابط',
    'tab_featured' => 'المميز',

    'section_layout' => 'التخطيط والبنية',
    'section_hero' => 'القسم الرئيسي',
    'section_background_landscape' => 'الخلفية (أفقي)',
    'section_background_portrait' => 'الخلفية (عمودي)',
    'section_cta_position' => 'موضع زر الدعوة لاتخاذ إجراء',
    'section_login_position' => 'لوحة تسجيل الدخول',
    'section_meridian' => 'خطوط Meridian',
    'section_content' => 'المحتوى',

    'field_layout' => 'التخطيط',
    'field_intro_screen_enabled' => 'شاشة الترحيب الافتتاحية',
    'field_hero_text_position' => 'موضع نص القسم الرئيسي',
    'field_hero_text_color' => 'لون نص القسم الرئيسي',
    'field_hero_text_opacity' => 'عتامة نص القسم الرئيسي',
    'field_animation_preset' => 'نمط الحركة المسبق',
    'field_backdrop_opacity' => 'عتامة الخلفية (:value%)',
    'field_cta_text' => 'النص',
    'field_cta_text_placeholder' => 'اتركه فارغًا لاستخدام النص الافتراضي للتخطيط',
    'field_about_enabled' => 'قسم "حول"',
    'field_about_text' => 'نص "حول"',

    'field_background_mode' => 'المصدر',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'معرّف الصورة',
        'random' => 'صورة عامة عشوائية',
        'latest_album_cover' => 'غلاف أحدث ألبوم',
        'random_from_album' => 'صورة عشوائية من ألبوم',
    ],
    'field_background_url' => 'رابط الصورة URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'معرّف الصورة',
    'field_background_photo_id_placeholder' => 'معرّف صورة من 24 حرفًا',
    'field_background_photo_id_hint' => 'معرّف الصورة المراد استخدامها. افتح صورة وانسخ آخر 24 حرفًا من الرابط URL.',
    'field_background_album_id' => 'معرّف الألبوم',
    'field_background_album_id_placeholder' => 'معرّف ألبوم من 24 حرفًا',
    'background_load_error' => 'تعذر تحميل الصورة. تأكد من صحة المعرّف ومن أن لديك صلاحية الوصول إليها.',
    'background_mode_hint' => [
        'random' => 'يتم اختيار صورة عامة عشوائية عند كل تحميل للصفحة.',
        'latest_album_cover' => 'يُستخدم غلاف أحدث ألبوم عام منشور.',
        'random_from_album' => 'يتم اختيار صورة عشوائية من هذا الألبوم عند كل تحميل للصفحة. لا تتحدث المعاينة إلا بعد الحفظ.',
    ],

    'preview_orientation_landscape' => 'معاينة الخلفية الأفقية',
    'preview_orientation_portrait' => 'معاينة الخلفية العمودية',

    'field_cta_position' => 'الموضع',
    'cta_position_options' => [
        'top-left' => 'أعلى اليسار',
        'top' => 'أعلى الوسط',
        'top-right' => 'أعلى اليمين',
        'left' => 'وسط اليسار',
        'center' => 'الوسط',
        'right' => 'وسط اليمين',
        'bottom-left' => 'أسفل اليسار',
        'bottom' => 'أسفل الوسط',
        'bottom-right' => 'أسفل اليمين',
    ],
    'field_cta_shift_type' => 'وحدة الإزاحة',
    'cta_shift_type_options' => [
        'relative' => 'نسبية (%)',
        'absolute' => 'مطلقة (px)',
    ],
    'cta_shift_type_hint' => 'الإزاحات النسبية هي نسبة مئوية من حجم منطقة العرض؛ الإزاحات المطلقة هي عدد ثابت من البكسلات.',
    'field_cta_shift_x' => 'الإزاحة الأفقية (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'يسار',
        'right' => 'يمين',
    ],
    'field_cta_shift_y' => 'الإزاحة العمودية (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'أعلى',
        'down' => 'أسفل',
    ],
    'reset_to_zero' => 'إعادة التعيين إلى 0',

    'field_login_position' => 'الموضع',
    'login_position_options' => [
        'side' => 'جانبي',
        'center' => 'الوسط',
    ],
    'field_login_position_hint' => 'على الشاشات الضيقة تكون لوحة تسجيل الدخول دائمًا في الوسط أسفل القسم الرئيسي، بغض النظر عن هذا الإعداد. تنزلق من حافة اتجاه القراءة عندما يكون نمط الحركة "Slide reveal" أو "Parallax scroll".',

    'field_meridian_explore_offset' => 'ارتفاع تسمية الاستكشاف (:value%)',
    'field_meridian_contact_offset' => 'ارتفاع تسمية التواصل (:value%)',
    'field_meridian_offset_hint' => 'مدى انخفاض التسمية على كل خط بكامل الارتفاع: 0% هو الأعلى، و100% هو الأسفل.',
    'field_meridian_explore_line_position' => 'موضع خط الاستكشاف (:value%)',
    'field_meridian_contact_line_position' => 'موضع خط التواصل (:value%)',
    'field_meridian_line_position_hint' => 'مدى بُعد كل خط عن الحافة اليسرى: 0% هي الحافة اليسرى، و100% هي الحافة اليمنى. يتوسط خط الاستكشاف تلقائيًا عند تعطيل خط التواصل.',

    'preview_title' => 'معاينة مباشرة',
    'preview_hint' => 'تتحدث فورًا أثناء التعديل — لا يتم حفظ أي شيء حتى تنقر على حفظ.',
    'flat_list_hint' => 'يمكن أيضًا تعديل هذه الإعدادات من قائمة الإعدادات العامة المسطحة.',

    'save' => 'حفظ',
    'saved' => 'تم حفظ إعدادات الصفحة الرئيسية.',
    'save_error' => 'فشل حفظ إعدادات الصفحة الرئيسية.',

    'se_required' => 'يتطلب هذا التخطيط/النمط Lychee SE.',
];
