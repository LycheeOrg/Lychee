<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Pagina iniziale',
    'tab_settings' => 'Impostazioni',
    'tab_links' => 'Link',
    'tab_featured' => 'In evidenza',

    'section_layout' => 'Layout e struttura',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Sfondo (orizzontale)',
    'section_background_portrait' => 'Sfondo (verticale)',
    'section_cta_position' => 'Posizione del pulsante di invito all\'azione',
    'section_login_position' => 'Pannello di accesso',
    'section_meridian' => 'Linee Meridian',
    'section_content' => 'Contenuto',

    'field_layout' => 'Layout',
    'field_intro_screen_enabled' => 'Schermata di introduzione',
    'field_hero_text_position' => 'Posizione del testo hero',
    'field_hero_text_color' => 'Colore del testo hero',
    'field_hero_text_opacity' => 'Opacità del testo hero',
    'field_animation_preset' => 'Preset di animazione',
    'field_backdrop_opacity' => 'Opacità dello sfondo (:value%)',
    'field_cta_text' => 'testo',
    'field_cta_text_placeholder' => 'Lascia vuoto per il valore predefinito del layout',
    'field_about_enabled' => 'Sezione Informazioni',
    'field_about_text' => 'Testo informativo',

    'field_background_mode' => 'Origine',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID foto',
        'random' => 'Foto pubblica casuale',
        'latest_album_cover' => 'Copertina dell\'album più recente',
        'random_from_album' => 'Foto casuale da un album',
    ],
    'field_background_url' => 'URL dell\'immagine',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID foto',
    'field_background_photo_id_placeholder' => 'ID foto di 24 caratteri',
    'field_background_photo_id_hint' => 'ID foto dell\'immagine da usare. Apri una foto e copia gli ultimi 24 caratteri dell\'URL.',
    'field_background_album_id' => 'ID album',
    'field_background_album_id_placeholder' => 'ID album di 24 caratteri',
    'background_load_error' => 'Impossibile caricare la foto. Verifica che l\'ID sia corretto e di avervi accesso.',
    'background_mode_hint' => [
        'random' => 'A ogni caricamento della pagina viene scelta una foto pubblica casuale.',
        'latest_album_cover' => 'Viene usata la copertina dell\'album pubblico pubblicato più di recente.',
        'random_from_album' => 'A ogni caricamento della pagina viene scelta una foto casuale da questo album. L\'anteprima si aggiorna solo dopo il salvataggio.',
    ],

    'preview_orientation_landscape' => 'Anteprima sfondo orizzontale',
    'preview_orientation_portrait' => 'Anteprima sfondo verticale',

    'field_cta_position' => 'Posizione',
    'cta_position_options' => [
        'top-left' => 'In alto a sinistra',
        'top' => 'In alto al centro',
        'top-right' => 'In alto a destra',
        'left' => 'Al centro a sinistra',
        'center' => 'Centro',
        'right' => 'Al centro a destra',
        'bottom-left' => 'In basso a sinistra',
        'bottom' => 'In basso al centro',
        'bottom-right' => 'In basso a destra',
    ],
    'field_cta_shift_type' => 'Unità di spostamento',
    'cta_shift_type_options' => [
        'relative' => 'Relativo (%)',
        'absolute' => 'Assoluto (px)',
    ],
    'cta_shift_type_hint' => 'Gli spostamenti relativi sono una percentuale della dimensione della finestra; quelli assoluti sono un numero fisso di pixel.',
    'field_cta_shift_x' => 'Spostamento orizzontale (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Sinistra',
        'right' => 'Destra',
    ],
    'field_cta_shift_y' => 'Spostamento verticale (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Su',
        'down' => 'Giù',
    ],
    'reset_to_zero' => 'Reimposta a 0',

    'field_login_position' => 'Posizione',
    'login_position_options' => [
        'side' => 'Laterale',
        'center' => 'Centro',
    ],
    'field_login_position_hint' => 'Sugli schermi stretti il pannello di accesso è sempre centrato sotto l\'hero, indipendentemente da questa impostazione. Scorre dal bordo della direzione di lettura quando il preset di animazione è "Slide reveal" o "Parallax scroll".',

    'field_meridian_explore_offset' => 'Altezza dell\'etichetta Esplora (:value%)',
    'field_meridian_contact_offset' => 'Altezza dell\'etichetta Contatti (:value%)',
    'field_meridian_offset_hint' => 'Quanto in basso si trova l\'etichetta lungo ciascuna linea a tutta altezza: 0% è in alto, 100% è in basso.',
    'field_meridian_explore_line_position' => 'Posizione della linea Esplora (:value%)',
    'field_meridian_contact_line_position' => 'Posizione della linea Contatti (:value%)',
    'field_meridian_line_position_hint' => 'Distanza di ciascuna linea dal bordo sinistro: 0% è il bordo sinistro, 100% il bordo destro. La linea Esplora si centra sempre quando la linea Contatti è disattivata.',

    'preview_title' => 'Anteprima dal vivo',
    'preview_hint' => 'Si aggiorna istantaneamente mentre modifichi — nulla viene salvato finché non fai clic su Salva.',
    'flat_list_hint' => 'Queste impostazioni sono modificabili anche dall\'elenco generico delle Impostazioni.',

    'save' => 'Salva',
    'saved' => 'Impostazioni della pagina iniziale salvate.',
    'save_error' => 'Impossibile salvare le impostazioni della pagina iniziale.',

    'se_required' => 'Questo layout/preset richiede Lychee SE.',
];
