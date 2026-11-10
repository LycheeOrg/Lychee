<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Açılış Sayfası',
    'tab_settings' => 'Ayarlar',
    'tab_links' => 'Bağlantılar',
    'tab_featured' => 'Öne Çıkanlar',

    'section_layout' => 'Düzen ve Yapı',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Arka Plan (Yatay)',
    'section_background_portrait' => 'Arka Plan (Dikey)',
    'section_cta_position' => 'Eylem Çağrısı Konumu',
    'section_login_position' => 'Giriş Paneli',
    'section_meridian' => 'Meridian Çizgileri',
    'section_content' => 'İçerik',

    'field_layout' => 'Düzen',
    'field_intro_screen_enabled' => 'Giriş açılış ekranı',
    'field_hero_text_position' => 'Hero metni konumu',
    'field_hero_text_color' => 'Hero metni rengi',
    'field_hero_text_opacity' => 'Hero metni opaklığı',
    'field_animation_preset' => 'Animasyon ön ayarı',
    'field_backdrop_opacity' => 'Arka plan opaklığı (:value%)',
    'field_cta_text' => 'metin',
    'field_cta_text_placeholder' => 'Düzenin varsayılanı için boş bırakın',
    'field_about_enabled' => 'Hakkında bölümü',
    'field_about_text' => 'Hakkında metni',

    'field_background_mode' => 'Kaynak',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Fotoğraf ID',
        'random' => 'Rastgele herkese açık fotoğraf',
        'latest_album_cover' => 'En son albüm kapağı',
        'random_from_album' => 'Albümden rastgele fotoğraf',
    ],
    'field_background_url' => 'Görsel URL\'si',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Fotoğraf ID',
    'field_background_photo_id_placeholder' => '24 karakterlik fotoğraf ID\'si',
    'field_background_photo_id_hint' => 'Kullanılacak görselin fotoğraf ID\'si. Bir fotoğrafı açın ve URL\'deki son 24 karakteri kopyalayın.',
    'field_background_album_id' => 'Albüm ID',
    'field_background_album_id_placeholder' => '24 karakterlik albüm ID\'si',
    'background_load_error' => 'Fotoğraf yüklenemedi. ID\'nin doğru olduğundan ve erişim izniniz bulunduğundan emin olun.',
    'background_mode_hint' => [
        'random' => 'Her sayfa yüklemesinde rastgele bir herkese açık fotoğraf seçilir.',
        'latest_album_cover' => 'En son yayımlanan herkese açık albümün kapağı kullanılır.',
        'random_from_album' => 'Her sayfa yüklemesinde bu albümden rastgele bir fotoğraf seçilir. Önizleme yalnızca kaydettikten sonra güncellenir.',
    ],

    'preview_orientation_landscape' => 'Yatay arka planı önizle',
    'preview_orientation_portrait' => 'Dikey arka planı önizle',

    'field_cta_position' => 'Konum',
    'cta_position_options' => [
        'top-left' => 'Sol Üst',
        'top' => 'Üst Orta',
        'top-right' => 'Sağ Üst',
        'left' => 'Sol Orta',
        'center' => 'Orta',
        'right' => 'Sağ Orta',
        'bottom-left' => 'Sol Alt',
        'bottom' => 'Alt Orta',
        'bottom-right' => 'Sağ Alt',
    ],
    'field_cta_shift_type' => 'Kaydırma birimi',
    'cta_shift_type_options' => [
        'relative' => 'Göreli (%)',
        'absolute' => 'Mutlak (px)',
    ],
    'cta_shift_type_hint' => 'Göreli kaydırmalar görüntü alanı boyutunun bir yüzdesidir; mutlak kaydırmalar sabit sayıda pikseldir.',
    'field_cta_shift_x' => 'Yatay Kaydırma (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Sol',
        'right' => 'Sağ',
    ],
    'field_cta_shift_y' => 'Dikey Kaydırma (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Yukarı',
        'down' => 'Aşağı',
    ],
    'reset_to_zero' => '0\'a sıfırla',

    'field_login_position' => 'Konum',
    'login_position_options' => [
        'side' => 'Yan',
        'center' => 'Orta',
    ],
    'field_login_position_hint' => 'Dar ekranlarda giriş paneli, bu ayardan bağımsız olarak her zaman hero\'nun altında ortalanır. Animasyon ön ayarı "Slide reveal" veya "Parallax scroll" olduğunda okuma yönündeki kenardan kayarak gelir.',

    'field_meridian_explore_offset' => 'Keşfet etiketi yüksekliği (:value%)',
    'field_meridian_contact_offset' => 'İletişim etiketi yüksekliği (:value%)',
    'field_meridian_offset_hint' => 'Etiketin tam yükseklikteki her çizgi boyunca ne kadar aşağıda durduğu: %0 en üst, %100 en alttır.',
    'field_meridian_explore_line_position' => 'Keşfet çizgisi konumu (:value%)',
    'field_meridian_contact_line_position' => 'İletişim çizgisi konumu (:value%)',
    'field_meridian_line_position_hint' => 'Her çizginin sol kenardan ne kadar uzakta durduğu: %0 sol kenar, %100 sağ kenardır. İletişim çizgisi kapalıyken keşfet çizgisi her zaman kendini ortalar.',

    'preview_title' => 'Canlı Önizleme',
    'preview_hint' => 'Siz düzenledikçe anında güncellenir — Kaydet\'e tıklayana kadar hiçbir şey kaydedilmez.',
    'flat_list_hint' => 'Bu ayarlar, genel düz Ayarlar listesinden de düzenlenebilir.',

    'save' => 'Kaydet',
    'saved' => 'Açılış sayfası ayarları kaydedildi.',
    'save_error' => 'Açılış sayfası ayarları kaydedilemedi.',

    'se_required' => 'Bu düzen/ön ayar Lychee SE gerektirir.',
];
