<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Mantenimiento',
    'description' => 'You will find on this page, all the required actions to keep your Lychee installation running smooth and nicely.',
    'cleaning' => [
        'title' => 'Limpieza %s',
        'result' => '%s eliminado.',
        'description' => 'Eliminar todo el contenido de <span class="font-mono">%s</span>',
        'button' => 'Limpio',
    ],
    'duplicate-finder' => [
        'title' => 'Duplicados',
        'description' => 'Este módulo cuenta los posibles duplicados entre imágenes.',
        'duplicates-all' => 'Duplicados en todos los álbumes',
        'duplicates-title' => 'Títulos duplicados por álbum',
        'duplicates-per-album' => 'Duplicados por álbum',
        'show' => 'Mostrar duplicados',
    ],
    'fix-jobs' => [
        'title' => 'Reparación del historial de trabajos',
        'description' => 'Marcar trabajos con estado <span class="text-ready-400">%s</span> o <span class="text-primary-500">%s</span> como <span class="text-danger-700">%s</span>.',
        'button' => 'Corregir historial laboral',
    ],
    'gen-sizevariants' => [
        'title' => 'Falta %s',
        'description' => 'Se han encontrado %d %s que podrían generarse.',
        'button' => '¡Generar!',
        'success' => 'Se generó exitosamente %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Tamaños de archivos faltantes',
        'description' => 'Se encontraron %d variantes pequeñas sin tamaño de archivo.',
        'button' => '¡Obtener datos!',
        'success' => 'Se calcularon correctamente los tamaños de %d variantes pequeñas.',
    ],
    'fix-tree' => [
        'title' => 'Estadísticas de árboles',
        'Oddness' => 'Rareza',
        'Duplicates' => 'Duplicados',
        'Wrong parents' => 'Padres equivocados',
        'Missing parents' => 'Padres desaparecidos',
        'button' => 'Arreglar árbol',
    ],
    'optimize' => [
        'title' => 'Optimizar la base de datos',
        'description' => 'Si nota una lentitud en la instalación, puede deberse a que su base de datos no tiene todo el índice necesario.',
        'button' => 'Optimizar la base de datos',
    ],
    'update' => [
        'title' => 'Actualizaciones',
        'check-button' => 'Buscar actualizaciones',
        'update-button' => 'Actualizar',
        'no-pending-updates' => 'No hay actualizaciones pendientes.',
    ],
    'missing-palettes' => [
        'title' => 'Paletas faltantes',
        'description' => 'Se encontraron %d paletas faltantes.',
        'button' => 'Crear faltantes',
    ],
    'statistics-check' => [
        'title' => 'Comprobación de integridad de las estadísticas',
        'missing_photos' => 'Faltan %d estadísticas de fotos.',
        'missing_albums' => 'Faltan %d estadísticas del álbum.',
        'button' => 'Crear faltantes',
    ],
    'flush-cache' => [
        'title' => 'Vaciar caché',
        'description' => 'Limpiar la caché de cada usuario para solucionar problemas de invalidación.',
        'button' => 'Vaciar',
    ],
    'old-orders' => [
        'title' => 'Old Orders',
        'description' => 'Found %d old orders.<br/><br/>An old order is older than 14 days, that have no associated user and are either still pending payment or have no items in them.',
        'button' => 'Delete old orders',
    ],
    'fulfill-orders' => [
        'title' => 'Orders to fulfill',
        'description' => 'Found %d orders with content that has not been made available.<br/><br/>Click on the button to assign content when possible.',
        'button' => 'Fulfill orders',
    ],
    'fulfill-precompute' => [
        'title' => 'Album Precomputed Fields',
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:backfill-album-fields',
        'button' => 'Compute fields',
    ],
];
