<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Aperçu du filigrane',
        'se_required' => 'Le module de filigrane nécessite que Lychee Supporter Edition (SE) ou SE Preview soit activé.',

        'section_settings' => 'Paramètres du filigrane',
        'section_preview' => 'Aperçu en direct',
        'disclaimer' => 'Cet aperçu donne une idée du rendu du filigrane. Le résultat final sur vos photos réelles peut légèrement différer.',

        'watermark_photo_id' => 'ID de la photo du filigrane',
        'watermark_photo_id_placeholder' => 'ID de photo (24 caractères)',
        'watermark_photo_id_hint' => 'ID de la photo utilisée comme filigrane. Ouvrez une photo et copiez les 24 derniers caractères de l’URL.',

        'preview_photo_id' => 'ID de la photo d’arrière-plan',
        'preview_photo_id_placeholder' => 'ID de photo (24 caractères)',
        'preview_photo_id_hint' => 'Saisissez un ID de photo à utiliser comme arrière-plan pour l’aperçu.',

        'size' => 'Taille (:value%)',
        'opacity' => 'Opacité (:value%)',
        'position' => 'Position',
        'position_options' => [
            'top-left' => 'Haut gauche',
            'top' => 'Haut centre',
            'top-right' => 'Haut droite',
            'left' => 'Milieu gauche',
            'center' => 'Centre',
            'right' => 'Milieu droite',
            'bottom-left' => 'Bas gauche',
            'bottom' => 'Bas centre',
            'bottom-right' => 'Bas droite',
        ],

        'section_shift' => 'Décalage',
        'shift_type' => 'Unité de décalage',
        'shift_type_options' => [
            'relative' => 'Relatif (%)',
            'absolute' => 'Absolu (px)',
        ],
        'shift_type_hint' => 'Les décalages relatifs sont un pourcentage de la taille de l’image ; les décalages absolus sont un nombre fixe de pixels.',
        'shift_mode_use_slider' => 'Utiliser le curseur',
        'shift_mode_use_classic' => 'Utiliser la saisie numérique',
        'shift_x' => 'Décalage horizontal (:value)',
        'shift_x_direction_options' => [
            'left' => 'Gauche',
            'right' => 'Droite',
        ],
        'shift_y' => 'Décalage vertical (:value)',
        'shift_y_direction_options' => [
            'up' => 'Haut',
            'down' => 'Bas',
        ],

        'save' => 'Enregistrer les paramètres',
        'saved' => 'Paramètres du filigrane enregistrés.',
        'save_error' => 'Échec de l’enregistrement des paramètres du filigrane.',
        'save_requires_se' => 'L’enregistrement des paramètres du filigrane nécessite une licence Supporter Edition (SE) complète. SE Preview ne permet que de prévisualiser l’effet.',

        'no_watermark_image' => 'Aucune image de filigrane configurée. Saisissez un ID de photo de filigrane et cliquez sur « Charger » pour prévisualiser.',
        'no_preview_photo' => 'Saisissez un ID de photo d’arrière-plan ci-dessus pour prévisualiser la superposition du filigrane.',
        'photo_load_error' => 'Impossible de charger la photo. Assurez-vous que l’ID est correct et que vous y avez accès.',
        'watermark_load_error' => 'Impossible de charger l’image du filigrane. Assurez-vous que l’ID de la photo est correct.',
    ],
];
