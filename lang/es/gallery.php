<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Jobs page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Galería',

	'smart_albums' => 'Álbumes inteligentes',
	'albums' => 'Álbumes',
	'root' => 'Álbumes',
	'favourites' => 'Favoritos',

	'original' => 'Original',
	'medium' => 'Mediano',
	'medium_hidpi' => 'Mediano HiDPI',
	'small' => 'Miniatura',
	'small_hidpi' => 'Miniatura HiDPI',
	'thumb' => 'Miniatura cuadrada',
	'thumb_hidpi' => 'Miniatura cuadrada HiDPI',
	'placeholder' => 'Marcador de imagen de baja calidad',
	'thumbnail' => 'Miniatura de foto',
	'live_video' => 'Parte de video de foto en vivo',

	'camera_data' => 'Fecha de la cámara',
	'album_reserved' => 'Todos los derechos reservados',

	'map' => [
		'error_gpx' => 'Error al cargar el archivo GPX',
		'osm_contributors' => 'Contribuidores de OpenStreetMap',
	],

	'search' => [
		'title' => 'Buscar',
		'no_results' => 'Nada coincide con su consulta de búsqueda.',
		'searchbox' => 'Buscar…',
		'minimum_chars' => 'Se requieren al menos %s caracteres.',
		'photos' => 'Fotos (%s)',
		'albums' => 'Álbumes (%s)',
	],

	'smart_album' => [
		'unsorted' => 'Sin ordenar',
		'starred' => 'Destacados',
		'recent' => 'Recientes',
		'public' => 'Público',
		'on_this_day' => 'En este día',
	],

	'layout' => [
		'squares' => 'Miniaturas cuadradas',
		'justified' => 'Con aspecto, justificado',
		'masonry' => 'Con aspecto, mampostería',
		'grid' => 'Con aspecto, cuadrícula',
	],

	'overlay' => [
		'none' => 'Ninguno',
		'exif' => 'Datos EXIF',
		'description' => 'Descripción',
		'date' => 'Fecha de toma',
	],

	'timeline' => [
		'title' => 'Línea de tiempo',
		'load_previous' => 'Cargar anterior',
		'default' => 'predeterminado',
		'disabled' => 'deshabilitado',
		'year' => 'Año',
		'month' => 'Mes',
		'day' => 'Día',
		'hour' => 'Hora',
	],

	'album' => [
		'header_albums' => 'Álbumes',
		'header_photos' => 'Fotos',
		'no_results' => 'Nada que ver aquí',
		'upload' => 'Subir fotos',

		'tabs' => [
			'about' => 'Acerca del álbum',
			'share' => 'Compartir álbum',
			'move' => 'Mover álbum',
			'danger' => 'ZONA DE PELIGRO',
		],

		'hero' => [
			'created' => 'Creado',
			'copyright' => 'Derechos de autor',
			'subalbums' => 'Subálbumes',
			'images' => 'Fotos',
			'download' => 'Descargar álbum',
			'share' => 'Compartir álbum',
			'stats_only_se' => 'Estadísticas disponibles en la Edición Soporte',
		],

		'stats' => [
			'number_of_visits' => 'Número de visitas',
			'number_of_downloads' => 'Número de descargas',
			'number_of_shares' => 'Número de compartidos',
			'lens' => 'Lente',
			'shutter' => 'Velocidad de obturación',
			'iso' => 'ISO',
			'model' => 'Modelo',
			'aperture' => 'Apertura',
			'no_data' => 'Sin datos',
		],

		'properties' => [
			'title' => 'Título',
			'description' => 'Descripción',
			'photo_ordering' => 'Ordenar fotos por',
			'children_ordering' => 'Ordenar álbumes por',
			'asc/desc' => 'asc/desc',
			'header' => 'Establecer encabezado de álbum',
			'compact_header' => 'Usar encabezado compacto',
			'license' => 'Establecer licencia',
			'copyright' => 'Establecer derechos de autor',
			'aspect_ratio' => 'Establecer relación de aspecto de miniaturas del álbum',
			'album_timeline' => 'Establecer modo de línea de tiempo del álbum',
			'photo_timeline' => 'Establecer modo de línea de tiempo de fotos',
			'layout' => 'Establecer diseño de fotos',
			'show_tags' => 'Establecer etiquetas a mostrar',
			'tags_required' => 'Se requieren etiquetas.',
		],
	],

	'photo' => [
		'actions' => [
			'star' => 'Destacar',
			'unstar' => 'Quitar destacado',
			'set_album_header' => 'Establecer como encabezado de álbum',
			'move' => 'Mover',
			'delete' => 'Eliminar',
			'header_set' => 'Encabezado establecido',
		],

		'details' => [
			'exif_data' => 'Datos EXIF',
			'about' => 'Acerca de',
			'basics' => 'Básicos',
			'title' => 'Título',
			'uploaded' => 'Subido',
			'description' => 'Descripción',
			'license' => 'Licencia',
			'reuse' => 'Reutilizar',
			'latitude' => 'Latitud',
			'longitude' => 'Longitud',
			'altitude' => 'Altitud',
			'location' => 'Ubicación',
			'image' => 'Imagen',
			'video' => 'Video',
			'size' => 'Tamaño',
			'format' => 'Formato',
			'resolution' => 'Resolución',
			'duration' => 'Duración',
			'fps' => 'Tasa de cuadros',
			'tags' => 'Etiquetas',
			'camera' => 'Cámara',
			'captured' => 'Capturado',
			'make' => 'Marca',
			'type' => 'Tipo/Modelo',
			'lens' => 'Lente',
			'shutter' => 'Velocidad de obturación',
			'aperture' => 'Apertura',
			'focal' => 'Longitud focal',
			'iso' => 'ISO %s',
			'stats' => [
				'header' => 'Estadísticas',
				'number_of_visits' => 'Número de visitas',
				'number_of_downloads' => 'Número de descargas',
				'number_of_shares' => 'Número de compartidos',
				'number_of_favourites' => 'Número de favoritos',
			],
			'links' => [
				'header' => 'Links',
				'copy' => 'Copy',
				'copy_success' => 'Link copied to clipboard.',
			],
		],

		'edit' => [
			'set_title' => 'Establecer título',
			'set_description' => 'Establecer descripción',
			'set_license' => 'Establecer licencia',
			'no_tags' => 'Sin etiquetas',
			'set_tags' => 'Establecer etiquetas',
			'set_created_at' => 'Establecer fecha de subida',
			'set_taken_at' => 'Establecer fecha de toma',
			'set_taken_at_info' => 'Cuando se establece, se mostrará una estrella %s para indicar que esta fecha no es la fecha original de EXIF.<br>Desmarque la casilla y guarde para restablecer a la fecha original.',
		],
	],

	'nsfw' => [
		'header' => 'Contenido sensible',
		'description' => 'Este álbum contiene contenido sensible que algunas personas pueden encontrar ofensivo o perturbador.',
		'consent' => 'Toca para consentir.',
	],

	'menus' => [
		'star' => 'Destacar',
		'unstar' => 'Quitar destacado',
		'star_all' => 'Destacar seleccionados',
		'unstar_all' => 'Quitar destacado seleccionados',
		'tag' => 'Etiquetar',
		'tag_all' => 'Etiquetar seleccionados',
		'set_cover' => 'Establecer portada del álbum',
		'remove_header' => 'Eliminar encabezado del álbum',
		'set_header' => 'Establecer encabezado del álbum',
		'copy_to' => 'Copiar a …',
		'copy_all_to' => 'Copiar seleccionados a …',
		'rename' => 'Renombrar',
		'move' => 'Mover',
		'move_all' => 'Mover seleccionados',
		'delete' => 'Eliminar',
		'delete_all' => 'Eliminar seleccionados',
		'download' => 'Descargar',
		'download_all' => 'Descargar seleccionados',
		'merge' => 'Fusionar',
		'merge_all' => 'Fusionar seleccionados',

		'upload_photo' => 'Subir foto',
		'import_link' => 'Importar desde enlace',
		'import_dropbox' => 'Importar desde Dropbox',
		'new_album' => 'Nuevo álbum',
		'new_tag_album' => 'Nuevo álbum de etiquetas',
		'upload_track' => 'Subir pista',
		'delete_track' => 'Eliminar pista',
	],

	'sort' => [
		'photo_select_1' => 'Hora de subida',
		'photo_select_2' => 'Fecha de toma',
		'photo_select_3' => 'Título',
		'photo_select_4' => 'Descripción',
		'photo_select_6' => 'Destacar',
		'photo_select_7' => 'Formato de foto',
		'ascending' => 'Ascendente',
		'descending' => 'Descendente',
		'album_select_1' => 'Hora de creación',
		'album_select_2' => 'Título',
		'album_select_3' => 'Descripción',
		'album_select_5' => 'Última fecha de toma',
		'album_select_6' => 'Primera fecha de toma',
	],

	'albums_protection' => [
		'private' => 'privado',
		'public' => 'público',
		'inherit_from_parent' => 'heredar de padre',
	],
];