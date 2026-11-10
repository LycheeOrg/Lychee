<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'روابط إضافية',
    'description' => 'إدارة قائمة مرتبة وحرة من الروابط الإضافية المعروضة في شريط التنقل و/أو تذييل الصفحة الرئيسية.',

    // Empty state
    'no_links' => 'لم يتم إعداد أي روابط إضافية بعد.',
    'create_first' => 'أنشئ رابطك الأول',

    // Table columns
    'col_label' => 'التسمية',
    'col_url' => 'URL',
    'col_placement' => 'الموضع',
    'col_enabled' => 'مُفعّل',
    'col_actions' => 'الإجراءات',

    // Placement labels
    'placement_nav' => 'التنقل',
    'placement_footer' => 'التذييل',
    'placement_both' => 'التنقل والتذييل',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'مدمج',
    'built_in_cannot_delete' => 'لا يمكن حذف هذا الرابط المدمج. يمكنك تعطيله بدلاً من ذلك، أو إعادة ترتيبه مثل أي رابط آخر.',
    'built_in_url_hint' => 'هذا رابط مدمج ولا يمكن تغيير وجهته.',

    // Buttons
    'create' => 'إنشاء رابط',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'cancel' => 'إلغاء',
    'save' => 'حفظ',

    // Form fields
    'field_label' => 'التسمية',
    'field_label_placeholder' => 'مثال: Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'الموضع',
    'field_open_in_new_tab' => 'فتح في علامة تبويب جديدة',
    'field_enabled' => 'مُفعّل',

    // Modal titles
    'modal_create_title' => 'إنشاء رابط',
    'modal_edit_title' => 'تعديل الرابط',

    // Delete confirmation
    'confirm_delete_header' => 'حذف الرابط',
    'confirm_delete_message' => 'هل أنت متأكد من أنك تريد حذف الرابط ":label"؟ لا يمكن التراجع عن هذا الإجراء.',

    // Toasts
    'created' => 'تم إنشاء الرابط بنجاح.',
    'updated' => 'تم تحديث الرابط بنجاح.',
    'deleted' => 'تم حذف الرابط بنجاح.',
    'reordered' => 'تمت إعادة ترتيب الروابط بنجاح.',
    'error_load' => 'فشل تحميل الروابط.',
    'error_save' => 'فشل حفظ الرابط.',
    'error_delete' => 'فشل حذف الرابط.',
    'error_reorder' => 'فشل إعادة ترتيب الروابط.',
];
