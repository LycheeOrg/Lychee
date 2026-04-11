<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Benutzer',
    'description' => 'Hier können die Benutzer der Lychee-Installation verwaltet werden. Es können Benutzer angelegt, bearbeitet und gelöscht werden.',
    'create' => 'Einen neuen Benutzer anlegen',
    'username' => 'Benutzername',
    'password' => 'Passwort',
    'legend' => 'Legende',
    'upload_rights' => 'Wenn diese Option ausgewählt ist, kann der Benutzer Inhalte hochladen.',
    'edit_rights' => 'Wenn diese Option ausgewählt ist, kann der Benutzer sein Profil (Benutzername, Passwort) ändern.',
    'upload_trust_level' => 'Upload trust level — controls whether uploads are immediately public.',

    'quota' => 'Wenn diese Option gesetzt ist, verfügt der Benutzer über ein Platzkontingent für Bilder (in kB).',
    'user_deleted' => 'Benutzer gelöscht',
    'user_created' => 'Benutzer erstellt',
    'user_updated' => 'Benutzer aktualisiert',
    'change_saved' => 'Änderung gespeichert!',
    'create_edit' => [
        'upload_rights' => 'Benutzer können Inhalte hochladen.',
        'edit_rights' => 'Der Benutzer kann sein Profil (Benutzername, Passwort) ändern.',
        'admin_rights' => 'Der Benutzer hat Administratorrechte.',
        'upload_trust_level' => 'Upload trust level',
        'upload_trust_level_check' => 'Check – uploads require admin approval before becoming public.',
        'upload_trust_level_monitor' => 'Monitor – upload are publics unless flagged for content.',
        'upload_trust_level_trusted' => 'Trusted – uploads are immediately public.',

        'quota' => 'Benutzer hat Kontingentgrenze.',
        'quota_kb' => 'Kontingent in kB (0 für Standard)',
        'note' => 'Verwaltungshinweis (nicht öffentlich sichtbar)',
        'create' => 'Erstellen',
        'edit' => 'Bearbeiten',
    ],
    'invite' => [
        'button' => 'Benutzer einladen',
        'links_are_not_revokable' => 'Einladungslinks sind nicht widerrufbar.',
        'link_is_valid_x_days' => 'Dieser Link ist für %d Tage gültig.',
    ],
    'line' => [
        'owner' => 'Besitzer',
        'admin' => 'Administrator',
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
    ],
];
