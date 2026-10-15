<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Toplu Albüm Düzenleme',
    'description' => 'Birden fazla albümün meta verilerini ve görünürlük ayarlarını tek seferde düzenleyin.',
    'warning' => 'Burada yapılan değişiklikler hemen uygulanır ve geri alınamaz. Etiket albümleri gösterilmez.',

    // Table columns
    'col_title' => 'Başlık',
    'col_owner' => 'Sahip',
    'col_license' => 'Lisans',
    'col_is_nsfw' => 'Hassas',
    'col_is_public' => 'Herkese açık',
    'col_is_link_required' => 'Bağlantı',
    'col_grants_full_photo_access' => 'Tam Fotoğraf',
    'col_grants_download' => 'İndirme',
    'col_grants_upload' => 'Yükleme',
    'col_photo_sorting' => 'Fotoğraf Sıralama',
    'col_album_sorting' => 'Albüm Sıralama',
    'col_created_at' => 'Oluşturulma',

    // Filter
    'filter_placeholder' => 'Başlığa göre ara...',

    // Pagination
    'per_page' => 'Sayfa başına',
    'total_selected' => ':n albüm seçildi|:n albüm seçildi',
    'select_all_page' => 'Bu sayfadakilerin tümünü seç',
    'select_all_matching' => 'Eşleşenlerin tümünü seç',
    'cap_warning' => 'Yalnızca ilk 1.000 albüm seçildi.',

    // Mode toggle
    'mode_paginated' => 'Sayfalı',
    'mode_infinite' => 'Sonsuz kaydırma',

    // Action buttons
    'action_delete' => 'Sil',
    'action_set_owner' => 'Sahibi Belirle',
    'action_edit_fields' => 'Alanları Düzenle',

    // Edit Fields modal
    'edit_fields_title' => 'Alanları Düzenle',
    'edit_fields_description' => 'Yalnızca işaretli alanlar güncellenir. Boş değerler alanı temizler.',
    'section_metadata' => 'Meta veriler',
    'section_visibility' => 'Görünürlük',
    'field_description' => 'Açıklama',
    'field_copyright' => 'Telif hakkı',
    'field_license' => 'Lisans',
    'field_photo_layout' => 'Fotoğraf Düzeni',
    'field_photo_sorting_col' => 'Fotoğraf Sıralama Sütunu',
    'field_photo_sorting_order' => 'Fotoğraf Sıralama Düzeni',
    'field_album_sorting_col' => 'Albüm Sıralama Sütunu',
    'field_album_sorting_order' => 'Albüm Sıralama Düzeni',
    'field_album_thumb_aspect_ratio' => 'Küçük Resim En-Boy Oranı',
    'field_album_timeline' => 'Albüm Zaman Çizelgesi',
    'field_photo_timeline' => 'Fotoğraf Zaman Çizelgesi',
    'field_published_at' => 'Flow Yayın Tarihi',
    'field_is_nsfw' => 'Hassas',
    'field_is_public' => 'Herkese açık',
    'field_is_link_required' => 'Bağlantı Gerekli',
    'field_grants_full_photo_access' => 'Tam Fotoğraf Erişimi',
    'field_grants_download' => 'İndirme',
    'field_grants_upload' => 'Yükleme (SE)',
    'apply' => 'Uygula',
    'cancel' => 'İptal',

    // Set Owner modal
    'set_owner_title' => 'Sahibi Belirle',
    'set_owner_description' => 'Seçilen tüm albümler kök düzeye taşınacak ve alt öğeleri de aktarılacaktır.',
    'set_owner_select_user' => 'Yeni sahibi seçin',
    'transfer' => 'Aktar',

    // Delete confirmation modal
    'delete_title' => 'Albümleri Sil',
    'delete_confirm' => ':count albümü ve tüm alt albümlerini ve fotoğraflarını kalıcı olarak silmek üzeresiniz. Bu işlem geri alınamaz.|:count albümü ve tüm alt albümlerini ve fotoğraflarını kalıcı olarak silmek üzeresiniz. Bu işlem geri alınamaz.',
    'confirm_delete' => 'Silmeyi Onayla',

    // Toasts
    'success_patch' => 'Albümler başarıyla güncellendi.',
    'success_set_owner' => 'Sahiplik başarıyla aktarıldı.',
    'success_delete' => 'Albümler başarıyla silindi.',
    'error_load' => 'Albümler yüklenemedi.',
    'error_load_ids' => 'Albüm kimlikleri yüklenemedi.',
    'error_patch' => 'Albümler güncellenemedi.',
    'error_missing_timezone' => 'Lütfen yayın tarihi için bir saat dilimi seçin.',
    'error_set_owner' => 'Sahiplik aktarılamadı.',
    'error_delete' => 'Albümler silinemedi.',
    'error_load_users' => 'Kullanıcılar yüklenemedi.',
];
