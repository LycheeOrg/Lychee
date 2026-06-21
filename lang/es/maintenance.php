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
        'load' => 'Recuento de cargas',
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
        'title' => 'Antiguas órdenes',
        'description' => 'Se han encontrado %d pedidos antiguos.<br/><br/>Un pedido antiguo es aquel que tiene más de 14 días de antigüedad, no tiene ningún usuario asociado y o bien sigue pendiente de pago o bien no contiene ningún artículo.',
        'button' => 'Eliminar pedidos antiguos',
    ],
    'fulfill-orders' => [
        'title' => 'Pedidos pendientes de tramitar',
        'description' => 'Se han encontrado %d pedidos con contenido que aún no está disponible.<br/><br/>Haz clic en el botón para asignar el contenido cuando sea posible.',
        'button' => 'Tramitar pedidos',
    ],
    'fulfill-precompute' => [
        'title' => 'Campos precalculados del álbum',
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-fields',
        'button' => 'Cálculo de campos',
    ],
    'flush-queue' => [
        'title' => 'Vaciar la cola',
        'description' => 'Se han encontrado %d trabajos pendientes en la cola.<br/><br/>PRECAUCIÓN: Al vaciar la cola se eliminarán de forma permanente todos los trabajos pendientes. Esta acción no se puede deshacer.',
        'button' => 'Borrar cola',
    ],
    'backfill-album-sizes' => [
        'title' => 'Estadísticas sobre el tamaño de los álbumes',
        'description' => 'Found %d albums without size statistics.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-sizes',
        'button' => 'Tamaños de los servidores',
    ],

    'face_quality' => [
        'title' => 'Face Quality Review',
        'description' => 'Review face detections by quality score and dismiss low-quality or erroneous faces.',
        'sort_by' => 'Sort by:',
        'sort_confidence' => 'Confidence',
        'sort_blur' => 'Blur (Laplacian)',
        'no_faces' => 'No qualifying faces. Everything looks good!',
        'col_face' => 'Face',
        'col_person' => 'Person',
        'col_cluster' => 'Cluster',
        'col_confidence' => 'Confidence',
        'col_blur' => 'Blur Score',
        'col_actions' => 'Actions',
        'unassigned' => 'Unassigned',
        'dismiss' => 'Dismiss face',
        'readd' => 'Re-add face',
        'load_error' => 'Failed to load faces.',
        'dismissed' => 'Face dismissed.',
        'readded' => 'Face re-added.',
        'dismiss_error' => 'Failed to dismiss face.',
        'readd_error' => 'Failed to re-add face.',
        'batch_dismiss' => 'Dismiss selected',
        'batch_dismissed' => ':count face(s) dismissed.',
        'batch_dismiss_error' => 'Failed to dismiss selected faces.',
        'batch_reactivate' => 'Reactivate selected',
        'batch_reactivated' => ':count face(s) reactivated.',
        'batch_reactivate_error' => 'Failed to reactivate selected faces.',
        'show_dismissed' => 'Show dismissed',
        'show_active' => 'Show active',
        'select_all' => 'Select all',
        'deselect_all' => 'Deselect all',
        'selected_count' => ':count selected',
    ],
    'bulk-scan-faces' => [
        'description' => 'Found %d photos that have not yet been scanned for facial recognition.<br/><br/>Requires the AI Vision service to be running.',
    ],
    'run-clustering' => [
        'description' => 'Trigger face clustering in the AI Vision service. Groups detected faces by similarity so you can assign them to people.',
        'success' => 'Clustering started successfully.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Destroy Dismissed Faces',
        'description' => 'Found %d dismissed faces. Destroying them will permanently delete their crop files and embeddings.',
        'action' => 'Destroy All',
        'success' => 'Dismissed faces destroyed successfully.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Sync Face Embeddings',
        'description' => 'Face count mismatch detected (%d difference). Syncing will pull latest face data from AI Vision service to Lychee.',
        'action' => 'Sync Now',
        'success' => 'Face embeddings synchronized successfully.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Reset Face Scan Status',
        'description' => 'Found %d photos with a stuck-pending or failed face scan status. Resetting them will allow them to be re-scanned.',
        'action' => 'Reset All',
        'success' => 'Face scan statuses reset successfully.',
    ],

    ];
