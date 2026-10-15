<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Vista previa de la marca de agua',
        'se_required' => 'El módulo de marca de agua requiere que Lychee Supporter Edition (SE) o SE Preview esté activado.',

        'section_settings' => 'Ajustes de la marca de agua',
        'section_preview' => 'Vista previa en directo',
        'disclaimer' => 'Esta vista previa da una idea de cómo se verá la marca de agua. El resultado final en tus fotos reales puede variar ligeramente.',

        'watermark_photo_id' => 'ID de la imagen de marca de agua',
        'watermark_photo_id_placeholder' => 'ID de foto de 24 caracteres',
        'watermark_photo_id_hint' => 'ID de la foto de la imagen usada como marca de agua. Abre una foto y copia los últimos 24 caracteres de la URL.',

        'preview_photo_id' => 'ID de la foto de fondo',
        'preview_photo_id_placeholder' => 'ID de foto de 24 caracteres',
        'preview_photo_id_hint' => 'Introduce un ID de foto para usarla como fondo de la vista previa.',

        'size' => 'Tamaño (:value%)',
        'opacity' => 'Opacidad (:value%)',
        'position' => 'Posición',
        'position_options' => [
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

        'section_shift' => 'Desplazamiento',
        'shift_type' => 'Unidad de desplazamiento',
        'shift_type_options' => [
            'relative' => 'Relativo (%)',
            'absolute' => 'Absoluto (px)',
        ],
        'shift_type_hint' => 'Los desplazamientos relativos son un porcentaje del tamaño de la imagen; los absolutos son un número fijo de píxeles.',
        'shift_mode_use_slider' => 'Usar control deslizante',
        'shift_mode_use_classic' => 'Usar entrada numérica',
        'shift_x' => 'Desplazamiento horizontal (:value)',
        'shift_x_direction_options' => [
            'left' => 'Izquierda',
            'right' => 'Derecha',
        ],
        'shift_y' => 'Desplazamiento vertical (:value)',
        'shift_y_direction_options' => [
            'up' => 'Arriba',
            'down' => 'Abajo',
        ],
        'reset_to_zero' => 'Restablecer a 0',

        'save' => 'Guardar ajustes',
        'saved' => 'Ajustes de la marca de agua guardados.',
        'save_error' => 'No se han podido guardar los ajustes de la marca de agua.',
        'save_requires_se' => 'Guardar los ajustes de la marca de agua requiere una licencia completa de Supporter Edition (SE). SE Preview solo permite previsualizar el efecto.',

        'no_watermark_image' => 'No hay ninguna imagen de marca de agua configurada. Introduce un ID de foto de marca de agua y haz clic en "Load" para previsualizarla.',
        'no_preview_photo' => 'Introduce arriba un ID de foto de fondo para previsualizar la superposición de la marca de agua.',
        'photo_load_error' => 'No se ha podido cargar la foto. Asegúrate de que el ID es correcto y de que tienes acceso a ella.',
        'watermark_load_error' => 'No se ha podido cargar la imagen de marca de agua. Asegúrate de que el ID de la foto es correcto.',
    ],
];
