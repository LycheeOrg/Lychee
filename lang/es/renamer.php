<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Reglas del renombrador',

    // Modal titles
    'create_rule' => 'Crear regla del renombrador',
    'edit_rule' => 'Editar regla del renombrador',

    // Form fields
    'rule_name' => 'Nombre de la regla',
    'description' => 'Descripción',
    'pattern' => 'Patrón',
    'replacement' => 'Reemplazo',
    'mode' => 'Modo',
    'order' => 'Orden',
    'enabled' => 'Activada',
    'photo_rule' => 'Regla aplicada a fotos',
    'album_rule' => 'Regla aplicada a álbumes',

    // Form placeholders and help text
    'description_placeholder' => 'Descripción opcional de lo que hace esta regla',
    'pattern_help' => 'Patrón a buscar (p. ej., IMG_, DSC_)',
    'replacement_help' => 'Texto de reemplazo (p. ej., Photo_, Camera_)',
    'order_help' => 'Los números más bajos se procesan primero (1 = máxima prioridad)',
    'enabled_help' => '(Solo las reglas activadas se aplicarán al renombrar)',

    // Mode options
    'mode_first' => 'Primera aparición',
    'mode_all' => 'Todas las apariciones',
    'mode_regex' => 'Expresión regular',
    'mode_trim' => 'Recortar espacios',
    'mode_strtolower' => 'minúsculas',
    'mode_strtoupper' => 'MAYÚSCULAS',
    'mode_ucwords' => 'Mayúscula En Cada Palabra',
    'mode_ucfirst' => 'Primera letra en mayúscula',

    'mode_first_description' => 'Reemplazar solo la primera aparición',
    'mode_all_description' => 'Reemplazar todas las apariciones',
    'mode_regex_description' => 'Usar coincidencia de patrones con expresiones regulares',
    'mode_trim_description' => 'Recortar espacios en blanco',
    'mode_strtolower_description' => 'Convertir el texto a minúsculas',
    'mode_strtoupper_description' => 'Convertir el texto a MAYÚSCULAS',
    'mode_ucwords_description' => 'Poner en mayúscula cada palabra',
    'mode_ucfirst_description' => 'Poner en mayúscula solo la primera letra',

    'regex_help' => 'Usa expresiones regulares para buscar patrones. Por ejemplo, para reemplazar <code>IMG_1234.jpeg</code> por <code>1234_JPG.jpeg</code>, puedes usar <code>/IMG_(\\d+)/</code> como patrón y <code>$1_JPG</code> como reemplazo. Encontrarás más explicaciones y ejemplos en los siguientes enlaces.',

    // Buttons
    'cancel' => 'Cancelar',
    'create' => 'Crear',
    'update' => 'Actualizar',
    'create_first_rule' => 'Crea tu primera regla',

    // Validation messages
    'rule_name_required' => 'El nombre de la regla es obligatorio',
    'pattern_required' => 'El patrón es obligatorio',
    'replacement_required' => 'El reemplazo es obligatorio',
    'mode_required' => 'El modo es obligatorio',
    'order_positive' => 'El orden debe ser un número positivo',

    // Success messages
    'rule_created' => 'Regla del renombrador creada correctamente',
    'rule_updated' => 'Regla del renombrador actualizada correctamente',
    'rule_deleted' => 'Regla del renombrador eliminada correctamente',

    // Error messages
    'failed_to_create' => 'No se pudo crear la regla del renombrador',
    'failed_to_update' => 'No se pudo actualizar la regla del renombrador',
    'failed_to_delete' => 'No se pudo eliminar la regla del renombrador',
    'failed_to_load' => 'No se pudieron cargar las reglas del renombrador',

    // List view
    'rules_count' => ':count reglas',
    'no_rules' => 'No se encontraron reglas del renombrador',
    'loading' => 'Cargando reglas del renombrador...',
    'pattern_label' => 'Patrón',
    'replace_with_label' => 'Reemplazar por',
    'photo' => 'Foto',
    'album' => 'Álbum',

    // Delete confirmation
    'confirm_delete_header' => 'Confirmar eliminación',
    'confirm_delete_message' => '¿Seguro que quieres eliminar la regla ":rule"?',
    'delete' => 'Eliminar',

    // Status messages
    'success' => 'Éxito',
    'error' => 'Error',

    // Placeholders
    'select_mode' => 'Selecciona el modo de renombrado',
    'execution_order' => 'Orden de ejecución',

    // Test functionality
    'test_input_placeholder' => 'Introduce un nombre de archivo para probar tus reglas del renombrador (p. ej., IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Resultado',
    'test_failed' => 'No se pudieron probar las reglas del renombrador',
    'apply_photo_rules' => 'Aplicar reglas de fotos',
    'apply_album_rules' => 'Aplicar reglas de álbumes',
];
