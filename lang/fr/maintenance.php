<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Maintenance',
    'description' => 'Sur cette page, vous trouverez toutes les actions nécessaires pour que votre installation de Lychee fonctionne de manière fluide et efficace.',
    'cleaning' => [
        'title' => 'Nettoyage de %s',
        'result' => '%s supprimé.',
        'description' => 'Supprimer tout le contenu de <span class="font-mono">%s</span>',
        'button' => 'Nettoyer',
    ],
    'duplicate-finder' => [
        'title' => 'Doublons',
        'description' => 'Ce module recense les doublons potentiels entre les photos.',
        'duplicates-all' => 'Doublons sur tous les albums',
        'duplicates-title' => 'Doublons de titre par album',
        'duplicates-per-album' => 'Doublons par album',
        'show' => 'Afficher les doublons',
        'load' => 'Compter les doublons',
    ],
    'fix-jobs' => [
        'title' => 'Correction de l’historique des tâches',
        'description' => 'Marquer les tâches avec le statut <span class="text-ready-400">%s</span> ou <span class="text-primary-500">%s</span> comme <span class="text-danger-700">%s</span>.',
        'button' => 'Corriger l’historique',
    ],
    'gen-sizevariants' => [
        'title' => 'Manquants : %s',
        'description' => '%d %s pouvant être générés ont été trouvés.',
        'button' => 'Générer !',
        'success' => '%d %s générés avec succès.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Tailles de fichiers manquantes',
        'description' => '%d variantes de petite taille sans information de taille de fichier ont été trouvées.',
        'button' => 'Récupérer les données !',
        'success' => 'Taille des fichiers calculée avec succès pour %d variantes.',
    ],
    'fix-tree' => [
        'title' => 'Statistiques de l’arborescence',
        'Oddness' => 'Anomalies',
        'Duplicates' => 'Doublons',
        'Wrong parents' => 'Parents incorrects',
        'Missing parents' => 'Parents manquants',
        'button' => 'Corriger l’arborescence',
    ],
    'optimize' => [
        'title' => 'Optimiser la base de données',
        'description' => 'Si vous constatez un ralentissement de votre installation, cela peut venir d’un manque d’index dans votre base de données.',
        'button' => 'Optimiser la base',
    ],
    'update' => [
        'title' => 'Mises à jour',
        'check-button' => 'Vérifier les mises à jour',
        'update-button' => 'Mettre à jour',
        'no-pending-updates' => 'Aucune mise à jour en attente.',
    ],
    'missing-palettes' => [
        'title' => 'Palettes manquantes',
        'description' => '%d palettes manquantes trouvées.',
        'button' => 'Créer les palettes manquantes',
    ],
    'statistics-check' => [
        'title' => 'Check de l’intégrité des Statistiques',
        'missing_photos' => '%d statistiques de photos manquantes.',
        'missing_albums' => '%d statistiques d’albums manquantes.',
        'button' => 'Créer les statistiques manquantes',
    ],
    'flush-cache' => [
        'title' => 'Vider le cache',
        'description' => 'Vider le cache de tous les utilisateurs pour résoudre les problèmes d’invalidation.',
        'button' => 'Vider',
    ],
    'old-orders' => [
        'title' => 'Anciennes commandes',
        'description' => '%d anciennes commandes trouvées.<br/><br/>Une commande est considérée comme ancienne si elle date de plus de 14 jours, n’est associée à aucun utilisateur, et est soit toujours en attente de paiement, soit sans aucun article.',
        'button' => 'Supprimer les anciennes commandes',
    ],
    'fulfill-orders' => [
        'title' => 'Commandes à traiter',
        'description' => '%d commandes dont le contenu n’a pas encore été mis à disposition ont été trouvées.<br/><br/>Cliquez sur le bouton pour attribuer le contenu lorsque cela est possible.',
        'button' => 'Traiter les commandes',
    ],
    'fulfill-precompute' => [
        'title' => 'Champs précalculés des albums',
        'description' => '%d albums avec des champs précalculés manquants ont été trouvés.<br/><br/>Équivaut à exécuter : php artisan lychee:recompute-album-fields',
        'button' => 'Calculer les champs',
    ],
    'flush-queue' => [
        'title' => 'Vider la file d’attente',
        'description' => '%d tâches en attente ont été trouvées dans la file.<br/><br/>ATTENTION : vider la file supprimera définitivement toutes les tâches en attente. Cette action est irréversible.',
        'button' => 'Vider la file',
    ],
    'backfill-album-sizes' => [
        'title' => 'Statistiques de taille des albums',
        'description' => '%d albums sans statistiques de taille ont été trouvés.<br/><br/>Équivaut à exécuter : php artisan lychee:recompute-album-sizes',
        'button' => 'Calculer les tailles',
    ],

    'face_quality' => [
        'title' => 'Contrôle qualité des visages',
        'description' => 'Passez en revue les détections de visages selon leur score de qualité et rejetez les visages de mauvaise qualité ou erronés.',
        'sort_by' => 'Trier par :',
        'sort_confidence' => 'Confiance',
        'sort_blur' => 'Flou (Laplacien)',
        'no_faces' => 'Aucun visage à examiner. Tout est en ordre !',
        'col_face' => 'Visage',
        'col_person' => 'Personne',
        'col_cluster' => 'Groupe',
        'col_confidence' => 'Confiance',
        'col_blur' => 'Score de flou',
        'col_actions' => 'Actions',
        'unassigned' => 'Non attribué',
        'dismiss' => 'Rejeter le visage',
        'readd' => 'Réintégrer le visage',
        'load_error' => 'Échec du chargement des visages.',
        'dismissed' => 'Visage rejeté.',
        'readded' => 'Visage réintégré.',
        'dismiss_error' => 'Échec du rejet du visage.',
        'readd_error' => 'Échec de la réintégration du visage.',
        'batch_dismiss' => 'Rejeter la sélection',
        'batch_dismissed' => ':count visage(s) rejeté(s).',
        'batch_dismiss_error' => 'Échec du rejet des visages sélectionnés.',
        'batch_reactivate' => 'Réactiver la sélection',
        'batch_reactivated' => ':count visage(s) réactivé(s).',
        'batch_reactivate_error' => 'Échec de la réactivation des visages sélectionnés.',
        'show_dismissed' => 'Afficher les rejetés',
        'show_active' => 'Afficher les actifs',
        'show_unassigned' => 'Non attribués uniquement',
        'select_all' => 'Tout sélectionner',
        'deselect_all' => 'Tout désélectionner',
        'selected_count' => ':count sélectionné(s)',
    ],
    'bulk-scan-faces' => [
        'description' => '%d photos n’ayant pas encore été analysées pour la reconnaissance faciale ont été trouvées.<br/><br/>Nécessite que le service AI Vision soit en cours d’exécution.',
    ],
    'run-clustering' => [
        'description' => 'Déclenche le regroupement des visages dans le service AI Vision. Regroupe les visages détectés par similarité afin de pouvoir les attribuer à des personnes.',
        'success' => 'Regroupement démarré avec succès.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Détruire les visages rejetés',
        'description' => '%d visages rejetés ont été trouvés. Les détruire supprimera définitivement leurs fichiers recadrés et leurs empreintes (embeddings).',
        'action' => 'Tout détruire',
        'success' => 'Visages rejetés détruits avec succès.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Synchroniser les empreintes de visages',
        'description' => 'Un écart dans le nombre de visages a été détecté (différence de %d). La synchronisation récupérera les dernières données de visages depuis le service AI Vision vers Lychee.',
        'action' => 'Synchroniser maintenant',
        'success' => 'Empreintes de visages synchronisées avec succès.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Réinitialiser le statut d’analyse des visages',
        'description' => '%d photos avec un statut d’analyse de visages bloqué en attente ou en échec ont été trouvées. Les réinitialiser permettra de les analyser à nouveau.',
        'action' => 'Tout réinitialiser',
        'success' => 'Statuts d’analyse des visages réinitialisés avec succès.',
    ],

        'bulk-scan-nsfw' => [
        'title' => 'Analyse NSFW en masse',
        'description' => 'Analyse toutes les photos non analysées à la recherche de contenu NSFW à l’aide du préréglage configuré. Nécessite que le service de classification NSFW soit en cours d’exécution.',
        'button' => 'Analyser tout le contenu non analysé',
        'success' => 'Analyse NSFW lancée avec succès.',
    ],
];
