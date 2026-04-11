<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Потребители',
    'description' => 'Тук можете да управлявате потребителите на вашата инсталация на Lychee. Можете да създавате, редактирате и изтривате потребители.',
    'create' => 'Създай нов потребител',
    'username' => 'Потребителско име',
    'password' => 'Парола',
    'legend' => 'Легенда',
    'upload_rights' => 'Ако е избрано, потребителят може да качва съдържание.',
    'edit_rights' => 'Ако е избрано, потребителят може да редактира своя профил (потребителско име, парола).',    'upload_trust_level' => 'Upload trust level — controls whether uploads are immediately public.',

    'quota' => 'Ако е зададено, потребителят има квота за място за снимки (в kB).',
    'user_deleted' => 'Потребителят е изтрит',
    'user_created' => 'Потребителят е създаден',
    'user_updated' => 'Потребителят е обновен',
    'change_saved' => 'Промяната е запазена!',

    'create_edit' => [
        'upload_rights' => 'Потребителят може да качва съдържание.',
        'edit_rights' => 'Потребителят може да редактира своя профил (потребителско име, парола).',
        'admin_rights' => 'Потребителят има администраторски права.',        'upload_trust_level' => 'Upload trust level',
        'upload_trust_level_check' => 'Check – uploads require admin approval before becoming public.',
        'upload_trust_level_monitor' => 'Monitor – reserved for future use, currently behaves as Trusted.',
        'upload_trust_level_trusted' => 'Trusted – uploads are immediately public.',
        
        'quota' => 'Потребителят има ограничение на квотата.',
        'quota_kb' => 'квота в kB (0 за подразбиране)',
        'note' => 'Бележка на админа (не е видима публично)',
        'create' => 'Създай',
        'edit' => 'Редактиране',
    ],

    'invite' => [
        'button' => 'Покани потребител',
        'links_are_not_revokable' => 'Линковете за покана не могат да бъдат отменяни.',
        'link_is_valid_x_days' => 'Този линк е валиден за %d дни.',
    ],

    'line' => [
        'owner' => 'Собственик',
        'admin' => 'Администратор',
        'edit' => 'Редактиране',
        'delete' => 'Изтриване',
    ],
];