<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Teilen',
    'info' => 'Diese Seite bietet einen Überblick über die mit den Alben verbundenen Freigabeberechtigungen und die Möglichkeit, diese zu bearbeiten.',
    'album_title' => 'Album Titel',
    'username' => 'Benutzername',
    'no_data' => 'Die Freigabeliste ist leer.',
    'share' => 'Teile',
    'add_new_access_permission' => 'Eine neue Zugangsberechtigung hinzufügen',
    'permission_deleted' => 'Berechtigung gelöscht!',
    'permission_created' => 'Berechtigung erstellt!',
    'propagate' => 'Vererbung',
    'propagate_help' => 'Vererbt die aktuellen Zugriffsberechtigungen an alle untergeordneten Objekte<br>(Unteralben und deren jeweilige Unteralben usw.)',
    'propagate_default' => 'Standardmäßig werden die vorhandenen Berechtigungen (Album-Benutzer)<br>aktualisiert und die fehlenden hinzugefügt.<br>Zusätzliche Berechtigungen, die nicht in dieser Liste enthalten sind, bleiben unberührt.',
    'propagate_overwrite' => 'Überschreibt die vorhandenen Berechtigungen, anstatt sie zu aktualisieren.<br> Dadurch werden auch alle Berechtigungen entfernt, die nicht in dieser Liste enthalten sind.',
    'propagate_warning' => 'Diese Aktion kann nicht rückgängig gemacht werden.',
    'permission_overwritten' => 'Vererbung erfolgreich! Berechtigungen überschrieben!',
    'permission_updated' => 'Vererbung erfolgreich! Berechtigungen aktualisiert!',
    'bluk_share' => 'Sammelfreigabe',
    'bulk_share_instr' => 'Wählen Sie mehrere Alben und Benutzer für die Freigabe aus.',
    'albums' => 'Alben',
    'users' => 'Benutzer',
    'no_users' => 'Keine auswählbaren Benutzer.',
    'no_albums' => 'Keine auswählbaren Alben.',
    'grants' => [
        'read' => 'Gewährt Lesezugriff',
        'original' => 'Gewährt Zugriff auf das Originalfoto',
        'download' => 'Herunterladen erlauben',
        'upload' => 'Hochladen erlauben',
        'edit' => 'Gewährt das Recht zur Bearbeitung',
        'delete' => 'Gewährt das Recht zu löschen',
    ],
];
