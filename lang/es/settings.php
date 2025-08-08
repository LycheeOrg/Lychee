<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Ajustes',
    'small_screen' => 'Para disfrutar de una mejor experiencia en la página de configuración,<br />te recomendamos que utilices una pantalla más grande.',
    'tabs' => [
        'basic' => 'Básico',
        'all_settings' => 'Todos los ajustes',
    ],
    'toasts' => [
        'change_saved' => '¡Cambio guardado!',
        'details' => 'Se han modificado los ajustes según lo solicitado.',
        'error' => 'Error!',
        'error_load_css' => 'No se pudo cargar dist/user.css',
        'error_load_js' => 'No se pudo cargar dist/custom.js',
        'error_save_css' => 'No se pudo guardar el CSS',
        'error_save_js' => 'No se pudo guardar JS',
        'thank_you' => 'Gracias por su apoyo.',
        'reload' => 'Actualice la página para disfrutar de todas las funciones.',
    ],
    'system' => [
        'header' => 'Sistema',
        'use_dark_mode' => 'Utiliza el modo oscuro para Lychee.',
        'language' => 'Idioma utilizado por Lychee',
        'nsfw_album_visibility' => 'Hacer que los álbumes confidenciales sean visibles de forma predeterminada.',
        'nsfw_album_explanation' => 'Si el álbum es público, aún es accesible, solo que está oculto a la vista y se puede revelar presionando <kbd>H</kbd></b>.',
        'cache_enabled' => 'Habilitar el almacenamiento en caché de respuestas.',
        'cache_enabled_details' => 'Esto acelerará significativamente el tiempo de respuesta de Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Si está utilizando álbumes protegidos con contraseña, no debe habilitar esto.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Obtén funciones exclusivas y apoya el desarrollo de Lychee. Desbloquea la <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">edición SE</a>.',
        'preview' => 'Habilitar la vista previa de las funciones de Lychee SE',
        'hide_call4action' => 'Oculten este formulario de registro de Lychee SE. Estoy contento con Lychee tal como está. :)',
        'hide_warning' => 'Si está habilitado, la única forma de registrar su clave de licencia será a través de la pestaña "Más" (arriba). Los cambios se aplican al recargar la página.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Para importar fotos desde tu Dropbox, necesitas una clave de aplicación Drop-ins válida desde su sitio web.',
        'api_key' => 'Clave API de Dropbox',
        'set_key' => 'Establecer la clave de Dropbox',
    ],
    'gallery' => [
        'header' => 'Galería',
        'photo_order_column' => 'Columna predeterminada utilizada para ordenar fotos',
        'photo_order_direction' => 'Orden predeterminado utilizado para ordenar las fotos',
        'album_order_column' => 'Columna predeterminada utilizada para ordenar álbumes',
        'album_order_direction' => 'Orden predeterminado utilizado para ordenar álbumes',
        'aspect_ratio' => 'Relación de aspecto predeterminada para las miniaturas de álbumes',
        'photo_layout' => 'Diseño para imágenes',
        'album_decoration' => 'Mostrar decoraciones en la portada del álbum (subálbum y/o recuento de fotos)',
        'album_decoration_direction' => 'Alinear las decoraciones del álbum horizontal o verticalmente',
        'photo_overlay' => 'Información de superposición de imágenes predeterminada',
        'license_default' => 'Licencia predeterminada utilizada para álbumes',
        'license_help' => '¿Necesitas ayuda para elegir?',
    ],
    'geolocation' => [
        'header' => 'Geolocalización',
        'map_display' => 'Mostrar el mapa con las coordenadas GPS dadas',
        'map_display_public' => 'Permitir que usuarios anónimos accedan al mapa',
        'map_provider' => 'Define el proveedor de mapas',
        'map_include_subalbums' => 'Incluye imágenes de los subálbumes en el mapa.',
        'location_decoding' => 'Utilice la decodificación de ubicación GPS',
        'location_show' => 'Mostrar ubicación extraída de coordenadas GPS',
        'location_show_public' => 'Los usuarios anónimos pueden acceder a la ubicación extraída de las coordenadas GPS',
    ],
    'cssjs' => [
        'header' => 'CSS y JavaScript personalizados',
        'change_css' => 'Cambiar CSS',
        'change_js' => 'Cambiar JS',
    ],
    'all' => [
        'old_setting_style' => 'Estilo de configuración antiguo',
        'expert_settings' => 'Modo experto',
        'change_detected' => 'Se cambiaron algunas configuraciones.',
        'save' => 'Guardar',
        'back_to_settings' => 'Volver a la configuración agrupada',
    ],
    'tool_option' => [
        'disabled' => 'desactivado',
        'enabled' => 'activado',
        'discover' => 'descubrir',
    ],
    'groups' => [
        'general' => 'General',
        'system' => 'Sistema',
        'modules' => 'Módulos',
        'advanced' => 'Avanzado',
    ],
];
