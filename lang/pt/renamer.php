<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Regras de renomeação',

    // Modal titles
    'create_rule' => 'Criar regra de renomeação',
    'edit_rule' => 'Editar regra de renomeação',

    // Form fields
    'rule_name' => 'Nome da regra',
    'description' => 'Descrição',
    'pattern' => 'Padrão',
    'replacement' => 'Substituição',
    'mode' => 'Modo',
    'order' => 'Ordem',
    'enabled' => 'Ativada',
    'photo_rule' => 'Regra aplicada a fotos',
    'album_rule' => 'Regra aplicada a álbuns',

    // Form placeholders and help text
    'description_placeholder' => 'Descrição opcional do que esta regra faz',
    'pattern_help' => 'Padrão a procurar (ex.: IMG_, DSC_)',
    'replacement_help' => 'Texto de substituição (ex.: Foto_, Camara_)',
    'order_help' => 'Os números mais baixos são processados primeiro (1 = prioridade máxima)',
    'enabled_help' => '(Apenas as regras ativadas serão aplicadas durante a renomeação)',

    // Mode options
    'mode_first' => 'Primeira ocorrência',
    'mode_all' => 'Todas as ocorrências',
    'mode_regex' => 'Expressão regular',
    'mode_trim' => 'Remover espaços',
    'mode_strtolower' => 'minúsculas',
    'mode_strtoupper' => 'MAIÚSCULAS',
    'mode_ucwords' => 'Maiúscula Em Cada Palavra',
    'mode_ucfirst' => 'Primeira letra maiúscula',

    'mode_first_description' => 'Substituir apenas a primeira ocorrência',
    'mode_all_description' => 'Substituir todas as ocorrências',
    'mode_regex_description' => 'Usar correspondência por expressão regular',
    'mode_trim_description' => 'Remover espaços no início e no fim',
    'mode_strtolower_description' => 'Converter o texto para minúsculas',
    'mode_strtoupper_description' => 'Converter o texto para MAIÚSCULAS',
    'mode_ucwords_description' => 'Colocar em maiúscula a primeira letra de cada palavra',
    'mode_ucfirst_description' => 'Colocar em maiúscula apenas a primeira letra',

    'regex_help' => 'Use expressões regulares para procurar padrões. Por exemplo, para substituir <code>IMG_1234.jpeg</code> por <code>1234_JPG.jpeg</code>, pode usar <code>/IMG_(\\d+)/</code> como padrão e <code>$1_JPG</code> como substituição. Pode encontrar mais explicações e exemplos nas ligações seguintes.',

    // Buttons
    'cancel' => 'Cancelar',
    'create' => 'Criar',
    'update' => 'Atualizar',
    'create_first_rule' => 'Crie a sua primeira regra',

    // Validation messages
    'rule_name_required' => 'O nome da regra é obrigatório',
    'pattern_required' => 'O padrão é obrigatório',
    'replacement_required' => 'A substituição é obrigatória',
    'mode_required' => 'O modo é obrigatório',
    'order_positive' => 'A ordem tem de ser um número positivo',

    // Success messages
    'rule_created' => 'Regra de renomeação criada com sucesso',
    'rule_updated' => 'Regra de renomeação atualizada com sucesso',
    'rule_deleted' => 'Regra de renomeação eliminada com sucesso',

    // Error messages
    'failed_to_create' => 'Falha ao criar a regra de renomeação',
    'failed_to_update' => 'Falha ao atualizar a regra de renomeação',
    'failed_to_delete' => 'Falha ao eliminar a regra de renomeação',
    'failed_to_load' => 'Falha ao carregar as regras de renomeação',

    // List view
    'rules_count' => ':count regras',
    'no_rules' => 'Nenhuma regra de renomeação encontrada',
    'loading' => 'A carregar as regras de renomeação...',
    'pattern_label' => 'Padrão',
    'replace_with_label' => 'Substituir por',
    'photo' => 'Foto',
    'album' => 'Álbum',

    // Delete confirmation
    'confirm_delete_header' => 'Confirmar eliminação',
    'confirm_delete_message' => 'Tem a certeza de que pretende eliminar a regra ":rule"?',
    'delete' => 'Eliminar',

    // Status messages
    'success' => 'Sucesso',
    'error' => 'Erro',

    // Placeholders
    'select_mode' => 'Selecionar o modo de renomeação',
    'execution_order' => 'Ordem de execução',

    // Test functionality
    'test_input_placeholder' => 'Introduza um nome de ficheiro para testar as suas regras de renomeação (ex.: IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Resultado',
    'test_failed' => 'Falha ao testar as regras de renomeação',
    'apply_photo_rules' => 'Aplicar regras de fotos',
    'apply_album_rules' => 'Aplicar regras de álbuns',
];
