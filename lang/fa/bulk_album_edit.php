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

    'title' => 'ویرایش گروهی آلبوم',
    'description' => 'ویرایش ابرداده‌ها و تنظیمات نمایش برای چندین آلبوم به‌طور همزمان.',
    'warning' => 'تغییرات اعمال‌شده در اینجا بلافاصله اجرا می‌شوند و قابل بازگشت نیستند. آلبوم‌های برچسب نمایش داده نمی‌شوند.',

    // Table columns
    'col_title' => 'عنوان',
    'col_owner' => 'مالک',
    'col_license' => 'مجوز',
    'col_is_nsfw' => 'حساس',
    'col_is_public' => 'عمومی',
    'col_is_link_required' => 'لینک',
    'col_grants_full_photo_access' => 'دسترسی کامل به عکس',
    'col_grants_download' => 'دانلود',
    'col_grants_upload' => 'بارگذاری',
    'col_photo_sorting' => 'ترتیب عکس',
    'col_album_sorting' => 'ترتیب آلبوم',
    'col_created_at' => 'تاریخ ایجاد',

    // Filter
    'filter_placeholder' => 'جستجو بر اساس عنوان...',

    // Pagination
    'per_page' => 'در هر صفحه',
    'total_selected' => ':n آلبوم انتخاب شد|:n آلبوم انتخاب شد',
    'select_all_page' => 'انتخاب همه در این صفحه',
    'select_all_matching' => 'انتخاب همه موارد منطبق',
    'cap_warning' => 'فقط ۱۰۰۰ آلبوم اول انتخاب شده‌اند.',

    // Mode toggle
    'mode_paginated' => 'صفحه‌بندی‌شده',
    'mode_infinite' => 'پیمایش نامحدود',

    // Action buttons
    'action_delete' => 'حذف',
    'action_set_owner' => 'تعیین مالک',
    'action_edit_fields' => 'ویرایش فیلدها',

    // Edit Fields modal
    'edit_fields_title' => 'ویرایش فیلدها',
    'edit_fields_description' => 'فقط فیلدهای علامت‌خورده به‌روزرسانی می‌شوند. مقادیر خالی، فیلد را پاک می‌کنند.',
    'section_metadata' => 'ابرداده',
    'section_visibility' => 'نمایش',
    'field_description' => 'توضیحات',
    'field_copyright' => 'کپی‌رایت',
    'field_license' => 'مجوز',
    'field_photo_layout' => 'چیدمان عکس',
    'field_photo_sorting_col' => 'ستون ترتیب عکس',
    'field_photo_sorting_order' => 'نوع ترتیب عکس',
    'field_album_sorting_col' => 'ستون ترتیب آلبوم',
    'field_album_sorting_order' => 'نوع ترتیب آلبوم',
    'field_album_thumb_aspect_ratio' => 'نسبت ابعاد بندانگشتی',
    'field_album_timeline' => 'جدول زمانی آلبوم',
    'field_photo_timeline' => 'جدول زمانی عکس',
    'field_is_nsfw' => 'حساس',
    'field_is_public' => 'عمومی',
    'field_is_link_required' => 'نیازمند لینک',
    'field_grants_full_photo_access' => 'دسترسی کامل به عکس',
    'field_grants_download' => 'دانلود',
    'field_grants_upload' => 'بارگذاری (SE)',
    'apply' => 'اعمال',
    'cancel' => 'انصراف',

    // Set Owner modal
    'set_owner_title' => 'تعیین مالک',
    'set_owner_description' => 'تمام آلبوم‌های انتخاب‌شده به سطح ریشه منتقل می‌شوند و زیرمجموعه‌های آن‌ها نیز منتقل خواهند شد.',
    'set_owner_select_user' => 'انتخاب مالک جدید',
    'transfer' => 'انتقال',

    // Delete confirmation modal
    'delete_title' => 'حذف آلبوم‌ها',
    'delete_confirm' => 'شما در حال حذف دائمی :count آلبوم به همراه تمام زیرآلبوم‌ها و عکس‌های آن هستید. این عملیات قابل بازگشت نیست.|شما در حال حذف دائمی :count آلبوم به همراه تمام زیرآلبوم‌ها و عکس‌های آن‌ها هستید. این عملیات قابل بازگشت نیست.',
    'confirm_delete' => 'تأیید حذف',

    // Toasts
    'success_patch' => 'آلبوم‌ها با موفقیت به‌روزرسانی شدند.',
    'success_set_owner' => 'مالکیت با موفقیت منتقل شد.',
    'success_delete' => 'آلبوم‌ها با موفقیت حذف شدند.',
    'error_load' => 'بارگذاری آلبوم‌ها ناموفق بود.',
    'error_load_ids' => 'بارگذاری شناسه‌های آلبوم ناموفق بود.',
    'error_patch' => 'به‌روزرسانی آلبوم‌ها ناموفق بود.',
    'error_set_owner' => 'انتقال مالکیت ناموفق بود.',
    'error_delete' => 'حذف آلبوم‌ها ناموفق بود.',
    'error_load_users' => 'بارگذاری کاربران ناموفق بود.',
];
