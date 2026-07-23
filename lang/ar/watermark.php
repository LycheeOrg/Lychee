<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'معاينة العلامة المائية',
        'se_required' => 'تتطلب وحدة العلامة المائية تفعيل إصدار الدعم (SE) الخاص بـ Lychee أو معاينة SE.',

        'section_settings' => 'إعدادات العلامة المائية',
        'section_preview' => 'معاينة حية',
        'disclaimer' => 'تعطي هذه المعاينة فكرة عن شكل العلامة المائية. قد تختلف النتيجة النهائية على صورك الفعلية قليلاً.',

        'watermark_photo_id' => 'معرف صورة العلامة المائية',
        'watermark_photo_id_placeholder' => 'معرف صورة من 24 حرفًا',
        'watermark_photo_id_hint' => 'معرف الصورة المستخدمة كعلامة مائية. افتح صورة وانسخ آخر 24 حرفًا من الرابط.',

        'preview_photo_id' => 'معرف صورة الخلفية',
        'preview_photo_id_placeholder' => 'معرف صورة من 24 حرفًا',
        'preview_photo_id_hint' => 'أدخل معرف صورة لاستخدامها كخلفية للمعاينة.',

        'size' => 'الحجم (:value%)',
        'opacity' => 'التعتيم (:value%)',
        'position' => 'الموضع',
        'position_options' => [
            'top-left' => 'أعلى اليسار',
            'top' => 'أعلى الوسط',
            'top-right' => 'أعلى اليمين',
            'left' => 'وسط اليسار',
            'center' => 'المركز',
            'right' => 'وسط اليمين',
            'bottom-left' => 'أسفل اليسار',
            'bottom' => 'أسفل الوسط',
            'bottom-right' => 'أسفل اليمين',
        ],

        'section_shift' => 'الإزاحة',
        'shift_type' => 'وحدة الإزاحة',
        'shift_type_options' => [
            'relative' => 'نسبية (%)',
            'absolute' => 'مطلقة (بكسل)',
        ],
        'shift_type_hint' => 'الإزاحات النسبية هي نسبة مئوية من حجم الصورة؛ أما الإزاحات المطلقة فهي عدد ثابت من البكسلات.',
        'shift_mode_use_slider' => 'استخدام شريط التمرير',
        'shift_mode_use_classic' => 'استخدام إدخال رقمي',
        'shift_x' => 'الإزاحة الأفقية (:value)',
        'shift_x_direction_options' => [
            'left' => 'يسار',
            'right' => 'يمين',
        ],
        'shift_y' => 'الإزاحة الرأسية (:value)',
        'shift_y_direction_options' => [
            'up' => 'أعلى',
            'down' => 'أسفل',
        ],

        'save' => 'حفظ الإعدادات',
        'saved' => 'تم حفظ إعدادات العلامة المائية.',
        'save_error' => 'فشل حفظ إعدادات العلامة المائية.',
        'save_requires_se' => 'يتطلب حفظ إعدادات العلامة المائية ترخيص إصدار الدعم (SE) الكامل. تسمح معاينة SE بمعاينة التأثير فقط.',

        'no_watermark_image' => 'لم يتم تهيئة صورة علامة مائية. أدخل معرف صورة العلامة المائية وانقر على "تحميل" للمعاينة.',
        'no_preview_photo' => 'أدخل معرف صورة الخلفية أعلاه لمعاينة تراكب العلامة المائية.',
        'photo_load_error' => 'تعذر تحميل الصورة. تأكد من صحة المعرف وأن لديك صلاحية الوصول إليها.',
        'watermark_load_error' => 'تعذر تحميل صورة العلامة المائية. تأكد من صحة معرف الصورة.',
    ],
];
