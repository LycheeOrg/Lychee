<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'Eine Live-Referenz der Tokens und Komponenten, aus denen das v8-Frontend tatsächlich aufgebaut ist. Jedes Farbfeld und jedes Steuerelement auf dieser Seite liest die echten CSS-Custom-Properties und Nuxt UI-Komponenten aus, die derzeit verwendet werden, sodass sie nie veralten kann, wie es bei einem statischen Mockup der Fall wäre.',

	'sections' => [
		'foundations' => 'Grundlagen',
		'colors' => 'Farbpalette',
		'typography' => 'Typografie',
		'buttons' => 'Schaltflächen',
		'badges' => 'Badges & Chips',
		'forms' => 'Formularelemente',
		'feedback' => 'Rückmeldungen & Zustände',
		'radius' => 'Radius & Erhebung',
	],

	'loading' => [
		'full_page' => 'Ganzseitige Ladeanzeige',
		'full_page_description' => 'Langsame, gestaffelte Einblendung — wird hinter der Vollbild-Abdunkelung verwendet, während eine Ansicht lädt.',
		'mini_spinner' => 'Mini-Ladeanzeige',
		'mini_spinner_description' => 'Schnelle Variante, sofort sichtbar — wird inline für Schaltflächen, Listen und kleine Ladezustände verwendet.',
	],

	'foundations' => [
		'system' => 'Komponenten-Kit',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => 'Schrift für Fließtext',
		'body_font_value' => 'Libre Franklin, selbst gehostet (Vazirmatn für Arabisch/Farsi)',
		'mono_font' => 'Monospace-Schrift',
		'mono_font_value' => 'System-Monospace-Stack (ui-monospace)',
		'icons' => 'Icon-Set',
		'icons_value' => 'Lucide, über Iconify (UIcon)',
		'theme' => 'Theme',
		'theme_value' => 'Ein einziges dunkles/helles Theme, umschaltbar in den Einstellungen — keine abschnittsweisen Überschreibungen',
	],

	'colors' => [
		'primary' => 'Primär — Markenfarbe, primäre Aktionen, Links, aktive Zustände',
		'secondary' => 'Sekundär — alternative Aktionen und Auswahlen',
		'neutral' => 'Neutral — Text, Rahmen, Flächen (Slate im hellen Modus, Zinc im dunklen)',
		'success' => 'Erfolg — Bestätigungen, positive Zustände',
		'warning' => 'Warnung — Vorsicht, nicht blockierende Probleme',
		'error' => 'Fehler — destruktive Aktionen, Fehlschläge',
		'info' => 'Info — informative Meldungen',
		'resolved_from' => 'Live aufgelöst aus :token',
	],

	'typography' => [
		'display' => 'Display',
		'heading' => 'Überschrift',
		'body' => 'Fließtext',
		'body_sample' => 'Victor jagt zwölf Boxkämpfer quer über den großen Sylter Deich — Albumtitel, Formularbeschriftungen und alltägliche Texte werden in diesem Stack dargestellt.',
		'mono' => 'Monospace',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'emphasis' => 'Skala der Texthervorhebung',
		'emphasis_sample' => 'Fotos, die an diesem Tag in vergangenen Jahren aufgenommen wurden',
	],

	'buttons' => [
		'intro' => 'Jede Farbe, die das Schaltflächensystem unterstützt, in den Varianten, die in der App tatsächlich verwendet werden.',
	],

	'forms' => [
		'input_label' => 'Albumtitel',
		'input_placeholder' => 'Sommer in Kyoto',
		'select_label' => 'Sortieren nach',
		'checkbox_label' => 'Sensible Alben anzeigen',
		'switch_label' => 'Öffentliches Album',
		'switch_description' => 'Jeder mit dem Link kann dieses Album ansehen',
		'textarea_label' => 'Beschreibung',
		'textarea_placeholder' => 'Fügen Sie eine Beschreibung für dieses Album hinzu…',
		'radio_legend' => 'Logik für Tag-Übereinstimmung',
	],

	'feedback' => [
		'alert_info' => 'Diesem Album wurden neue Fotos hinzugefügt.',
		'alert_success' => 'Einstellungen gespeichert.',
		'alert_warning' => 'Dieses Album ist für jeden mit dem Link sichtbar.',
		'alert_error' => 'Der Server ist nicht erreichbar — Änderungen wurden nicht gespeichert.',
		'empty_title' => 'Noch keine Freigaben',
		'empty_description' => 'Freigegebene Alben und ihre Berechtigungen werden hier angezeigt, sobald Sie Ihr erstes Album freigeben.',
	],
];
