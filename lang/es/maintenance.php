<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Mantenimiento',
	'description' => 'En esta página encontrará todas las acciones necesarias para mantener su instalación de Lychee funcionando de manera fluida y correcta.',
	'cleaning' => [
		'title' => 'Limpiando %s',
		'result' => '%s eliminado.',
		'description' => 'Eliminar todo el contenido de <span class="font-mono">%s</span>',
		'button' => 'Limpiar',
	],
	'duplicate-finder' => [
		'title' => 'Duplicados',
		'description' => 'Este módulo cuenta posibles duplicados entre imágenes.',
		'duplicates-all' => 'Duplicados en todos los álbumes',
		'duplicates-title' => 'Duplicados de título por álbum',
		'duplicates-per-album' => 'Duplicados por álbum',
		'show' => 'Mostrar duplicados',
	],
	'fix-jobs' => [
		'title' => 'Reparar historial de trabajos',
		'description' => 'Marcar trabajos con estado <span class="text-ready-400">%s</span> o <span class="text-primary-500">%s</span> como <span class="text-danger-700">%s</span>.',
		'button' => 'Reparar historial de trabajos',
	],
	'gen-sizevariants' => [
		'title' => 'Faltan %s',
		'description' => 'Se encontraron %d %s que podrían generarse.',
		'button' => '¡Generar!',
		'success' => 'Se generaron con éxito %d %s.',
	],
	'fill-filesize-sizevariants' => [
		'title' => 'Tamaños de archivo faltantes',
		'description' => 'Se encontraron %d variantes pequeñas sin tamaño de archivo.',
		'button' => '¡Obtener datos!',
		'success' => 'Se calcularon con éxito los tamaños de %d variantes pequeñas.',
	],
	'fix-tree' => [
		'title' => 'Estadísticas del árbol',
		'Oddness' => 'Anomalías',
		'Duplicates' => 'Duplicados',
		'Wrong parents' => 'Padres incorrectos',
		'Missing parents' => 'Padres faltantes',
		'button' => 'Reparar árbol',
	],
	'optimize' => [
		'title' => 'Optimizar base de datos',
		'description' => 'Si nota lentitud en su instalación, puede deberse a que su base de datos no tiene todos los índices necesarios.',
		'button' => 'Optimizar base de datos',
	],
	'update' => [
		'title' => 'Actualizaciones',
		'check-button' => 'Buscar actualizaciones',
		'update-button' => 'Actualizar',
		'no-pending-updates' => 'No hay actualizaciones pendientes.',
	],
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'Comprobación de integridad de estadísticas',
        'missing_photos' => '%d estadísticas de fotos faltantes.',
        'missing_albums' => '%d estadísticas de álbumes faltantes.',
        'button' => 'Crear faltantes',
    ],
	'flush-cache' => [
		'title' => 'Vaciar caché',
		'description' => 'Vaciar la caché de todos los usuarios para resolver problemas de invalidación.',
		'button' => 'Vaciar',
	],
];