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

    'title' => 'صفحه فرود',
    'tab_settings' => 'تنظیمات',
    'tab_links' => 'پیوندها',
    'tab_featured' => 'ویژه',

    'section_layout' => 'چیدمان و ساختار',
    'section_hero' => 'بخش اصلی (Hero)',
    'section_background_landscape' => 'پس‌زمینه (افقی)',
    'section_background_portrait' => 'پس‌زمینه (عمودی)',
    'section_cta_position' => 'موقعیت دکمه فراخوان (CTA)',
    'section_login_position' => 'پنل ورود',
    'section_meridian' => 'خطوط Meridian',
    'section_content' => 'محتوا',

    'field_layout' => 'چیدمان',
    'field_intro_screen_enabled' => 'صفحه آغازین معرفی',
    'field_hero_text_position' => 'موقعیت متن بخش اصلی',
    'field_hero_text_color' => 'رنگ متن بخش اصلی',
    'field_hero_text_opacity' => 'شفافیت متن بخش اصلی',
    'field_animation_preset' => 'پیش‌تنظیم انیمیشن',
    'field_backdrop_opacity' => 'شفافیت پس‌زمینه (:value%)',
    'field_cta_text' => 'متن',
    'field_cta_text_placeholder' => 'برای پیش‌فرض چیدمان خالی بگذارید',
    'field_about_enabled' => 'بخش درباره',
    'field_about_text' => 'متن درباره',

    'field_background_mode' => 'منبع',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'شناسه عکس',
        'random' => 'عکس عمومی تصادفی',
        'latest_album_cover' => 'کاور جدیدترین آلبوم',
        'random_from_album' => 'عکس تصادفی از آلبوم',
    ],
    'field_background_url' => 'URL تصویر',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'شناسه عکس',
    'field_background_photo_id_placeholder' => 'شناسه ۲۴ نویسه‌ای عکس',
    'field_background_photo_id_hint' => 'شناسه عکسی که باید استفاده شود. یک عکس را باز کنید و ۲۴ نویسه آخر URL را کپی کنید.',
    'field_background_album_id' => 'شناسه آلبوم',
    'field_background_album_id_placeholder' => 'شناسه ۲۴ نویسه‌ای آلبوم',
    'background_load_error' => 'عکس بارگذاری نشد. مطمئن شوید شناسه درست است و به آن دسترسی دارید.',
    'background_mode_hint' => [
        'random' => 'در هر بار بارگذاری صفحه، یک عکس عمومی تصادفی انتخاب می‌شود.',
        'latest_album_cover' => 'از کاور جدیدترین آلبوم عمومی منتشرشده استفاده می‌شود.',
        'random_from_album' => 'در هر بار بارگذاری صفحه، یک عکس تصادفی از این آلبوم انتخاب می‌شود. پیش‌نمایش فقط پس از ذخیره به‌روز می‌شود.',
    ],

    'preview_orientation_landscape' => 'پیش‌نمایش پس‌زمینه افقی',
    'preview_orientation_portrait' => 'پیش‌نمایش پس‌زمینه عمودی',

    'field_cta_position' => 'موقعیت',
    'cta_position_options' => [
        'top-left' => 'بالا چپ',
        'top' => 'بالا وسط',
        'top-right' => 'بالا راست',
        'left' => 'وسط چپ',
        'center' => 'مرکز',
        'right' => 'وسط راست',
        'bottom-left' => 'پایین چپ',
        'bottom' => 'پایین وسط',
        'bottom-right' => 'پایین راست',
    ],
    'field_cta_shift_type' => 'واحد جابه‌جایی',
    'cta_shift_type_options' => [
        'relative' => 'نسبی (%)',
        'absolute' => 'مطلق (px)',
    ],
    'cta_shift_type_hint' => 'جابه‌جایی نسبی درصدی از اندازه نمای صفحه است؛ جابه‌جایی مطلق تعداد ثابتی پیکسل است.',
    'field_cta_shift_x' => 'جابه‌جایی افقی (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'چپ',
        'right' => 'راست',
    ],
    'field_cta_shift_y' => 'جابه‌جایی عمودی (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'بالا',
        'down' => 'پایین',
    ],
    'reset_to_zero' => 'بازنشانی به 0',

    'field_login_position' => 'موقعیت',
    'login_position_options' => [
        'side' => 'کنار',
        'center' => 'مرکز',
    ],
    'field_login_position_hint' => 'در صفحه‌های باریک، پنل ورود صرف‌نظر از این تنظیم همیشه در مرکز و زیر بخش اصلی قرار می‌گیرد. وقتی پیش‌تنظیم انیمیشن "نمایش لغزشی" یا "اسکرول پارالاکس" باشد، از لبه جهت خواندن به داخل می‌لغزد.',

    'field_meridian_explore_offset' => 'ارتفاع برچسب کاوش (:value%)',
    'field_meridian_contact_offset' => 'ارتفاع برچسب تماس (:value%)',
    'field_meridian_offset_hint' => 'برچسب هر خط تمام‌قد چه اندازه پایین‌تر قرار می‌گیرد: 0% بالا و 100% پایین است.',
    'field_meridian_explore_line_position' => 'موقعیت خط کاوش (:value%)',
    'field_meridian_contact_line_position' => 'موقعیت خط تماس (:value%)',
    'field_meridian_line_position_hint' => 'فاصله هر خط از لبه چپ: 0% لبه چپ و 100% لبه راست است. وقتی خط تماس خاموش باشد، خط کاوش همیشه خود را در مرکز قرار می‌دهد.',

    'preview_title' => 'پیش‌نمایش زنده',
    'preview_hint' => 'با ویرایش، فوراً به‌روز می‌شود — تا روی ذخیره کلیک نکنید چیزی ذخیره نمی‌شود.',
    'flat_list_hint' => 'این تنظیمات از فهرست عمومی تنظیمات نیز قابل ویرایش هستند.',

    'save' => 'ذخیره',
    'saved' => 'تنظیمات صفحه فرود ذخیره شد.',
    'save_error' => 'ذخیره تنظیمات صفحه فرود ناموفق بود.',

    'se_required' => 'این چیدمان/پیش‌تنظیم به Lychee SE نیاز دارد.',
];
