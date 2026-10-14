<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Utilizadores',
    'description' => 'Aqui pode gerir os utilizadores da sua instalação Lychee. Pode criar, editar e eliminar utilizadores.',
    'create' => 'Criar um novo utilizador',
    'username' => 'Nome de utilizador',
    'password' => 'Palavra-passe',
    'legend' => 'Legenda',
    'upload_rights' => 'Quando selecionado, o utilizador pode carregar conteúdo.',
    'edit_rights' => 'Quando selecionado, o utilizador pode modificar o seu perfil (nome de utilizador, palavra-passe).',
    'upload_trust_level' => 'Nível de confiança de carregamento — controla se os carregamentos ficam imediatamente públicos.',

    'quota' => 'Quando definido, o utilizador tem uma quota de espaço para imagens (em kB).',
    'user_deleted' => 'Utilizador eliminado',
    'user_created' => 'Utilizador criado',
    'user_updated' => 'Utilizador atualizado',
    'change_saved' => 'Alteração guardada!',
    'create_edit' => [
        'upload_rights' => 'O utilizador pode carregar conteúdo.',
        'edit_rights' => 'O utilizador pode modificar o seu perfil (nome de utilizador, palavra-passe).',
        'admin_rights' => 'O utilizador tem direitos de administrador.',
        'upload_trust_level' => 'Nível de confiança de carregamento',
        'upload_trust_level_check' => 'Verificar – os carregamentos requerem aprovação de um administrador antes de ficarem públicos.',
        'upload_trust_level_monitor' => 'Monitorizar – os carregamentos são públicos, exceto se o conteúdo for sinalizado.',
        'upload_trust_level_trust_but_verify' => 'Confiar mas verificar – os carregamentos são públicos; deteções de revisão aprovadas automaticamente, deteções de bloqueio configuráveis.',
        'upload_trust_level_trusted' => 'Confiável – os carregamentos ficam imediatamente públicos.',
        'trust_level_options' => [
            'trusted' => 'Confiável',
            'trust_but_verify' => 'Confiar mas verificar',
            'monitor' => 'Monitorizar',
            'check' => 'Verificar',
        ],
        'quota' => 'O utilizador tem limite de quota.',
        'quota_kb' => 'quota em kB (0 para o valor predefinido)',
        'note' => 'Nota de administrador (não visível publicamente)',
        'create' => 'Criar',
        'edit' => 'Editar',
    ],
    'invite' => [
        'button' => 'Convidar utilizador',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Esta ligação é válida durante %d dias.',
    ],
    'line' => [
        'owner' => 'Proprietário',
        'admin' => 'Utilizador administrador',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
    ],
];
