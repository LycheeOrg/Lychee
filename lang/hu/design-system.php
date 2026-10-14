<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'Élő referencia azokról a tokenekről és komponensekről, amelyekből a v8 felület ténylegesen felépül. Az oldalon minden színminta és vezérlő a ma használt valódi CSS egyéni tulajdonságokat és Nuxt UI komponenseket olvassa, így sosem avulhat el úgy, mint egy statikus látványterv.',

	'sections' => [
		'foundations' => 'Alapok',
		'colors' => 'Színpaletta',
		'typography' => 'Tipográfia',
		'buttons' => 'Gombok',
		'badges' => 'Jelvények és címkék',
		'forms' => 'Űrlapvezérlők',
		'feedback' => 'Visszajelzések és állapotok',
		'radius' => 'Lekerekítés és kiemelés',
	],

	'loading' => [
		'full_page' => 'Teljes oldalas betöltő',
		'full_page_description' => 'Lassú, lépcsőzetes megjelenés — a teljes képernyős elsötétítés mögött használatos, amíg egy nézet betöltődik.',
		'mini_spinner' => 'Mini töltésjelző',
		'mini_spinner_description' => 'Gyors változat, azonnal látható — gombokban, listákban és kisebb betöltési állapotoknál használatos.',
	],

	'foundations' => [
		'system' => 'Komponenskészlet',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => 'Szövegtörzs betűtípusa',
		'body_font_value' => 'Libre Franklin, saját tárhelyen (Vazirmatn arab/perzsa nyelvhez)',
		'mono_font' => 'Fix szélességű betűtípus',
		'mono_font_value' => 'Rendszer fix szélességű betűkészlete (ui-monospace)',
		'icons' => 'Ikonkészlet',
		'icons_value' => 'Lucide, Iconify-on keresztül (UIcon)',
		'theme' => 'Téma',
		'theme_value' => 'Egyetlen sötét/világos téma, a Beállításokban váltható — szakaszonkénti felülbírálás nélkül',
	],

	'colors' => [
		'primary' => 'Elsődleges — márkaszín, elsődleges műveletek, linkek, aktív állapotok',
		'secondary' => 'Másodlagos — alternatív műveletek és kijelölések',
		'neutral' => 'Semleges — szöveg, szegélyek, felületek (világos módban Slate, sötétben Zinc)',
		'success' => 'Siker — megerősítések, pozitív állapotok',
		'warning' => 'Figyelmeztetés — óvatosság, nem blokkoló problémák',
		'error' => 'Hiba — romboló műveletek, hibák',
		'info' => 'Információ — tájékoztató üzenetek',
		'resolved_from' => 'Élőben feloldva innen: :token',
	],

	'typography' => [
		'display' => 'Kiemelt cím',
		'heading' => 'Címsor',
		'body' => 'Szövegtörzs',
		'body_sample' => 'Árvíztűrő tükörfúrógép — az albumcímek, űrlapcímkék és a mindennapi szövegek ezzel a betűkészlettel jelennek meg.',
		'mono' => 'Fix szélességű',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'emphasis' => 'Szövegkiemelési skála',
		'emphasis_sample' => 'Az elmúlt években ezen a napon készült fényképek',
	],

	'buttons' => [
		'intro' => 'A gombrendszer által támogatott összes szín, az alkalmazásban ténylegesen használt változatokban.',
	],

	'forms' => [
		'input_label' => 'Album címe',
		'input_placeholder' => 'Nyár Kiotóban',
		'select_label' => 'Rendezés',
		'checkbox_label' => 'Érzékeny albumok megjelenítése',
		'switch_label' => 'Nyilvános album',
		'switch_description' => 'Bárki megtekintheti ezt az albumot, aki rendelkezik a linkkel',
		'textarea_label' => 'Leírás',
		'textarea_placeholder' => 'Adjon leírást ehhez az albumhoz…',
		'radio_legend' => 'Címkeegyezési logika',
	],

	'feedback' => [
		'alert_info' => 'Új fényképek kerültek ebbe az albumba.',
		'alert_success' => 'Beállítások mentve.',
		'alert_warning' => 'Ez az album bárki számára látható, aki rendelkezik a linkkel.',
		'alert_error' => 'Nem sikerült elérni a szervert — a módosítások nem lettek mentve.',
		'empty_title' => 'Még nincsenek megosztások',
		'empty_description' => 'A megosztott albumok és jogosultságaik itt jelennek meg, miután megosztotta az első albumát.',
	],
];
