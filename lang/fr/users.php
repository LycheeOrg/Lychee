<?php

return [
    /* --------------------------------------------------------------------------
    | Users page
    |-------------------------------------------------------------------------- */
    'title' => 'Utilisateurs',
    'description' => 'Ici, vous pouvez gérer les utilisateurs de votre installation Lychee. Vous pouvez créer, modifier et supprimer des utilisateurs.',
    'create' => 'Créer un nouvel utilisateur',
    'username' => 'Nom d’utilisateur',
    'password' => 'Mot de passe',
    'legend' => 'Légende',
    'upload_rights' => 'Si cette option est cochée, l’utilisateur peut téléverser du contenu.',
    'edit_rights' => 'Si cette option est cochée, l’utilisateur peut modifier son profil (nom d’utilisateur, mot de passe).',
    'upload_trust_level' => 'Niveau de confiance des téléversements — détermine si les téléversements deviennent immédiatement publics.',

    'quota' => 'Si défini, l’utilisateur dispose d’un quota d’espace pour les photos (en Ko).',
    'user_deleted' => 'Utilisateur supprimé',
    'user_created' => 'Utilisateur créé',
    'user_updated' => 'Utilisateur mis à jour',
    'change_saved' => 'Changement enregistré !',
    'create_edit' => [
        'upload_rights' => 'L’utilisateur peut téléverser du contenu.',
        'edit_rights' => 'L’utilisateur peut modifier son nom d’utilisateur et son mot de passe.',
        'admin_rights' => 'L’utilisateur peut administrer Lychee.',
        'upload_trust_level' => 'Niveau de confiance des téléversements',
        'upload_trust_level_check' => 'Vérifier – les téléversements nécessitent l’approbation d’un administrateur avant de devenir publics.',
        'upload_trust_level_monitor' => 'Surveiller – les téléversements sont publics, sauf s’ils sont signalés pour leur contenu.',
        'upload_trust_level_trust_but_verify' => 'Confiance vérifiée – les téléversements sont publics ; les éléments signalés pour vérification sont automatiquement approuvés, ceux signalés pour blocage restent configurables.',
        'upload_trust_level_trusted' => 'De confiance – les téléversements sont immédiatement publics.',
        'trust_level_options' => [
            'trusted' => 'De confiance',
            'trust_but_verify' => 'Confiance vérifiée',
            'monitor' => 'Surveiller',
            'check' => 'Vérifier',
        ],
        'quota' => 'Quota d’espace (en Ko).',
        'quota_kb' => 'quota en Ko (0 par défaut)',
        'note' => 'Note admin (non visible publiquement)',
        'create' => 'Créer',
        'edit' => 'Éditer',
    ],
    'invite' => [
        'button' => 'Inviter un utilisateur',
        'links_are_not_revokable' => 'Les liens d’invitation ne sont pas révocables.',
        'link_is_valid_x_days' => 'Ce lien est valable pendant %d jours.',
    ],
    'line' => [
        'owner' => 'Propriétaire',
        'admin' => 'Utilisateur administrateur',
        'edit' => 'Éditer',
        'delete' => 'Supprimer',
    ],
];
