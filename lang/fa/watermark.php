<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'پیش‌نمایش واترمارک',
        'se_required' => 'ماژول واترمارک نیازمند فعال بودن نسخه حمایت‌کنندگان Lychee (SE) یا SE Preview است.',

        'section_settings' => 'تنظیمات واترمارک',
        'section_preview' => 'پیش‌نمایش زنده',
        'disclaimer' => 'این پیش‌نمایش تصوری از ظاهر واترمارک ارائه می‌دهد. نتیجه نهایی روی عکس‌های واقعی شما ممکن است کمی متفاوت باشد.',

        'watermark_photo_id' => 'شناسه تصویر واترمارک',
        'watermark_photo_id_placeholder' => 'شناسه ۲۴ نویسه‌ای عکس',
        'watermark_photo_id_hint' => 'شناسه عکسی که به‌عنوان واترمارک استفاده می‌شود. یک عکس را باز کنید و ۲۴ نویسه آخر URL را کپی کنید.',

        'preview_photo_id' => 'شناسه عکس پس‌زمینه',
        'preview_photo_id_placeholder' => 'شناسه ۲۴ نویسه‌ای عکس',
        'preview_photo_id_hint' => 'شناسه یک عکس را برای استفاده به‌عنوان پس‌زمینه پیش‌نمایش وارد کنید.',

        'size' => 'اندازه (:value%)',
        'opacity' => 'شفافیت (:value%)',
        'position' => 'موقعیت',
        'position_options' => [
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

        'section_shift' => 'جابه‌جایی / فاصله',
        'shift_type' => 'واحد جابه‌جایی',
        'shift_type_options' => [
            'relative' => 'نسبی (%)',
            'absolute' => 'مطلق (px)',
        ],
        'shift_type_hint' => 'جابه‌جایی نسبی درصدی از اندازه تصویر است؛ جابه‌جایی مطلق تعداد ثابتی پیکسل است.',
        'shift_mode_use_slider' => 'استفاده از اسلایدر',
        'shift_mode_use_classic' => 'استفاده از ورودی عددی',
        'shift_x' => 'جابه‌جایی افقی (:value)',
        'shift_x_direction_options' => [
            'left' => 'چپ',
            'right' => 'راست',
        ],
        'shift_y' => 'جابه‌جایی عمودی (:value)',
        'shift_y_direction_options' => [
            'up' => 'بالا',
            'down' => 'پایین',
        ],
        'reset_to_zero' => 'بازنشانی به ۰',

        'save' => 'ذخیره تنظیمات',
        'saved' => 'تنظیمات واترمارک ذخیره شد.',
        'save_error' => 'ذخیره تنظیمات واترمارک ناموفق بود.',
        'save_requires_se' => 'ذخیره تنظیمات واترمارک نیازمند مجوز کامل نسخه حمایت‌کنندگان (SE) است. SE Preview فقط امکان پیش‌نمایش جلوه را می‌دهد.',

        'no_watermark_image' => 'هیچ تصویر واترمارکی پیکربندی نشده است. شناسه عکس واترمارک را وارد کرده و برای پیش‌نمایش روی "بارگذاری" کلیک کنید.',
        'no_preview_photo' => 'برای پیش‌نمایش واترمارک، شناسه عکس پس‌زمینه را در بالا وارد کنید.',
        'photo_load_error' => 'بارگذاری عکس ممکن نشد. مطمئن شوید شناسه درست است و به آن دسترسی دارید.',
        'watermark_load_error' => 'بارگذاری تصویر واترمارک ممکن نشد. مطمئن شوید شناسه عکس درست است.',
    ],
];
