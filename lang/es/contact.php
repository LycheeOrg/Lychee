<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contact form page
    |--------------------------------------------------------------------------
    */
    'title' => 'Contacto',
    'description' => '¡Nos encantará saber de ti!',
    'name_label' => 'Nombre',
    'name_placeholder' => 'Tu nombre',
    'email_label' => 'Correo electrónico o medio de contacto',
    'email_placeholder' => 'tu@correo.com',
    'message_label' => 'Mensaje',
    'message_placeholder' => 'Escribe tu mensaje aquí...',
    'security_question_label' => 'Pregunta de seguridad',
    'security_answer_placeholder' => 'Tu respuesta',
    'sample_qa_label' => 'Pregunta y respuesta de ejemplo',
    'consent_label' => 'Acepto la',
    'consent_privacy_link' => 'política de privacidad',
    'name_required_error' => 'El nombre es obligatorio.',
    'email_required_error' => 'El medio de contacto es obligatorio.',
    'message_min_length_error' => 'El mensaje debe tener al menos 10 caracteres.',
    'security_answer_required_error' => 'Responde a la pregunta de seguridad.',
    'consent_required_error' => 'Debes aceptar la política de privacidad.',
    'submit_button' => 'Enviar mensaje',
    'clear_button' => 'Borrar',
    'success_message' => 'Gracias por tu mensaje. Te responderemos pronto.',
    'submit_error' => 'Se produjo un error al enviar tu mensaje. Inténtalo de nuevo.',
    'rate_limit_error' => 'Demasiados envíos. Inténtalo de nuevo más tarde.',
    'validation_error' => 'Corrige los errores que se indican a continuación.',

    /*
    |--------------------------------------------------------------------------
    | Admin contact messages page
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'title' => 'Mensajes de contacto',
        'description' => 'Mensajes enviados a través del formulario de contacto.',
        'no_messages' => 'Todavía no hay mensajes de contacto.',
        'name_column' => 'Nombre',
        'email_column' => 'Correo electrónico',
        'message_column' => 'Mensaje',
        'date_column' => 'Fecha',
        'read_column' => 'Leído',
        'search_placeholder' => 'Buscar mensajes...',
        'filter_unread' => 'No leídos',
        'filter_read' => 'Leídos',
        'filter_all' => 'Todos',
        'mark_read' => 'Marcar como leído',
        'mark_unread' => 'Marcar como no leído',
        'delete_confirm_header' => 'Eliminar mensaje',
        'delete_confirm_message' => '¿Seguro que quieres eliminar el mensaje de ":name"?',
        'delete_warning' => 'Esta acción no se puede deshacer.',
        'delete_success' => 'Mensaje eliminado.',
        'delete_error' => 'No se pudo eliminar el mensaje.',
        'update_error' => 'No se pudo actualizar el mensaje.',
        'cancel' => 'Cancelar',
        'delete' => 'Eliminar',
    ],
];
