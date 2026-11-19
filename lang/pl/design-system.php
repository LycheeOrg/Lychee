<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Bieżące odniesienie do tokenów i komponentów, z których faktycznie zbudowany jest frontend v8. Każda próbka i kontrolka na tej stronie odczytuje rzeczywiste właściwości niestandardowe CSS i komponenty Nuxt UI używane obecnie, więc nigdy nie zdezaktualizuje się tak jak statyczna makieta.',

    'sections' => [
        'foundations' => 'Podstawy',
        'colors' => 'Paleta kolorów',
        'typography' => 'Typografia',
        'buttons' => 'Przyciski',
        'badges' => 'Odznaki i etykiety',
        'forms' => 'Kontrolki formularzy',
        'feedback' => 'Komunikaty i stany',
        'radius' => 'Zaokrąglenia i cienie',
    ],

    'loading' => [
        'full_page' => 'Wskaźnik ładowania całej strony',
        'full_page_description' => 'Powolne, stopniowe pojawianie się — używany pod pełnoekranowym zaciemnieniem podczas ładowania widoku.',
        'mini_spinner' => 'Mały spinner',
        'mini_spinner_description' => 'Szybki wariant, widoczny natychmiast — używany w przyciskach, listach i małych stanach ładowania.',
    ],

    'foundations' => [
        'system' => 'Zestaw komponentów',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Czcionka tekstu',
        'body_font_value' => 'Libre Franklin, hostowana lokalnie (Vazirmatn dla arabskiego/perskiego)',
        'mono_font' => 'Czcionka o stałej szerokości',
        'mono_font_value' => 'Systemowy stos czcionek o stałej szerokości (ui-monospace)',
        'icons' => 'Zestaw ikon',
        'icons_value' => 'Lucide, przez Iconify (UIcon)',
        'theme' => 'Motyw',
        'theme_value' => 'Jeden motyw ciemny/jasny, przełączany w Ustawieniach — bez nadpisań dla poszczególnych sekcji',
    ],

    'colors' => [
        'primary' => 'Podstawowy — kolor marki, główne akcje, linki, stany aktywne',
        'secondary' => 'Drugorzędny — alternatywne akcje i zaznaczenia',
        'neutral' => 'Neutralny — tekst, obramowania, powierzchnie (Slate w trybie jasnym, Zinc w ciemnym)',
        'success' => 'Sukces — potwierdzenia, stany pozytywne',
        'warning' => 'Ostrzeżenie — uwaga, problemy nieblokujące',
        'error' => 'Błąd — akcje destrukcyjne, niepowodzenia',
        'info' => 'Informacja — komunikaty informacyjne',
        'resolved_from' => 'Odczytano na żywo z :token',
    ],

    'typography' => [
        'display' => 'Display',
        'heading' => 'Nagłówek',
        'body' => 'Tekst główny',
        'body_sample' => 'Pchnąć w tę łódź jeża lub ośm skrzyń fig — tytuły albumów, etykiety formularzy i codzienne teksty są wyświetlane tym krojem.',
        'mono' => 'Stała szerokość',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Skala wyróżnienia tekstu',
        'emphasis_sample' => 'Zdjęcia zrobione tego dnia w poprzednich latach',
    ],

    'buttons' => [
        'intro' => 'Każdy kolor obsługiwany przez system przycisków, w wariantach faktycznie używanych w aplikacji.',
    ],

    'forms' => [
        'input_label' => 'Tytuł albumu',
        'input_placeholder' => 'Lato w Kioto',
        'select_label' => 'Sortuj według',
        'checkbox_label' => 'Pokaż wrażliwe albumy',
        'switch_label' => 'Album publiczny',
        'switch_description' => 'Każdy, kto ma link, może wyświetlić ten album',
        'textarea_label' => 'Opis',
        'textarea_placeholder' => 'Dodaj opis tego albumu…',
        'radio_legend' => 'Logika dopasowania tagów',
    ],

    'feedback' => [
        'alert_info' => 'Do tego albumu dodano nowe zdjęcia.',
        'alert_success' => 'Ustawienia zapisane.',
        'alert_warning' => 'Ten album jest widoczny dla każdego, kto ma link.',
        'alert_error' => 'Nie można połączyć się z serwerem — zmiany nie zostały zapisane.',
        'empty_title' => 'Brak udostępnień',
        'empty_description' => 'Udostępnione albumy i ich uprawnienia pojawią się tutaj po udostępnieniu pierwszego albumu.',
    ],
];
