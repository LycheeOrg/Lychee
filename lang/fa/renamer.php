<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'قوانین تغییر نام',

    // Modal titles
    'create_rule' => 'ایجاد قانون تغییر نام',
    'edit_rule' => 'ویرایش قانون تغییر نام',

    // Form fields
    'rule_name' => 'نام قانون',
    'description' => 'توضیحات',
    'pattern' => 'الگو',
    'replacement' => 'جایگزین',
    'mode' => 'حالت',
    'order' => 'ترتیب',
    'enabled' => 'فعال',
    'photo_rule' => 'قانون اعمال شده روی عکس‌ها',
    'album_rule' => 'قانون اعمال شده روی آلبوم‌ها',

    // Form placeholders and help text
    'description_placeholder' => 'توضیحات اختیاری در مورد عملکرد این قانون',
    'pattern_help' => 'الگو برای تطبیق (مثال، IMG_، DSC_)',
    'replacement_help' => 'متن جایگزین (مثال، Photo_، Camera_)',
    'order_help' => 'اعداد کمتر ابتدا پردازش می‌شوند (۱ = بالاترین اولویت)',
    'enabled_help' => '(فقط قوانین فعال در زمان تغییر نام اعمال می‌شوند)',

    // Mode options
    'mode_first' => 'اولین رخداد',
    'mode_all' => 'تمام رخدادها',
    'mode_regex' => 'عبارت منظم',
    'mode_trim' => 'حذف فاصله‌های اضافی',
    'mode_strtolower' => 'حروف کوچک',
    'mode_strtoupper' => 'حروف بزرگ',
    'mode_ucwords' => 'حرف اول هر کلمه بزرگ',
    'mode_ucfirst' => 'حرف اول بزرگ',

    'mode_first_description' => 'فقط اولین تطبیق را جایگزین کن',
    'mode_all_description' => 'همه تطبیق‌ها را جایگزین کن',
    'mode_regex_description' => 'از الگوی عبارت منظم استفاده کن',
    'mode_trim_description' => 'حذف فاصله‌های خالی ابتدا و انتهای متن',
    'mode_strtolower_description' => 'تبدیل متن به حروف کوچک',
    'mode_strtoupper_description' => 'تبدیل متن به حروف بزرگ',
    'mode_ucwords_description' => 'بزرگ کردن حرف اول هر کلمه',
    'mode_ucfirst_description' => 'بزرگ کردن فقط حرف اول',

    'regex_help' => 'از عبارت‌های منظم برای تطبیق الگوها استفاده کنید. برای مثال، برای جایگزینی <code>IMG_1234.jpeg</code> با <code>1234_JPG.jpeg</code>، می‌توانید از <code>/IMG_(\d+)/</code> به عنوان الگو و <code>$1_JPG</code> به عنوان جایگزین استفاده کنید. توضیحات و مثال‌های بیشتر را می‌توانید در لینک‌های زیر بیابید.',

    // Buttons
    'cancel' => 'لغو',
    'create' => 'ایجاد',
    'update' => 'به‌روزرسانی',
    'create_first_rule' => 'اولین قانون خود را ایجاد کنید',

    // Validation messages
    'rule_name_required' => 'نام قانون الزامی است',
    'pattern_required' => 'الگو الزامی است',
    'replacement_required' => 'جایگزین الزامی است',
    'mode_required' => 'حالت الزامی است',
    'order_positive' => 'ترتیب باید عدد مثبت باشد',

    // Success messages
    'rule_created' => 'قانون تغییر نام با موفقیت ایجاد شد',
    'rule_updated' => 'قانون تغییر نام با موفقیت به‌روزرسانی شد',
    'rule_deleted' => 'قانون تغییر نام با موفقیت حذف شد',

    // Error messages
    'failed_to_create' => 'ایجاد قانون تغییر نام ناموفق بود',
    'failed_to_update' => 'به‌روزرسانی قانون تغییر نام ناموفق بود',
    'failed_to_delete' => 'حذف قانون تغییر نام ناموفق بود',
    'failed_to_load' => 'بارگیری قوانین تغییر نام ناموفق بود',

    // List view
    'rules_count' => ':count قانون',
    'no_rules' => 'هیچ قانون تغییر نامی یافت نشد',
    'loading' => 'در حال بارگیری قوانین تغییر نام...',
    'pattern_label' => 'الگو',
    'replace_with_label' => 'جایگزین با',
    'photo' => 'عکس',
    'album' => 'آلبوم',

    // Delete confirmation
    'confirm_delete_header' => 'تایید حذف',
    'confirm_delete_message' => 'آیا مطمئن هستید که می‌خواهید قانون ":rule" را حذف کنید؟',
    'delete' => 'حذف',

    // Status messages
    'success' => 'موفقیت',
    'error' => 'خطا',

    // Placeholders
    'select_mode' => 'انتخاب حالت تغییر نام',
    'execution_order' => 'ترتیب اجرا',

    // Test functionality
    'test_input_placeholder' => 'نام فایلی را برای آزمایش قوانین تغییر نام وارد کنید (مثلاً، IMG_1234.jpg)',
    'test_original' => 'اصلی',
    'test_result' => 'نتیجه',
    'test_failed' => 'آزمایش قوانین تغییر نام ناموفق بود',
    'apply_photo_rules' => 'اعمال قوانین عکس',
    'apply_album_rules' => 'اعمال قوانین آلبوم',
];
