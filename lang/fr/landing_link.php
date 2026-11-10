<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'Liens supplémentaires',
    'description' => 'Gérez une liste ordonnée de liens supplémentaires affichés dans la navigation et/ou le pied de page de la page d’accueil.',

    // Empty state
    'no_links' => 'Aucun lien supplémentaire configuré pour l’instant.',
    'create_first' => 'Créer votre premier lien',

    // Table columns
    'col_label' => 'Libellé',
    'col_url' => 'URL',
    'col_placement' => 'Emplacement',
    'col_enabled' => 'Activé',
    'col_actions' => 'Actions',

    // Placement labels
    'placement_nav' => 'Nav',
    'placement_footer' => 'Pied de page',
    'placement_both' => 'Nav et pied de page',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Intégré',
    'built_in_cannot_delete' => 'Ce lien intégré ne peut pas être supprimé. Désactivez-le plutôt, ou réordonnez-le comme n’importe quel autre lien.',
    'built_in_url_hint' => 'Il s’agit d’un lien intégré et sa cible ne peut pas être modifiée.',

    // Buttons
    'create' => 'Créer un lien',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
    'cancel' => 'Annuler',
    'save' => 'Enregistrer',

    // Form fields
    'field_label' => 'Libellé',
    'field_label_placeholder' => 'p. ex. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Emplacement',
    'field_open_in_new_tab' => 'Ouvrir dans un nouvel onglet',
    'field_enabled' => 'Activé',

    // Modal titles
    'modal_create_title' => 'Créer un lien',
    'modal_edit_title' => 'Modifier le lien',

    // Delete confirmation
    'confirm_delete_header' => 'Supprimer le lien',
    'confirm_delete_message' => 'Voulez-vous vraiment supprimer le lien « :label » ? Cette action est irréversible.',

    // Toasts
    'created' => 'Lien créé avec succès.',
    'updated' => 'Lien mis à jour avec succès.',
    'deleted' => 'Lien supprimé avec succès.',
    'reordered' => 'Liens réordonnés avec succès.',
    'error_load' => 'Échec du chargement des liens.',
    'error_save' => 'Échec de l’enregistrement du lien.',
    'error_delete' => 'Échec de la suppression du lien.',
    'error_reorder' => 'Échec de la réorganisation des liens.',
];
