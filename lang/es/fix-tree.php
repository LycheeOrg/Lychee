<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Fix-tree Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Mantenimiento',
	'intro' => 'Esta página le permite reordenar y corregir sus álbumes manualmente.<br />Antes de realizar cualquier modificación, le recomendamos encarecidamente que lea sobre las estructuras de árbol de conjuntos anidados.',
	'warning' => 'Aquí realmente puede romper su instalación de Lychee, modifique los valores bajo su propio riesgo.',

	'help' => [
		'header' => 'Ayuda',
		'hover' => 'Pase el cursor sobre los ids o títulos para resaltar los álbumes relacionados.',
		'left' => '<span class="text-muted-color-emphasis font-bold">Izquierda</span>',
		'right' => '<span class="text-muted-color-emphasis font-bold">Derecha</span>',
		'convenience' => 'Para su comodidad, los botones <i class="pi pi-angle-up" ></i> y <i class="pi pi-angle-down" ></i> le permiten cambiar los valores de %s y %s respectivamente en +1 y -1 con propagación.',
		'left-right-warn' => 'Los íconos <i class="text-warning-600 pi pi-chevron-circle-left" ></i> y <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indican que el valor de %s (y respectivamente %s) está duplicado en algún lugar.',
		'parent-marked' => 'El marcado <span class="font-bold text-danger-600">Id de Padre</span> indica que los valores de %s y %s no satisfacen las estructuras de árbol de conjuntos anidados. Edite el <span class="font-bold text-danger-600">Id de Padre</span> o los valores de %s/%s.',
		'slowness' => 'Esta página será lenta con un gran número de álbumes.',
	],

	'buttons' => [
		'reset' => 'Restablecer',
		'check' => 'Verificar',
		'apply' => 'Aplicar',
	],

	'table' => [
		'title' => 'Título',
		'left' => 'Izquierda',
		'right' => 'Derecha',
		'id' => 'Id',
		'parent' => 'Id de Padre',
	],

	'errors' => [
		'invalid' => '¡Árbol inválido!',
		'invalid_details' => 'No estamos aplicando esto ya que se garantiza que será un estado roto.',
		'invalid_left' => 'El álbum %s tiene un valor izquierdo inválido.',
		'invalid_right' => 'El álbum %s tiene un valor derecho inválido.',
		'invalid_left_right' => 'El álbum %s tiene valores izquierdo/derecho inválidos. Izquierda debe ser estrictamente menor que derecha: %s < %s.',
		'duplicate_left' => 'El álbum %s tiene un valor izquierdo duplicado %s.',
		'duplicate_right' => 'El álbum %s tiene un valor derecho duplicado %s.',
		'parent' => 'El álbum %s tiene un id de padre inesperado %s.',
		'unknown' => 'El álbum %s tiene un error desconocido.',
	],
];