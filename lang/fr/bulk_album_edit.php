<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Modification groupée d’albums',
    'description' => 'Modifiez les métadonnées et les paramètres de visibilité de plusieurs albums à la fois.',
    'warning' => 'Les modifications effectuées ici sont appliquées immédiatement et ne peuvent pas être annulées. Les albums de type tag ne sont pas affichés.',

    // Table columns
    'col_title' => 'Titre',
    'col_owner' => 'Propriétaire',
    'col_license' => 'Licence',
    'col_is_nsfw' => 'Sensible',
    'col_is_public' => 'Public',
    'col_is_link_required' => 'Lien',
    'col_grants_full_photo_access' => 'Photo complète',
    'col_grants_download' => 'Téléchargement',
    'col_grants_upload' => 'Téléversement',
    'col_photo_sorting' => 'Tri photos',
    'col_album_sorting' => 'Tri albums',
    'col_created_at' => 'Créé le',

    // Filter
    'filter_placeholder' => 'Rechercher par titre...',

    // Pagination
    'per_page' => 'Par page',
    'total_selected' => ':n album sélectionné|:n albums sélectionnés',
    'select_all_page' => 'Tout sélectionner sur cette page',
    'select_all_matching' => 'Sélectionner toutes les correspondances',
    'cap_warning' => 'Seuls les 1 000 premiers albums ont été sélectionnés.',

    // Mode toggle
    'mode_paginated' => 'Paginé',
    'mode_infinite' => 'Défilement infini',

    // Action buttons
    'action_delete' => 'Supprimer',
    'action_set_owner' => 'Définir le propriétaire',
    'action_edit_fields' => 'Modifier les champs',

    // Edit Fields modal
    'edit_fields_title' => 'Modifier les champs',
    'edit_fields_description' => 'Seuls les champs cochés seront mis à jour. Les valeurs vides effacent le champ.',
    'section_metadata' => 'Métadonnées',
    'section_visibility' => 'Visibilité',
    'field_description' => 'Description',
    'field_copyright' => 'Copyright',
    'field_license' => 'Licence',
    'field_photo_layout' => 'Disposition des photos',
    'field_photo_sorting_col' => 'Colonne de tri des photos',
    'field_photo_sorting_order' => 'Ordre de tri des photos',
    'field_album_sorting_col' => 'Colonne de tri des albums',
    'field_album_sorting_order' => 'Ordre de tri des albums',
    'field_album_thumb_aspect_ratio' => 'Ratio d’aspect des vignettes',
    'field_album_timeline' => 'Chronologie de l’album',
    'field_photo_timeline' => 'Chronologie des photos',
    'field_is_nsfw' => 'Sensible',
    'field_is_public' => 'Public',
    'field_is_link_required' => 'Lien requis',
    'field_grants_full_photo_access' => 'Accès complet aux photos',
    'field_grants_download' => 'Téléchargement',
    'field_grants_upload' => 'Téléversement (SE)',
    'apply' => 'Appliquer',
    'cancel' => 'Annuler',

    // Set Owner modal
    'set_owner_title' => 'Définir le propriétaire',
    'set_owner_description' => 'Tous les albums sélectionnés seront déplacés à la racine et leurs descendants seront également transférés.',
    'set_owner_select_user' => 'Sélectionner le nouveau propriétaire',
    'transfer' => 'Transférer',

    // Delete confirmation modal
    'delete_title' => 'Supprimer des albums',
    'delete_confirm' => 'Vous êtes sur le point de supprimer définitivement :count album ainsi que tous ses sous-albums et photos. Cette action est irréversible.|Vous êtes sur le point de supprimer définitivement :count albums ainsi que tous leurs sous-albums et photos. Cette action est irréversible.',
    'confirm_delete' => 'Confirmer la suppression',

    // Toasts
    'success_patch' => 'Albums mis à jour avec succès.',
    'success_set_owner' => 'Propriété transférée avec succès.',
    'success_delete' => 'Albums supprimés avec succès.',
    'error_load' => 'Échec du chargement des albums.',
    'error_load_ids' => 'Échec du chargement des identifiants d’albums.',
    'error_patch' => 'Échec de la mise à jour des albums.',
    'error_set_owner' => 'Échec du transfert de propriété.',
    'error_delete' => 'Échec de la suppression des albums.',
    'error_load_users' => 'Échec du chargement des utilisateurs.',
];
