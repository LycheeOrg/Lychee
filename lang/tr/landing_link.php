<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'Ek bağlantılar',
    'description' => 'Açılış sayfasının gezinme çubuğunda ve/veya alt bilgisinde gösterilen, istediğiniz sırada ek bağlantıların listesini yönetin.',

    // Empty state
    'no_links' => 'Henüz yapılandırılmış ek bağlantı yok.',
    'create_first' => 'İlk bağlantınızı oluşturun',

    // Table columns
    'col_label' => 'Etiket',
    'col_url' => 'URL',
    'col_placement' => 'Yerleşim',
    'col_enabled' => 'Etkin',
    'col_actions' => 'İşlemler',

    // Placement labels
    'placement_nav' => 'Gezinme',
    'placement_footer' => 'Alt bilgi',
    'placement_both' => 'Gezinme ve alt bilgi',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Yerleşik',
    'built_in_cannot_delete' => 'Bu yerleşik bağlantı silinemez. Bunun yerine devre dışı bırakın veya diğer bağlantılar gibi yeniden sıralayın.',
    'built_in_url_hint' => 'Bu yerleşik bir bağlantıdır ve hedefi değiştirilemez.',

    // Buttons
    'create' => 'Bağlantı oluştur',
    'edit' => 'Düzenle',
    'delete' => 'Sil',
    'cancel' => 'İptal',
    'save' => 'Kaydet',

    // Form fields
    'field_label' => 'Etiket',
    'field_label_placeholder' => 'örn. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Yerleşim',
    'field_open_in_new_tab' => 'Yeni sekmede aç',
    'field_enabled' => 'Etkin',

    // Modal titles
    'modal_create_title' => 'Bağlantı oluştur',
    'modal_edit_title' => 'Bağlantıyı düzenle',

    // Delete confirmation
    'confirm_delete_header' => 'Bağlantıyı sil',
    'confirm_delete_message' => '":label" bağlantısını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',

    // Toasts
    'created' => 'Bağlantı başarıyla oluşturuldu.',
    'updated' => 'Bağlantı başarıyla güncellendi.',
    'deleted' => 'Bağlantı başarıyla silindi.',
    'reordered' => 'Bağlantılar başarıyla yeniden sıralandı.',
    'error_load' => 'Bağlantılar yüklenemedi.',
    'error_save' => 'Bağlantı kaydedilemedi.',
    'error_delete' => 'Bağlantı silinemedi.',
    'error_reorder' => 'Bağlantılar yeniden sıralanamadı.',
];
