<?php
return [
    /*
    |--------------------------------------------------------------------------
    | صفحه پروفایل
    |--------------------------------------------------------------------------
    */
    'title' => 'پروفایل',
    'login' => [
        'header' => 'پروفایل',
        'enter_current_password' => 'رمزعبور فعلی خود را وارد کنید:',
        'current_password' => 'رمزعبور فعلی',
        'credentials_update' => 'اطلاعات کاربری شما به موارد زیر تغییر خواهد کرد:',
        'username' => 'نام کاربری',
        'new_password' => 'رمزعبور جدید',
        'confirm_new_password' => 'تأیید رمزعبور جدید',
        'email_instruction' => 'برای فعال سازی دریافت اعلان‌های ایمیلی، ایمیل خود را وارد کنید. برای توقف دریافت ایمیل، کافی است ایمیل خود را حذف کنید.',
        'email' => 'ایمیل',
        'change' => 'تأیید تغییرات',
        'api_token' => 'توکن API …',
        'missing_fields' => 'فیلدهای ناقص',
    ],
    'register' => [
        'username_exists' => 'Username already exists.',
        'password_mismatch' => 'The passwords do not match.',
        'signup' => 'Sign Up',
        'error' => 'An error occurred while registering your account.',
        'success' => 'Your account has been successfully created.',
    ],
    'token' => [
        'unavailable' => 'شما قبلاً این توکن را مشاهده کرده‌اید.',
        'no_data' => 'هیچ توکن API ایجاد نشده است.',
        'disable' => 'غیرفعال کردن',
        'disabled' => 'توکن غیرفعال شد',
        'warning' => 'این توکن دیگر نمایش داده نخواهد شد. آن را کپی کرده و در جای امنی نگهداری کنید.',
        'reset' => 'بازنویسی توکن',
        'create' => 'ایجاد توکن جدید',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth در دسترس نیست',
        'setup_env' => 'اطلاعات کاربری را در .env تنظیم کنید',
        'token_registered' => 'توکن %s ثبت شد.',
        'setup' => 'راه‌اندازی %s',
        'reset' => 'بازنویسی',
        'credential_deleted' => 'اطلاعات کاربری حذف شد!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'این فقط امکان استفاده از WebAuthn برای احراز هویت به جای نام کاربری و رمز عبور را فراهم می‌کند.',
        'empty' => 'لیست اطلاعات کاربری خالی است!',
        'not_secure' => 'محیط ایمن نیست. U2F در دسترس نیست.',
        'new' => 'ثبت دستگاه جدید.',
        'credential_deleted' => 'اطلاعات کاربری حذف شد!',
        'credential_updated' => 'اطلاعات کاربری به‌روزرسانی شد!',
        'credential_registred' => 'ثبت‌نام موفقیت آمیز بود!',
        '5_chars' => 'حداقل ۵ کاراکتر.',
    ],
];
