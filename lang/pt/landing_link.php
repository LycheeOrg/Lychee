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

    'title' => 'Ligações extra',
    'description' => 'Gira uma lista ordenada e livre de ligações extra apresentadas na navegação e/ou no rodapé da página inicial.',

    // Empty state
    'no_links' => 'Ainda não há ligações extra configuradas.',
    'create_first' => 'Crie a sua primeira ligação',

    // Table columns
    'col_label' => 'Rótulo',
    'col_url' => 'URL',
    'col_placement' => 'Posicionamento',
    'col_enabled' => 'Ativa',
    'col_actions' => 'Ações',

    // Placement labels
    'placement_nav' => 'Navegação',
    'placement_footer' => 'Rodapé',
    'placement_both' => 'Navegação e rodapé',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Integrada',
    'built_in_cannot_delete' => 'Esta ligação integrada não pode ser eliminada. Em vez disso, desative-a ou reordene-a como qualquer outra ligação.',
    'built_in_url_hint' => 'Esta é uma ligação integrada e o seu destino não pode ser alterado.',

    // Buttons
    'create' => 'Criar ligação',
    'edit' => 'Editar',
    'delete' => 'Eliminar',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',

    // Form fields
    'field_label' => 'Rótulo',
    'field_label_placeholder' => 'ex.: Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Posicionamento',
    'field_open_in_new_tab' => 'Abrir num novo separador',
    'field_enabled' => 'Ativa',

    // Modal titles
    'modal_create_title' => 'Criar ligação',
    'modal_edit_title' => 'Editar ligação',

    // Delete confirmation
    'confirm_delete_header' => 'Eliminar ligação',
    'confirm_delete_message' => 'Tem a certeza de que pretende eliminar a ligação ":label"? Esta ação não pode ser anulada.',

    // Toasts
    'created' => 'Ligação criada com sucesso.',
    'updated' => 'Ligação atualizada com sucesso.',
    'deleted' => 'Ligação eliminada com sucesso.',
    'reordered' => 'Ligações reordenadas com sucesso.',
    'error_load' => 'Falha ao carregar as ligações.',
    'error_save' => 'Falha ao guardar a ligação.',
    'error_delete' => 'Falha ao eliminar a ligação.',
    'error_reorder' => 'Falha ao reordenar as ligações.',
];
