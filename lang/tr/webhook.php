<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Fotoğraflar eklendiğinde, taşındığında veya silindiğinde tetiklenen giden webhook\'ları yapılandırın.',

    // Empty state
    'no_webhooks' => 'Henüz yapılandırılmış webhook yok.',
    'create_first' => 'İlk webhook\'unuzu oluşturun',

    // Table columns
    'col_name' => 'Ad',
    'col_event' => 'Olay',
    'col_method' => 'Yöntem',
    'col_url' => 'URL',
    'col_format' => 'Biçim',
    'col_enabled' => 'Etkin',
    'col_actions' => 'İşlemler',

    // Event labels
    'event_photo_add' => 'Fotoğraf eklendi',
    'event_photo_move' => 'Fotoğraf taşındı',
    'event_photo_delete' => 'Fotoğraf silindi',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Sorgu dizesi',

    // Buttons
    'create' => 'Webhook oluştur',
    'edit' => 'Düzenle',
    'delete' => 'Sil',
    'cancel' => 'İptal',
    'save' => 'Kaydet',

    // Form fields
    'field_name' => 'Ad',
    'field_name_placeholder' => 'örn. Webhook\'um',
    'field_event' => 'Olay',
    'field_method' => 'HTTP yöntemi',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Payload biçimi',
    'field_enabled' => 'Etkin',
    'field_secret' => 'Gizli anahtar',
    'field_secret_placeholder' => 'Mevcut gizli anahtarı korumak için boş bırakın',
    'field_secret_header' => 'Gizli anahtar başlığı',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Fotoğraf kimliğini gönder',
    'field_send_album_id' => 'Albüm kimliğini gönder',
    'field_send_title' => 'Başlığı gönder',
    'field_send_size_variants' => 'Boyut varyantlarını gönder',

    // Modal titles
    'modal_create_title' => 'Webhook oluştur',
    'modal_edit_title' => 'Webhook\'u düzenle',

    // Delete confirmation
    'confirm_delete_header' => 'Webhook\'u sil',
    'confirm_delete_message' => '":name" webhook\'unu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
    'delete_warning' => 'Bu işlem geri alınamaz.',

    // Toasts
    'created' => 'Webhook başarıyla oluşturuldu.',
    'updated' => 'Webhook başarıyla güncellendi.',
    'deleted' => 'Webhook başarıyla silindi.',
    'error_load' => 'Webhook\'lar yüklenemedi.',
    'error_save' => 'Webhook kaydedilemedi.',
    'error_delete' => 'Webhook silinemedi.',

    // Secret badge
    'has_secret' => 'Gizli anahtar ayarlı',
    'no_secret' => 'Gizli anahtar yok',
];
