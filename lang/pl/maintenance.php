<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Konserwacja',
    'description' => 'Na tej stronie znajdziesz wszystkie wymagane czynności, aby instalacja Lychee działała płynnie i przyjemnie.',
    'cleaning' => [
        'title' => 'Czyszczenie %s',
        'result' => '%s usunięte.',
        'description' => 'Usuń całą zawartość z <span class="font-mono">%s</span>.',
        'button' => 'Czyszczenie',
    ],
    'duplicate-finder' => [
        'title' => 'duplikaty',
        'description' => 'Moduł ten zlicza potencjalne duplikaty pomiędzy obrazami.',
        'duplicates-all' => 'Duplikaty we wszystkich albumach',
        'duplicates-title' => 'Duplikaty tytułów na album',
        'duplicates-per-album' => 'Duplikaty na album',
        'show' => 'Pokaż duplikaty',
        'load' => 'Wczytaj liczniki',
    ],
    'fix-jobs' => [
        'title' => 'Naprawianie historii zadań',
        'description' => 'Oznaczanie zadań o statusie <span class="text-ready-400">%s</span> lub <span class="text-primary-500">%s</span> jako <span class="text-danger-700">%s</span>.',
        'button' => 'Napraw historię zadań',
    ],
    'gen-sizevariants' => [
        'title' => 'Brakujące %s',
        'description' => 'Znaleziono %d %s, które można wygenerować.',
        'button' => 'Generuj!',
        'success' => 'Pomyślnie wygenerowano %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Brakujące rozmiary plików',
        'description' => 'Znaleziono %d małych wariantów bez rozmiaru pliku.',
        'button' => 'Pobierz dane!',
        'success' => 'Pomyślnie obliczono rozmiary %d małych wariantów.',
    ],
    'fix-tree' => [
        'title' => 'Statystyki drzew',
        'Oddness' => 'Nieparzystość',
        'Duplicates' => 'Duplikaty',
        'Wrong parents' => 'Błędni rodzice',
        'Missing parents' => 'Brakujacy rodzice',
        'button' => 'Napraw drzewo',
    ],
    'optimize' => [
        'title' => 'Optymalizacja bazy danych',
        'description' => 'Jeśli zauważysz spowolnienie w instalacji, może to być spowodowane tym, że baza danych
        nie ma wszystkich potrzebnych indeksów.',
        'button' => 'Optymalizacja bazy danych',
    ],
    'missing-palettes' => [
        'title' => 'Brakujące palety',
        'description' => 'Znaleziono brakujące palety: %d.',
        'button' => 'Utwórz brakujące',
    ],
    'statistics-check' => [
        'title' => 'Sprawdzanie integralności statystyk',
        'missing_photos' => 'Brakujące statystyki zdjęć: %d.',
        'missing_albums' => 'Brakujące statystyki albumów: %d.',
        'button' => 'Utwórz brakujące',
    ],
    'flush-cache' => [
        'title' => 'Opróżnianie pamięci podręcznej',
        'description' => 'Opróżnianie pamięci podręcznej każdego użytkownika w celu rozwiązania problemów z unieważnianiem.',
        'button' => 'Opróżnianie',
    ],
    'old-orders' => [
        'title' => 'Stare zamówienia',
        'description' => 'Znaleziono stare zamówienia: %d.<br/><br/>Stare zamówienie to zamówienie starsze niż 14 dni, bez przypisanego użytkownika, które nadal oczekuje na płatność lub nie zawiera żadnych pozycji.',
        'button' => 'Usuń stare zamówienia',
    ],
    'fulfill-orders' => [
        'title' => 'Zamówienia do realizacji',
        'description' => 'Znaleziono zamówienia z treścią, która nie została jeszcze udostępniona: %d.<br/><br/>Kliknij przycisk, aby przypisać treść tam, gdzie to możliwe.',
        'button' => 'Zrealizuj zamówienia',
    ],
    'fulfill-precompute' => [
        'title' => 'Wstępnie obliczone pola albumów',
        'description' => 'Znaleziono albumy z brakującymi wstępnie obliczonymi polami: %d.<br/><br/>Odpowiednik uruchomienia: php artisan lychee:recompute-album-fields',
        'button' => 'Oblicz pola',
    ],
    'flush-queue' => [
        'title' => 'Opróżnij kolejkę',
        'description' => 'Znaleziono oczekujące zadania w kolejce: %d.<br/><br/>UWAGA: Opróżnienie kolejki trwale usunie wszystkie oczekujące zadania. Tej operacji nie można cofnąć.',
        'button' => 'Wyczyść kolejkę',
    ],
    'backfill-album-sizes' => [
        'title' => 'Statystyki rozmiarów albumów',
        'description' => 'Znaleziono albumy bez statystyk rozmiaru: %d.<br/><br/>Odpowiednik uruchomienia: php artisan lychee:recompute-album-sizes',
        'button' => 'Oblicz rozmiary',
    ],

    'face_quality' => [
        'title' => 'Przegląd jakości twarzy',
        'description' => 'Przeglądaj wykryte twarze według oceny jakości i odrzucaj twarze niskiej jakości lub błędnie wykryte.',
        'sort_by' => 'Sortuj według:',
        'sort_confidence' => 'Pewność',
        'sort_blur' => 'Rozmycie (Laplacian)',
        'no_faces' => 'Brak twarzy spełniających kryteria. Wszystko wygląda dobrze!',
        'col_face' => 'Twarz',
        'col_person' => 'Osoba',
        'col_cluster' => 'Klaster',
        'col_confidence' => 'Pewność',
        'col_blur' => 'Ocena rozmycia',
        'col_actions' => 'Akcje',
        'unassigned' => 'Nieprzypisana',
        'dismiss' => 'Odrzuć twarz',
        'readd' => 'Dodaj twarz ponownie',
        'load_error' => 'Nie udało się załadować twarzy.',
        'dismissed' => 'Twarz odrzucona.',
        'readded' => 'Twarz dodana ponownie.',
        'dismiss_error' => 'Nie udało się odrzucić twarzy.',
        'readd_error' => 'Nie udało się ponownie dodać twarzy.',
        'batch_dismiss' => 'Odrzuć zaznaczone',
        'batch_dismissed' => 'Odrzucone twarze: :count.',
        'batch_dismiss_error' => 'Nie udało się odrzucić zaznaczonych twarzy.',
        'batch_reactivate' => 'Przywróć zaznaczone',
        'batch_reactivated' => 'Przywrócone twarze: :count.',
        'batch_reactivate_error' => 'Nie udało się przywrócić zaznaczonych twarzy.',
        'show_dismissed' => 'Pokaż odrzucone',
        'show_active' => 'Pokaż aktywne',
        'show_unassigned' => 'Tylko nieprzypisane',
        'select_all' => 'Zaznacz wszystko',
        'deselect_all' => 'Odznacz wszystko',
        'selected_count' => 'Zaznaczono: :count',
    ],
    'bulk-scan-faces' => [
        'description' => 'Znaleziono zdjęcia, które nie zostały jeszcze przeskanowane pod kątem rozpoznawania twarzy: %d.<br/><br/>Wymaga uruchomionej usługi AI Vision.',
    ],
    'run-clustering' => [
        'description' => 'Uruchom grupowanie twarzy w usłudze AI Vision. Grupuje wykryte twarze według podobieństwa, aby można było przypisać je do osób.',
        'success' => 'Grupowanie zostało pomyślnie uruchomione.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Zniszcz odrzucone twarze',
        'description' => 'Znaleziono odrzucone twarze: %d. Zniszczenie ich trwale usunie pliki wycinków i embeddingi.',
        'action' => 'Zniszcz wszystkie',
        'success' => 'Odrzucone twarze zostały pomyślnie zniszczone.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Synchronizuj embeddingi twarzy',
        'description' => 'Wykryto niezgodność liczby twarzy (różnica: %d). Synchronizacja pobierze najnowsze dane twarzy z usługi AI Vision do Lychee.',
        'action' => 'Synchronizuj teraz',
        'success' => 'Embeddingi twarzy zostały pomyślnie zsynchronizowane.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Zresetuj status skanowania twarzy',
        'description' => 'Znaleziono zdjęcia z zawieszonym lub nieudanym statusem skanowania twarzy: %d. Zresetowanie pozwoli na ich ponowne przeskanowanie.',
        'action' => 'Zresetuj wszystkie',
        'success' => 'Statusy skanowania twarzy zostały pomyślnie zresetowane.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Usuń osierocone embeddingi twarzy',
            'description' => 'Usługa AI Vision może przechowywać do %d embeddingów, które nie istnieją już w Lychee. Czyszczenie wyśle bieżącą listę twarzy do AI Vision i trwale usunie wszystko, co pozostało.',
            'action' => 'Wyczyść teraz',
            'success' => 'Osierocone embeddingi twarzy zostały pomyślnie usunięte.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Masowe skanowanie NSFW',
        'description' => 'Przeskanuj wszystkie nieprzeskanowane zdjęcia pod kątem treści NSFW przy użyciu skonfigurowanego presetu. Wymaga uruchomionej usługi klasyfikacji NSFW.',
        'button' => 'Skanuj wszystkie nieprzeskanowane',
        'success' => 'Skanowanie NSFW zostało pomyślnie zlecone.',
    ],
];
