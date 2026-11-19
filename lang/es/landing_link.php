<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'Enlaces adicionales',
    'description' => 'Gestiona una lista ordenada de enlaces adicionales que se muestran en la navegación y/o el pie de página de la página de inicio.',

    // Empty state
    'no_links' => 'Todavía no hay enlaces adicionales configurados.',
    'create_first' => 'Crea tu primer enlace',

    // Table columns
    'col_label' => 'Etiqueta',
    'col_url' => 'URL',
    'col_placement' => 'Ubicación',
    'col_enabled' => 'Activado',
    'col_actions' => 'Acciones',

    // Placement labels
    'placement_nav' => 'Nav',
    'placement_footer' => 'Pie de página',
    'placement_both' => 'Nav y pie de página',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Integrado',
    'built_in_cannot_delete' => 'Este enlace integrado no se puede eliminar. Desactívalo o reordénalo como cualquier otro enlace.',
    'built_in_url_hint' => 'Este es un enlace integrado y su destino no se puede cambiar.',

    // Buttons
    'create' => 'Crear enlace',
    'edit' => 'Editar',
    'delete' => 'Eliminar',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',

    // Form fields
    'field_label' => 'Etiqueta',
    'field_label_placeholder' => 'p. ej., Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Ubicación',
    'field_open_in_new_tab' => 'Abrir en una pestaña nueva',
    'field_enabled' => 'Activado',

    // Modal titles
    'modal_create_title' => 'Crear enlace',
    'modal_edit_title' => 'Editar enlace',

    // Delete confirmation
    'confirm_delete_header' => 'Eliminar enlace',
    'confirm_delete_message' => '¿Seguro que quieres eliminar el enlace ":label"? Esta acción no se puede deshacer.',

    // Toasts
    'created' => 'Enlace creado correctamente.',
    'updated' => 'Enlace actualizado correctamente.',
    'deleted' => 'Enlace eliminado correctamente.',
    'reordered' => 'Enlaces reordenados correctamente.',
    'error_load' => 'No se pudieron cargar los enlaces.',
    'error_save' => 'No se pudo guardar el enlace.',
    'error_delete' => 'No se pudo eliminar el enlace.',
    'error_reorder' => 'No se pudieron reordenar los enlaces.',
];
