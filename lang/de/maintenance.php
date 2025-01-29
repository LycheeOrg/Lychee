<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Wartung',
	'description' => 'Auf dieser Seite finden Sie alle notwendigen Funktionen für den reibungslosen Betrieb Ihrer Lychee Installation.',
	'cleaning' => [
		'title' => 'Säubern %s',
		'result' => '%s gelöscht.',
		'description' => 'Lösche den gesamten Inhalt aus <span class="font-mono">%s</span>',
		'button' => 'Säubern',
	],
	'duplicate-finder' => [
		'title' => 'Duplicates',
		'description' => 'This module counts potential duplicates betwen pictures.',
		'duplicates-all' => 'Duplicates over all albums',
		'duplicates-title' => 'Title duplicates per album',
		'duplicates-per-album' => 'Duplicates per album',
		'show' => 'Show duplicates',
	],
	'fix-jobs' => [
		'title' => 'Job Historie reparieren',
		'description' => 'Markiere Jobs mit dem Status <span class="text-ready-400">%s</span> oder <span class="text-primary-500">%s</span> als <span class="text-danger-700">%s</span>.',
		'button' => 'Repariere Job Historie',
	],
	'gen-sizevariants' => [
		'title' => 'Fehlende %s',
		'description' => 'Es wurden %d %s gefunden, welche noch angelegt werden können.',
		'button' => 'Anlegen',
		'success' => 'Erfolgreich angelegt. %d %s.',
	],
	'fill-filesize-sizevariants' => [
		'title' => 'Fehlende größenvariante',
		'description' => 'Es wurden %d kleine Varianten ohne Dateigröße gefunden.',
		'button' => 'Daten sammeln',
		'success' => 'Die Daten für %d kleine Varianten wurden erfolgreich verarbeitet.',
	],
	'fix-tree' => [
		'title' => 'Baumstruktur Statistik',
		'Oddness' => 'Eigenartig',
		'Duplicates' => 'Duplikate',
		'Wrong parents' => 'Falsche Oberkategorie',
		'Missing parents' => 'Fehlende Oberkategorie',
		'button' => 'Baumstruktur reparieren',
	],
	'optimize' => [
		'title' => 'Datenbank optimieren',
		'description' => 'Wenn Sie eine Verlangsamung Ihrer Installation festgestellt haben, könnte dies an fehlenden Datenbankindizes liegen.',
		'button' => 'Datenbank optimieren',
	],
	'update' => [
		'title' => 'Updates',
		'check-button' => 'Auf Updates prüfen',
		'update-button' => 'Update',
		'no-pending-updates' => 'Keine Updates verfügbar.',
	],
	'flush-cache' => [
		'title' => 'Flush Cache',
		'description' => 'Flush the cache of every user to solve invalidation problems.',
		'button' => 'Flush',
	],
];
