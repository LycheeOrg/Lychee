<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Onderhoud',
    'description' => 'Op deze pagina vindt u alle benodigde acties om uw Lychee-installatie soepel en netjes te laten werken.',
    'cleaning' => [
        'title' => 'Opschonen %s',
        'result' => '%s verwijderd.',
        'description' => 'Verwijder alle inhoud van <span class="font-mono">%s</span>',
        'button' => 'Opschonen',
    ],
    'duplicate-finder' => [
        'title' => 'Duplicaten',
        'description' => 'Deze module telt mogelijke duplicaten tussen foto’s.',
        'duplicates-all' => 'Duplicaten over alle albums',
        'duplicates-title' => 'Titelduplicaten per album',
        'duplicates-per-album' => 'Duplicaten per album',
        'show' => 'Toon duplicaten',
        'load' => 'Aantallen laden',
    ],
    'fix-jobs' => [
        'title' => 'Taakgeschiedenis herstellen',
        'description' => 'Markeer taken met status <span class="text-ready-400">%s</span> of <span class="text-primary-500">%s</span> als <span class="text-danger-700">%s</span>.',
        'button' => 'Herstel taakgeschiedenis',
    ],
    'gen-sizevariants' => [
        'title' => 'Ontbrekende %s',
        'description' => '%d %s gevonden die gegenereerd kunnen worden.',
        'button' => 'Genereer!',
        'success' => 'Succesvol %d %s gegenereerd.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Bestandsgroottes ontbreken',
        'description' => '%d kleine varianten zonder bestandsgrootte gevonden.',
        'button' => 'Gegevens ophalen!',
        'success' => 'Succesvol de groottes van %d kleine varianten berekend.',
    ],
    'fix-tree' => [
        'title' => 'Boomstatistieken',
        'Oddness' => 'Onregelmatigheden',
        'Duplicates' => 'Duplicaten',
        'Wrong parents' => 'Verkeerde ouders',
        'Missing parents' => 'Ontbrekende ouders',
        'button' => 'Herstel boom',
    ],
    'optimize' => [
        'title' => 'Database optimaliseren',
        'description' => 'Als u vertragingen in uw installatie opmerkt, kan dit komen doordat uw database niet alle benodigde indexen heeft.',
        'button' => 'Optimaliseer database',
    ],
    'update' => [
        'title' => 'Updates',
        'check-button' => 'Controleer op updates',
        'update-button' => 'Bijwerken',
        'no-pending-updates' => 'Geen updates in behandeling.',
    ],
    'missing-palettes' => [
        'title' => 'Ontbrekende paletten',
        'description' => '%d ontbrekende paletten gevonden.',
        'button' => 'Ontbrekende aanmaken',
    ],
    'statistics-check' => [
        'title' => 'Controle op statistische integriteit',
        'missing_photos' => '%d fotostatistieken ontbreken.',
        'missing_albums' => '%d albumstatistieken ontbreken.',
        'button' => 'Maak ontbrekende aan',
    ],
    'flush-cache' => [
        'title' => 'Cache legen',
        'description' => 'Leeg de cache van elke gebruiker om invalidatieproblemen op te lossen.',
        'button' => 'Leeg cache',
    ],
    'old-orders' => [
        'title' => 'Oude Orders',
        'description' => '%d oude orders gevonden.<br/><br/>Een oude order is ouder dan 14 dagen, heeft geen gekoppelde gebruiker en is nog in afwachting van betaling of bevat geen items.',
        'button' => 'Verwijder oude orders',
    ],
    'fulfill-orders' => [
        'title' => 'Af te handelen orders',
        'description' => '%d orders gevonden met inhoud die nog niet beschikbaar is gemaakt.<br/><br/>Klik op de knop om waar mogelijk inhoud toe te wijzen.',
        'button' => 'Orders afhandelen',
    ],
    'fulfill-precompute' => [
        'title' => 'Vooraf berekende albumvelden',
        'description' => '%d albums gevonden met ontbrekende vooraf berekende velden.<br/><br/>Gelijk aan het uitvoeren van: php artisan lychee:recompute-album-fields',
        'button' => 'Velden berekenen',
    ],
    'flush-queue' => [
        'title' => 'Wachtrij leegmaken',
        'description' => '%d wachtende taken gevonden in de wachtrij.<br/><br/>LET OP: Het legen van de wachtrij verwijdert alle wachtende taken permanent. Dit kan niet ongedaan worden gemaakt.',
        'button' => 'Wachtrij legen',
    ],
    'backfill-album-sizes' => [
        'title' => 'Albumgroottestatistieken',
        'description' => '%d albums gevonden zonder groottestatistieken.<br/><br/>Gelijk aan het uitvoeren van: php artisan lychee:recompute-album-sizes',
        'button' => 'Groottes berekenen',
    ],

    'face_quality' => [
        'title' => 'Beoordeling gezichtskwaliteit',
        'description' => 'Beoordeel gedetecteerde gezichten op kwaliteitsscore en wijs gezichten van lage kwaliteit of foutieve gezichten af.',
        'sort_by' => 'Sorteren op:',
        'sort_confidence' => 'Betrouwbaarheid',
        'sort_blur' => 'Wazigheid (Laplace)',
        'no_faces' => 'Geen gezichten die hieraan voldoen. Alles ziet er goed uit!',
        'col_face' => 'Gezicht',
        'col_person' => 'Persoon',
        'col_cluster' => 'Cluster',
        'col_confidence' => 'Betrouwbaarheid',
        'col_blur' => 'Wazigheidsscore',
        'col_actions' => 'Acties',
        'unassigned' => 'Niet toegewezen',
        'dismiss' => 'Gezicht afwijzen',
        'readd' => 'Gezicht opnieuw toevoegen',
        'load_error' => 'Laden van gezichten mislukt.',
        'dismissed' => 'Gezicht afgewezen.',
        'readded' => 'Gezicht opnieuw toegevoegd.',
        'dismiss_error' => 'Afwijzen van gezicht mislukt.',
        'readd_error' => 'Opnieuw toevoegen van gezicht mislukt.',
        'batch_dismiss' => 'Selectie afwijzen',
        'batch_dismissed' => ':count gezicht(en) afgewezen.',
        'batch_dismiss_error' => 'Afwijzen van geselecteerde gezichten mislukt.',
        'batch_reactivate' => 'Selectie heractiveren',
        'batch_reactivated' => ':count gezicht(en) gereactiveerd.',
        'batch_reactivate_error' => 'Heractiveren van geselecteerde gezichten mislukt.',
        'show_dismissed' => 'Toon afgewezen',
        'show_active' => 'Toon actief',
        'show_unassigned' => 'Alleen niet-toegewezen',
        'select_all' => 'Alles selecteren',
        'deselect_all' => 'Alles deselecteren',
        'selected_count' => ':count geselecteerd',
    ],
    'bulk-scan-faces' => [
        'description' => '%d foto’s gevonden die nog niet zijn gescand voor gezichtsherkenning.<br/><br/>Vereist dat de AI Vision-service actief is.',
    ],
    'run-clustering' => [
        'description' => 'Start gezichtsclustering in de AI Vision-service. Groepeert gedetecteerde gezichten op gelijkenis zodat u ze aan personen kunt toewijzen.',
        'success' => 'Clustering succesvol gestart.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Afgewezen gezichten vernietigen',
        'description' => '%d afgewezen gezichten gevonden. Het vernietigen ervan verwijdert hun uitsnedebestanden en embeddings permanent.',
        'action' => 'Alles vernietigen',
        'success' => 'Afgewezen gezichten succesvol vernietigd.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Gezichtsembeddings synchroniseren',
        'description' => 'Afwijking in aantal gezichten gedetecteerd (%d verschil). Synchroniseren haalt de meest recente gezichtsgegevens op van de AI Vision-service naar Lychee.',
        'action' => 'Nu synchroniseren',
        'success' => 'Gezichtsembeddings succesvol gesynchroniseerd.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Status van gezichtsscan resetten',
        'description' => '%d foto’s gevonden met een vastgelopen of mislukte gezichtsscanstatus. Door deze te resetten kunnen ze opnieuw worden gescand.',
        'action' => 'Alles resetten',
        'success' => 'Status van gezichtsscans succesvol gereset.',
    ],

        'bulk-scan-nsfw' => [
        'title' => 'Bulk NSFW-scan',
        'description' => 'Scan alle niet-gescande foto’s op NSFW-inhoud met behulp van de geconfigureerde voorinstelling. Vereist dat de NSFW-classificatieservice actief is.',
        'button' => 'Scan alle niet-gescande',
        'success' => 'NSFW-scan succesvol gestart.',
    ],
];
