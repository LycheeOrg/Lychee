<?php

return [
	/*
	|--------------------------------------------------------------------------
	| صفحه تنظیمات
	|--------------------------------------------------------------------------
	*/
	'title' => 'تنظیمات',
	'small_screen' => 'برای تجربه بهتر در صفحه تنظیمات،<br />توصیه می‌کنیم از صفحه نمایش بزرگتری استفاده کنید.',
	'tabs' => [
		'basic' => 'پایه',
		'all_settings' => 'همه تنظیمات',
	],
	'toasts' => [
		'change_saved' => 'تغییر ذخیره شد!',
		'details' => 'تنظیمات طبق درخواست تغییر یافت',
		'error' => 'خطا!',
		'error_load_css' => 'امکان بارگذاری dist/user.css وجود ندارد',
		'error_load_js' => 'امکان بارگذاری dist/custom.js وجود ندارد',
		'error_save_css' => 'امکان ذخیره CSS وجود ندارد',
		'error_save_js' => 'امکان ذخیره JS وجود ندارد',
		'thank_you' => 'از حمایت شما سپاسگزاریم.',
		'reload' => 'برای عملکرد کامل، صفحه خود را مجدداً بارگذاری کنید.',
	],
	'system' => [
		'header' => 'سیستم',
		'use_dark_mode' => 'استفاده از حالت تاریک برای لیچی',
		'language' => 'زبان مورد استفاده توسط لیچی',
		'nsfw_album_visibility' => 'نمایش آلبوم‌های حساس به صورت پیش فرض.',
		'nsfw_album_explanation' => 'اگر آلبوم عمومی باشد، همچنان قابل دسترسی است اما فقط از دید پنهان است و <b>با فشردن <kbd>H</kbd> قابل نمایش است</b>.',
		'cache_enabled' => 'فعال‌سازی کش پاسخ‌ها.',
		'cache_enabled_details' => 'این کار زمان پاسخ دهی لیچی را به طور قابل توجهی افزایش می‌دهد.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>اگر از آلبوم‌های رمز گذاری شده استفاده می‌کنید، نباید این گزینه را فعال کنید.',
	],
	'lychee_se' => [
		'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
		'call4action' => 'ویژگی‌های انحصاری دریافت کنید و از توسعه Lychee حمایت کنید. <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">نسخه SE</a> را فعال کنید.',
		'preview' => 'پیش‌نمایش ویژگی‌های Lychee SE را فعال کنید',
		'hide_call4action' => 'این فرم ثبت نام Lychee SE را مخفی کن. من از Lychee فعلی راضی هستم. :)',
		'hide_warning' => 'در صورت فعال‌سازی، تنها راه ثبت کلید مجوز از طریق تب More در بالا خواهد بود. تغییرات با بارگذاری مجدد صفحه اعمال می‌شوند.',
	],
	'dropbox' => [
		'header' => 'Dropbox',
		'instruction' => 'برای وارد کردن عکس‌ها از Dropbox، به کلید drop-ins معتبر از وب‌سایت آن‌ها نیاز دارید.',
		'api_key' => 'کلید API دراپ باکس',
		'set_key' => 'تنظیم کلید Dropbox',
	],
	'gallery' => [
		'header' => 'گالری',
		'photo_order_column' => 'ستون پیش فرض برای مرتب‌سازی عکس‌ها',
		'photo_order_direction' => 'ترتیب پیش فرض برای مرتب‌سازی عکس‌ها',
		'album_order_column' => 'ستون پیش فرض برای مرتب‌سازی آلبوم‌ها',
		'album_order_direction' => 'ترتیب پیش فرض برای مرتب‌سازی آلبوم‌ها',
		'aspect_ratio' => 'نسبت تصویر پیش فرض برای بندانگشتی آلبوم',
		'photo_layout' => 'چیدمان تصاویر',
		'album_decoration' => 'نمایش تزئینات روی جلد آلبوم (تعداد زیرآلبوم و/یا عکس)',
		'album_decoration_direction' => 'تراز افقی یا عمودی تزئینات آلبوم',
		'photo_overlay' => 'اطلاعات پیش فرض پوشش تصویر',
		'license_default' => 'مجوز پیش فرض برای آلبوم‌ها',
		'license_help' => 'در انتخاب نیاز به راهنمایی دارید؟',
	],
	'geolocation' => [
		'header' => 'مکان‌یابی جغرافیایی',
		'map_display' => 'نمایش نقشه با توجه به مختصات GPS',
		'map_display_public' => 'اجازه دسترسی کاربران ناشناس به نقشه',
		'map_provider' => 'تعیین ارائه دهنده نقشه',
		'map_include_subalbums' => 'نمایش تصاویر زیرآلبوم‌ها روی نقشه',
		'location_decoding' => 'استفاده از رمزگشایی موقعیت GPS',
		'location_show' => 'نمایش موقعیت استخراج شده از مختصات GPS',
		'location_show_public' => 'کاربران ناشناس می‌توانند به موقعیت استخراج شده از مختصات GPS دسترسی داشته باشند',
	],
	'cssjs' => [
		'header' => 'CSS و JS سفارشی',
		'change_css' => 'تغییر CSS',
		'change_js' => 'تغییر JS',
	],
	'all' => [
		'old_setting_style' => 'سبک قدیمی تنظیمات',
		'expert_settings' => 'حالت حرفه‌ای',
		'change_detected' => 'برخی تنظیمات تغییر کرده‌اند.',
		'save' => 'ذخیره',
		'back_to_settings' => 'بازگشت به تنظیمات گروه‌بندی‌شده',
	],

	'tool_option' => [
		'disabled' => 'غیرفعال',
		'enabled' => 'فعال',
		'discover' => 'کشف',
	],

	'groups' => [
		'general' => 'عمومی',
		'system' => 'سیستم',
		'modules' => 'ماژول‌ها',
		'advanced' => 'پیشرفته',
	],
];