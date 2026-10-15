<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Partilha',
    'info' => 'Esta página apresenta uma visão geral dos direitos de partilha associados aos álbuns e permite editá-los.',
    'album_title' => 'Título do álbum',
    'username' => 'Nome de utilizador',
    'no_data' => 'A lista de partilhas está vazia.',
    'filter_placeholder' => 'Pesquisar por título do álbum...',
    'hide_empty' => 'Ocultar álbuns sem partilhas',
    'screen_too_small' => 'Esta página requer um ecrã maior.',
    'share' => 'Partilhar',
    'add_new_access_permission' => 'Adicionar uma nova permissão de acesso',
    'permission_deleted' => 'Permissão eliminada!',
    'permission_created' => 'Permissão criada!',
    'propagate' => 'Propagar',
    'propagate_help' => 'Propagar as permissões de acesso atuais a todos os descendentes<br>(sub-álbuns e respetivos sub-álbuns, etc.)',
    'propagate_default' => 'Por predefinição, as permissões existentes (álbum-utilizador)<br>são atualizadas e as em falta são adicionadas.<br>As permissões adicionais que não constam desta lista não são alteradas.',
    'propagate_overwrite' => 'Substituir as permissões existentes em vez de as atualizar.<br>Isto também remove todas as permissões que não constam desta lista.',
    'propagate_warning' => 'Esta ação não pode ser anulada.',
    'permission_overwritten' => 'Propagação bem-sucedida! Permissões substituídas!',
    'permission_updated' => 'Propagação bem-sucedida! Permissões atualizadas!',
    'bluk_share' => 'Partilha em massa',
    'bulk_share_instr' => 'Selecione vários álbuns e utilizadores com quem partilhar.',
    'albums' => 'Álbuns',
    'users' => 'Utilizadores',
    'no_users' => 'Nenhum utilizador selecionável.',
    'no_albums' => 'Nenhum álbum selecionável.',
    'grants' => [
        'read' => 'Concede acesso de leitura',
        'original' => 'Concede acesso à foto original',
        'download' => 'Concede transferência',
        'upload' => 'Concede carregamento',
        'edit' => 'Concede edição',
        'move' => 'Concede mover, copiar e fundir',
        'delete' => 'Concede eliminação',
    ],
];
