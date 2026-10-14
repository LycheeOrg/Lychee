<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'Živá referencia tokenov a komponentov, z ktorých je front-end v8 skutočne postavený. Každá vzorka a ovládací prvok na tejto stránke čerpá zo skutočných vlastných vlastností CSS a komponentov Nuxt UI, ktoré sa dnes používajú, takže nikdy nemôže zastarať ako statický návrh.',

	'sections' => [
		'foundations' => 'Základy',
		'colors' => 'Farebná paleta',
		'typography' => 'Typografia',
		'buttons' => 'Tlačidlá',
		'badges' => 'Odznaky a čipy',
		'forms' => 'Formulárové prvky',
		'feedback' => 'Spätná väzba a stavy',
		'radius' => 'Zaoblenie a vyvýšenie',
	],

	'loading' => [
		'full_page' => 'Celostránkový načítavač',
		'full_page_description' => 'Pomalé, postupné odhalenie — používa sa za celoobrazovkovým zatemnením, kým sa načítava zobrazenie.',
		'mini_spinner' => 'Malý indikátor načítania',
		'mini_spinner_description' => 'Rýchla varianta, viditeľná okamžite — používa sa v rámci tlačidiel, zoznamov a malých stavov načítania.',
	],

	'foundations' => [
		'system' => 'Sada komponentov',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => 'Písmo textu',
		'body_font_value' => 'Libre Franklin, hostované lokálne (Vazirmatn pre arabčinu/perzštinu)',
		'mono_font' => 'Neproporcionálne písmo',
		'mono_font_value' => 'Systémová sada neproporcionálnych písiem (ui-monospace)',
		'icons' => 'Sada ikon',
		'icons_value' => 'Lucide, cez Iconify (UIcon)',
		'theme' => 'Motív',
		'theme_value' => 'Jeden tmavý/svetlý motív, prepínaný v Nastaveniach — bez prepisov pre jednotlivé sekcie',
	],

	'colors' => [
		'primary' => 'Primárna — farba značky, hlavné akcie, odkazy, aktívne stavy',
		'secondary' => 'Sekundárna — alternatívne akcie a výbery',
		'neutral' => 'Neutrálna — text, okraje, plochy (Slate vo svetlom režime, Zinc v tmavom)',
		'success' => 'Úspech — potvrdenia, pozitívne stavy',
		'warning' => 'Varovanie — opatrnosť, neblokujúce problémy',
		'error' => 'Chyba — deštruktívne akcie, zlyhania',
		'info' => 'Info — informačné správy',
		'resolved_from' => 'Načítané naživo z :token',
	],

	'typography' => [
		'display' => 'Display',
		'heading' => 'Nadpis',
		'body' => 'Text',
		'body_sample' => 'Kŕdeľ ďatľov učí koňa žrať kôru — názvy albumov, popisy formulárov a bežný text sa zobrazujú týmto písmom.',
		'mono' => 'Neproporcionálne',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'emphasis' => 'Škála zvýraznenia textu',
		'emphasis_sample' => 'Fotografie nasnímané v tento deň v minulých rokoch',
	],

	'buttons' => [
		'intro' => 'Každá farba, ktorú systém tlačidiel podporuje, vo variantoch skutočne používaných v aplikácii.',
	],

	'forms' => [
		'input_label' => 'Názov albumu',
		'input_placeholder' => 'Leto v Kjóte',
		'select_label' => 'Triediť podľa',
		'checkbox_label' => 'Zobraziť citlivé albumy',
		'switch_label' => 'Verejný album',
		'switch_description' => 'Tento album si môže pozrieť ktokoľvek s odkazom',
		'textarea_label' => 'Popis',
		'textarea_placeholder' => 'Pridajte popis tohto albumu…',
		'radio_legend' => 'Logika zhody štítkov',
	],

	'feedback' => [
		'alert_info' => 'Do tohto albumu boli pridané nové fotografie.',
		'alert_success' => 'Nastavenia boli uložené.',
		'alert_warning' => 'Tento album je viditeľný pre kohokoľvek s odkazom.',
		'alert_error' => 'Nepodarilo sa spojiť so serverom — zmeny neboli uložené.',
		'empty_title' => 'Zatiaľ žiadne zdieľania',
		'empty_description' => 'Zdieľané albumy a ich oprávnenia sa tu zobrazia, keď nazdieľate svoj prvý album.',
	],
];
