<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Configuraciones',
	'small_screen' => 'Para una mejor experiencia en la página de Configuraciones,<br />recomendamos usar una pantalla más grande.',
	'tabs' => [
		'basic' => 'Básico',
		'all_settings' => 'Todas las configuraciones',
	],
	'toasts' => [
		'change_saved' => '¡Cambio guardado!',
		'details' => 'Las configuraciones se han modificado según lo solicitado',
		'error' => '¡Error!',
		'error_load_css' => 'No se pudo cargar dist/user.css',
		'error_load_js' => 'No se pudo cargar dist/custom.js',
		'error_save_css' => 'No se pudo guardar CSS',
		'error_save_js' => 'No se pudo guardar JS',
		'thank_you' => 'Gracias por su apoyo.',
		'reload' => 'Recargue su página para obtener todas las funcionalidades.',
	],
	'system' => [
		'header' => 'Sistema',
		'use_dark_mode' => 'Usar modo oscuro para Lychee',
		'language' => 'Idioma utilizado por Lychee',
		'nsfw_album_visibility' => 'Hacer visibles los álbumes sensibles por defecto.',
		'nsfw_album_explanation' => 'Si el álbum es público, sigue siendo accesible, solo oculto de la vista y <b>se puede revelar presionando <kbd>H</kbd></b>.',
		'cache_enabled' => 'Habilitar almacenamiento en caché de respuestas.',
		'cache_enabled_details' => 'Esto acelerará significativamente el tiempo de respuesta de Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Si está utilizando álbumes protegidos con contraseña, no debería habilitar esto.',
	],
	'lychee_se' => [
		'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
		'call4action' => 'Obtén funciones exclusivas y apoya el desarrollo de Lychee. Desbloquea la <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">edición SE</a>.',
		'preview' => 'Habilitar vista previa de las funciones de Lychee SE',
		'hide_call4action' => 'Ocultar este formulario de registro de Lychee SE. Estoy feliz con Lychee tal como está. :)',
		'hide_warning' => 'Si se habilita, la única forma de registrar su clave de licencia será a través de la pestaña Más arriba. Los cambios se aplican al recargar la página.',
	],
	'dropbox' => [
		'header' => 'Dropbox',
		'instruction' => 'Para importar fotos de tu Dropbox, necesitas una clave de aplicación drop-ins válida de su sitio web.',
		'api_key' => 'Clave de API de Dropbox',
		'set_key' => 'Establecer clave de Dropbox',
	],
	'gallery' => [
		'header' => 'Galería',
		'photo_order_column' => 'Columna predeterminada utilizada para ordenar fotos',
		'photo_order_direction' => 'Orden predeterminado utilizado para ordenar fotos',
		'album_order_column' => 'Columna predeterminada utilizada para ordenar álbumes',
		'album_order_direction' => 'Orden predeterminado utilizado para ordenar álbumes',
		'aspect_ratio' => 'Relación de aspecto predeterminada para miniaturas de álbumes',
		'photo_layout' => 'Diseño para fotos',
		'album_decoration' => 'Mostrar decoraciones en la portada del álbum (subálbum y/o recuento de fotos)',
		'album_decoration_direction' => 'Alinear decoraciones de álbumes horizontal o verticalmente',
		'photo_overlay' => 'Información de superposición de imagen predeterminada',
		'license_default' => 'Licencia predeterminada utilizada para álbumes',
		'license_help' => '¿Necesitas ayuda para elegir?',
	],
	'geolocation' => [
		'header' => 'Geo-localización',
		'map_display' => 'Mostrar el mapa dado las coordenadas GPS',
		'map_display_public' => 'Permitir a usuarios anónimos acceder al mapa',
		'map_provider' => 'Define el proveedor del mapa',
		'map_include_subalbums' => 'Incluye fotos de los subálbumes en el mapa',
		'location_decoding' => 'Usar decodificación de ubicación GPS',
		'location_show' => 'Mostrar ubicación extraída de las coordenadas GPS',
		'location_show_public' => 'Los usuarios anónimos pueden acceder a la ubicación extraída de las coordenadas GPS',
	],
	'cssjs' => [
		'header' => 'CSS y Js Personalizados',
		'change_css' => 'Cambiar CSS',
		'change_js' => 'Cambiar JS',
	],
	'all' => [
		'old_setting_style' => 'Estilo de configuraciones antiguo',
		'expert_settings' => 'Modo Experto',
		'change_detected' => 'Algunas configuraciones han cambiado.',
		'save' => 'Guardar',
		'back_to_settings' => 'Volver a configuraciones agrupadas',
	],

	'tool_option' => [
		'disabled' => 'deshabilitado',
		'enabled' => 'habilitado',
		'discover' => 'descubrir',
	],

	'groups' => [
		'general' => 'General',
		'system' => 'Sistema',
		'modules' => 'Módulos',
		'advanced' => 'Avanzado',
	],
];