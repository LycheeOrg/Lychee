<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'En levande referens över de tokens och komponenter som v8-gränssnittet faktiskt är byggt av. Varje färgprov och kontroll på den här sidan läser de verkliga anpassade CSS-egenskaperna och Nuxt UI-komponenterna som används i dag, så den kan aldrig bli inaktuell på det sätt som en statisk skiss kan.',

    'sections' => [
        'foundations' => 'Grunder',
        'colors' => 'Färgpalett',
        'typography' => 'Typografi',
        'buttons' => 'Knappar',
        'badges' => 'Märken och chips',
        'forms' => 'Formulärkontroller',
        'feedback' => 'Återkoppling och tillstånd',
        'radius' => 'Hörnradie och upphöjning',
    ],

    'loading' => [
        'full_page' => 'Helsidesladdare',
        'full_page_description' => 'Långsam, stegvis visning — används bakom den mörka helskärmsövertäckningen medan en vy läses in.',
        'mini_spinner' => 'Minispinner',
        'mini_spinner_description' => 'Snabb variant, syns direkt — används inline för knappar, listor och små laddningstillstånd.',
    ],

    'foundations' => [
        'system' => 'Komponentbibliotek',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Brödtextteckensnitt',
        'body_font_value' => 'Libre Franklin, självhostat (Vazirmatn för arabiska/persiska)',
        'mono_font' => 'Teckensnitt med fast bredd',
        'mono_font_value' => 'Systemets monospace-stack (ui-monospace)',
        'icons' => 'Ikonuppsättning',
        'icons_value' => 'Lucide, via Iconify (UIcon)',
        'theme' => 'Tema',
        'theme_value' => 'Ett enda mörkt/ljust tema som växlas i Inställningar — inga åsidosättningar per sektion',
    ],

    'colors' => [
        'primary' => 'Primär — varumärkesfärg, primära åtgärder, länkar, aktiva tillstånd',
        'secondary' => 'Sekundär — alternativa åtgärder och val',
        'neutral' => 'Neutral — text, kanter, ytor (Slate i ljust läge, Zinc i mörkt)',
        'success' => 'Lyckad — bekräftelser, positiva tillstånd',
        'warning' => 'Varning — försiktighet, icke-blockerande problem',
        'error' => 'Fel — destruktiva åtgärder, misslyckanden',
        'info' => 'Info — informationsmeddelanden',
        'resolved_from' => 'Hämtad live från :token',
    ],

    'typography' => [
        'display' => 'Display',
        'heading' => 'Rubrik',
        'body' => 'Brödtext',
        'body_sample' => 'Flygande bäckasiner söka hwila på mjuka tuvor — albumtitlar, formuläretiketter och vardaglig text återges med denna stack.',
        'mono' => 'Fast bredd',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Skala för textbetoning',
        'emphasis_sample' => 'Foton tagna den här dagen tidigare år',
    ],

    'buttons' => [
        'intro' => 'Alla färger som knappsystemet stöder, i de varianter som faktiskt används i appen.',
    ],

    'forms' => [
        'input_label' => 'Albumtitel',
        'input_placeholder' => 'Sommar i Kyoto',
        'select_label' => 'Sortera efter',
        'checkbox_label' => 'Visa känsliga album',
        'switch_label' => 'Offentligt album',
        'switch_description' => 'Alla med länken kan visa detta album',
        'textarea_label' => 'Beskrivning',
        'textarea_placeholder' => 'Lägg till en beskrivning för detta album…',
        'radio_legend' => 'Logik för taggmatchning',
    ],

    'feedback' => [
        'alert_info' => 'Nya foton har lagts till i detta album.',
        'alert_success' => 'Inställningarna har sparats.',
        'alert_warning' => 'Detta album är synligt för alla med länken.',
        'alert_error' => 'Det gick inte att nå servern — ändringarna sparades inte.',
        'empty_title' => 'Inga delningar än',
        'empty_description' => 'Delade album och deras behörigheter visas här när du har delat ditt första album.',
    ],
];
