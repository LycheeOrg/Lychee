<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Mantenimiento',
    'intro' => 'Esta página le permite reordenar y corregir sus álbumes manualmente.<br />Antes de realizar cualquier modificación, le recomendamos encarecidamente que lea la información sobre las estructuras de árbol Nested Set.',
    'warning' => 'Aquí puedes romper tu instalación de Lychee, modifica los valores bajo tu propia responsabilidad.',
    'help' => [
        'header' => 'Ayuda',
        'hover' => 'Pasa el cursor por encima de los ID o títulos para resaltar los álbumes relacionados.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Izquierda</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Derecha</span>',
        'convenience' => 'Para su comodidad, los botones <i class="pi pi-angle-up" ></i> y <i class="pi pi-angle-down" ></i> le permiten cambiar los valores de %s y %s respectivamente en +1 y -1 con propagación.',
        'left-right-warn' => 'Los símbolos <i class="text-warning-600 pi pi-chevron-circle-left" ></i> y <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indican que el valor de %s (y respectivamente %s) está duplicado en algún lugar.',
        'parent-marked' => 'El <span class="font-bold text-danger-600">ID principal</span> marcado indica que %s y %s no cumplen con las estructuras de árbol del conjunto anidado. Edite el <span class="font-bold text-danger-600">ID principal</span> o los valores %s/%s.',
        'slowness' => 'Esta página será lenta si hay un gran número de álbumes.',
    ],
    'buttons' => [
        'reset' => 'Restablecer',
        'check' => 'Comprobar',
        'apply' => 'Aplicar',
    ],
    'table' => [
        'title' => 'Título',
        'left' => 'Izquierda',
        'right' => 'Derecha',
        'id' => 'Id',
        'parent' => 'Identificación del padre',
    ],
    'errors' => [
        'invalid' => '¡Árbol inválido!',
        'invalid_details' => 'No estamos aplicando esto porque está garantizado que será un estado roto.',
        'invalid_left' => 'El álbum %s tiene un valor restante no válido.',
        'invalid_right' => 'El álbum %s tiene un valor de derecho no válido.',
        'invalid_left_right' => 'El álbum %s tiene valores izquierdo/derecho no válidos. El valor izquierdo debe ser estrictamente menor que el derecho: %s < %s.',
        'duplicate_left' => 'El álbum %s tiene un valor duplicado restante %s.',
        'duplicate_right' => 'El álbum %s tiene un valor de derecho duplicado %s.',
        'parent' => 'El álbum %s tiene un ID de padre inesperado %s.',
        'unknown' => 'El álbum %s tiene un error desconocido.',
    ],
];
