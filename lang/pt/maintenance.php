<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Manutenção',
    'description' => 'Nesta página encontrará todas as ações necessárias para manter a sua instalação do Lychee a funcionar de forma fluida e correta.',
    'cleaning' => [
        'title' => 'Limpar %s',
        'result' => '%s eliminado.',
        'description' => 'Remover todo o conteúdo de <span class="font-mono">%s</span>',
        'button' => 'Limpar',
    ],
    'duplicate-finder' => [
        'title' => 'Duplicados',
        'description' => 'Este módulo conta potenciais duplicados entre imagens.',
        'duplicates-all' => 'Duplicados em todos os álbuns',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplicados por álbum',
        'show' => 'Mostrar duplicados',
        'load' => 'Carregar contagens',
    ],
    'fix-jobs' => [
        'title' => 'Corrigir histórico de tarefas',
        'description' => 'Marcar tarefas com o estado <span class="text-ready-400">%s</span> ou <span class="text-primary-500">%s</span> como <span class="text-danger-700">%s</span>.',
        'button' => 'Corrigir histórico de tarefas',
    ],
    'gen-sizevariants' => [
        'title' => '%s em falta',
        'description' => 'Encontrados %d %s que podem ser gerados.',
        'button' => 'Gerar!',
        'success' => '%d %s gerados com sucesso.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Tamanhos de ficheiro em falta',
        'description' => 'Encontradas %d variantes pequenas sem tamanho de ficheiro.',
        'button' => 'Obter dados!',
        'success' => 'Tamanhos de %d variantes pequenas calculados com sucesso.',
    ],
    'fix-tree' => [
        'title' => 'Estatísticas da árvore',
        'Oddness' => 'Anomalias',
        'Duplicates' => 'Duplicados',
        'Wrong parents' => 'Pais incorretos',
        'Missing parents' => 'Pais em falta',
        'button' => 'Corrigir árvore',
    ],
    'optimize' => [
        'title' => 'Otimizar base de dados',
        'description' => 'Se notar lentidão na sua instalação, pode dever-se ao facto de a base de dados não ter todos os índices necessários.',
        'button' => 'Otimizar base de dados',
    ],
    'missing-palettes' => [
        'title' => 'Paletas em falta',
        'description' => 'Encontradas %d paletas em falta.',
        'button' => 'Criar em falta',
    ],
    'statistics-check' => [
        'title' => 'Verificação da integridade das estatísticas',
        'missing_photos' => '%d estatísticas de fotos em falta.',
        'missing_albums' => '%d estatísticas de álbuns em falta.',
        'button' => 'Criar em falta',
    ],
    'flush-cache' => [
        'title' => 'Limpar cache',
        'description' => 'Limpar a cache de todos os utilizadores para resolver problemas de invalidação.',
        'button' => 'Limpar',
    ],
    'old-orders' => [
        'title' => 'Encomendas antigas',
        'description' => 'Encontradas %d encomendas antigas.<br/>Uma encomenda antiga tem mais de 14 dias, não tem utilizador associado e está ainda a aguardar pagamento ou não contém artigos.',
        'button' => 'Eliminar encomendas antigas',
    ],
    'fulfill-orders' => [
        'title' => 'Encomendas por concluir',
        'description' => 'Encontradas %d encomendas com conteúdo que ainda não foi disponibilizado.<br/>Clique no botão para atribuir o conteúdo quando possível.',
        'button' => 'Concluir encomendas',
    ],
    'fulfill-precompute' => [
        'title' => 'Campos pré-calculados dos álbuns',
        'description' => 'Encontrados %d álbuns com campos pré-calculados em falta.<br/>Equivalente a executar: php artisan lychee:recompute-album-stats',
        'button' => 'Calcular campos',
    ],
    'flush-queue' => [
        'title' => 'Esvaziar fila',
        'description' => 'Encontradas %d tarefas pendentes na fila.<br/>ATENÇÃO: esvaziar a fila elimina permanentemente todas as tarefas pendentes. Esta ação não pode ser anulada.',
        'button' => 'Esvaziar fila',
    ],
    'backfill-album-sizes' => [
        'title' => 'Estatísticas de tamanho dos álbuns',
        'description' => 'Encontrados %d álbuns sem estatísticas de tamanho.<br/>Equivalente a executar: php artisan lychee:recompute-album-sizes',
        'description_queue' => 'Recalcula em segundo plano as estatísticas de tamanho de todos os álbuns.<br/>Equivalente a executar: php artisan lychee:recompute-album-sizes',
        'button' => 'Calcular tamanhos',
    ],
    'recompute-buckets' => [
        'title' => 'Grupos da linha do tempo',
        'description' => 'Recalcula o grupo da linha do tempo de cada álbum e de cada foto e depois atualiza as listagens de álbuns e fotos.<br/>Equivale a executar: php artisan lychee:recompute-buckets',
        'button' => 'Recalcular',
    ],

    'face_quality' => [
        'title' => 'Revisão da qualidade dos rostos',
        'description' => 'Rever as deteções de rostos por pontuação de qualidade e descartar rostos de baixa qualidade ou erróneos.',
        'sort_by' => 'Ordenar por:',
        'sort_confidence' => 'Confiança',
        'sort_blur' => 'Desfocagem (Laplaciano)',
        'no_faces' => 'Nenhum rosto qualificado. Está tudo bem!',
        'col_face' => 'Rosto',
        'col_person' => 'Pessoa',
        'col_cluster' => 'Grupo',
        'col_confidence' => 'Confiança',
        'col_blur' => 'Pontuação de desfocagem',
        'col_actions' => 'Ações',
        'unassigned' => 'Não atribuído',
        'dismiss' => 'Descartar rosto',
        'readd' => 'Voltar a adicionar rosto',
        'load_error' => 'Falha ao carregar os rostos.',
        'dismissed' => 'Rosto descartado.',
        'readded' => 'Rosto adicionado novamente.',
        'dismiss_error' => 'Falha ao descartar o rosto.',
        'readd_error' => 'Falha ao voltar a adicionar o rosto.',
        'batch_dismiss' => 'Descartar selecionados',
        'batch_dismissed' => ':count rosto(s) descartado(s).',
        'batch_dismiss_error' => 'Falha ao descartar os rostos selecionados.',
        'batch_reactivate' => 'Reativar selecionados',
        'batch_reactivated' => ':count rosto(s) reativado(s).',
        'batch_reactivate_error' => 'Falha ao reativar os rostos selecionados.',
        'show_dismissed' => 'Mostrar descartados',
        'show_active' => 'Mostrar ativos',
        'show_unassigned' => 'Apenas não atribuídos',
        'select_all' => 'Selecionar tudo',
        'deselect_all' => 'Desmarcar tudo',
        'selected_count' => ':count selecionado(s)',
    ],
    'bulk-scan-faces' => [
        'description' => 'Encontradas %d fotos que ainda não foram analisadas para reconhecimento facial.<br/>Requer que o serviço AI Vision esteja em execução.',
    ],
    'run-clustering' => [
        'description' => 'Iniciar o agrupamento de rostos no serviço AI Vision. Agrupa os rostos detetados por semelhança para que os possa atribuir a pessoas.',
        'success' => 'Agrupamento iniciado com sucesso.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Destruir rostos descartados',
        'description' => 'Encontrados %d rostos descartados. Destruí-los eliminará permanentemente os respetivos ficheiros de recorte e embeddings.',
        'action' => 'Destruir tudo',
        'success' => 'Rostos descartados destruídos com sucesso.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Sincronizar embeddings de rostos',
        'description' => 'Detetada uma discrepância no número de rostos (diferença de %d). A sincronização irá obter os dados de rostos mais recentes do serviço AI Vision para o Lychee.',
        'action' => 'Sincronizar agora',
        'success' => 'Embeddings de rostos sincronizados com sucesso.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Repor estado da análise de rostos',
        'description' => 'Encontradas %d fotos com o estado de análise de rostos bloqueado em pendente ou falhado. Repô-las permitirá que sejam analisadas novamente.',
        'action' => 'Repor tudo',
        'success' => 'Estados da análise de rostos repostos com sucesso.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Purgar embeddings de rostos órfãos',
            'description' => 'O serviço AI Vision pode conter até %d embedding(s) que já não existem no Lychee. A purga enviará a lista atual de rostos para o AI Vision e eliminará permanentemente tudo o que sobrar.',
            'action' => 'Purgar agora',
            'success' => 'Embeddings de rostos órfãos purgados com sucesso.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Análise NSFW em massa',
        'description' => 'Analisar todas as fotos ainda não analisadas para conteúdo NSFW com a predefinição configurada. Requer que o serviço de classificação NSFW esteja em execução.',
        'button' => 'Analisar todas as não analisadas',
        'success' => 'Análise NSFW iniciada com sucesso.',
    ],
];
