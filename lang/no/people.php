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
	'title' => 'Personer',
	'description' => 'Bla gjennom bilder etter personene i dem.',
	'no_people' => 'Ingen personer funnet ennå. Skann noen bilder for å oppdage ansikter.',
	'photos_label' => 'bilde(r)',
	'faces_label' => 'ansikt(er)',
	'hidden_faces' => 'ansikt(er) skjult av personvernhensyn',
	'unknown' => 'Ukjent',
	'confidence' => 'Sikkerhet',
	'laplacian_variance' => 'Laplace-varians',
	'assign_face' => 'Tilordne ansikt',
	'dismiss_face' => 'Avvis ansikt',
	'undismiss_face' => 'Angre avvisning av ansikt',
	'scan_faces' => 'Skann etter ansikter',
	'scanning' => 'Skanner etter ansikter…',
	'scan_success' => 'Ansiktsskanning satt i kø.',
	'not_searchable' => 'Skjult',
	'searchable' => 'Synlig',
	'claim_by_selfie' => 'Finn meg i bilder',
	'claim_by_selfie_description' => 'Last opp en selfie for å finne og koble til personprofilen din.',
	'claims' => [
		'success' => 'Koblet til profilen din.',
		'no_face' => 'Ingen ansikt oppdaget i selfien.',
		'no_match' => 'Ingen samsvarende person funnet.',
		'already_claimed' => 'Denne personen er allerede koblet til en annen bruker.',
		'low_confidence' => 'For lav treffsikkerhet. Prøv et tydeligere bilde.',
	],
	'person' => [
		'edit' => 'Rediger',
		'delete' => 'Slett',
		'delete_confirm' => 'Er du sikker på at du vil slette «%s»?',
		'delete_warning' => 'Denne handlingen kan ikke angres! Alle ansikter tilordnet denne personen vil bli fjernet.',
		'merge' => 'Slå sammen med…',
		'toggle_searchable' => 'Slå av/på synlighet',
		'claim' => 'Dette er meg',
		'unclaim' => 'Koble fra meg',
		'photos_title' => 'Bilder av %s',
	],
	'clusters_title' => 'Ansiktsklynger',
	'run_clustering' => 'Kjør klynging',
	'no_clusters' => 'Ingen klynger funnet. Kjør klynging for å gruppere oppdagede ansikter.',
	'faces' => 'ansikter',
	'enter_name' => 'Personnavn…',
	'assign' => 'Tilordne',
	'dismiss' => 'Avvis',
	'assignment' => [
		'title' => 'Tilordne ansikt til person',
		'batch_title' => 'Tilordne :count ansikt(er) til person',
		'select_person' => 'Velg eksisterende person…',
		'new_person' => 'Eller opprett ny person',
		'new_person_placeholder' => 'Navn på ny person…',
		'confirm' => 'Tilordne',
		'cancel' => 'Avbryt',
		'success' => 'Ansikt tilordnet.',
		'dismiss' => 'Avvis',
		'dismissed' => 'Ansikt avvist.',
	],
	'people_detected' => ':count person oppdaget|:count personer oppdaget',
	'filter_active' => 'Viser bilder med :name',
	'people_in_photo' => 'Personer på dette bildet',
	'remove_from_person' => 'Fjern fra person',
	'remove_from_person_success' => 'Ansikt fjernet.',
	'batch_select' => 'Velg ansikter',
	'batch_cancel' => 'Avbryt utvalg',
	'batch_selected' => ':count valgt',
	'batch_assign' => 'Tilordne valgte',
	'batch_unassign' => 'Fjern tilordning for valgte',
	'assign_to_user' => 'Tilordne til bruker',
	'search_user' => 'Søk bruker…',
	'cluster_detail_title' => 'Klynge (:count ansikter)',
	'assigned_faces_to' => 'Tilordnet :count ansikt(er) til «:name»',
	'assigned_faces' => 'Tilordnet :count ansikt(er)',
	'dismissed_faces' => 'Avviste :count ansikt(er)',
	'clustering_started' => 'Klynging startet. Last inn siden på nytt når den er fullført.',

	'face_recognition_warning' => [
		'title' => 'Juridisk merknad — ansiktsgjenkjenning',
		'legal_notice' => 'Ansiktsgjenkjenningsteknologi kan være underlagt strenge lovbestemte begrensninger eller være direkte forbudt i din jurisdiksjon. Før du tar denne tjenesten i bruk, må du sørge for at du overholder alle gjeldende lover og forskrifter.',
		'example_title' => 'Eksempel — Nederland:',
		'example_body' => 'I henhold til Nederlands implementering av EUs personvernforordning (GDPR) er biometriske data (inkludert ansiktsgjenkjenningsdata) klassifisert som en særlig kategori av personopplysninger (artikkel 9 GDPR). Behandling av slike data er forbudt med mindre et spesifikt rettslig grunnlag foreligger (f.eks. uttrykkelig informert samtykke). Det nederlandske datatilsynet (Autoriteit Persoonsgegevens) har utstedt veiledning som klargjør at bruk av ansiktsgjenkjenning på enkeltpersoner uten et gyldig rettslig grunnlag utgjør et alvorlig brudd, som potensielt kan <span class="text-muted-color-emphasis font-bold">medføre bøter på opptil 20 millioner euro eller 4 % av global årsomsetning</span>.',
		'similar_rules' => 'Lignende eller strengere regler kan gjelde i andre EU-/EØS-land, Storbritannia, Canada og mange andre jurisdiksjoner.',
		'no_liability' => 'Lychee er utviklet under <a href="https://lycheeorg.dev/license" class="text-primary-400 underline" target="_blank">MIT-lisensen</a>. <span class="text-muted-color-emphasis">Forfatterne og bidragsyterne</span> til LycheeOrg <span class="text-muted-color-emphasis">påtar seg intet ansvar for ulovlig bruk</span>.<br/>Det er <span class="text-muted-color-emphasis">ditt eget ansvar å innhente nødvendig samtykke, implementere hensiktsmessige tiltak og kontrollere lovligheten</span> før du tar i bruk denne programvaren.',
		'acknowledge' => 'Jeg har lest og forstått den juridiske merknaden ovenfor',
		'accept' => 'Godta og lukk advarsel',
	],
	'merge' => [
		'title' => 'Slå sammen person',
		'into' => 'med…',
		'select_target' => 'Velg målperson…',
		'warning' => 'Dette vil flytte alle ansikter fra kildepersonen til målpersonen og slette kildepersonen. Dette kan ikke angres.',
		'confirm' => 'Slå sammen',
		'success' => 'Personer slått sammen.',
	],
];
