<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Uma referência ao vivo dos tokens e componentes com que o front end v8 é realmente construído. Cada amostra e controlo nesta página lê as propriedades CSS personalizadas e os componentes Nuxt UI efetivamente em uso, pelo que nunca pode ficar desatualizada como aconteceria com uma maqueta estática.',

    'sections' => [
        'foundations' => 'Fundamentos',
        'colors' => 'Paleta de cores',
        'typography' => 'Tipografia',
        'buttons' => 'Botões',
        'badges' => 'Distintivos e chips',
        'forms' => 'Controlos de formulário',
        'feedback' => 'Feedback e estados',
        'radius' => 'Raio e elevação',
    ],

    'loading' => [
        'full_page' => 'Carregamento de página inteira',
        'full_page_description' => 'Revelação lenta e escalonada — usada por trás do ecrã escurecido enquanto uma vista carrega.',
        'mini_spinner' => 'Indicador de carregamento pequeno',
        'mini_spinner_description' => 'Variante rápida, visível imediatamente — usada em linha em botões, listas e pequenos estados de carregamento.',
    ],

    'foundations' => [
        'system' => 'Kit de componentes',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Tipo de letra do corpo',
        'body_font_value' => 'Libre Franklin, alojado localmente (Vazirmatn para árabe/persa)',
        'mono_font' => 'Tipo de letra monoespaçado',
        'mono_font_value' => 'Pilha monoespaçada do sistema (ui-monospace)',
        'icons' => 'Conjunto de ícones',
        'icons_value' => 'Lucide, via Iconify (UIcon)',
        'theme' => 'Tema',
        'theme_value' => 'Tema único escuro/claro, alternado nas Definições — sem substituições por secção',
    ],

    'colors' => [
        'primary' => 'Primária — cor da marca, ações principais, ligações, estados ativos',
        'secondary' => 'Secundária — ações e seleções alternativas',
        'neutral' => 'Neutra — texto, contornos, superfícies (Slate no modo claro, Zinc no escuro)',
        'success' => 'Sucesso — confirmações, estados positivos',
        'warning' => 'Aviso — precaução, problemas não bloqueantes',
        'error' => 'Erro — ações destrutivas, falhas',
        'info' => 'Info — mensagens informativas',
        'resolved_from' => 'Resolvido ao vivo a partir de :token',
    ],

    'typography' => [
        'display' => 'Destaque',
        'heading' => 'Título',
        'body' => 'Texto do corpo',
        'body_sample' => 'À beira-mar, o pequeno João fotografou gaivotas, barcos e um pôr do sol dourado — títulos de álbuns, rótulos de formulários e textos do dia a dia usam esta pilha.',
        'mono' => 'Monoespaçado',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Escala de ênfase do texto',
        'emphasis_sample' => 'Fotos tiradas neste dia em anos anteriores',
    ],

    'buttons' => [
        'intro' => 'Todas as cores suportadas pelo sistema de botões, nas variantes efetivamente usadas na aplicação.',
    ],

    'forms' => [
        'input_label' => 'Título do álbum',
        'input_placeholder' => 'Verão em Quioto',
        'select_label' => 'Ordenar por',
        'checkbox_label' => 'Mostrar álbuns sensíveis',
        'switch_label' => 'Álbum público',
        'switch_description' => 'Qualquer pessoa com a ligação pode ver este álbum',
        'textarea_label' => 'Descrição',
        'textarea_placeholder' => 'Adicione uma descrição para este álbum…',
        'radio_legend' => 'Lógica de correspondência de etiquetas',
    ],

    'feedback' => [
        'alert_info' => 'Foram adicionadas novas fotos a este álbum.',
        'alert_success' => 'Definições guardadas.',
        'alert_warning' => 'Este álbum está visível para qualquer pessoa com a ligação.',
        'alert_error' => 'Não foi possível contactar o servidor — as alterações não foram guardadas.',
        'empty_title' => 'Ainda sem partilhas',
        'empty_description' => 'Os álbuns partilhados e as respetivas permissões aparecerão aqui assim que partilhar o seu primeiro álbum.',
    ],
];
