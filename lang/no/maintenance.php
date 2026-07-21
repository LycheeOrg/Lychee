<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Vedlikehold',
    'description' => 'På denne siden finner du alle nødvendige handlinger for å holde Lychee-installasjonen din i gang knirkefritt.',
    'cleaning' => [
        'title' => 'Renser %s',
        'result' => '%s ble slettet.',
        'description' => 'Fjern alt innhold fra <span class="font-mono">%s</span>',
        'button' => 'Rens',
    ],
    'duplicate-finder' => [
        'title' => 'Duplikater',
        'description' => 'Denne modulen teller potensielle duplikater mellom bilder.',
        'duplicates-all' => 'Duplikater i alle album',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplikater per album',
        'show' => 'Vis duplikater',
        'load' => 'Last inn antall',
    ],
    'fix-jobs' => [
        'title' => 'Reparasjonshistorikk for jobber',
        'description' => 'Merk jobber med status <span class="text-ready-400">%s</span> eller <span class="text-primary-500">%s</span> som <span class="text-danger-700">%s</span>.',
        'button' => 'Reparer jobbhistorikk',
    ],
    'gen-sizevariants' => [
        'title' => 'Manglende %s',
        'description' => 'Fant %d %s som kunne genereres.',
        'button' => 'Generere!',
        'success' => 'Genererte %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Filstørrelser mangler',
        'description' => 'Fant %d små varianter uten filstørrelse.',
        'button' => 'Hent data!',
        'success' => 'Beregnet størrelser for %d små varianter.',
    ],
    'fix-tree' => [
        'title' => 'Trestatistikk',
        'Oddness' => 'Merkelighet',
        'Duplicates' => 'Duplikater',
        'Wrong parents' => 'Feil foreldre',
        'Missing parents' => 'Manglende foreldre',
        'button' => 'Reparer tre',
    ],
    'optimize' => [
        'title' => 'Optimaliser databasen',
        'description' => 'Hvis du merker at installasjonen er treg, kan det skyldes at databasen din ikke har all den nødvendige indeksen.',
        'button' => 'Optimaliser databasen',
    ],
    'update' => [
        'title' => 'Oppdateringer',
        'check-button' => 'Se etter oppdateringer',
        'update-button' => 'Oppdater',
        'no-pending-updates' => 'Ingen ventende oppdateringer.',
    ],
    'missing-palettes' => [
        'title' => 'Manglende paletter',
        'description' => 'Fant %d manglende paletter.',
        'button' => 'Opprett manglende',
    ],
    'statistics-check' => [
        'title' => 'Statistikk integritetskontroll',
        'missing_photos' => '%d fotostatistikk mangler.',
        'missing_albums' => '%d albumstatistikk mangler.',
        'button' => 'Opprett manglende',
    ],
    'flush-cache' => [
        'title' => 'Tøm buffer',
        'description' => 'Tøm hurtigbufferen til alle brukere for å løse ugyldighetsproblemer.',
        'button' => 'Tøm',
    ],
    'old-orders' => [
        'title' => 'Gamle Ordre',
        'description' => 'Fant %d gamle bestillinger.<br/><br/>En gammel bestilling er eldre enn 14 dager, har ingen tilknyttet bruker, og venter enten fortsatt på betaling eller har ingen varer i seg.',
        'button' => 'Slett gamle bestillinger',
    ],
    'fulfill-orders' => [
        'title' => 'Bestillinger å fullføre',
        'description' => 'Fant %d bestillinger med innhold som ikke er gjort tilgjengelig.<br/><br/>Klikk på knappen for å tilordne innhold der det er mulig.',
        'button' => 'Fullfør bestillinger',
    ],
    'fulfill-precompute' => [
        'title' => 'Forhåndsberegnede albumfelt',
        'description' => 'Fant %d album med manglende forhåndsberegnede felt.<br/><br/>Tilsvarer å kjøre: php artisan lychee:recompute-album-fields',
        'button' => 'Beregn felt',
    ],
    'flush-queue' => [
        'title' => 'Tøm kø',
        'description' => 'Fant %d ventende jobber i køen.<br/><br/>ADVARSEL: Tømming av køen vil permanent slette alle ventende jobber. Dette kan ikke angres.',
        'button' => 'Tøm kø',
    ],
    'backfill-album-sizes' => [
        'title' => 'Albumstørrelsesstatistikk',
        'description' => 'Fant %d album uten størrelsesstatistikk.<br/><br/>Tilsvarer å kjøre: php artisan lychee:recompute-album-sizes',
        'button' => 'Beregn størrelser',
    ],

    'face_quality' => [
        'title' => 'Gjennomgang av ansiktskvalitet',
        'description' => 'Gjennomgå ansiktsgjenkjenninger etter kvalitetspoengsum og avvis lavkvalitets- eller feilaktige ansikter.',
        'sort_by' => 'Sorter etter:',
        'sort_confidence' => 'Sikkerhet',
        'sort_blur' => 'Uskarphet (Laplace)',
        'no_faces' => 'Ingen kvalifiserende ansikter. Alt ser bra ut!',
        'col_face' => 'Ansikt',
        'col_person' => 'Person',
        'col_cluster' => 'Klynge',
        'col_confidence' => 'Sikkerhet',
        'col_blur' => 'Uskarphetspoengsum',
        'col_actions' => 'Handlinger',
        'unassigned' => 'Ikke tilordnet',
        'dismiss' => 'Avvis ansikt',
        'readd' => 'Legg til ansikt på nytt',
        'load_error' => 'Kunne ikke laste inn ansikter.',
        'dismissed' => 'Ansikt avvist.',
        'readded' => 'Ansikt lagt til på nytt.',
        'dismiss_error' => 'Kunne ikke avvise ansikt.',
        'readd_error' => 'Kunne ikke legge til ansikt på nytt.',
        'batch_dismiss' => 'Avvis valgte',
        'batch_dismissed' => ':count ansikt(er) avvist.',
        'batch_dismiss_error' => 'Kunne ikke avvise valgte ansikter.',
        'batch_reactivate' => 'Reaktiver valgte',
        'batch_reactivated' => ':count ansikt(er) reaktivert.',
        'batch_reactivate_error' => 'Kunne ikke reaktivere valgte ansikter.',
        'show_dismissed' => 'Vis avviste',
        'show_active' => 'Vis aktive',
        'show_unassigned' => 'Kun ikke tilordnede',
        'select_all' => 'Velg alle',
        'deselect_all' => 'Fjern alle valg',
        'selected_count' => ':count valgt',
    ],
    'bulk-scan-faces' => [
        'description' => 'Fant %d bilder som ennå ikke er skannet for ansiktsgjenkjenning.<br/><br/>Krever at AI Vision-tjenesten kjører.',
    ],
    'run-clustering' => [
        'description' => 'Utløs ansiktsklynging i AI Vision-tjenesten. Grupperer oppdagede ansikter etter likhet, slik at du kan tilordne dem til personer.',
        'success' => 'Klynging startet.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Slett avviste ansikter',
        'description' => 'Fant %d avviste ansikter. Sletting av dem vil permanent fjerne beskjæringsfilene og embeddingene deres.',
        'action' => 'Slett alle',
        'success' => 'Avviste ansikter slettet.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Synkroniser ansikts-embeddinger',
        'description' => 'Avvik i antall ansikter oppdaget (%d differanse). Synkronisering vil hente nyeste ansiktsdata fra AI Vision-tjenesten til Lychee.',
        'action' => 'Synkroniser nå',
        'success' => 'Ansikts-embeddinger synkronisert.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Tilbakestill status for ansiktsskanning',
        'description' => 'Fant %d bilder med en fastlåst eller mislykket status for ansiktsskanning. Tilbakestilling gjør at de kan skannes på nytt.',
        'action' => 'Tilbakestill alle',
        'success' => 'Status for ansiktsskanning tilbakestilt.',
    ],

        'bulk-scan-nsfw' => [
        'title' => 'Masseskanning for NSFW',
        'description' => 'Skann alle uskannede bilder for NSFW-innhold ved hjelp av den konfigurerte forhåndsinnstillingen. Krever at NSFW-klassifiseringstjenesten kjører.',
        'button' => 'Skann alle uskannede',
        'success' => 'NSFW-skanning igangsatt.',
    ],
];
