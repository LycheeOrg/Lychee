<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'خطافات الويب',
    'description' => 'قم بتهيئة خطافات الويب الصادرة التي يتم تشغيلها عند إضافة الصور أو نقلها أو حذفها.',

    // Empty state
    'no_webhooks' => 'لم يتم تهيئة أي خطافات ويب بعد.',
    'create_first' => 'أنشئ أول خطاف ويب الخاص بك',

    // Table columns
    'col_name' => 'الاسم',
    'col_event' => 'الحدث',
    'col_method' => 'الطريقة',
    'col_url' => 'الرابط',
    'col_format' => 'الصيغة',
    'col_enabled' => 'مفعل',
    'col_actions' => 'الإجراءات',

    // Event labels
    'event_photo_add' => 'تمت إضافة صورة',
    'event_photo_move' => 'تم نقل صورة',
    'event_photo_delete' => 'تم حذف صورة',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'سلسلة الاستعلام',

    // Buttons
    'create' => 'إنشاء خطاف ويب',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'cancel' => 'إلغاء',
    'save' => 'حفظ',

    // Form fields
    'field_name' => 'الاسم',
    'field_name_placeholder' => 'مثال: خطاف الويب الخاص بي',
    'field_event' => 'الحدث',
    'field_method' => 'طريقة HTTP',
    'field_url' => 'الرابط',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'صيغة البيانات',
    'field_enabled' => 'مفعل',
    'field_secret' => 'السر',
    'field_secret_placeholder' => 'اتركه فارغًا للاحتفاظ بالسر الحالي',
    'field_secret_header' => 'ترويسة السر',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'إرسال معرف الصورة',
    'field_send_album_id' => 'إرسال معرف الألبوم',
    'field_send_title' => 'إرسال العنوان',
    'field_send_size_variants' => 'إرسال أحجام الصور المختلفة',

    // Modal titles
    'modal_create_title' => 'إنشاء خطاف ويب',
    'modal_edit_title' => 'تعديل خطاف الويب',

    // Delete confirmation
    'confirm_delete_header' => 'حذف خطاف الويب',
    'confirm_delete_message' => 'هل أنت متأكد أنك تريد حذف خطاف الويب ":name"؟ لا يمكن التراجع عن هذا الإجراء.',
    'delete_warning' => 'لا يمكن التراجع عن هذا الإجراء.',

    // Toasts
    'created' => 'تم إنشاء خطاف الويب بنجاح.',
    'updated' => 'تم تحديث خطاف الويب بنجاح.',
    'deleted' => 'تم حذف خطاف الويب بنجاح.',
    'error_load' => 'فشل تحميل خطافات الويب.',
    'error_save' => 'فشل حفظ خطاف الويب.',
    'error_delete' => 'فشل حذف خطاف الويب.',

    // Secret badge
    'has_secret' => 'تم تعيين السر',
    'no_secret' => 'لا يوجد سر',
];
