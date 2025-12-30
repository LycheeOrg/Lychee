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
    'update' => [
        'title' => 'Aktualizacje',
        'check-button' => 'Sprawdź aktualizacje',
        'update-button' => 'Aktualizacja',
        'no-pending-updates' => 'Brak oczekujących aktualizacji.',
    ],
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'Statistics integrity Check',
        'missing_photos' => '%d photo statistics missing.',
        'missing_albums' => '%d album statistics missing.',
        'button' => 'Create missing',
    ],
    'flush-cache' => [
        'title' => 'Opróżnianie pamięci podręcznej',
        'description' => 'Opróżnianie pamięci podręcznej każdego użytkownika w celu rozwiązania problemów z unieważnianiem.',
        'button' => 'Opróżnianie',
    ],
    'old-orders' => [
        'title' => 'Old Orders',
        'description' => 'Found %d old orders.<br/><br/>An old order is older than 14 days, that have no associated user and are either still pending payment or have no items in them.',
        'button' => 'Delete old orders',
    ],
    'fulfill-orders' => [
        'title' => 'Orders to fulfill',
        'description' => 'Found %d orders with content that has not been made available.<br/><br/>Click on the button to assign content when possible.',
        'button' => 'Fulfill orders',
    ],
    'fulfill-precompute' => [
        'title' => 'Album Precomputed Fields',
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:backfill-album-fields',
        'button' => 'Compute fields',
    ],
];
