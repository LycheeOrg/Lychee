<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Pré-visualização da marca de água',
        'se_required' => 'O módulo de marca de água requer que o Lychee Supporter Edition (SE) ou o SE Preview esteja ativado.',

        'section_settings' => 'Definições da marca de água',
        'section_preview' => 'Pré-visualização em tempo real',
        'disclaimer' => 'Esta pré-visualização dá uma ideia do aspeto da marca de água. O resultado final nas suas fotos pode ser ligeiramente diferente.',

        'watermark_photo_id' => 'ID da imagem da marca de água',
        'watermark_photo_id_placeholder' => 'ID de foto com 24 caracteres',
        'watermark_photo_id_hint' => 'ID da foto da imagem usada como marca de água. Abra uma foto e copie os últimos 24 caracteres do URL.',

        'preview_photo_id' => 'ID da foto de fundo',
        'preview_photo_id_placeholder' => 'ID de foto com 24 caracteres',
        'preview_photo_id_hint' => 'Introduza um ID de foto para usar como fundo da pré-visualização.',

        'size' => 'Tamanho (:value%)',
        'opacity' => 'Opacidade (:value%)',
        'position' => 'Posição',
        'position_options' => [
            'top-left' => 'Superior esquerdo',
            'top' => 'Superior centro',
            'top-right' => 'Superior direito',
            'left' => 'Meio esquerdo',
            'center' => 'Centro',
            'right' => 'Meio direito',
            'bottom-left' => 'Inferior esquerdo',
            'bottom' => 'Inferior centro',
            'bottom-right' => 'Inferior direito',
        ],

        'section_shift' => 'Deslocamento',
        'shift_type' => 'Unidade de deslocamento',
        'shift_type_options' => [
            'relative' => 'Relativo (%)',
            'absolute' => 'Absoluto (px)',
        ],
        'shift_type_hint' => 'Os deslocamentos relativos são uma percentagem do tamanho da imagem; os absolutos são um número fixo de píxeis.',
        'shift_mode_use_slider' => 'Usar controlo deslizante',
        'shift_mode_use_classic' => 'Usar campo numérico',
        'shift_x' => 'Deslocamento horizontal (:value)',
        'shift_x_direction_options' => [
            'left' => 'Esquerda',
            'right' => 'Direita',
        ],
        'shift_y' => 'Deslocamento vertical (:value)',
        'shift_y_direction_options' => [
            'up' => 'Cima',
            'down' => 'Baixo',
        ],
        'reset_to_zero' => 'Repor a 0',

        'save' => 'Guardar definições',
        'saved' => 'Definições da marca de água guardadas.',
        'save_error' => 'Falha ao guardar as definições da marca de água.',
        'save_requires_se' => 'Guardar as definições da marca de água requer uma licença completa do Supporter Edition (SE). O SE Preview só permite pré-visualizar o efeito.',

        'no_watermark_image' => 'Nenhuma imagem de marca de água configurada. Introduza um ID de foto da marca de água e clique em "Carregar" para pré-visualizar.',
        'no_preview_photo' => 'Introduza acima um ID de foto de fundo para pré-visualizar a sobreposição da marca de água.',
        'photo_load_error' => 'Não foi possível carregar a foto. Verifique se o ID está correto e se tem acesso a ela.',
        'watermark_load_error' => 'Não foi possível carregar a imagem da marca de água. Verifique se o ID da foto está correto.',
    ],
];
