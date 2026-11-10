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
    'description' => 'Configure webhooks de saída acionados quando fotos são adicionadas, movidas ou eliminadas.',

    // Empty state
    'no_webhooks' => 'Ainda não há webhooks configurados.',
    'create_first' => 'Crie o seu primeiro webhook',

    // Table columns
    'col_name' => 'Nome',
    'col_event' => 'Evento',
    'col_method' => 'Método',
    'col_url' => 'URL',
    'col_format' => 'Formato',
    'col_enabled' => 'Ativo',
    'col_actions' => 'Ações',

    // Event labels
    'event_photo_add' => 'Foto adicionada',
    'event_photo_move' => 'Foto movida',
    'event_photo_delete' => 'Foto eliminada',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query string',

    // Buttons
    'create' => 'Criar webhook',
    'edit' => 'Editar',
    'delete' => 'Eliminar',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',

    // Form fields
    'field_name' => 'Nome',
    'field_name_placeholder' => 'ex.: O meu webhook',
    'field_event' => 'Evento',
    'field_method' => 'Método HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Formato do payload',
    'field_enabled' => 'Ativo',
    'field_secret' => 'Segredo',
    'field_secret_placeholder' => 'Deixe vazio para manter o segredo existente',
    'field_secret_header' => 'Cabeçalho do segredo',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Enviar ID da foto',
    'field_send_album_id' => 'Enviar ID do álbum',
    'field_send_title' => 'Enviar título',
    'field_send_size_variants' => 'Enviar variantes de tamanho',

    // Modal titles
    'modal_create_title' => 'Criar webhook',
    'modal_edit_title' => 'Editar webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Eliminar webhook',
    'confirm_delete_message' => 'Tem a certeza de que pretende eliminar o webhook ":name"? Esta ação não pode ser anulada.',
    'delete_warning' => 'Esta ação não pode ser anulada.',

    // Toasts
    'created' => 'Webhook criado com sucesso.',
    'updated' => 'Webhook atualizado com sucesso.',
    'deleted' => 'Webhook eliminado com sucesso.',
    'error_load' => 'Falha ao carregar os webhooks.',
    'error_save' => 'Falha ao guardar o webhook.',
    'error_delete' => 'Falha ao eliminar o webhook.',

    // Secret badge
    'has_secret' => 'Segredo definido',
    'no_secret' => 'Sem segredo',
];
