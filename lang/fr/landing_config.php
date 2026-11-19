<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Page d’accueil',
    'tab_settings' => 'Paramètres',
    'tab_links' => 'Liens',
    'tab_featured' => 'Mis en avant',

    'section_layout' => 'Mise en page et structure',
    'section_hero' => 'Bandeau',
    'section_background_landscape' => 'Arrière-plan (paysage)',
    'section_background_portrait' => 'Arrière-plan (portrait)',
    'section_cta_position' => 'Position de l’appel à l’action',
    'section_login_position' => 'Panneau de connexion',
    'section_meridian' => 'Rails Meridian',
    'section_content' => 'Contenu',

    'field_layout' => 'Mise en page',
    'field_intro_screen_enabled' => 'Écran d’introduction',
    'field_hero_text_position' => 'Position du texte du bandeau',
    'field_hero_text_color' => 'Couleur du texte du bandeau',
    'field_hero_text_opacity' => 'Opacité du texte du bandeau',
    'field_animation_preset' => 'Préréglage d’animation',
    'field_backdrop_opacity' => 'Opacité du voile d’arrière-plan (:value %)',
    'field_cta_text' => 'texte',
    'field_cta_text_placeholder' => 'Laisser vide pour utiliser la valeur par défaut de la mise en page',
    'field_about_enabled' => 'Section À propos',
    'field_about_text' => 'Texte À propos',

    'field_background_mode' => 'Source',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID de photo',
        'random' => 'Photo publique aléatoire',
        'latest_album_cover' => 'Couverture du dernier album',
        'random_from_album' => 'Photo aléatoire d’un album',
    ],
    'field_background_url' => 'URL de l’image',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID de photo',
    'field_background_photo_id_placeholder' => 'ID de photo de 24 caractères',
    'field_background_photo_id_hint' => 'ID de la photo à utiliser. Ouvrez une photo et copiez les 24 derniers caractères de l’URL.',
    'field_background_album_id' => 'ID d’album',
    'field_background_album_id_placeholder' => 'ID d’album de 24 caractères',
    'background_load_error' => 'Impossible de charger la photo. Vérifiez que l’ID est correct et que vous y avez accès.',
    'background_mode_hint' => [
        'random' => 'Une photo publique aléatoire est choisie à chaque chargement de la page.',
        'latest_album_cover' => 'La couverture de l’album public publié le plus récemment est utilisée.',
        'random_from_album' => 'Une photo aléatoire de cet album est choisie à chaque chargement de la page. L’aperçu n’est mis à jour qu’après l’enregistrement.',
    ],

    'preview_orientation_landscape' => 'Aperçu de l’arrière-plan paysage',
    'preview_orientation_portrait' => 'Aperçu de l’arrière-plan portrait',

    'field_cta_position' => 'Position',
    'cta_position_options' => [
        'top-left' => 'En haut à gauche',
        'top' => 'En haut au centre',
        'top-right' => 'En haut à droite',
        'left' => 'Au milieu à gauche',
        'center' => 'Centre',
        'right' => 'Au milieu à droite',
        'bottom-left' => 'En bas à gauche',
        'bottom' => 'En bas au centre',
        'bottom-right' => 'En bas à droite',
    ],
    'field_cta_shift_type' => 'Unité de décalage',
    'cta_shift_type_options' => [
        'relative' => 'Relatif (%)',
        'absolute' => 'Absolu (px)',
    ],
    'cta_shift_type_hint' => 'Les décalages relatifs sont un pourcentage de la taille de la fenêtre d’affichage ; les décalages absolus sont un nombre fixe de pixels.',
    'field_cta_shift_x' => 'Décalage horizontal (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Gauche',
        'right' => 'Droite',
    ],
    'field_cta_shift_y' => 'Décalage vertical (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Haut',
        'down' => 'Bas',
    ],
    'reset_to_zero' => 'Réinitialiser à 0',

    'field_login_position' => 'Position',
    'login_position_options' => [
        'side' => 'Côté',
        'center' => 'Centre',
    ],
    'field_login_position_hint' => 'Sur les écrans étroits, le panneau de connexion est toujours centré sous le bandeau, quel que soit ce paramètre. Il glisse depuis le bord correspondant au sens de lecture lorsque le préréglage d’animation est « Slide reveal » ou « Parallax scroll ».',

    'field_meridian_explore_offset' => 'Hauteur du libellé Explorer (:value %)',
    'field_meridian_contact_offset' => 'Hauteur du libellé Contact (:value %)',
    'field_meridian_offset_hint' => 'Position du libellé le long de chaque rail pleine hauteur : 0 % correspond au haut, 100 % au bas.',
    'field_meridian_explore_line_position' => 'Position du rail Explorer (:value %)',
    'field_meridian_contact_line_position' => 'Position du rail Contact (:value %)',
    'field_meridian_line_position_hint' => 'Distance de chaque rail par rapport au bord gauche : 0 % correspond au bord gauche, 100 % au bord droit. Le rail Explorer se centre toujours lorsque le rail Contact est désactivé.',

    'preview_title' => 'Aperçu en direct',
    'preview_hint' => 'Mis à jour instantanément pendant la modification — rien n’est enregistré tant que vous n’avez pas cliqué sur Enregistrer.',
    'flat_list_hint' => 'Ces paramètres sont également modifiables depuis la liste générique des paramètres.',

    'save' => 'Enregistrer',
    'saved' => 'Paramètres de la page d’accueil enregistrés.',
    'save_error' => 'Échec de l’enregistrement des paramètres de la page d’accueil.',

    'se_required' => 'Cette mise en page ou ce préréglage nécessite Lychee SE.',
];
