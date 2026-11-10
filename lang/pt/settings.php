<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Definições',
    'small_screen' => 'Para uma melhor experiência nesta página,<br />recomendamos que utilize um ecrã maior.',
    'tabs' => [
        'basic' => 'Básico',
        'all_settings' => 'Todas as definições',
    ],
    'toasts' => [
        'change_saved' => 'Alteração guardada!',
        'details' => 'As definições foram alteradas conforme pedido',
        'error' => 'Erro!',
        'error_load_css' => 'Não foi possível carregar dist/user.css',
        'error_load_js' => 'Não foi possível carregar dist/custom.js',
        'error_save_css' => 'Não foi possível guardar o CSS',
        'error_save_js' => 'Não foi possível guardar o JS',
        'thank_you' => 'Obrigado pelo seu apoio.',
        'reload' => 'Recarregue a página para ter todas as funcionalidades.',
    ],
    'system' => [
        'header' => 'Sistema',
        'use_dark_mode' => 'Usar o modo escuro no Lychee',
        'language' => 'Idioma utilizado pelo Lychee',
        'nsfw_album_visibility' => 'Tornar os álbuns sensíveis visíveis por predefinição.',
        'nsfw_album_explanation' => 'Se o álbum for público, continua acessível, apenas oculto da vista, e <b>pode ser revelado premindo <kbd>H</kbd></b>.',
        'cache_enabled' => 'Ativar a cache das respostas.',
        'cache_enabled_details' => 'Isto acelera significativamente o tempo de resposta do Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Se utilizar álbuns protegidos por palavra-passe, não deve ativar esta opção.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Obtenha funcionalidades exclusivas e apoie o desenvolvimento do Lychee. Desbloqueie a <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">edição SE</a>.',
        'preview' => 'Ativar a pré-visualização das funcionalidades do Lychee SE',
        'hide_call4action' => 'Ocultar este formulário de registo do Lychee SE. Estou satisfeito com o Lychee tal como está. :)',
        'hide_warning' => 'Se ativado, a única forma de registar a sua chave de licença será através do separador Mais acima. As alterações são aplicadas ao recarregar a página.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Para importar fotos da sua Dropbox, precisa de uma chave de aplicação drop-ins válida obtida no site da Dropbox.',
        'api_key' => 'Chave da API da Dropbox',
        'set_key' => 'Definir chave da Dropbox',
    ],
    'gallery' => [
        'header' => 'Galeria',
        'photo_order_column' => 'Coluna predefinida para ordenar fotos',
        'photo_order_direction' => 'Ordem predefinida para ordenar fotos',
        'album_order_column' => 'Coluna predefinida para ordenar álbuns',
        'album_order_direction' => 'Ordem predefinida para ordenar álbuns',
        'aspect_ratio' => 'Proporção predefinida das miniaturas de álbuns',
        'photo_layout' => 'Layout das imagens',
        'album_decoration' => 'Mostrar decorações na capa do álbum (número de subálbuns e/ou fotos)',
        'album_decoration_direction' => 'Alinhar as decorações dos álbuns horizontal ou verticalmente',
        'photo_overlay' => 'Informação predefinida sobreposta à imagem',
        'album_date_scrubber_enabled' => 'Mostrar a barra de navegação por datas nas vistas de álbum',
        'rounded_corners_enabled' => 'Arredondar os cantos das miniaturas de fotos e álbuns',
        'album_border_enabled' => 'Mostrar uma margem à volta das miniaturas de fotos e álbuns',
        'photo_ken_burns_on_hover_enabled' => 'Ativar o efeito Ken Burns ao passar o rato sobre as fotos',
        'photo_ken_burns_on_hover_scale' => 'Intensidade do zoom Ken Burns ao passar o rato (%)',
        'photo_ken_burns_on_hover_duration' => 'Duração do zoom Ken Burns ao passar o rato (segundos)',
        'license_default' => 'Licença predefinida dos álbuns',
        'license_help' => 'Precisa de ajuda para escolher?',
    ],
    'geolocation' => [
        'header' => 'Geolocalização',
        'map_display' => 'Mostrar o mapa a partir das coordenadas GPS',
        'map_display_public' => 'Permitir que utilizadores anónimos acedam ao mapa',
        'map_provider' => 'Define o fornecedor do mapa',
        'map_include_subalbums' => 'Incluir as imagens dos subálbuns no mapa',
        'location_decoding' => 'Usar a descodificação da localização GPS',
        'location_show' => 'Mostrar a localização extraída das coordenadas GPS',
        'location_show_public' => 'Os utilizadores anónimos podem aceder à localização extraída das coordenadas GPS',
        'gps_coordinate_display' => 'Mostrar as coordenadas GPS',
        'gps_coordinate_display_public' => 'Permitir que utilizadores anónimos acedam às coordenadas GPS',
    ],
    'cssjs' => [
        'header' => 'CSS e JS personalizados',
        'change_css' => 'Alterar CSS',
        'change_js' => 'Alterar JS',
    ],
    'all' => [
        'old_setting_style' => 'Estilo antigo das definições',
        'expert_settings' => 'Modo avançado',
        'change_detected' => 'Algumas definições foram alteradas.',
        'save' => 'Guardar',
        'back_to_settings' => 'Voltar às definições agrupadas',
    ],
    'tool_option' => [
        'disabled' => 'desativado',
        'enabled' => 'ativado',
        'discover' => 'descobrir',
    ],
    'groups' => [
        'general' => 'Geral',
        'system' => 'Sistema',
        'modules' => 'Módulos',
        'advanced' => 'Avançado',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Usar o painel de administração',
            'help' => 'Substituir o submenu de administração aninhado por uma única ligação para a nova página do painel de administração.',
        ],
    ],
    'password_field' => [
        'set' => 'Definida',
        'not_set' => 'Não definida',
        'placeholder' => 'Nova palavra-passe',
        'clear' => 'Limpar',
    ],
];
