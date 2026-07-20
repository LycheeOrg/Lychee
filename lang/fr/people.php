<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| People / Facial Recognition
	|--------------------------------------------------------------------------
	*/
	'title' => 'Personnes',
	'description' => 'Parcourez les photos par les personnes qui y figurent.',
	'no_people' => 'Aucune personne trouvée pour le moment. Analysez des photos pour détecter des visages.',
	'photos_label' => 'photo(s)',
	'faces_label' => 'visage(s)',
	'hidden_faces' => 'visage(s) masqué(s) pour la confidentialité',
	'unknown' => 'Inconnu',
	'confidence' => 'Confiance',
	'laplacian_variance' => 'Variance laplacienne',
	'assign_face' => 'Assigner le visage',
	'dismiss_face' => 'Ignorer le visage',
	'undismiss_face' => 'Réactiver le visage',
	'scan_faces' => 'Rechercher des visages',
	'scanning' => 'Recherche de visages en cours…',
	'scan_success' => 'Analyse des visages mise en file d’attente avec succès.',
	'not_searchable' => 'Masqué',
	'searchable' => 'Visible',
	'claim_by_selfie' => 'Me retrouver dans les photos',
	'claim_by_selfie_description' => 'Téléversez un selfie pour retrouver et associer votre profil de personne.',
	'claims' => [
		'success' => 'Association réussie à votre profil.',
		'no_face' => 'Aucun visage détecté dans le selfie.',
		'no_match' => 'Aucune personne correspondante trouvée.',
		'already_claimed' => 'Cette personne est déjà associée à un autre utilisateur.',
		'low_confidence' => 'Niveau de confiance de la correspondance trop faible. Veuillez essayer une photo plus nette.',
	],
	'person' => [
		'edit' => 'Modifier',
		'delete' => 'Supprimer',
		'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer « %s » ?',
		'delete_warning' => 'Cette action est irréversible ! Tous les visages assignés à cette personne seront désassignés.',
		'merge' => 'Fusionner avec…',
		'toggle_searchable' => 'Afficher/masquer la visibilité',
		'claim' => 'C’est moi',
		'unclaim' => 'Dissocier de moi',
		'photos_title' => 'Photos de %s',
	],
	'clusters_title' => 'Clusters de visages',
	'run_clustering' => 'Lancer le clustering',
	'no_clusters' => 'Aucun cluster trouvé. Lancez le clustering pour regrouper les visages détectés.',
	'faces' => 'visages',
	'enter_name' => 'Nom de la personne…',
	'assign' => 'Assigner',
	'dismiss' => 'Ignorer',
	'assignment' => [
		'title' => 'Assigner le visage à une personne',
		'batch_title' => 'Assigner :count visage(s) à une personne',
		'select_person' => 'Sélectionner une personne existante…',
		'new_person' => 'Ou créer une nouvelle personne',
		'new_person_placeholder' => 'Nom de la nouvelle personne…',
		'confirm' => 'Assigner',
		'cancel' => 'Annuler',
		'success' => 'Visage assigné avec succès.',
		'dismiss' => 'Ignorer',
		'dismissed' => 'Visage ignoré avec succès.',
	],
	'people_detected' => ':count personne détectée|:count personnes détectées',
	'filter_active' => 'Affichage des photos avec :name',
	'people_in_photo' => 'Personnes sur cette photo',
	'remove_from_person' => 'Retirer de la personne',
	'remove_from_person_success' => 'Visage retiré avec succès.',
	'batch_select' => 'Sélectionner des visages',
	'batch_cancel' => 'Annuler la sélection',
	'batch_selected' => ':count sélectionné(s)',
	'batch_assign' => 'Assigner la sélection',
	'batch_unassign' => 'Désassigner la sélection',
	'assign_to_user' => 'Assigner à un utilisateur',
	'search_user' => 'Rechercher un utilisateur…',
	'cluster_detail_title' => 'Cluster (:count visages)',
	'assigned_faces_to' => 'Assigné :count visage(s) à « :name »',
	'assigned_faces' => 'Assigné :count visage(s)',
	'dismissed_faces' => 'Ignoré :count visage(s)',
	'clustering_started' => 'Clustering démarré. Rechargez la page une fois terminé.',

	'face_recognition_warning' => [
		'title' => 'Mention légale — Reconnaissance faciale',
		'legal_notice' => 'La technologie de reconnaissance faciale peut faire l’objet de restrictions légales strictes, voire être purement et simplement interdite dans votre juridiction. Avant de déployer ce service, assurez-vous de respecter l’ensemble des lois et réglementations applicables.',
		'example_title' => 'Exemple — les Pays-Bas :',
		'example_body' => 'Dans le cadre de la transposition néerlandaise du Règlement général sur la protection des données de l’UE (RGPD), les données biométriques (y compris les empreintes de reconnaissance faciale) sont classées comme données sensibles relevant d’une catégorie particulière (article 9 du RGPD). Le traitement de telles données est interdit, sauf si une base légale spécifique s’applique (par exemple un consentement explicite et éclairé). L’autorité néerlandaise de protection des données (Autoriteit Persoonsgegevens) a publié des lignes directrices précisant clairement que l’utilisation de la reconnaissance faciale sur des personnes sans fondement légal valable constitue une infraction grave, pouvant <span class="text-muted-color-emphasis font-bold">entraîner des amendes allant jusqu’à 20 millions d’euros ou 4 % du chiffre d’affaires annuel mondial</span>.',
		'similar_rules' => 'Des règles similaires, voire plus strictes, peuvent s’appliquer dans d’autres pays de l’UE/EEE, au Royaume-Uni, au Canada et dans de nombreuses autres juridictions.',
		'no_liability' => 'Lychee est développé sous <a href="https://lycheeorg.dev/license" class="text-primary-400 underline" target="_blank">licence MIT</a>. <span class="text-muted-color-emphasis">Les auteurs et contributeurs</span> de LycheeOrg <span class="text-muted-color-emphasis">n’assument aucune responsabilité en cas d’usage illégal</span>.<br/>Il vous incombe <span class="text-muted-color-emphasis">exclusivement d’obtenir tout consentement requis, de mettre en place des garanties appropriées et de vérifier la légalité</span> de l’utilisation avant d’exploiter ce logiciel.',
		'acknowledge' => 'J’ai lu et compris la mention légale ci-dessus',
		'accept' => 'Accepter et fermer l’avertissement',
	],
	'merge' => [
		'title' => 'Fusionner la personne',
		'into' => 'avec…',
		'select_target' => 'Sélectionner la personne cible…',
		'warning' => 'Cette opération déplacera tous les visages de la personne source vers la personne cible, puis supprimera la personne source. Cette action est irréversible.',
		'confirm' => 'Fusionner',
		'success' => 'Personnes fusionnées avec succès.',
	],
];
