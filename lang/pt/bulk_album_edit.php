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

    'title' => 'Edição de álbuns em massa',
    'description' => 'Edite os metadados e as definições de visibilidade de vários álbuns de uma só vez.',
    'warning' => 'As alterações feitas aqui são aplicadas imediatamente e não podem ser anuladas. Os álbuns de etiquetas não são apresentados.',

    // Table columns
    'col_title' => 'Título',
    'col_owner' => 'Proprietário',
    'col_license' => 'Licença',
    'col_is_nsfw' => 'Sensível',
    'col_is_public' => 'Público',
    'col_is_link_required' => 'Ligação',
    'col_grants_full_photo_access' => 'Foto completa',
    'col_grants_download' => 'Transferir',
    'col_grants_upload' => 'Carregar',
    'col_photo_sorting' => 'Ordenação de fotos',
    'col_album_sorting' => 'Ordenação de álbuns',
    'col_created_at' => 'Criado',

    // Filter
    'filter_placeholder' => 'Pesquisar por título...',

    // Pagination
    'per_page' => 'Por página',
    'total_selected' => ':n álbum selecionado|:n álbuns selecionados',
    'select_all_page' => 'Selecionar todos nesta página',
    'select_all_matching' => 'Selecionar todos os correspondentes',
    'cap_warning' => 'Apenas os primeiros 1.000 álbuns foram selecionados.',

    // Mode toggle
    'mode_paginated' => 'Paginado',
    'mode_infinite' => 'Deslocamento infinito',

    // Action buttons
    'action_delete' => 'Eliminar',
    'action_set_owner' => 'Definir proprietário',
    'action_edit_fields' => 'Editar campos',

    // Edit Fields modal
    'edit_fields_title' => 'Editar campos',
    'edit_fields_description' => 'Apenas os campos assinalados serão atualizados. Valores vazios limpam o campo.',
    'section_metadata' => 'Metadados',
    'section_visibility' => 'Visibilidade',
    'field_description' => 'Descrição',
    'field_copyright' => 'Direitos de autor',
    'field_license' => 'Licença',
    'field_photo_layout' => 'Disposição das fotos',
    'field_photo_sorting_col' => 'Coluna de ordenação das fotos',
    'field_photo_sorting_order' => 'Ordem de ordenação das fotos',
    'field_album_sorting_col' => 'Coluna de ordenação dos álbuns',
    'field_album_sorting_order' => 'Ordem de ordenação dos álbuns',
    'field_album_thumb_aspect_ratio' => 'Proporção da miniatura',
    'field_album_timeline' => 'Cronologia dos álbuns',
    'field_photo_timeline' => 'Cronologia das fotos',
    'field_published_at' => 'Data de publicação no Flow',
    'field_is_nsfw' => 'Sensível',
    'field_is_public' => 'Público',
    'field_is_link_required' => 'Ligação obrigatória',
    'field_grants_full_photo_access' => 'Acesso à foto completa',
    'field_grants_download' => 'Transferir',
    'field_grants_upload' => 'Carregar (SE)',
    'apply' => 'Aplicar',
    'cancel' => 'Cancelar',

    // Set Owner modal
    'set_owner_title' => 'Definir proprietário',
    'set_owner_description' => 'Todos os álbuns selecionados serão movidos para o nível raiz e os seus descendentes também serão transferidos.',
    'set_owner_select_user' => 'Selecionar novo proprietário',
    'transfer' => 'Transferir',

    // Delete confirmation modal
    'delete_title' => 'Eliminar álbuns',
    'delete_confirm' => 'Está prestes a eliminar permanentemente :count álbum e todos os seus subálbuns e fotos. Esta ação não pode ser anulada.|Está prestes a eliminar permanentemente :count álbuns e todos os seus subálbuns e fotos. Esta ação não pode ser anulada.',
    'confirm_delete' => 'Confirmar eliminação',

    // Toasts
    'success_patch' => 'Álbuns atualizados com sucesso.',
    'success_set_owner' => 'Propriedade transferida com sucesso.',
    'success_delete' => 'Álbuns eliminados com sucesso.',
    'error_load' => 'Falha ao carregar os álbuns.',
    'error_load_ids' => 'Falha ao carregar os IDs dos álbuns.',
    'error_patch' => 'Falha ao atualizar os álbuns.',
    'error_missing_timezone' => 'Selecione um fuso horário para a data de publicação.',
    'error_set_owner' => 'Falha ao transferir a propriedade.',
    'error_delete' => 'Falha ao eliminar os álbuns.',
    'error_load_users' => 'Falha ao carregar os utilizadores.',
];
