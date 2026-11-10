<?php
return [
    'preview' => [
        /**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
        'title' => 'Vorschau des Wasserzeichens',
        'se_required' => 'Für das Wasserzeichen-Modul muss die Lychee Supporter Edition (SE) oder die SE-Vorschau aktiviert sein.',
        'section_settings' => 'Einstellungen für Wasserzeichen',
        'section_preview' => 'Live-Vorschau',
        'disclaimer' => 'Diese Vorschau vermittelt einen Eindruck davon, wie das Wasserzeichen aussehen wird. Das Endergebnis auf Ihren tatsächlichen Fotos kann geringfügig davon abweichen.',
        'watermark_photo_id' => 'Wasserzeichen-Bild-ID',
        'watermark_photo_id_placeholder' => 'Ausweis mit Foto und 24 Zeichen',
        'watermark_photo_id_hint' => 'Foto-ID des Bildes, das als Wasserzeichen verwendet wird. Öffnen Sie ein Foto und kopieren Sie die letzten 24 Zeichen aus der URL.',
        'preview_photo_id' => 'Hintergrundfoto ID',
        'preview_photo_id_placeholder' => '24 Zeichen Foto ID',
        'preview_photo_id_hint' => 'Geben Sie eine Foto-ID ein, welche als Hintergrund für die Vorschau verwendet werden soll.',
        'size' => 'Größe (:value%)',
        'opacity' => 'Deckkraft (:value%)',
        'position' => 'Position',
        'position_options' => [
            'top-left' => 'Oben links',
            'top' => 'Oben in der Mitte',
            'top-right' => 'Oben rechts',
            'left' => 'Mitte links',
            'center' => 'Mitte',
            'right' => 'Mitte rechts',
            'bottom-left' => 'Unten links',
            'bottom' => 'Unten in der Mitte',
            'bottom-right' => 'Unten rechts',
        ],
        'section_shift' => 'Verschiebung / Versatz',
        'shift_type' => 'Verschiebungseinheit',
        'shift_type_options' => [
            'relative' => 'Relativ (%)',
            'absolute' => 'Absolut (px)',
        ],
        'shift_type_hint' => 'Relative Verschiebungen werden als Prozentsatz der Bildgröße angegeben; absolute Verschiebungen als feste Pixelanzahl.',
        'shift_mode_use_slider' => 'Schieberegler verwenden',
        'shift_mode_use_classic' => 'Zahleneingabe verwenden',
        'shift_x' => 'Horizontale Verschiebung (:value)',
        'shift_x_direction_options' => [
            'left' => 'Links',
            'right' => 'Richtig',
        ],
        'shift_y' => 'Vertikale Verschiebung (:value)',
        'shift_y_direction_options' => [
            'up' => 'Nach oben',
            'down' => 'Nach unten',
        ],
        'reset_to_zero' => 'Auf 0 zurücksetzen',
        'save' => 'Einstellungen speichern',
        'saved' => 'Wasserzeichen-Einstellungen gespeichert.',
        'save_error' => 'Wasserzeichen-Einstellungen konnten nicht gespeichert werden.',
        'save_requires_se' => 'Zum Speichern der Wasserzeichen-Einstellungen ist eine vollständige Supporter Edition (SE)-Lizenz erforderlich. Die SE-Vorschau erlaubt nur die Vorschau des Effekts.',
        'no_watermark_image' => 'Kein Wasserzeichenbild konfiguriert. Geben Sie eine Wasserzeichen-Foto-ID ein und klicken Sie auf „Laden“, um eine Vorschau anzuzeigen.',
        'no_preview_photo' => 'Geben Sie oben eine Hintergrundfoto-ID ein, um eine Vorschau der Wasserzeichen-Überlagerung anzuzeigen.',
        'photo_load_error' => 'Foto konnte nicht geladen werden. Stellen Sie sicher, dass die ID korrekt ist und Sie Zugriff darauf haben.',
        'watermark_load_error' => 'Wasserzeichenbild konnte nicht geladen werden. Stellen Sie sicher, dass die Foto-ID korrekt ist.',
    ],
];
