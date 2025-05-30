<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Onderhoud',
	'description' => 'Op deze pagina vindt u alle benodigde acties om uw Lychee-installatie soepel en netjes te laten werken.',
	'cleaning' => [
		'title' => 'Opschonen %s',
		'result' => '%s verwijderd.',
		'description' => 'Verwijder alle inhoud van <span class="font-mono">%s</span>',
		'button' => 'Opschonen',
	],
	'duplicate-finder' => [
		'title' => 'Duplicaten',
		'description' => 'Deze module telt mogelijke duplicaten tussen foto’s.',
		'duplicates-all' => 'Duplicaten over alle albums',
		'duplicates-title' => 'Titelduplicaten per album',
		'duplicates-per-album' => 'Duplicaten per album',
		'show' => 'Toon duplicaten',
	],
	'fix-jobs' => [
		'title' => 'Taakgeschiedenis herstellen',
		'description' => 'Markeer taken met status <span class="text-ready-400">%s</span> of <span class="text-primary-500">%s</span> als <span class="text-danger-700">%s</span>.',
		'button' => 'Herstel taakgeschiedenis',
	],
	'gen-sizevariants' => [
		'title' => 'Ontbrekende %s',
		'description' => '%d %s gevonden die gegenereerd kunnen worden.',
		'button' => 'Genereer!',
		'success' => 'Succesvol %d %s gegenereerd.',
	],
	'fill-filesize-sizevariants' => [
		'title' => 'Bestandsgroottes ontbreken',
		'description' => '%d kleine varianten zonder bestandsgrootte gevonden.',
		'button' => 'Gegevens ophalen!',
		'success' => 'Succesvol de groottes van %d kleine varianten berekend.',
	],
	'fix-tree' => [
		'title' => 'Boomstatistieken',
		'Oddness' => 'Onregelmatigheden',
		'Duplicates' => 'Duplicaten',
		'Wrong parents' => 'Verkeerde ouders',
		'Missing parents' => 'Ontbrekende ouders',
		'button' => 'Herstel boom',
	],
	'optimize' => [
		'title' => 'Database optimaliseren',
		'description' => 'Als u vertragingen in uw installatie opmerkt, kan dit komen doordat uw database niet alle benodigde indexen heeft.',
		'button' => 'Optimaliseer database',
	],
	'update' => [
		'title' => 'Updates',
		'check-button' => 'Controleer op updates',
		'update-button' => 'Bijwerken',
		'no-pending-updates' => 'Geen updates in behandeling.',
	],
	'statistics-check' => [
		'title' => 'Controle op statistische integriteit',
		'missing_photos' => '%d fotostatistieken ontbreken.',
		'missing_albums' => '%d albumstatistieken ontbreken.',
		'button' => 'Maak ontbrekende aan',
	],
	'flush-cache' => [
		'title' => 'Cache legen',
		'description' => 'Leeg de cache van elke gebruiker om invalidatieproblemen op te lossen.',
		'button' => 'Leeg cache',
	],
];