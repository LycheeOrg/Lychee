<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Configurez les webhooks sortants déclenchés lors de l’ajout, du déplacement ou de la suppression de photos.',

    // Empty state
    'no_webhooks' => 'Aucun webhook configuré pour le moment.',
    'create_first' => 'Créez votre premier webhook',

    // Table columns
    'col_name' => 'Nom',
    'col_event' => 'Événement',
    'col_method' => 'Méthode',
    'col_url' => 'URL',
    'col_format' => 'Format',
    'col_enabled' => 'Activé',
    'col_actions' => 'Actions',

    // Event labels
    'event_photo_add' => 'Photo ajoutée',
    'event_photo_move' => 'Photo déplacée',
    'event_photo_delete' => 'Photo supprimée',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Chaîne de requête',

    // Buttons
    'create' => 'Créer un webhook',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
    'cancel' => 'Annuler',
    'save' => 'Enregistrer',

    // Form fields
    'field_name' => 'Nom',
    'field_name_placeholder' => 'ex. Mon webhook',
    'field_event' => 'Événement',
    'field_method' => 'Méthode HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://exemple.com/hook',
    'field_format' => 'Format de charge utile',
    'field_enabled' => 'Activé',
    'field_secret' => 'Secret',
    'field_secret_placeholder' => 'Laisser vide pour conserver le secret existant',
    'field_secret_header' => 'En-tête du secret',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Envoyer l’ID de la photo',
    'field_send_album_id' => 'Envoyer l’ID de l’album',
    'field_send_title' => 'Envoyer le titre',
    'field_send_size_variants' => 'Envoyer les variantes de taille',

    // Modal titles
    'modal_create_title' => 'Créer un webhook',
    'modal_edit_title' => 'Modifier le webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Supprimer le webhook',
    'confirm_delete_message' => 'Êtes-vous sûr de vouloir supprimer le webhook « :name » ? Cette action est irréversible.',
    'delete_warning' => 'Cette action est irréversible.',

    // Toasts
    'created' => 'Webhook créé avec succès.',
    'updated' => 'Webhook mis à jour avec succès.',
    'deleted' => 'Webhook supprimé avec succès.',
    'error_load' => 'Échec du chargement des webhooks.',
    'error_save' => 'Échec de l’enregistrement du webhook.',
    'error_delete' => 'Échec de la suppression du webhook.',

    // Secret badge
    'has_secret' => 'Secret défini',
    'no_secret' => 'Aucun secret',
];
