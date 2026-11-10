<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Configura webhooks salientes que se activan cuando se añaden, mueven o borran fotos.',

    // Empty state
    'no_webhooks' => 'Aún no hay webhooks configurados.',
    'create_first' => 'Crea tu primer webhook',

    // Table columns
    'col_name' => 'Nombre',
    'col_event' => 'Evento',
    'col_method' => 'Método',
    'col_url' => 'URL',
    'col_format' => 'Formato',
    'col_enabled' => 'Activado',
    'col_actions' => 'Acciones',

    // Event labels
    'event_photo_add' => 'Foto añadida',
    'event_photo_move' => 'Foto movida',
    'event_photo_delete' => 'Foto borrada',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query string',

    // Buttons
    'create' => 'Crear webhook',
    'edit' => 'Editar',
    'delete' => 'Borrar',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',

    // Form fields
    'field_name' => 'Nombre',
    'field_name_placeholder' => 'p. ej. Mi webhook',
    'field_event' => 'Evento',
    'field_method' => 'Método HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Formato de la carga útil',
    'field_enabled' => 'Activado',
    'field_secret' => 'Secreto',
    'field_secret_placeholder' => 'Déjalo vacío para conservar el secreto actual',
    'field_secret_header' => 'Cabecera del secreto',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Enviar ID de la foto',
    'field_send_album_id' => 'Enviar ID del álbum',
    'field_send_title' => 'Enviar título',
    'field_send_size_variants' => 'Enviar variantes de tamaño',

    // Modal titles
    'modal_create_title' => 'Crear webhook',
    'modal_edit_title' => 'Editar webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Borrar webhook',
    'confirm_delete_message' => '¿Estás seguro de que quieres borrar el webhook ":name"? Esta acción no se puede deshacer.',
    'delete_warning' => 'Esta acción no se puede deshacer.',

    // Toasts
    'created' => 'Webhook creado correctamente.',
    'updated' => 'Webhook actualizado correctamente.',
    'deleted' => 'Webhook borrado correctamente.',
    'error_load' => 'No se han podido cargar los webhooks.',
    'error_save' => 'No se ha podido guardar el webhook.',
    'error_delete' => 'No se ha podido borrar el webhook.',

    // Secret badge
    'has_secret' => 'Secreto definido',
    'no_secret' => 'Sin secreto',
];
