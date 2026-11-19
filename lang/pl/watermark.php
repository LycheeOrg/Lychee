<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Podgląd znaku wodnego',
        'se_required' => 'Moduł znaku wodnego wymaga włączenia Lychee Supporter Edition (SE) lub podglądu SE.',

        'section_settings' => 'Ustawienia znaku wodnego',
        'section_preview' => 'Podgląd na żywo',
        'disclaimer' => 'Ten podgląd daje wyobrażenie o wyglądzie znaku wodnego. Ostateczny efekt na Twoich zdjęciach może się nieznacznie różnić.',

        'watermark_photo_id' => 'ID obrazu znaku wodnego',
        'watermark_photo_id_placeholder' => '24-znakowe ID zdjęcia',
        'watermark_photo_id_hint' => 'ID zdjęcia używanego jako znak wodny. Otwórz zdjęcie i skopiuj ostatnie 24 znaki z adresu URL.',

        'preview_photo_id' => 'ID zdjęcia tła',
        'preview_photo_id_placeholder' => '24-znakowe ID zdjęcia',
        'preview_photo_id_hint' => 'Wprowadź ID zdjęcia, które posłuży jako tło podglądu.',

        'size' => 'Rozmiar (:value%)',
        'opacity' => 'Krycie (:value%)',
        'position' => 'Pozycja',
        'position_options' => [
            'top-left' => 'Lewy górny róg',
            'top' => 'Góra, środek',
            'top-right' => 'Prawy górny róg',
            'left' => 'Lewa, środek',
            'center' => 'Środek',
            'right' => 'Prawa, środek',
            'bottom-left' => 'Lewy dolny róg',
            'bottom' => 'Dół, środek',
            'bottom-right' => 'Prawy dolny róg',
        ],

        'section_shift' => 'Przesunięcie',
        'shift_type' => 'Jednostka przesunięcia',
        'shift_type_options' => [
            'relative' => 'Względne (%)',
            'absolute' => 'Bezwzględne (px)',
        ],
        'shift_type_hint' => 'Przesunięcie względne to procent rozmiaru obrazu; bezwzględne to stała liczba pikseli.',
        'shift_mode_use_slider' => 'Użyj suwaka',
        'shift_mode_use_classic' => 'Użyj pola liczbowego',
        'shift_x' => 'Przesunięcie poziome (:value)',
        'shift_x_direction_options' => [
            'left' => 'W lewo',
            'right' => 'W prawo',
        ],
        'shift_y' => 'Przesunięcie pionowe (:value)',
        'shift_y_direction_options' => [
            'up' => 'W górę',
            'down' => 'W dół',
        ],
        'reset_to_zero' => 'Resetuj do 0',

        'save' => 'Zapisz ustawienia',
        'saved' => 'Ustawienia znaku wodnego zostały zapisane.',
        'save_error' => 'Nie udało się zapisać ustawień znaku wodnego.',
        'save_requires_se' => 'Zapisanie ustawień znaku wodnego wymaga pełnej licencji Supporter Edition (SE). Podgląd SE pozwala jedynie podejrzeć efekt.',

        'no_watermark_image' => 'Nie skonfigurowano obrazu znaku wodnego. Wprowadź ID zdjęcia znaku wodnego i kliknij "Wczytaj", aby zobaczyć podgląd.',
        'no_preview_photo' => 'Wprowadź powyżej ID zdjęcia tła, aby zobaczyć podgląd nałożonego znaku wodnego.',
        'photo_load_error' => 'Nie udało się wczytać zdjęcia. Upewnij się, że ID jest poprawne i masz do niego dostęp.',
        'watermark_load_error' => 'Nie udało się wczytać obrazu znaku wodnego. Upewnij się, że ID zdjęcia jest poprawne.',
    ],
];
