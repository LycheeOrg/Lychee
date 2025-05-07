<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Wartung',
    'intro' => 'Auf dieser Seite finden Sie die doppelten Bilder, die in Ihrer Datenbank gefunden wurden.',
    'found' => ' Duplikate gefunden!',
    'invalid-search' => ' Mindestens die Prüfsumme oder die Titelbedingung muss geprüft werden.',
    'checksum-must-match' => 'Die Prüfsumme muss übereinstimmen.',
    'title-must-match' => 'Der Titel muss übereinstimmen.',
    'must-be-in-same-album' => 'Müssen im selben Album sein.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Foto',
        'checksum' => 'Prüfsumme',
    ],
    'warning' => [
        'no-original-left' => 'Kein Original übrig.',
        'keep-one' => 'Sie haben alle Duplikate in dieser Gruppe ausgewählt. Bitte wählen Sie mindestens ein Duplikat, das Sie behalten möchten.',
    ],
    'delete-selected' => 'Ausgewählte löschen',
];
