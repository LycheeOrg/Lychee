<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Živá reference tokenů a komponent, ze kterých je front end v8 skutečně postaven. Každý vzorek a ovládací prvek na této stránce čte skutečné vlastní vlastnosti CSS a komponenty Nuxt UI, které se dnes používají, takže nikdy nemůže zastarat jako statická maketa.',

    'sections' => [
        'foundations' => 'Základy',
        'colors' => 'Barevná paleta',
        'typography' => 'Typografie',
        'buttons' => 'Tlačítka',
        'badges' => 'Odznaky a čipy',
        'forms' => 'Formulářové prvky',
        'feedback' => 'Zpětná vazba a stavy',
        'radius' => 'Zaoblení a vyvýšení',
    ],

    'loading' => [
        'full_page' => 'Celostránkový načítač',
        'full_page_description' => 'Pomalé, postupné odhalení — používá se za celoobrazovkovým zatemněním během načítání zobrazení.',
        'mini_spinner' => 'Malý indikátor načítání',
        'mini_spinner_description' => 'Rychlá varianta, viditelná okamžitě — používá se v řádku pro tlačítka, seznamy a malé stavy načítání.',
    ],

    'foundations' => [
        'system' => 'Sada komponent',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Písmo textu',
        'body_font_value' => 'Libre Franklin, hostované lokálně (Vazirmatn pro arabštinu/perštinu)',
        'mono_font' => 'Neproporcionální písmo',
        'mono_font_value' => 'Systémová sada neproporcionálních písem (ui-monospace)',
        'icons' => 'Sada ikon',
        'icons_value' => 'Lucide, přes Iconify (UIcon)',
        'theme' => 'Motiv',
        'theme_value' => 'Jediný tmavý/světlý motiv, přepínaný v Nastavení — bez přepisů pro jednotlivé sekce',
    ],

    'colors' => [
        'primary' => 'Primární — barva značky, primární akce, odkazy, aktivní stavy',
        'secondary' => 'Sekundární — alternativní akce a výběry',
        'neutral' => 'Neutrální — text, okraje, plochy (Slate ve světlém režimu, Zinc v tmavém)',
        'success' => 'Úspěch — potvrzení, pozitivní stavy',
        'warning' => 'Varování — opatrnost, neblokující problémy',
        'error' => 'Chyba — destruktivní akce, selhání',
        'info' => 'Informace — informační zprávy',
        'resolved_from' => 'Načteno živě z :token',
    ],

    'typography' => [
        'display' => 'Display',
        'heading' => 'Nadpis',
        'body' => 'Text',
        'body_sample' => 'Příliš žluťoučký kůň úpěl ďábelské ódy — názvy alb, popisky formulářů a běžné texty se vykreslují tímto písmem.',
        'mono' => 'Neproporcionální',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Škála zvýraznění textu',
        'emphasis_sample' => 'Fotografie pořízené v tento den v minulých letech',
    ],

    'buttons' => [
        'intro' => 'Všechny barvy, které systém tlačítek podporuje, ve variantách skutečně používaných v aplikaci.',
    ],

    'forms' => [
        'input_label' => 'Název alba',
        'input_placeholder' => 'Léto v Kjótu',
        'select_label' => 'Řadit podle',
        'checkbox_label' => 'Zobrazit citlivá alba',
        'switch_label' => 'Veřejné album',
        'switch_description' => 'Toto album může zobrazit kdokoli s odkazem',
        'textarea_label' => 'Popis',
        'textarea_placeholder' => 'Přidejte popis tohoto alba…',
        'radio_legend' => 'Logika shody štítků',
    ],

    'feedback' => [
        'alert_info' => 'Do tohoto alba byly přidány nové fotografie.',
        'alert_success' => 'Nastavení uloženo.',
        'alert_warning' => 'Toto album je viditelné pro kohokoli s odkazem.',
        'alert_error' => 'Nelze se spojit se serverem — změny nebyly uloženy.',
        'empty_title' => 'Zatím žádná sdílení',
        'empty_description' => 'Sdílená alba a jejich oprávnění se zde zobrazí, jakmile nasdílíte své první album.',
    ],
];
