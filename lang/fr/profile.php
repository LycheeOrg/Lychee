<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Profile page
    |--------------------------------------------------------------------------
    */
    'title' => 'Profil',
    'login' => [
        'header' => 'Profil',
        'enter_current_password' => 'Entrez votre mot de passe actuel :',
        'current_password' => 'Mot de passe actuel',
        'credentials_update' => 'Vos identifiants seront mis à jour comme suit :',
        'username' => 'Nom d’utilisateur',
        'new_password' => 'Nouveau mot de passe',
        'confirm_new_password' => 'Confirmer le nouveau mot de passe',
        'email_instruction' => 'Ajoutez votre adresse e-mail ci-dessous pour recevoir des notifications par mail. Pour ne plus en recevoir, supprimez simplement votre adresse.',
        'email' => 'E-mail',
        'change' => 'Modifier l’identifiant',
        'api_token' => 'Jeton API …',
        'missing_fields' => 'Champs manquants',
    ],
    'register' => [
        'username_exists' => 'Le nom d’utilisateur existe déjà.',
        'password_mismatch' => 'Les mots de passe ne correspondent pas.',
        'signup' => 'S’inscrire',
        'error' => 'Une erreur est survenue lors de l’enregistrement de votre compte.',
        'success' => 'Votre compte a été créé avec succès.',
    ],
    'token' => [
        'unavailable' => 'Vous avez déjà visualisé ce jeton.',
        'no_data' => 'Aucun jeton API n’a été généré.',
        'disable' => 'Désactiver',
        'disabled' => 'Jeton désactivé',
        'warning' => 'Ce jeton ne sera plus affiché. Copiez-le et conservez-le dans un endroit sûr.',
        'reset' => 'Réinitialiser le jeton',
        'create' => 'Créer un nouveau jeton',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth non disponible',
        'setup_env' => 'Configurez les identifiants dans votre fichier .env',
        'token_registered' => 'Jeton %s enregistré.',
        'setup' => 'Configurer %s',
        'reset' => 'réinitialiser',
        'credential_deleted' => 'Identifiant supprimé !',
    ],
    'u2f' => [
        'header' => 'Clé de sécurité / MFA / 2FA',
        'info' => 'Permet d’utiliser WebAuthn pour s’authentifier à la place du nom d’utilisateur et mot de passe.',
        'empty' => 'La liste des identifiants est vide !',
        'not_secure' => 'Environnement non sécurisé. U2F non disponible.',
        'new' => 'Enregistrer un nouvel appareil.',
        'credential_deleted' => 'Identifiant supprimé !',
        'credential_updated' => 'Identifiant mis à jour !',
        'credential_registred' => 'Enregistrement réussi !',
        '5_chars' => 'Au moins 5 caractères.',
    ],
    'preferences' => [
        'header' => 'Préférences',
        'save' => 'Enregistrer les préférences',
        'reset' => 'Réinitialiser',
        'change_saved' => 'Préférences enregistrées !',
    ],
    'shared_albums' => [
        'instruction' => 'Choose how shared albums (albums from other users) appear in your gallery:',
        'mode_default' => 'Use Server Default',
        'mode_default_desc' => 'Inherit the server\'s default visibility mode.',
        'mode_show' => 'Show Inline',
        'mode_show_desc' => 'Shared albums appear below your own albums.',
        'mode_separate' => 'Separate Tabs',
        'mode_separate_desc' => 'View albums in separate "My Albums" and "Shared with Me" tabs.',
        'mode_separate_shared_only' => 'Shared Only',
        'mode_separate_shared_only_desc' => 'Separate tabs showing only directly shared albums (excludes public albums).',
        'mode_hide' => 'Masquer',
        'mode_hide_desc' => 'Don\'t show any shared albums.',
    ],
];
