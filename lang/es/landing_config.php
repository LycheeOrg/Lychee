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

    'title' => 'Página de inicio',
    'tab_settings' => 'Ajustes',
    'tab_links' => 'Enlaces',
    'tab_featured' => 'Destacados',

    'section_layout' => 'Diseño y estructura',
    'section_hero' => 'Cabecera',
    'section_background_landscape' => 'Fondo (horizontal)',
    'section_background_portrait' => 'Fondo (vertical)',
    'section_cta_position' => 'Posición de la llamada a la acción',
    'section_login_position' => 'Panel de inicio de sesión',
    'section_meridian' => 'Rieles de Meridian',
    'section_content' => 'Contenido',

    'field_layout' => 'Diseño',
    'field_intro_screen_enabled' => 'Pantalla de introducción',
    'field_hero_text_position' => 'Posición del texto de la cabecera',
    'field_hero_text_color' => 'Color del texto de la cabecera',
    'field_hero_text_opacity' => 'Opacidad del texto de la cabecera',
    'field_animation_preset' => 'Ajuste predefinido de animación',
    'field_backdrop_opacity' => 'Opacidad del velo de fondo (:value %)',
    'field_cta_text' => 'texto',
    'field_cta_text_placeholder' => 'Déjalo vacío para usar el valor predeterminado del diseño',
    'field_about_enabled' => 'Sección «Acerca de»',
    'field_about_text' => 'Texto de «Acerca de»',

    'field_background_mode' => 'Origen',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID de la foto',
        'random' => 'Foto pública aleatoria',
        'latest_album_cover' => 'Portada del último álbum',
        'random_from_album' => 'Foto aleatoria de un álbum',
    ],
    'field_background_url' => 'URL de la imagen',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID de la foto',
    'field_background_photo_id_placeholder' => 'ID de foto de 24 caracteres',
    'field_background_photo_id_hint' => 'ID de la foto de la imagen que se usará. Abre una foto y copia los últimos 24 caracteres de la URL.',
    'field_background_album_id' => 'ID del álbum',
    'field_background_album_id_placeholder' => 'ID de álbum de 24 caracteres',
    'background_load_error' => 'No se pudo cargar la foto. Asegúrate de que el ID es correcto y de que tienes acceso a ella.',
    'background_mode_hint' => [
        'random' => 'Se elige una foto pública aleatoria cada vez que se carga la página.',
        'latest_album_cover' => 'Se usa la portada del álbum público publicado más recientemente.',
        'random_from_album' => 'Se elige una foto aleatoria de este álbum cada vez que se carga la página. La vista previa solo se actualiza después de guardar.',
    ],

    'preview_orientation_landscape' => 'Vista previa del fondo horizontal',
    'preview_orientation_portrait' => 'Vista previa del fondo vertical',

    'field_cta_position' => 'Posición',
    'cta_position_options' => [
        'top-left' => 'Arriba a la izquierda',
        'top' => 'Arriba en el centro',
        'top-right' => 'Arriba a la derecha',
        'left' => 'En medio a la izquierda',
        'center' => 'Centro',
        'right' => 'En medio a la derecha',
        'bottom-left' => 'Abajo a la izquierda',
        'bottom' => 'Abajo en el centro',
        'bottom-right' => 'Abajo a la derecha',
    ],
    'field_cta_shift_type' => 'Unidad de desplazamiento',
    'cta_shift_type_options' => [
        'relative' => 'Relativo (%)',
        'absolute' => 'Absoluto (px)',
    ],
    'cta_shift_type_hint' => 'Los desplazamientos relativos son un porcentaje del tamaño de la ventana gráfica; los absolutos son un número fijo de píxeles.',
    'field_cta_shift_x' => 'Desplazamiento horizontal (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Izquierda',
        'right' => 'Derecha',
    ],
    'field_cta_shift_y' => 'Desplazamiento vertical (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Arriba',
        'down' => 'Abajo',
    ],
    'reset_to_zero' => 'Restablecer a 0',

    'field_login_position' => 'Posición',
    'login_position_options' => [
        'side' => 'Lateral',
        'center' => 'Centro',
    ],
    'field_login_position_hint' => 'En pantallas estrechas, el panel de inicio de sesión siempre se centra debajo de la cabecera, independientemente de este ajuste. Entra deslizándose desde el borde del sentido de lectura cuando el ajuste predefinido de animación es "Slide reveal" o "Parallax scroll".',

    'field_meridian_explore_offset' => 'Altura de la etiqueta «Explorar» (:value %)',
    'field_meridian_contact_offset' => 'Altura de la etiqueta «Contacto» (:value %)',
    'field_meridian_offset_hint' => 'A qué altura de cada riel de altura completa se sitúa su etiqueta: 0 % es la parte superior y 100 % la inferior.',
    'field_meridian_explore_line_position' => 'Posición del riel «Explorar» (:value %)',
    'field_meridian_contact_line_position' => 'Posición del riel «Contacto» (:value %)',
    'field_meridian_line_position_hint' => 'A qué distancia del borde izquierdo se sitúa cada riel: 0 % es el borde izquierdo y 100 % el borde derecho. El riel «Explorar» siempre se centra cuando el riel «Contacto» está desactivado.',

    'preview_title' => 'Vista previa en directo',
    'preview_hint' => 'Se actualiza al instante mientras editas; no se guarda nada hasta que hagas clic en Guardar.',
    'flat_list_hint' => 'Estos ajustes también se pueden editar desde la lista genérica de Ajustes.',

    'save' => 'Guardar',
    'saved' => 'Ajustes de la página de inicio guardados.',
    'save_error' => 'No se pudieron guardar los ajustes de la página de inicio.',

    'se_required' => 'Este diseño/ajuste predefinido requiere Lychee SE.',
];
