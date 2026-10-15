<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Manutenção',
    'intro' => 'Esta página permite reordenar e corrigir manualmente os seus álbuns.<br />Antes de qualquer modificação, recomendamos vivamente que leia sobre as estruturas de árvore Nested Set.',
    'warning' => 'Pode mesmo danificar a sua instalação Lychee aqui; modifique os valores por sua conta e risco.',
    'help' => [
        'header' => 'Ajuda',
        'hover' => 'Passe o cursor sobre IDs ou títulos para destacar os álbuns relacionados.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Esquerda</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Direita</span>',
        'convenience' => 'Para sua conveniência, os botões <i class="pi pi-angle-up" ></i> e <i class="pi pi-angle-down" ></i> permitem alterar os valores de %s e %s respetivamente em +1 e -1, com propagação.',
        'left-right-warn' => 'Os ícones <i class="text-warning-600 pi pi-chevron-circle-left" ></i> e <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indicam que o valor de %s (e respetivamente de %s) está duplicado algures.',
        'parent-marked' => 'Um <span class="font-bold text-danger-600">Parent Id</span> marcado indica que %s e %s não satisfazem as estruturas de árvore Nested Set. Edite o <span class="font-bold text-danger-600">Parent Id</span> ou os valores %s/%s.',
        'slowness' => 'Esta página será lenta com um grande número de álbuns.',
    ],
    'buttons' => [
        'reset' => 'Repor',
        'check' => 'Verificar',
        'apply' => 'Aplicar',
    ],
    'no-changes' => 'Sem alterações a aplicar.',
    'table' => [
        'title' => 'Título',
        'left' => 'Esquerda',
        'right' => 'Direita',
        'id' => 'Id',
        'parent' => 'Parent Id',
    ],
    'errors' => [
        'invalid' => 'Árvore inválida!',
        'invalid_details' => 'Não aplicamos isto, pois resultaria garantidamente num estado danificado.',
        'invalid_left' => 'O álbum %s tem um valor esquerdo inválido.',
        'invalid_right' => 'O álbum %s tem um valor direito inválido.',
        'invalid_left_right' => 'O álbum %s tem valores esquerdo/direito inválidos. O esquerdo deve ser estritamente menor do que o direito: %s < %s.',
        'duplicate_left' => 'O álbum %s tem um valor esquerdo duplicado %s.',
        'duplicate_right' => 'O álbum %s tem um valor direito duplicado %s.',
        'parent' => 'O álbum %s tem um parent id inesperado %s.',
        'unknown' => 'O álbum %s tem um erro desconhecido.',
    ],
];
