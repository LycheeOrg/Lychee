<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Gallery sizes: preview page and live drawer (Feature 110)
    |--------------------------------------------------------------------------
    */

    'title' => 'Galeriegrößen',
    'live_title' => 'Galeriegrößen (live)',
    'live_hint' => 'Änderungen gelten für die Galerie hinter diesem Bereich. Schließen ohne Speichern verwirft sie.',
    'spotlight_action' => 'Galeriegrößen anpassen (live)',
    'not_allowed' => 'Nur Administratoren können die Galeriegrößen ändern.',
    'section_albums' => 'Alben',
    'section_photos' => 'Fotos',
    'section_placeholders' => 'Platzhalter',
    'album_min_width' => 'Minimale Kachelbreite',
    'album_aspect_ratio' => 'Seitenverhältnis',
    'albums_per_row_mobile' => 'Pro Zeile auf Smartphones',
    'layout' => 'Layout',
    'layouts' => [
        'justified' => 'Bündig',
        'masonry' => 'Masonry',
        'square' => 'Quadratisch',
        'grid' => 'Raster',
    ],
    'gap' => 'Abstand',
    'justified_row_height' => 'Zeilenhöhe (bündig)',
    'masonry_column_width' => 'Spaltenbreite (Masonry)',
    'grid_column_width' => 'Spaltenbreite (Raster)',
    'square_column_width' => 'Spaltenbreite (quadratisch)',
    'px' => ':value px',
    'album_count' => 'Alben',
    'photo_count' => 'Fotos',
    'shuffle' => 'Mischen',
    'album_title' => 'Album :n',
    'width' => 'Breite',
    'presets' => [
        'phone' => 'Smartphone',
        'tablet' => 'Tablet',
        'laptop' => 'Laptop',
        'desktop' => 'Desktop',
        'wide' => 'Breitbild',
        'window' => 'Dieses Fenster',
    ],
    'shown_at' => ':width px · angezeigt mit :scale %',
    'summary' => ':albums Alben pro Zeile · :rows Fotozeilen',
    'unsaved' => 'Ungespeicherte Änderungen',
    'save' => 'Speichern',
    'revert' => 'Zurücksetzen',
    'close' => 'Schließen',
    'saved' => 'Galeriegrößen gespeichert',
    'save_error' => 'Die Galeriegrößen konnten nicht gespeichert werden',
    'load_error' => 'Die Einstellungen konnten nicht geladen werden',
    'reset_hint' => 'Doppelklicken, um den gespeicherten Wert wiederherzustellen',
    'live_empty' => 'Nichts auf dieser Seite verwendet diese Größen.',
];
