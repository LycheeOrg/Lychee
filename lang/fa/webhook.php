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

    'title' => 'وب‌هوک‌ها',
    'description' => 'وب‌هوک‌های خروجی که هنگام افزودن، جابه‌جایی یا حذف عکس‌ها فعال می‌شوند را پیکربندی کنید.',

    // Empty state
    'no_webhooks' => 'هنوز هیچ وب‌هوکی پیکربندی نشده است.',
    'create_first' => 'اولین وب‌هوک خود را ایجاد کنید',

    // Table columns
    'col_name' => 'نام',
    'col_event' => 'رویداد',
    'col_method' => 'روش',
    'col_url' => 'آدرس',
    'col_format' => 'قالب',
    'col_enabled' => 'فعال',
    'col_actions' => 'عملیات',

    // Event labels
    'event_photo_add' => 'عکس افزوده شد',
    'event_photo_move' => 'عکس جابه‌جا شد',
    'event_photo_delete' => 'عکس حذف شد',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'رشته پرسمان (Query String)',

    // Buttons
    'create' => 'ایجاد وب‌هوک',
    'edit' => 'ویرایش',
    'delete' => 'حذف',
    'cancel' => 'انصراف',
    'save' => 'ذخیره',

    // Form fields
    'field_name' => 'نام',
    'field_name_placeholder' => 'مثلاً: وب‌هوک من',
    'field_event' => 'رویداد',
    'field_method' => 'روش HTTP',
    'field_url' => 'آدرس',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'قالب محتوا',
    'field_enabled' => 'فعال',
    'field_secret' => 'رمز',
    'field_secret_placeholder' => 'برای حفظ رمز موجود، خالی بگذارید',
    'field_secret_header' => 'هدر رمز',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'ارسال شناسه عکس',
    'field_send_album_id' => 'ارسال شناسه آلبوم',
    'field_send_title' => 'ارسال عنوان',
    'field_send_size_variants' => 'ارسال نسخه‌های اندازه',

    // Modal titles
    'modal_create_title' => 'ایجاد وب‌هوک',
    'modal_edit_title' => 'ویرایش وب‌هوک',

    // Delete confirmation
    'confirm_delete_header' => 'حذف وب‌هوک',
    'confirm_delete_message' => 'آیا مطمئن هستید که می‌خواهید وب‌هوک ":name" را حذف کنید؟ این عمل قابل بازگشت نیست.',
    'delete_warning' => 'این عمل قابل بازگشت نیست.',

    // Toasts
    'created' => 'وب‌هوک با موفقیت ایجاد شد.',
    'updated' => 'وب‌هوک با موفقیت به‌روزرسانی شد.',
    'deleted' => 'وب‌هوک با موفقیت حذف شد.',
    'error_load' => 'بارگذاری وب‌هوک‌ها ناموفق بود.',
    'error_save' => 'ذخیره وب‌هوک ناموفق بود.',
    'error_delete' => 'حذف وب‌هوک ناموفق بود.',

    // Secret badge
    'has_secret' => 'رمز تنظیم شده',
    'no_secret' => 'بدون رمز',
];
