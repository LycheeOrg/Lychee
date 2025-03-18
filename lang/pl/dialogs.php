<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Dialogs
	|--------------------------------------------------------------------------
	*/
	'button' => [
		'close' => 'Zamknij',
		'cancel' => 'Anuluj',
		'save' => 'Zapisz',
		'delete' => 'Usuń',
		'move' => 'Przenieś',
	],
	'about' => [
		'subtitle' => 'Hostowane samodzielnie zarządzanie zdjęciami w dobrym stylu',
		'description' => 'Lychee to darmowe narzędzie do zarządzania zdjęciami, które działa na serwerze lub w przestrzeni internetowej. Instalacja to kwestia kilku sekund. Przesyłaj, zarządzaj i udostępniaj zdjęcia jak z natywnej aplikacji. Lychee zawiera wszystko, czego potrzebujesz, a wszystkie zdjęcia są bezpiecznie przechowywane.',
		'update_available' => 'Aktualizacja dostępna !',
		'thank_you' => 'Dziękujemy za wsparcie !',
		'get_supporter_or_register' => 'Uzyskaj ekskluzywne funkcje i wspieraj rozwój Lychee.<br />Odblokuj <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">Supporter Edition</a> lub zarejestruj swój klucz licencyjny.',
		'here' => 'tutaj',
	],
	'dropbox' => [
		'not_configured' => 'Dropbox nie jest skonfigurowany.',
	],
	'import_from_link' => [
		'instructions' => 'Wprowadź bezpośredni link do zdjęcia, aby je zaimportować:',
		'import' => 'Import',
	],
	'keybindings' => [
		'header' => 'Keyboard shortcuts',
		'don_t_show_again' => 'Nie pokazuj ponownie',
		'side_wide' => 'Skróty dla całej witryny',
		'back_cancel' => 'Wstecz/Anuluj',
		'confirm' => 'Potwierdź',
		'login' => 'Logowanie',
		'toggle_full_screen' => 'Przełącz na pełny ekran',
		'toggle_sensitive_albums' => 'Przełączanie wrażliwych albumów',

		'albums' => 'Skróty do albumów',
		'new_album' => 'Nowy album',
		'upload_photos' => 'Przesyłanie zdjęć',
		'search' => 'Wyszukiwanie',
		'show_this_modal' => 'Pokaż ten modal',
		'select_all' => 'Wybierz wszystko',
		'move_selection' => 'Przenieś wybrane',
		'delete_selection' => 'Usuń zaznaczone',

		'album' => 'Skróty do albumów',
		'slideshow' => 'Uruchamianie/zatrzymywanie pokazu slajdów',
		'toggle' => 'Przełącz panel',

		'photo' => 'Skróty do zdjęć',
		'previous' => 'Poprzednie zdjęcie',
		'next' => 'Następne zdjęcie',
		'cycle' => 'Tryb nakładania',
		'star' => 'Oznacz zdjęcie gwiazdką',
		'move' => 'Przenieś zdjęcie',
		'delete' => 'Usuń zdjęcie',
		'edit' => 'Edytuj informacje',
		'show_hide_meta' => 'Pokaż informacje',

		'keep_hidden' => 'Będziemy to ukrywać.',
	],
	'login' => [
		'username' => 'Nazwa użytkownika',
		'password' => 'Hasło',
		'unknown_invalid' => 'Nieznany użytkownik lub nieprawidłowe hasło.',
		'signin' => 'Logowanie',
	],
	'register' => [
		'enter_license' => 'Wprowadź swój klucz licencyjny poniżej:',
		'license_key' => 'Klucz licencyjny',
		'invalid_license' => 'Nieprawidłowy klucz licencyjny.',
		'register' => 'Zarejestruj się',
	],
	'share_album' => [
		'url_copied' => 'Skopiowano adres URL do schowka!',
	],
	'upload' => [
		'completed' => 'Zakończono',
		'uploaded' => 'Przesłano:',
		'release' => 'Zwolnij plik do przesłania !',
		'select' => 'Kliknij tutaj, aby wybrać pliki do przesłania',
		'drag' => '(Lub przeciągnij pliki na stronę)',
		'loading' => 'Ładowanie',
		'resume' => 'Wznowienie',
		'uploading' => 'Przesyłanie',
		'finished' => 'Zakończono',
		'failed_error' => 'Przesyłanie nie powiodło się. Serwer zwrócił błąd!',
	],
	'visibility' => [
		'public' => 'Publiczny',
		'public_expl' => 'Anonimowi użytkownicy mogą uzyskać dostęp do tego albumu, z zastrzeżeniem poniższych ograniczeń.',
		'full' => 'Oryginał',
		'full_expl' => 'Anonimowi użytkownicy mogą przeglądać zdjęcia w pełnej rozdzielczości.',
		'hidden' => 'Ukryty',
		'hidden_expl' => 'Anonimowi użytkownicy potrzebują bezpośredniego linku, aby uzyskać dostęp do tego albumu.',
		'downloadable' => 'Do pobrania',
		'downloadable_expl' => 'Anonimowi użytkownicy mogą pobrać ten album.',
		'upload' => 'Allow uploads',
		'upload_expl' => '<i class="pi pi-exclamation-triangle text-warning-700 mr-1"></i> Anonymous users can upload photos to this album.',
		'password' => 'Hasło',
		'password_prot' => 'Chronione hasłem',
		'password_prot_expl' => 'Anonimowi użytkownicy potrzebują udostępnionego hasła, aby uzyskać dostęp do tego albumu.',
		'password_prop_not_compatible' => 'Buforowanie odpowiedzi jest sprzeczne z tym ustawieniem.<br>Z powodu buforowania odpowiedzi, odblokowanie tego albumu<br>ujawni również jego zawartość innym anonimowym użytkownikom.',
		'nsfw' => 'Wrażliwy',
		'nsfw_expl' => 'Album zawiera wrażliwe treści.',
		'visibility_updated' => 'Zaktualizowano widoczność.',
	],
	'move_album' => [
		'confirm_single' => 'Czy na pewno chcesz przenieść album "%1$s" do albumu "%2$s"?',
		'confirm_multiple' => 'Czy na pewno chcesz przenieść wszystkie wybrane albumy do albumu "%s"?',
		'move_single' => 'Przenieś album',
		'move_to' => 'Przenieś do',
		'move_to_single' => 'Przenieś %s do:',
		'move_to_multiple' => 'Przenieś %d albumy do:',
		'no_album_target' => 'Brak albumu do przeniesienia',
		'moved_single' => 'Album przeniesiony!',
		'moved_single_details' => '%1$s przeniesiony do %2$s',
		'moved_details' => 'Albumy przeniesione do %s',
	],
	'new_album' => [
		'menu' => 'Utwórz album',
		'info' => 'Wprowadź tytuł nowego albumu:',
		'title' => 'tytuł',
		'create' => 'Utwórz album',
	],
	'new_tag_album' => [
		'menu' => 'Utwórz album ze znacznikami',
		'info' => 'Wprowadź tytuł nowego tagu albumu:',
		'title' => 'tytuł',
		'set_tags' => 'Ustawianie wyświetlania tagów',
		'warn' => 'Pamiętaj, aby nacisnąć enter po każdym tagu',
		'create' => 'Utwórz album ze znacznikami',
	],
	'delete_album' => [
		'confirmation' => 'Czy na pewno chcesz usunąć album "%s" i wszystkie znajdujące się w nim zdjęcia?',
		'confirmation_multiple' => 'Czy na pewno chcesz usunąć wszystkie %d wybrane albumy i wszystkie zawarte w nich zdjęcia?',
		'warning' => 'Tego działania nie można cofnąć!',
		'delete' => 'Usuwanie albumu i zdjęć',
	],
	'transfer' => [
		'query' => 'Przeniesienie własności albumu na',
		'confirmation' => 'Czy na pewno chcesz przenieść własność albumu "%s" i wszystkich zawartych w nim zdjęć do "%s"?',
		'lost_access_warning' => 'Dostęp do tego albumu zostanie utracony.',
		'warning' => 'Tego działania nie można cofnąć!',
		'transfer' => 'Przeniesienie własności albumu i zdjęć',
	],
	'rename' => [
		'photo' => 'Wprowadź nowy tytuł dla tego zdjęcia:',
		'album' => 'Wprowadź nowy tytuł dla tego albumu:',
		'rename' => 'Zmiana nazwy',
	],
	'merge' => [
		'merge_to' => 'Scal %s do:',
		'merge_to_multiple' => 'Scal %d albumów do:',
		'no_albums' => 'Brak albumów do połączenia.',
		'confirm' => 'Czy na pewno chcesz połączyć album "%1$s" z albumem "%2$s"?',
		'confirm_multiple' => 'Czy na pewno chcesz połączyć wszystkie wybrane albumy w album "%s"?',
		'merge' => 'Połącz albumy',
		'merged' => 'Albumy zostały połączone do %s!',
	],
	'unlock' => [
		'password_required' => 'Ten album jest chroniony hasłem. Wprowadź hasło poniżej, aby wyświetlić zdjęcia z tego albumu:',
		'password' => 'Hasło',
		'unlock' => 'Odblokowanie',
	],
	'photo_tags' => [
		'question' => 'Wprowadź tagi dla tego zdjęcia.',
		'question_multiple' => 'Wprowadź tagi dla wszystkich %d wybranych zdjęć. Istniejące tagi zostaną nadpisane.',
		'no_tags' => 'Brak tagów',
		'set_tags' => 'Ustaw tagi',
		'updated' => 'Tagi zaktualizowane!',
		'tags_override_info' => 'Jeśli opcja ta nie jest zaznaczona, tagi zostaną dodane do istniejących tagów zdjęcia.',
	],
	'photo_copy' => [
		'no_albums' => 'Brak albumów do skopiowania',
		'copy_to' => 'Kopiuj %s do:',
		'copy_to_multiple' => 'Kopiuj %d zdjęć do:',
		'confirm' => 'Kopiuj %s do %s.',
		'confirm_multiple' => 'Skopiuj %d zdjęć do %s.',
		'copy' => 'Kopiuj',
		'copied' => 'Zdjęcia skopiowane!',
	],
	'photo_delete' => [
		'confirm' => 'Czy na pewno chcesz usunąć zdjęcie "%s"?',
		'confirm_multiple' => 'Czy na pewno chcesz usunąć wszystkie %d wybrane zdjęcia?',
		'deleted' => 'Zdjęcia usunięte!',
	],
	'move_photo' => [
		'move_single' => 'Przenieś %s do:',
		'move_multiple' => 'Przenieś %d zdjęć do:',
		'confirm' => 'Przenieś %s do %s.',
		'confirm_multiple' => 'Przenieś %d zdjęć do %s.',
		'moved' => 'Zdjęcie(a) przeniesione do %s!',
	],
	'target_user' => [
		'placeholder' => 'Wybierz użytkownika',
	],
	'target_album' => [
		'placeholder' => 'Wybierz album',
	],
	'webauthn' => [
		'u2f' => 'U2F',
		'success' => 'Uwierzytelnianie powiodło się!',
		'error' => 'Ups, wygląda na to, że coś poszło nie tak. Przeładuj stronę i spróbuj ponownie!',
	],
	'se' => [
		'available' => 'Dostępne w Supporter Edition',
	],
	'session_expired' => [
		'title' => 'Sesja wygasła',
		'message' => 'Twoja sesja wygasła.<br />Przeładuj stronę.',
		'reload' => 'Przeładuj',
		'go_to_gallery' => 'Przejdź do galerii',
	],
];