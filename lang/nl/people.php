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
	'title' => 'Personen',
	'description' => 'Blader door foto’s op basis van de personen die erop staan.',
	'no_people' => 'Nog geen personen gevonden. Scan enkele foto’s om gezichten te detecteren.',
	'photos_label' => 'foto(’s)',
	'faces_label' => 'gezicht(en)',
	'hidden_faces' => 'gezicht(en) verborgen voor privacy',
	'unknown' => 'Onbekend',
	'confidence' => 'Betrouwbaarheid',
	'laplacian_variance' => 'Laplace-variantie',
	'assign_face' => 'Gezicht toewijzen',
	'dismiss_face' => 'Gezicht afwijzen',
	'undismiss_face' => 'Afwijzing ongedaan maken',
	'scan_faces' => 'Scannen op gezichten',
	'scanning' => 'Scannen op gezichten…',
	'scan_success' => 'Gezichtsscan succesvol in wachtrij geplaatst.',
	'not_searchable' => 'Verborgen',
	'searchable' => 'Zichtbaar',
	'claim_by_selfie' => 'Vind mij in foto’s',
	'claim_by_selfie_description' => 'Upload een selfie om uw persoonsprofiel te vinden en te koppelen.',
	'claims' => [
		'success' => 'Succesvol gekoppeld aan uw profiel.',
		'no_face' => 'Geen gezicht gedetecteerd in de selfie.',
		'no_match' => 'Geen overeenkomende persoon gevonden.',
		'already_claimed' => 'Deze persoon is al gekoppeld aan een andere gebruiker.',
		'low_confidence' => 'Overeenkomst te onzeker. Probeer een duidelijkere foto.',
	],
	'person' => [
		'edit' => 'Bewerken',
		'delete' => 'Verwijderen',
		'delete_confirm' => 'Weet u zeker dat u "%s" wilt verwijderen?',
		'delete_warning' => 'Deze actie kan niet ongedaan worden gemaakt! Alle gezichten die aan deze persoon zijn toegewezen, worden losgekoppeld.',
		'merge' => 'Samenvoegen met…',
		'toggle_searchable' => 'Zichtbaarheid wijzigen',
		'claim' => 'Dit ben ik',
		'unclaim' => 'Loskoppelen van mij',
		'photos_title' => 'Foto’s van %s',
	],
	'clusters_title' => 'Gezichtsclusters',
	'run_clustering' => 'Clustering uitvoeren',
	'no_clusters' => 'Geen clusters gevonden. Voer clustering uit om gedetecteerde gezichten te groeperen.',
	'faces' => 'gezichten',
	'enter_name' => 'Naam van persoon…',
	'assign' => 'Toewijzen',
	'dismiss' => 'Afwijzen',
	'assignment' => [
		'title' => 'Gezicht toewijzen aan persoon',
		'batch_title' => ':count gezicht(en) toewijzen aan persoon',
		'select_person' => 'Selecteer bestaande persoon…',
		'new_person' => 'Of maak nieuwe persoon aan',
		'new_person_placeholder' => 'Naam van nieuwe persoon…',
		'confirm' => 'Toewijzen',
		'cancel' => 'Annuleren',
		'success' => 'Gezicht succesvol toegewezen.',
		'dismiss' => 'Afwijzen',
		'dismissed' => 'Gezicht succesvol afgewezen.',
	],
	'people_detected' => ':count persoon gedetecteerd|:count personen gedetecteerd',
	'filter_active' => 'Foto’s met :name worden weergegeven',
	'people_in_photo' => 'Personen op deze foto',
	'remove_from_person' => 'Verwijderen van persoon',
	'remove_from_person_success' => 'Gezicht succesvol verwijderd.',
	'batch_select' => 'Gezichten selecteren',
	'batch_cancel' => 'Selectie annuleren',
	'batch_selected' => ':count geselecteerd',
	'batch_assign' => 'Selectie toewijzen',
	'batch_unassign' => 'Selectie loskoppelen',
	'assign_to_user' => 'Toewijzen aan gebruiker',
	'search_user' => 'Gebruiker zoeken…',
	'cluster_detail_title' => 'Cluster (:count gezichten)',
	'assigned_faces_to' => ':count gezicht(en) toegewezen aan ":name"',
	'assigned_faces' => ':count gezicht(en) toegewezen',
	'dismissed_faces' => ':count gezicht(en) afgewezen',
	'clustering_started' => 'Clustering gestart. Herlaad de pagina wanneer voltooid.',

	'face_recognition_warning' => [
		'title' => 'Juridische kennisgeving — Gezichtsherkenning',
		'legal_notice' => 'Gezichtsherkenningstechnologie kan onderworpen zijn aan strikte wettelijke beperkingen of in uw rechtsgebied volledig verboden zijn. Zorg ervoor dat u voldoet aan alle toepasselijke wet- en regelgeving voordat u deze dienst inschakelt.',
		'example_title' => 'Voorbeeld — Nederland:',
		'example_body' => 'Volgens de Nederlandse implementatie van de Algemene Verordening Gegevensbescherming (AVG) van de EU worden biometrische gegevens (waaronder gezichtsherkenningsembeddings) geclassificeerd als bijzondere persoonsgegevens (artikel 9 AVG). Het verwerken van dergelijke gegevens is verboden, tenzij een specifieke wettelijke grondslag van toepassing is (bijvoorbeeld uitdrukkelijke, geïnformeerde toestemming). De Autoriteit Persoonsgegevens heeft richtlijnen uitgevaardigd die duidelijk maken dat het gebruik van gezichtsherkenning op personen zonder geldige wettelijke grondslag een ernstige overtreding vormt, die mogelijk kan leiden tot <span class="text-muted-color-emphasis font-bold">boetes tot €20 miljoen of 4% van de wereldwijde jaaromzet</span>.',
		'similar_rules' => 'Vergelijkbare of strengere regels kunnen van toepassing zijn in andere EU-/EER-landen, het Verenigd Koninkrijk, Canada en vele andere rechtsgebieden.',
		'no_liability' => 'Lychee is ontwikkeld onder de <a href="https://lycheeorg.dev/license" class="text-primary-400 underline" target="_blank">MIT-licentie</a>. <span class="text-muted-color-emphasis">De auteurs en medewerkers</span> van LycheeOrg <span class="text-muted-color-emphasis">aanvaarden geen aansprakelijkheid voor onrechtmatig gebruik</span>.<br/>Het is <span class="text-muted-color-emphasis">uw eigen verantwoordelijkheid om alle vereiste toestemming te verkrijgen, passende waarborgen te implementeren en de wettelijke toelaatbaarheid te controleren</span> voordat u deze software in gebruik neemt.',
		'acknowledge' => 'Ik heb bovenstaande juridische kennisgeving gelezen en begrepen',
		'accept' => 'Accepteren en waarschuwing sluiten',
	],
	'merge' => [
		'title' => 'Persoon samenvoegen',
		'into' => 'met…',
		'select_target' => 'Selecteer doelpersoon…',
		'warning' => 'Hierdoor worden alle gezichten van de bronpersoon verplaatst naar de doelpersoon en wordt de bronpersoon verwijderd. Dit kan niet ongedaan worden gemaakt.',
		'confirm' => 'Samenvoegen',
		'success' => 'Personen succesvol samengevoegd.',
	],
];
