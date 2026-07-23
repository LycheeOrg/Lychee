<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'التعديل الجماعي للألبومات',
    'description' => 'تعديل البيانات الوصفية وإعدادات الظهور لعدة ألبومات دفعة واحدة.',
    'warning' => 'يتم تطبيق التغييرات هنا فورًا ولا يمكن التراجع عنها. لا تظهر ألبومات العلامات هنا.',

    // Table columns
    'col_title' => 'العنوان',
    'col_owner' => 'المالك',
    'col_license' => 'الترخيص',
    'col_is_nsfw' => 'حساس',
    'col_is_public' => 'عام',
    'col_is_link_required' => 'الرابط',
    'col_grants_full_photo_access' => 'الصورة الكاملة',
    'col_grants_download' => 'تنزيل',
    'col_grants_upload' => 'تحميل',
    'col_photo_sorting' => 'ترتيب الصور',
    'col_album_sorting' => 'ترتيب الألبومات',
    'col_created_at' => 'تاريخ الإنشاء',

    // Filter
    'filter_placeholder' => 'البحث حسب العنوان...',

    // Pagination
    'per_page' => 'لكل صفحة',
    'total_selected' => ':n ألبوم محدد|:n ألبومات محددة',
    'select_all_page' => 'تحديد الكل في هذه الصفحة',
    'select_all_matching' => 'تحديد كل المطابقات',
    'cap_warning' => 'تم تحديد أول 1,000 ألبوم فقط.',

    // Mode toggle
    'mode_paginated' => 'مقسّم إلى صفحات',
    'mode_infinite' => 'تمرير لانهائي',

    // Action buttons
    'action_delete' => 'حذف',
    'action_set_owner' => 'تعيين المالك',
    'action_edit_fields' => 'تعديل الحقول',

    // Edit Fields modal
    'edit_fields_title' => 'تعديل الحقول',
    'edit_fields_description' => 'سيتم تحديث الحقول المحددة فقط. القيم الفارغة تؤدي إلى مسح الحقل.',
    'section_metadata' => 'البيانات الوصفية',
    'section_visibility' => 'الظهور',
    'field_description' => 'الوصف',
    'field_copyright' => 'حقوق الطبع والنشر',
    'field_license' => 'الترخيص',
    'field_photo_layout' => 'تخطيط الصور',
    'field_photo_sorting_col' => 'عمود ترتيب الصور',
    'field_photo_sorting_order' => 'اتجاه ترتيب الصور',
    'field_album_sorting_col' => 'عمود ترتيب الألبومات',
    'field_album_sorting_order' => 'اتجاه ترتيب الألبومات',
    'field_album_thumb_aspect_ratio' => 'نسبة عرض إلى ارتفاع الصورة المصغرة',
    'field_album_timeline' => 'الجدول الزمني للألبوم',
    'field_photo_timeline' => 'الجدول الزمني للصور',
    'field_is_nsfw' => 'حساس',
    'field_is_public' => 'عام',
    'field_is_link_required' => 'الرابط مطلوب',
    'field_grants_full_photo_access' => 'الوصول الكامل للصورة',
    'field_grants_download' => 'تنزيل',
    'field_grants_upload' => 'تحميل (SE)',
    'apply' => 'تطبيق',
    'cancel' => 'إلغاء',

    // Set Owner modal
    'set_owner_title' => 'تعيين المالك',
    'set_owner_description' => 'سيتم نقل جميع الألبومات المحددة إلى المستوى الجذري، كما سيتم نقل جميع الألبومات الفرعية التابعة لها.',
    'set_owner_select_user' => 'اختر المالك الجديد',
    'transfer' => 'نقل',

    // Delete confirmation modal
    'delete_title' => 'حذف الألبومات',
    'delete_confirm' => 'أنت على وشك حذف :count ألبوم وجميع الألبومات الفرعية والصور التابعة له نهائيًا. لا يمكن التراجع عن هذا الإجراء.|أنت على وشك حذف :count ألبومات وجميع الألبومات الفرعية والصور التابعة لها نهائيًا. لا يمكن التراجع عن هذا الإجراء.',
    'confirm_delete' => 'تأكيد الحذف',

    // Toasts
    'success_patch' => 'تم تحديث الألبومات بنجاح.',
    'success_set_owner' => 'تم نقل الملكية بنجاح.',
    'success_delete' => 'تم حذف الألبومات بنجاح.',
    'error_load' => 'فشل تحميل الألبومات.',
    'error_load_ids' => 'فشل تحميل معرفات الألبومات.',
    'error_patch' => 'فشل تحديث الألبومات.',
    'error_set_owner' => 'فشل نقل الملكية.',
    'error_delete' => 'فشل حذف الألبومات.',
    'error_load_users' => 'فشل تحميل المستخدمين.',
];
