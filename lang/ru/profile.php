<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Profile page
    |--------------------------------------------------------------------------
    */
    'title' => 'Профиль',
    'login' => [
        'header' => 'Профиль',
        'enter_current_password' => 'Введите ваш текущий пароль:',
        'current_password' => 'Текущий пароль',
        'credentials_update' => 'Ваши данные будут изменены на следующие:',
        'username' => 'Имя пользователя',
        'new_password' => 'Новый пароль',
        'confirm_new_password' => 'Подтвердите новый пароль',
        'email_instruction' => 'Добавьте ваш email ниже, чтобы получать уведомления по электронной почте. Чтобы прекратить получение писем, просто удалите ваш email ниже.',
        'email' => 'Email',
        'change' => 'Изменить логин',
        'api_token' => 'API Токен …',
        'missing_fields' => 'Отсутствуют поля',
    ],
    'register' => [
        'username_exists' => 'Имя пользователя уже существует.',
        'password_mismatch' => 'Пароли не совпадают.',
        'signup' => 'Зарегистрироваться',
        'error' => 'Произошла ошибка при регистрации вашей учетной записи.',
        'success' => 'Ваша учетная запись была успешно создана.',
    ],
    'token' => [
        'unavailable' => 'Вы уже просмотрели этот токен.',
        'no_data' => 'Токен API не был сгенерирован.',
        'disable' => 'Отключить',
        'disabled' => 'Токен отключён',
        'warning' => 'Этот токен больше не будет отображаться. Скопируйте его и сохраните в надежном месте.',
        'reset' => 'Сбросить токен',
        'create' => 'Создать новый токен',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth недоступен',
        'setup_env' => 'Настройте данные для входа в вашем .env',
        'token_registered' => 'Токен %s зарегистрирован.',
        'setup' => 'Настроить %s',
        'reset' => 'сбросить',
        'credential_deleted' => 'Данные удалены!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'Это предоставляет возможность использовать WebAuthn для аутентификации вместо имени пользователя и пароля.',
        'empty' => 'Список данных пуст!',
        'not_secure' => 'Среда не защищена. U2F недоступен.',
        'new' => 'Зарегистрировать новое устройство.',
        'credential_deleted' => 'Данные удалены!',
        'credential_updated' => 'Данные обновлены!',
        'credential_registred' => 'Регистрация прошла успешно!',
        '5_chars' => 'Не менее 5 символов.',
    ],
    'preferences' => [
        'header' => 'Preferences',
        'save' => 'Save Preference',
        'reset' => 'Reset',
        'change_saved' => 'Preference saved!',
    ],
    'shared_albums' => [
        'instruction' => 'Choose how shared albums (albums from other users) appear in your gallery:',
        'mode_default' => 'Use Server Default',
        'mode_default_desc' => 'Inherit the server\'s default visibility mode.',
        'mode_show' => 'Show Inline',
        'mode_show_desc' => 'Shared albums appear below your own albums.',
        'mode_separate' => 'Separate Tabs',
        'mode_separate_desc' => 'View albums in separate "My Albums" and "Shared with Me" tabs.',
        'mode_separate_shared_only' => 'Shared Only',
        'mode_separate_shared_only_desc' => 'Separate tabs showing only directly shared albums (excludes public albums).',
        'mode_hide' => 'Hide',
        'mode_hide_desc' => 'Don\'t show any shared albums.',
    ],
];
