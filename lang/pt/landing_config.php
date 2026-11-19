<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Página inicial',
    'tab_settings' => 'Definições',
    'tab_links' => 'Ligações',
    'tab_featured' => 'Em destaque',

    'section_layout' => 'Layout e estrutura',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Fundo (paisagem)',
    'section_background_portrait' => 'Fundo (retrato)',
    'section_cta_position' => 'Posição do botão de ação (CTA)',
    'section_login_position' => 'Painel de início de sessão',
    'section_meridian' => 'Linhas Meridian',
    'section_content' => 'Conteúdo',

    'field_layout' => 'Layout',
    'field_intro_screen_enabled' => 'Ecrã de introdução',
    'field_hero_text_position' => 'Posição do texto do hero',
    'field_hero_text_color' => 'Cor do texto do hero',
    'field_hero_text_opacity' => 'Opacidade do texto do hero',
    'field_animation_preset' => 'Predefinição de animação',
    'field_backdrop_opacity' => 'Opacidade do fundo (:value%)',
    'field_cta_text' => 'texto',
    'field_cta_text_placeholder' => 'Deixe vazio para usar o padrão do layout',
    'field_about_enabled' => 'Secção Sobre',
    'field_about_text' => 'Texto Sobre',

    'field_background_mode' => 'Origem',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID da foto',
        'random' => 'Foto pública aleatória',
        'latest_album_cover' => 'Capa do álbum mais recente',
        'random_from_album' => 'Foto aleatória de um álbum',
    ],
    'field_background_url' => 'URL da imagem',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID da foto',
    'field_background_photo_id_placeholder' => 'ID da foto com 24 caracteres',
    'field_background_photo_id_hint' => 'ID da foto da imagem a utilizar. Abra uma foto e copie os últimos 24 caracteres do URL.',
    'field_background_album_id' => 'ID do álbum',
    'field_background_album_id_placeholder' => 'ID do álbum com 24 caracteres',
    'background_load_error' => 'Não foi possível carregar a foto. Verifique se o ID está correto e se tem acesso à mesma.',
    'background_mode_hint' => [
        'random' => 'É escolhida uma foto pública aleatória a cada carregamento da página.',
        'latest_album_cover' => 'É utilizada a capa do álbum público publicado mais recentemente.',
        'random_from_album' => 'É escolhida uma foto aleatória deste álbum a cada carregamento da página. A pré-visualização só é atualizada depois de guardar.',
    ],

    'preview_orientation_landscape' => 'Pré-visualizar fundo em paisagem',
    'preview_orientation_portrait' => 'Pré-visualizar fundo em retrato',

    'field_cta_position' => 'Posição',
    'cta_position_options' => [
        'top-left' => 'Superior esquerdo',
        'top' => 'Superior central',
        'top-right' => 'Superior direito',
        'left' => 'Centro esquerdo',
        'center' => 'Centro',
        'right' => 'Centro direito',
        'bottom-left' => 'Inferior esquerdo',
        'bottom' => 'Inferior central',
        'bottom-right' => 'Inferior direito',
    ],
    'field_cta_shift_type' => 'Unidade de deslocamento',
    'cta_shift_type_options' => [
        'relative' => 'Relativo (%)',
        'absolute' => 'Absoluto (px)',
    ],
    'cta_shift_type_hint' => 'Os deslocamentos relativos são uma percentagem do tamanho da janela de visualização; os absolutos são um número fixo de píxeis.',
    'field_cta_shift_x' => 'Deslocamento horizontal (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Esquerda',
        'right' => 'Direita',
    ],
    'field_cta_shift_y' => 'Deslocamento vertical (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Cima',
        'down' => 'Baixo',
    ],
    'reset_to_zero' => 'Repor a 0',

    'field_login_position' => 'Posição',
    'login_position_options' => [
        'side' => 'Lateral',
        'center' => 'Centro',
    ],
    'field_login_position_hint' => 'Em ecrãs estreitos, o painel de início de sessão fica sempre centrado abaixo do hero, independentemente desta definição. Desliza a partir do lado de início da leitura quando a predefinição de animação é "Slide reveal" ou "Parallax scroll".',

    'field_meridian_explore_offset' => 'Altura da etiqueta Explorar (:value%)',
    'field_meridian_contact_offset' => 'Altura da etiqueta Contacto (:value%)',
    'field_meridian_offset_hint' => 'A que altura de cada linha vertical se encontra a respetiva etiqueta: 0% é o topo, 100% é a base.',
    'field_meridian_explore_line_position' => 'Posição da linha Explorar (:value%)',
    'field_meridian_contact_line_position' => 'Posição da linha Contacto (:value%)',
    'field_meridian_line_position_hint' => 'A que distância da margem esquerda se encontra cada linha: 0% é a margem esquerda, 100% é a margem direita. A linha Explorar centra-se sempre quando a linha Contacto está desativada.',

    'preview_title' => 'Pré-visualização em direto',
    'preview_hint' => 'Atualiza instantaneamente enquanto edita — nada é guardado até clicar em Guardar.',
    'flat_list_hint' => 'Estas definições também podem ser editadas na lista genérica de Definições.',

    'save' => 'Guardar',
    'saved' => 'Definições da página inicial guardadas.',
    'save_error' => 'Falha ao guardar as definições da página inicial.',

    'se_required' => 'Este layout/predefinição requer o Lychee SE.',
];
