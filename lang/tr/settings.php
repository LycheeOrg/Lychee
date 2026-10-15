<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Ayarlar',
    'small_screen' => 'Bu sayfada daha iyi bir deneyim için,<br />daha büyük bir ekran kullanmanızı öneririz.',
    'tabs' => [
        'basic' => 'Temel',
        'all_settings' => 'Tüm ayarlar',
    ],
    'toasts' => [
        'change_saved' => 'Değişiklik kaydedildi!',
        'details' => 'Ayarlar istek doğrultusunda değiştirildi',
        'error' => 'Hata!',
        'error_load_css' => 'dist/user.css yüklenemedi',
        'error_load_js' => 'dist/custom.js yüklenemedi',
        'error_save_css' => 'CSS kaydedilemedi',
        'error_save_js' => 'JS kaydedilemedi',
        'thank_you' => 'Desteğiniz için teşekkür ederiz.',
        'reload' => 'Tüm işlevler için sayfanızı yeniden yükleyin.',
    ],
    'system' => [
        'header' => 'Sistem',
        'use_dark_mode' => 'Lychee için karanlık modu kullan',
        'language' => 'Lychee tarafından kullanılan dil',
        'nsfw_album_visibility' => 'Hassas albümleri varsayılan olarak görünür yap.',
        'nsfw_album_explanation' => 'Albüm herkese açıksa yine de erişilebilir; yalnızca görünümden gizlenir ve <b><kbd>H</kbd> tuşuna basılarak gösterilebilir</b>.',
        'cache_enabled' => 'Yanıtların önbelleğe alınmasını etkinleştir.',
        'cache_enabled_details' => 'Bu, Lychee\'nin yanıt süresini önemli ölçüde hızlandırır.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Parola korumalı albümler kullanıyorsanız bunu etkinleştirmemelisiniz.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Özel özelliklere erişin ve Lychee\'nin gelişimini destekleyin. <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE sürümünün</a> kilidini açın.',
        'preview' => 'Lychee SE özelliklerinin önizlemesini etkinleştir',
        'hide_call4action' => 'Bu Lychee SE kayıt formunu gizle. Lychee\'den olduğu gibi memnunum. :)',
        'hide_warning' => 'Etkinleştirilirse, lisans anahtarınızı kaydetmenin tek yolu yukarıdaki Diğer sekmesi olacaktır. Değişiklikler sayfa yeniden yüklendiğinde uygulanır.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Dropbox\'ınızdan fotoğraf içe aktarmak için, Dropbox web sitesinden alınmış geçerli bir drop-ins uygulama anahtarına ihtiyacınız vardır.',
        'api_key' => 'Dropbox API Anahtarı',
        'set_key' => 'Dropbox Anahtarını Ayarla',
    ],
    'gallery' => [
        'header' => 'Galeri',
        'photo_order_column' => 'Fotoğrafları sıralamak için kullanılan varsayılan sütun',
        'photo_order_direction' => 'Fotoğrafları sıralamak için kullanılan varsayılan düzen',
        'album_order_column' => 'Albümleri sıralamak için kullanılan varsayılan sütun',
        'album_order_direction' => 'Albümleri sıralamak için kullanılan varsayılan düzen',
        'aspect_ratio' => 'Albüm küçük resimleri için varsayılan en boy oranı',
        'photo_layout' => 'Resimlerin düzeni',
        'album_decoration' => 'Albüm kapağında süslemeleri göster (alt albüm ve/veya fotoğraf sayısı)',
        'album_decoration_direction' => 'Albüm süslemelerini yatay veya dikey hizala',
        'photo_overlay' => 'Varsayılan görsel kaplama bilgisi',
        'album_date_scrubber_enabled' => 'Albüm görünümlerinde tarih kaydırıcısını göster',
        'rounded_corners_enabled' => 'Fotoğraf ve albüm küçük resimlerinin köşelerini yuvarla',
        'album_border_enabled' => 'Fotoğraf ve albüm küçük resimlerinin çevresinde kenarlık göster',
        'photo_ken_burns_on_hover_enabled' => 'Fotoğrafın üzerine gelindiğinde Ken Burns efektini etkinleştir',
        'photo_ken_burns_on_hover_scale' => 'Ken Burns üzerine gelme yakınlaştırma miktarı (%)',
        'photo_ken_burns_on_hover_duration' => 'Ken Burns üzerine gelme yakınlaştırma süresi (saniye)',
        'license_default' => 'Albümler için kullanılan varsayılan lisans',
        'license_help' => 'Seçmek için yardım mı gerekiyor?',
    ],
    'geolocation' => [
        'header' => 'Coğrafi konum',
        'map_display' => 'GPS koordinatlarına göre haritayı göster',
        'map_display_public' => 'Anonim kullanıcıların haritaya erişmesine izin ver',
        'map_provider' => 'Harita sağlayıcısını belirler',
        'map_include_subalbums' => 'Alt albümlerdeki resimleri haritaya dahil eder',
        'location_decoding' => 'GPS konum çözümlemesini kullan',
        'location_show' => 'GPS koordinatlarından çıkarılan konumu göster',
        'location_show_public' => 'Anonim kullanıcılar GPS koordinatlarından çıkarılan konuma erişebilir',
        'gps_coordinate_display' => 'GPS koordinatlarını göster',
        'gps_coordinate_display_public' => 'Anonim kullanıcıların GPS koordinatlarına erişmesine izin ver',
    ],
    'cssjs' => [
        'header' => 'Özel CSS ve JS',
        'change_css' => 'CSS\'i değiştir',
        'change_js' => 'JS\'i değiştir',
    ],
    'all' => [
        'old_setting_style' => 'Eski ayar görünümü',
        'expert_settings' => 'Uzman Modu',
        'change_detected' => 'Bazı ayarlar değişti.',
        'save' => 'Kaydet',
        'back_to_settings' => 'Gruplanmış ayarlara dön',
    ],
    'tool_option' => [
        'disabled' => 'devre dışı',
        'enabled' => 'etkin',
        'discover' => 'keşfet',
    ],
    'groups' => [
        'general' => 'Genel',
        'system' => 'Sistem',
        'modules' => 'Modüller',
        'advanced' => 'Gelişmiş',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Yönetici panosunu kullan',
            'help' => 'İç içe yönetici alt menüsünü yeni yönetici panosu sayfasına giden tek bir bağlantıyla değiştirir.',
        ],
    ],
    'password_field' => [
        'set' => 'Ayarlandı',
        'not_set' => 'Ayarlanmadı',
        'placeholder' => 'Yeni parola',
        'clear' => 'Temizle',
    ],
];
