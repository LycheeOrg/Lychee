<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'Una referencia en vivo de los tokens y componentes con los que realmente está construido el front end v8. Cada muestra y control de esta página lee las propiedades CSS personalizadas reales y los componentes de Nuxt UI en uso hoy, por lo que nunca puede quedar desactualizada como lo haría una maqueta estática.',

    'sections' => [
        'foundations' => 'Fundamentos',
        'colors' => 'Paleta de colores',
        'typography' => 'Tipografía',
        'buttons' => 'Botones',
        'badges' => 'Insignias y chips',
        'forms' => 'Controles de formulario',
        'feedback' => 'Mensajes y estados',
        'radius' => 'Radio y elevación',
    ],

    'loading' => [
        'full_page' => 'Cargador de página completa',
        'full_page_description' => 'Aparición lenta y escalonada — se usa detrás del oscurecimiento a pantalla completa mientras se carga una vista.',
        'mini_spinner' => 'Mini indicador de carga',
        'mini_spinner_description' => 'Variante rápida, visible de inmediato — se usa en línea para botones, listas y pequeños estados de carga.',
    ],

    'foundations' => [
        'system' => 'Kit de componentes',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Fuente de cuerpo',
        'body_font_value' => 'Libre Franklin, autoalojada (Vazirmatn para árabe/persa)',
        'mono_font' => 'Fuente monoespaciada',
        'mono_font_value' => 'Pila monoespaciada del sistema (ui-monospace)',
        'icons' => 'Conjunto de iconos',
        'icons_value' => 'Lucide, mediante Iconify (UIcon)',
        'theme' => 'Tema',
        'theme_value' => 'Un único tema oscuro/claro, alternado en Ajustes — sin anulaciones por sección',
    ],

    'colors' => [
        'primary' => 'Primario — color de la marca, acciones principales, enlaces, estados activos',
        'secondary' => 'Secundario — acciones alternativas y selecciones',
        'neutral' => 'Neutro — texto, bordes, superficies (Slate en modo claro, Zinc en oscuro)',
        'success' => 'Éxito — confirmaciones, estados positivos',
        'warning' => 'Advertencia — precaución, problemas no bloqueantes',
        'error' => 'Error — acciones destructivas, fallos',
        'info' => 'Información — mensajes informativos',
        'resolved_from' => 'Resuelto en vivo desde :token',
    ],

    'typography' => [
        'display' => 'Display',
        'heading' => 'Encabezado',
        'body' => 'Texto de cuerpo',
        'body_sample' => 'El veloz murciélago hindú comía feliz cardillo y kiwi — los títulos de álbumes, las etiquetas de formularios y los textos cotidianos se muestran con esta pila.',
        'mono' => 'Monospace',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Escala de énfasis de texto',
        'emphasis_sample' => 'Fotos tomadas en este día en años anteriores',
    ],

    'buttons' => [
        'intro' => 'Todos los colores que admite el sistema de botones, en las variantes realmente usadas en la aplicación.',
    ],

    'forms' => [
        'input_label' => 'Título del álbum',
        'input_placeholder' => 'Verano en Kioto',
        'select_label' => 'Ordenar por',
        'checkbox_label' => 'Mostrar álbumes sensibles',
        'switch_label' => 'Álbum público',
        'switch_description' => 'Cualquiera con el enlace puede ver este álbum',
        'textarea_label' => 'Descripción',
        'textarea_placeholder' => 'Añade una descripción para este álbum…',
        'radio_legend' => 'Lógica de coincidencia de etiquetas',
    ],

    'feedback' => [
        'alert_info' => 'Se añadieron fotos nuevas a este álbum.',
        'alert_success' => 'Ajustes guardados.',
        'alert_warning' => 'Este álbum es visible para cualquiera con el enlace.',
        'alert_error' => 'No se pudo conectar con el servidor — los cambios no se guardaron.',
        'empty_title' => 'Aún no hay compartidos',
        'empty_description' => 'Los álbumes compartidos y sus permisos aparecerán aquí cuando compartas tu primer álbum.',
    ],
];
