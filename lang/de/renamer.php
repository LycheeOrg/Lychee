<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Renamer Rules
	|--------------------------------------------------------------------------
	*/

	// Page title
	'title' => 'Umbenennungsregeln',

	// Modal titles
	'create_rule' => 'Umbenennungsregel erstellen',
	'edit_rule' => 'Umbenennungsregel bearbeiten',

	// Form fields
	'rule_name' => 'Regelname',
	'description' => 'Beschreibung',
	'pattern' => 'Muster',
	'replacement' => 'Ersetzen durch',
	'mode' => 'Modus',
	'order' => 'Reihenfolge',
	'enabled' => 'Aktiviert',
	'photo_rule' => 'Regel auf Fotos anwenden',
	'album_rule' => 'Regel auf Alben anwenden',

	// Form placeholders and help text
	'description_placeholder' => 'Optionale Beschreibung der Regelfunktion',
	'pattern_help' => 'Zu findendes Muster (z. B. IMG_, DSC_)',
	'replacement_help' => 'Ersetzungstext (z. B. Foto_, Kamera_)',
	'order_help' => 'Niedrigere Zahlen werden zuerst verarbeitet (1 = höchste Priorität)',
	'enabled_help' => '(Nur aktivierte Regeln werden beim Umbenennen angewendet)',

	// Mode options
	'mode_first' => 'Erstes Vorkommen',
	'mode_all' => 'Alle Vorkommen',
	'mode_regex' => 'Regulärer Ausdruck (Regex)',
	'mode_trim' => 'Leerzeichen entfernen',
	'mode_strtolower' => 'Kleinschreibung',
	'mode_strtoupper' => 'GROSSSCHREIBUNG',
	'mode_ucwords' => 'Jedes Wort großschreiben',
	'mode_ucfirst' => 'Ersten Buchstaben großschreiben',

	'mode_first_description' => 'Nur das erste Vorkommen ersetzen',
	'mode_all_description' => 'Alle Vorkommen ersetzen',
	'mode_regex_description' => 'Mustersuche per regulärem Ausdruck verwenden',
	'mode_trim_description' => 'Leerzeichen am Anfang und Ende entfernen',
	'mode_strtolower_description' => 'Text in Kleinschreibung umwandeln',
	'mode_strtoupper_description' => 'Text in Großschreibung umwandeln',
	'mode_ucwords_description' => 'Anfangsbuchstaben jedes Wortes großschreiben',
	'mode_ucfirst_description' => 'Nur den ersten Buchstaben großschreiben',

	'regex_help' => 'Verwenden Sie reguläre Ausdrücke für die Mustersuche. Um beispielsweise <code>IMG_1234.jpeg</code> durch <code>1234_JPG.jpeg</code> zu ersetzen, können Sie <code>/IMG_(\d+)/</code> als Suchmuster und <code>$1_JPG</code> als Ersetzungstext nutzen. Weitere Erklärungen finden Sie unter den folgenden Links.',

	// Buttons
	'cancel' => 'Abbrechen',
	'create' => 'Erstellen',
	'update' => 'Aktualisieren',
	'create_first_rule' => 'Erste Regel erstellen',

	// Validation messages
	'rule_name_required' => 'Ein Regelname ist erforderlich',
	'pattern_required' => 'Ein Muster ist erforderlich',
	'replacement_required' => 'Ein Ersetzungstext ist erforderlich',
	'mode_required' => 'Ein Modus ist erforderlich',
	'order_positive' => 'Die Reihenfolge muss eine positive Zahl sein',

	// Success messages
	'rule_created' => 'Umbenennungsregel erfolgreich erstellt',
	'rule_updated' => 'Umbenennungsregel erfolgreich aktualisiert',
	'rule_deleted' => 'Umbenennungsregel erfolgreich gelöscht',

	// Error messages
	'failed_to_create' => 'Umbenennungsregel konnte nicht erstellt werden',
	'failed_to_update' => 'Umbenennungsregel konnte nicht aktualisiert werden',
	'failed_to_delete' => 'Umbenennungsregel konnte nicht gelöscht werden',
	'failed_to_load' => 'Umbenennungsregeln konnten nicht geladen werden',

	// List view
	'rules_count' => ':count Regeln',
	'no_rules' => 'Keine Umbenennungsregeln gefunden',
	'loading' => 'Umbenennungsregeln werden geladen …',
	'pattern_label' => 'Muster',
	'replace_with_label' => 'Ersetzen durch',
	'photo' => 'Foto',
	'album' => 'Album',

	// Delete confirmation
	'confirm_delete_header' => 'Löschen bestätigen',
	'confirm_delete_message' => 'Sind Sie sicher, dass Sie die Regel „:rule“ löschen möchten?',
	'delete' => 'Löschen',

	// Status messages
	'success' => 'Erfolg',
	'error' => 'Fehler',

	// Placeholders
	'select_mode' => 'Wählen Sie einen Umbenennungsmodus',
	'execution_order' => 'Ausführungsreihenfolge',

	// Test functionality
	'test_input_placeholder' => 'Geben Sie einen Dateinamen ein, um die Regeln zu testen (z. B. IMG_1234.jpg)',
	'test_original' => 'Original',
	'test_result' => 'Ergebnis',
	'test_failed' => 'Test der Umbenennungsregeln fehlgeschlagen',
	'apply_photo_rules' => 'Fotoregeln anwenden',
	'apply_album_rules' => 'Albenregeln anwenden',
];
