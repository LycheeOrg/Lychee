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
        'left-right-warn' => 'The <i class="text-warning-600 pi pi-chevron-circle-left" ></i> and <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indicates that the value of %s (and respectively %s) is duplicated somewhere.',
        'parent-marked' => 'Marked <span class="font-bold text-danger-600">Parent Id</span> indicates that the %s and %s do not satisfy the Nest Set tree structures. Edit either the <span class="font-bold text-danger-600">Parent Id</span> or the %s/%s values.',
        'slowness' => 'This page will be slow with a large number of albums.',
    ],
    'buttons' => [
        'reset' => 'Reset',
        'check' => 'Check',
        'apply' => 'Apply',
    ],
    'table' => [
        'title' => 'Title',
        'left' => 'Left',
        'right' => 'Right',
        'id' => 'Id',
        'parent' => 'Parent Id',
    ],
    'errors' => [
        'invalid' => 'Invalid tree!',
        'invalid_details' => 'We are not applying this as it is guaranteed to be a broken state.',
        'invalid_left' => 'Album %s has an invalid left value.',
        'invalid_right' => 'Album %s has an invalid right value.',
        'invalid_left_right' => 'Album %s has an invalid left/right values. Left should be strictly smaller than right: %s < %s.',
        'duplicate_left' => 'Album %s has a duplicate left value %s.',
        'duplicate_right' => 'Album %s has a duplicate right value %s.',
        'parent' => 'Album %s has an unexpected parent id %s.',
        'unknown' => 'Album %s has an unknown error.',
    ],
];
