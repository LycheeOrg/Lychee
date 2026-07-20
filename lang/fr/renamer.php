<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Règles de renommage',

    // Modal titles
    'create_rule' => 'Créer une règle de renommage',
    'edit_rule' => 'Modifier la règle de renommage',

    // Form fields
    'rule_name' => 'Nom de la règle',
    'description' => 'Description',
    'pattern' => 'Motif',
    'replacement' => 'Remplacement',
    'mode' => 'Mode',
    'order' => 'Ordre',
    'enabled' => 'Activé',
    'photo_rule' => 'Règle appliquée aux photos',
    'album_rule' => 'Règle appliquée aux albums',

    // Form placeholders and help text
    'description_placeholder' => 'Description facultative de ce que fait cette règle',
    'pattern_help' => 'Motif à rechercher (ex. IMG_, DSC_)',
    'replacement_help' => 'Texte de remplacement (ex. Photo_, Camera_)',
    'order_help' => 'Les nombres les plus petits sont traités en premier (1 = priorité la plus élevée)',
    'enabled_help' => '(Seules les règles activées seront appliquées lors du renommage)',

    // Mode options
    'mode_first' => 'Première occurrence',
    'mode_all' => 'Toutes les occurrences',
    'mode_regex' => 'Expression régulière',
    'mode_trim' => 'Supprimer les espaces',
    'mode_strtolower' => 'minuscules',
    'mode_strtoupper' => 'MAJUSCULES',
    'mode_ucwords' => 'Majuscule à chaque mot',
    'mode_ucfirst' => 'Majuscule en première lettre',

    'mode_first_description' => 'Remplacer uniquement la première occurrence',
    'mode_all_description' => 'Remplacer toutes les occurrences',
    'mode_regex_description' => 'Utiliser une expression régulière pour la recherche',
    'mode_trim_description' => 'Supprimer les espaces',
    'mode_strtolower_description' => 'Convertir le texte en minuscules',
    'mode_strtoupper_description' => 'Convertir le texte en MAJUSCULES',
    'mode_ucwords_description' => 'Mettre une majuscule à chaque mot',
    'mode_ucfirst_description' => 'Mettre une majuscule uniquement à la première lettre',

    'regex_help' => 'Utilisez des expressions régulières pour rechercher des motifs. Par exemple, pour remplacer <code>IMG_1234.jpeg</code> par <code>1234_JPG.jpeg</code>, vous pouvez utiliser <code>/IMG_(\d+)/</code> comme motif et <code>$1_JPG</code> comme remplacement. Vous trouverez davantage d’explications et d’exemples dans les liens suivants.',

    // Buttons
    'cancel' => 'Annuler',
    'create' => 'Créer',
    'update' => 'Mettre à jour',
    'create_first_rule' => 'Créez votre première règle',

    // Validation messages
    'rule_name_required' => 'Le nom de la règle est requis',
    'pattern_required' => 'Le motif est requis',
    'replacement_required' => 'Le remplacement est requis',
    'mode_required' => 'Le mode est requis',
    'order_positive' => 'L’ordre doit être un nombre positif',

    // Success messages
    'rule_created' => 'Règle de renommage créée avec succès',
    'rule_updated' => 'Règle de renommage mise à jour avec succès',
    'rule_deleted' => 'Règle de renommage supprimée avec succès',

    // Error messages
    'failed_to_create' => 'Échec de la création de la règle de renommage',
    'failed_to_update' => 'Échec de la mise à jour de la règle de renommage',
    'failed_to_delete' => 'Échec de la suppression de la règle de renommage',
    'failed_to_load' => 'Échec du chargement des règles de renommage',

    // List view
    'rules_count' => ':count règles',
    'no_rules' => 'Aucune règle de renommage trouvée',
    'loading' => 'Chargement des règles de renommage...',
    'pattern_label' => 'Motif',
    'replace_with_label' => 'Remplacer par',
    'photo' => 'Photo',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Confirmer la suppression',
    'confirm_delete_message' => 'Êtes-vous sûr de vouloir supprimer la règle « :rule » ?',
    'delete' => 'Supprimer',

    // Status messages
    'success' => 'Succès',
    'error' => 'Erreur',

    // Placeholders
    'select_mode' => 'Sélectionner le mode de renommage',
    'execution_order' => 'Ordre d’exécution',

    // Test functionality
    'test_input_placeholder' => 'Saisissez un nom de fichier pour tester vos règles de renommage (ex. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Résultat',
    'test_failed' => 'Échec du test des règles de renommage',
    'apply_photo_rules' => 'Appliquer les règles pour les photos',
    'apply_album_rules' => 'Appliquer les règles pour les albums',
];
