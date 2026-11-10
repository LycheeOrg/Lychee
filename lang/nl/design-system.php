<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Een live naslag van de tokens en componenten waaruit de v8-frontend werkelijk is opgebouwd. Elk staal en elk bedieningselement op deze pagina leest de echte CSS custom properties en Nuxt UI-componenten die vandaag in gebruik zijn, zodat het nooit verouderd kan raken zoals een statische mockup.',

    'sections' => [
        'foundations' => 'Basis',
        'colors' => 'Kleurenpalet',
        'typography' => 'Typografie',
        'buttons' => 'Knoppen',
        'badges' => 'Badges & chips',
        'forms' => 'Formulierelementen',
        'feedback' => 'Feedback & statussen',
        'radius' => 'Afronding & diepte',
    ],

    'loading' => [
        'full_page' => 'Volledige-paginalader',
        'full_page_description' => 'Langzame, getrapte onthulling — gebruikt achter de schermvullende verduistering terwijl een weergave laadt.',
        'mini_spinner' => 'Mini-spinner',
        'mini_spinner_description' => 'Snelle variant, direct zichtbaar — inline gebruikt voor knoppen, lijsten en kleine laadstatussen.',
    ],

    'foundations' => [
        'system' => 'Componentenkit',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Lettertype voor tekst',
        'body_font_value' => 'Libre Franklin, zelf gehost (Vazirmatn voor Arabisch/Farsi)',
        'mono_font' => 'Monospace-lettertype',
        'mono_font_value' => 'Systeem-monospacestack (ui-monospace)',
        'icons' => 'Pictogrammenset',
        'icons_value' => 'Lucide, via Iconify (UIcon)',
        'theme' => 'Thema',
        'theme_value' => 'Eén donker/licht thema, te wisselen in Instellingen — geen afwijkingen per sectie',
    ],

    'colors' => [
        'primary' => 'Primair — merkkleur, primaire acties, links, actieve statussen',
        'secondary' => 'Secundair — alternatieve acties en selecties',
        'neutral' => 'Neutraal — tekst, randen, vlakken (Slate in lichte modus, Zinc in donkere)',
        'success' => 'Succes — bevestigingen, positieve statussen',
        'warning' => 'Waarschuwing — voorzichtigheid, niet-blokkerende problemen',
        'error' => 'Fout — destructieve acties, mislukkingen',
        'info' => 'Info — informatieve berichten',
        'resolved_from' => 'Live bepaald uit :token',
    ],

    'typography' => [
        'display' => 'Display',
        'heading' => 'Kop',
        'body' => 'Broodtekst',
        'body_sample' => 'Pa\'s wijze lynx bezag vroom het fikse aquaduct — albumtitels, formulierlabels en alledaagse tekst worden in deze stack weergegeven.',
        'mono' => 'Monospace',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Schaal voor tekstnadruk',
        'emphasis_sample' => 'Foto\'s genomen op deze dag in voorgaande jaren',
    ],

    'buttons' => [
        'intro' => 'Elke kleur die het knoppensysteem ondersteunt, in de varianten die daadwerkelijk in de app worden gebruikt.',
    ],

    'forms' => [
        'input_label' => 'Albumtitel',
        'input_placeholder' => 'Zomer in Kyoto',
        'select_label' => 'Sorteren op',
        'checkbox_label' => 'Gevoelige albums tonen',
        'switch_label' => 'Openbaar album',
        'switch_description' => 'Iedereen met de link kan dit album bekijken',
        'textarea_label' => 'Beschrijving',
        'textarea_placeholder' => 'Voeg een beschrijving toe voor dit album…',
        'radio_legend' => 'Logica voor tagovereenkomst',
    ],

    'feedback' => [
        'alert_info' => 'Er zijn nieuwe foto\'s aan dit album toegevoegd.',
        'alert_success' => 'Instellingen opgeslagen.',
        'alert_warning' => 'Dit album is zichtbaar voor iedereen met de link.',
        'alert_error' => 'Kan de server niet bereiken — wijzigingen zijn niet opgeslagen.',
        'empty_title' => 'Nog geen deelacties',
        'empty_description' => 'Gedeelde albums en hun rechten verschijnen hier zodra u uw eerste album deelt.',
    ],
];
