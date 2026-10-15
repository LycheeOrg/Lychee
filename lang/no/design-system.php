<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'En levende referanse for tokenene og komponentene v8-frontenden faktisk er bygget av. Hver fargeprøve og kontroll på denne siden leser de ekte CSS-egenskapene og Nuxt UI-komponentene som er i bruk i dag, så den kan aldri bli utdatert slik en statisk mockup kan.',

	'sections' => [
		'foundations' => 'Grunnlag',
		'colors' => 'Fargepalett',
		'typography' => 'Typografi',
		'buttons' => 'Knapper',
		'badges' => 'Merker og brikker',
		'forms' => 'Skjemakontroller',
		'feedback' => 'Tilbakemelding og tilstander',
		'radius' => 'Hjørneradius og elevasjon',
	],

	'loading' => [
		'full_page' => 'Helsideslaster',
		'full_page_description' => 'Langsom, forskjøvet visning — brukes bak fullskjermsmørkleggingen mens en visning lastes.',
		'mini_spinner' => 'Mini-spinner',
		'mini_spinner_description' => 'Rask variant, synlig umiddelbart — brukes inline for knapper, lister og små lastetilstander.',
	],

	'foundations' => [
		'system' => 'Komponentsett',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => 'Brødtekstskrift',
		'body_font_value' => 'Libre Franklin, selvhostet (Vazirmatn for arabisk/farsi)',
		'mono_font' => 'Monospace-skrift',
		'mono_font_value' => 'Systemets monospace-stakk (ui-monospace)',
		'icons' => 'Ikonsett',
		'icons_value' => 'Lucide, via Iconify (UIcon)',
		'theme' => 'Tema',
		'theme_value' => 'Ett mørkt/lyst tema, byttes i Innstillinger — ingen overstyringer per seksjon',
	],

	'colors' => [
		'primary' => 'Primær — merkevarefarge, primære handlinger, lenker, aktive tilstander',
		'secondary' => 'Sekundær — alternative handlinger og valg',
		'neutral' => 'Nøytral — tekst, kanter, flater (Slate i lys modus, Zinc i mørk)',
		'success' => 'Suksess — bekreftelser, positive tilstander',
		'warning' => 'Advarsel — forsiktighet, ikke-blokkerende problemer',
		'error' => 'Feil — destruktive handlinger, feil',
		'info' => 'Info — informasjonsmeldinger',
		'resolved_from' => 'Hentet direkte fra :token',
	],

	'typography' => [
		'display' => 'Display',
		'heading' => 'Overskrift',
		'body' => 'Brødtekst',
		'body_sample' => 'Vår sære Zulu fra badeøya spilte jo whist og quickstep i min taxi — albumtitler, skjemaetiketter og vanlig tekst vises med denne stakken.',
		'mono' => 'Monospace',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'emphasis' => 'Skala for tekstvektlegging',
		'emphasis_sample' => 'Bilder tatt på denne dagen i tidligere år',
	],

	'buttons' => [
		'intro' => 'Alle fargene knappesystemet støtter, i variantene som faktisk brukes i appen.',
	],

	'forms' => [
		'input_label' => 'Albumtittel',
		'input_placeholder' => 'Sommer i Kyoto',
		'select_label' => 'Sorter etter',
		'checkbox_label' => 'Vis sensitive album',
		'switch_label' => 'Offentlig album',
		'switch_description' => 'Alle med lenken kan se dette albumet',
		'textarea_label' => 'Beskrivelse',
		'textarea_placeholder' => 'Legg til en beskrivelse for dette albumet…',
		'radio_legend' => 'Logikk for tagg-samsvar',
	],

	'feedback' => [
		'alert_info' => 'Nye bilder ble lagt til i dette albumet.',
		'alert_success' => 'Innstillinger lagret.',
		'alert_warning' => 'Dette albumet er synlig for alle med lenken.',
		'alert_error' => 'Kunne ikke nå serveren — endringene ble ikke lagret.',
		'empty_title' => 'Ingen delinger ennå',
		'empty_description' => 'Delte album og tillatelsene deres vises her når du deler ditt første album.',
	],
];
