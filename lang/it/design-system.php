<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Un riferimento dal vivo per i token e i componenti con cui è effettivamente costruito il front end v8. Ogni campione e controllo di questa pagina legge le vere proprietà CSS personalizzate e i componenti Nuxt UI in uso oggi, quindi non può mai diventare obsoleto come un mockup statico.',

    'sections' => [
        'foundations' => 'Fondamenti',
        'colors' => 'Tavolozza colori',
        'typography' => 'Tipografia',
        'buttons' => 'Pulsanti',
        'badges' => 'Badge e chip',
        'forms' => 'Controlli dei moduli',
        'feedback' => 'Feedback e stati',
        'radius' => 'Raggi ed elevazione',
    ],

    'loading' => [
        'full_page' => 'Caricamento a pagina intera',
        'full_page_description' => 'Comparsa lenta e scaglionata — usato dietro l\'oscuramento a schermo intero mentre una vista si carica.',
        'mini_spinner' => 'Indicatore di caricamento piccolo',
        'mini_spinner_description' => 'Variante veloce, visibile subito — usata in linea per pulsanti, elenchi e piccoli stati di caricamento.',
    ],

    'foundations' => [
        'system' => 'Kit di componenti',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Font del testo',
        'body_font_value' => 'Libre Franklin, ospitato localmente (Vazirmatn per arabo/farsi)',
        'mono_font' => 'Font monospazio',
        'mono_font_value' => 'Stack monospazio di sistema (ui-monospace)',
        'icons' => 'Set di icone',
        'icons_value' => 'Lucide, tramite Iconify (UIcon)',
        'theme' => 'Tema',
        'theme_value' => 'Un unico tema scuro/chiaro, attivabile nelle Impostazioni — nessuna variazione per sezione',
    ],

    'colors' => [
        'primary' => 'Primario — colore del brand, azioni principali, link, stati attivi',
        'secondary' => 'Secondario — azioni e selezioni alternative',
        'neutral' => 'Neutro — testo, bordi, superfici (Slate in modalità chiara, Zinc in scura)',
        'success' => 'Successo — conferme, stati positivi',
        'warning' => 'Avviso — attenzione, problemi non bloccanti',
        'error' => 'Errore — azioni distruttive, errori',
        'info' => 'Info — messaggi informativi',
        'resolved_from' => 'Risolto dal vivo da :token',
    ],

    'typography' => [
        'display' => 'Display',
        'heading' => 'Titolo',
        'body' => 'Testo del corpo',
        'body_sample' => 'Quel vituperabile xenofobo zelante assaggia il whisky ed esclama: alleluja! — titoli degli album, etichette dei moduli e testi quotidiani usano questo stack.',
        'mono' => 'Monospazio',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Scala di enfasi del testo',
        'emphasis_sample' => 'Foto scattate in questo giorno negli anni passati',
    ],

    'buttons' => [
        'intro' => 'Ogni colore supportato dal sistema dei pulsanti, nelle varianti effettivamente usate nell\'app.',
    ],

    'forms' => [
        'input_label' => 'Titolo dell\'album',
        'input_placeholder' => 'Estate a Kyoto',
        'select_label' => 'Ordina per',
        'checkbox_label' => 'Mostra album sensibili',
        'switch_label' => 'Album pubblico',
        'switch_description' => 'Chiunque abbia il link può vedere questo album',
        'textarea_label' => 'Descrizione',
        'textarea_placeholder' => 'Aggiungi una descrizione per questo album…',
        'radio_legend' => 'Logica di corrispondenza dei tag',
    ],

    'feedback' => [
        'alert_info' => 'Sono state aggiunte nuove foto a questo album.',
        'alert_success' => 'Impostazioni salvate.',
        'alert_warning' => 'Questo album è visibile a chiunque abbia il link.',
        'alert_error' => 'Impossibile raggiungere il server — le modifiche non sono state salvate.',
        'empty_title' => 'Ancora nessuna condivisione',
        'empty_description' => 'Gli album condivisi e i relativi permessi appariranno qui dopo che avrai condiviso il tuo primo album.',
    ],
];
