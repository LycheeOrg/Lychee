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

    'title' => 'پیوندهای اضافی',
    'description' => 'فهرست مرتب و دلخواهی از پیوندهای اضافی را که در منوی ناوبری و/یا پانویس صفحه فرود نمایش داده می‌شوند مدیریت کنید.',

    // Empty state
    'no_links' => 'هنوز هیچ پیوند اضافی پیکربندی نشده است.',
    'create_first' => 'اولین پیوند خود را ایجاد کنید',

    // Table columns
    'col_label' => 'برچسب',
    'col_url' => 'URL',
    'col_placement' => 'محل قرارگیری',
    'col_enabled' => 'فعال',
    'col_actions' => 'اقدامات',

    // Placement labels
    'placement_nav' => 'ناوبری',
    'placement_footer' => 'پانویس',
    'placement_both' => 'ناوبری و پانویس',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'داخلی',
    'built_in_cannot_delete' => 'این پیوند داخلی قابل حذف نیست. به‌جای آن غیرفعالش کنید، یا مانند هر پیوند دیگری ترتیب آن را تغییر دهید.',
    'built_in_url_hint' => 'این یک پیوند داخلی است و مقصد آن قابل تغییر نیست.',

    // Buttons
    'create' => 'ایجاد پیوند',
    'edit' => 'ویرایش',
    'delete' => 'حذف',
    'cancel' => 'لغو',
    'save' => 'ذخیره',

    // Form fields
    'field_label' => 'برچسب',
    'field_label_placeholder' => 'مثلاً Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'محل قرارگیری',
    'field_open_in_new_tab' => 'باز کردن در زبانه جدید',
    'field_enabled' => 'فعال',

    // Modal titles
    'modal_create_title' => 'ایجاد پیوند',
    'modal_edit_title' => 'ویرایش پیوند',

    // Delete confirmation
    'confirm_delete_header' => 'حذف پیوند',
    'confirm_delete_message' => 'آیا مطمئن هستید که می‌خواهید پیوند ":label" را حذف کنید؟ این عمل قابل بازگشت نیست.',

    // Toasts
    'created' => 'پیوند با موفقیت ایجاد شد.',
    'updated' => 'پیوند با موفقیت به‌روزرسانی شد.',
    'deleted' => 'پیوند با موفقیت حذف شد.',
    'reordered' => 'ترتیب پیوندها با موفقیت تغییر کرد.',
    'error_load' => 'بارگذاری پیوندها ناموفق بود.',
    'error_save' => 'ذخیره پیوند ناموفق بود.',
    'error_delete' => 'حذف پیوند ناموفق بود.',
    'error_reorder' => 'تغییر ترتیب پیوندها ناموفق بود.',
];
