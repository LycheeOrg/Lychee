<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Une référence vivante des jetons (tokens) et composants sur lesquels repose réellement le front-end v8. Chaque nuancier et contrôle de cette page lit les vraies propriétés CSS personnalisées et les composants Nuxt UI utilisés aujourd’hui, si bien qu’elle ne peut jamais devenir obsolète comme le ferait une maquette statique.',

    'sections' => [
        'foundations' => 'Fondations',
        'colors' => 'Palette de couleurs',
        'typography' => 'Typographie',
        'buttons' => 'Boutons',
        'badges' => 'Badges et puces',
        'forms' => 'Contrôles de formulaire',
        'feedback' => 'Retours et états',
        'radius' => 'Arrondis et élévation',
    ],

    'loading' => [
        'full_page' => 'Chargeur pleine page',
        'full_page_description' => 'Apparition lente et échelonnée — utilisé derrière l’écran noir plein écran pendant le chargement d’une vue.',
        'mini_spinner' => 'Mini indicateur de chargement',
        'mini_spinner_description' => 'Variante rapide, visible immédiatement — utilisée en ligne pour les boutons, les listes et les petits états de chargement.',
    ],

    'foundations' => [
        'system' => 'Kit de composants',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Police du texte',
        'body_font_value' => 'Libre Franklin, auto-hébergée (Vazirmatn pour l’arabe/le persan)',
        'mono_font' => 'Police à chasse fixe',
        'mono_font_value' => 'Pile de polices à chasse fixe du système (ui-monospace)',
        'icons' => 'Jeu d’icônes',
        'icons_value' => 'Lucide, via Iconify (UIcon)',
        'theme' => 'Thème',
        'theme_value' => 'Un seul thème sombre/clair, activé dans les Paramètres — aucune surcharge par section',
    ],

    'colors' => [
        'primary' => 'Primaire — couleur de marque, actions principales, liens, états actifs',
        'secondary' => 'Secondaire — actions et sélections alternatives',
        'neutral' => 'Neutre — texte, bordures, surfaces (Slate en mode clair, Zinc en mode sombre)',
        'success' => 'Succès — confirmations, états positifs',
        'warning' => 'Avertissement — prudence, problèmes non bloquants',
        'error' => 'Erreur — actions destructrices, échecs',
        'info' => 'Info — messages informatifs',
        'resolved_from' => 'Résolu en direct depuis :token',
    ],

    'typography' => [
        'display' => 'Titre d’affichage',
        'heading' => 'Titre',
        'body' => 'Texte courant',
        'body_sample' => 'Portez ce vieux whisky au juge blond qui fume — les titres d’albums, les libellés de formulaires et les textes courants s’affichent avec cette pile de polices.',
        'mono' => 'Monospace',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Échelle d’emphase du texte',
        'emphasis_sample' => 'Photos prises ce jour-là les années précédentes',
    ],

    'buttons' => [
        'intro' => 'Toutes les couleurs prises en charge par le système de boutons, dans les variantes réellement utilisées dans l’application.',
    ],

    'forms' => [
        'input_label' => 'Titre de l’album',
        'input_placeholder' => 'L’été à Kyoto',
        'select_label' => 'Trier par',
        'checkbox_label' => 'Afficher les albums sensibles',
        'switch_label' => 'Album public',
        'switch_description' => 'Toute personne disposant du lien peut voir cet album',
        'textarea_label' => 'Description',
        'textarea_placeholder' => 'Ajoutez une description pour cet album…',
        'radio_legend' => 'Logique de correspondance des tags',
    ],

    'feedback' => [
        'alert_info' => 'De nouvelles photos ont été ajoutées à cet album.',
        'alert_success' => 'Paramètres enregistrés.',
        'alert_warning' => 'Cet album est visible par toute personne disposant du lien.',
        'alert_error' => 'Impossible de joindre le serveur — les modifications n’ont pas été enregistrées.',
        'empty_title' => 'Aucun partage pour l’instant',
        'empty_description' => 'Les albums partagés et leurs autorisations apparaîtront ici dès que vous aurez partagé votre premier album.',
    ],
];
