<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Edición masiva de álbumes',
    'description' => 'Edita los metadatos y la configuración de visibilidad de varios álbumes a la vez.',
    'warning' => 'Los cambios realizados aquí se aplican de inmediato y no se pueden deshacer. Los álbumes de etiquetas no se muestran.',

    // Table columns
    'col_title' => 'Título',
    'col_owner' => 'Propietario',
    'col_license' => 'Licencia',
    'col_is_nsfw' => 'Sensible',
    'col_is_public' => 'Público',
    'col_is_link_required' => 'Enlace',
    'col_grants_full_photo_access' => 'Foto completa',
    'col_grants_download' => 'Descarga',
    'col_grants_upload' => 'Subida',
    'col_photo_sorting' => 'Orden de fotos',
    'col_album_sorting' => 'Orden de álbumes',
    'col_created_at' => 'Creado',
    'taken_range' => 'Tomadas: :range',
    'taken_none' => 'Sin datos',

    // Filter
    'filter_placeholder' => 'Buscar por título...',

    // Pagination
    'per_page' => 'Por página',
    'total_selected' => ':n álbum seleccionado|:n álbumes seleccionados',
    'select_all_page' => 'Seleccionar todo en esta página',
    'select_all_matching' => 'Seleccionar todas las coincidencias',
    'cap_warning' => 'Solo se han seleccionado los primeros 1.000 álbumes.',

    // Mode toggle
    'mode_paginated' => 'Paginado',
    'mode_infinite' => 'Desplazamiento infinito',

    // Action buttons
    'action_delete' => 'Eliminar',
    'action_set_owner' => 'Establecer propietario',
    'action_edit_fields' => 'Editar campos',

    // Edit Fields modal
    'edit_fields_title' => 'Editar campos',
    'edit_fields_description' => 'Solo se actualizarán los campos marcados. Los valores vacíos borran el campo.',
    'section_metadata' => 'Metadatos',
    'section_visibility' => 'Visibilidad',
    'field_description' => 'Descripción',
    'field_copyright' => 'Copyright',
    'field_license' => 'Licencia',
    'field_photo_layout' => 'Diseño de fotos',
    'field_photo_sorting_col' => 'Columna de orden de fotos',
    'field_photo_sorting_order' => 'Sentido de orden de fotos',
    'field_album_sorting_col' => 'Columna de orden de álbumes',
    'field_album_sorting_order' => 'Sentido de orden de álbumes',
    'field_album_thumb_aspect_ratio' => 'Relación de aspecto de miniaturas',
    'field_album_timeline' => 'Línea de tiempo de álbumes',
    'field_photo_timeline' => 'Línea de tiempo de fotos',
    'field_published_at' => 'Fecha de publicación en Flow',
    'field_is_nsfw' => 'Sensible',
    'field_is_public' => 'Público',
    'field_is_link_required' => 'Enlace requerido',
    'field_grants_full_photo_access' => 'Acceso a foto completa',
    'field_grants_download' => 'Descarga',
    'field_grants_upload' => 'Subida (SE)',
    'apply' => 'Aplicar',
    'cancel' => 'Cancelar',

    // Set Owner modal
    'set_owner_title' => 'Establecer propietario',
    'set_owner_description' => 'Todos los álbumes seleccionados se moverán al nivel raíz y sus descendientes también se transferirán.',
    'set_owner_select_user' => 'Selecciona el nuevo propietario',
    'transfer' => 'Transferir',

    // Delete confirmation modal
    'delete_title' => 'Eliminar álbumes',
    'delete_confirm' => 'Estás a punto de eliminar permanentemente :count álbum y todos sus subálbumes y fotos. Esta acción no se puede deshacer.|Estás a punto de eliminar permanentemente :count álbumes y todos sus subálbumes y fotos. Esta acción no se puede deshacer.',
    'confirm_delete' => 'Confirmar eliminación',

    // Toasts
    'success_patch' => 'Álbumes actualizados correctamente.',
    'success_set_owner' => 'Propiedad transferida correctamente.',
    'success_delete' => 'Álbumes eliminados correctamente.',
    'error_load' => 'No se pudieron cargar los álbumes.',
    'error_load_ids' => 'No se pudieron cargar los ID de los álbumes.',
    'error_patch' => 'No se pudieron actualizar los álbumes.',
    'error_missing_timezone' => 'Selecciona una zona horaria para la fecha de publicación.',
    'error_set_owner' => 'No se pudo transferir la propiedad.',
    'error_delete' => 'No se pudieron eliminar los álbumes.',
    'error_load_users' => 'No se pudieron cargar los usuarios.',
];
