<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'v8 ön yüzünün gerçekte üzerine kurulduğu token ve bileşenler için canlı bir başvuru kaynağı. Bu sayfadaki her renk örneği ve kontrol, bugün kullanılan gerçek CSS özel özelliklerini ve Nuxt UI bileşenlerini okur; bu nedenle statik bir maket gibi asla güncelliğini yitirmez.',

    'sections' => [
        'foundations' => 'Temeller',
        'colors' => 'Renk paleti',
        'typography' => 'Tipografi',
        'buttons' => 'Düğmeler',
        'badges' => 'Rozetler ve çipler',
        'forms' => 'Form denetimleri',
        'feedback' => 'Geri bildirim ve durumlar',
        'radius' => 'Köşe yarıçapı ve yükseklik',
    ],

    'loading' => [
        'full_page' => 'Tam sayfa yükleyici',
        'full_page_description' => 'Yavaş, kademeli beliriş — bir görünüm yüklenirken tam ekran karartmanın arkasında kullanılır.',
        'mini_spinner' => 'Mini yükleme göstergesi',
        'mini_spinner_description' => 'Hızlı varyant, hemen görünür — düğmeler, listeler ve küçük yükleme durumları için satır içinde kullanılır.',
    ],

    'foundations' => [
        'system' => 'Bileşen kiti',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => 'Gövde yazı tipi',
        'body_font_value' => 'Libre Franklin, kendi sunucusunda barındırılan (Arapça/Farsça için Vazirmatn)',
        'mono_font' => 'Eş aralıklı yazı tipi',
        'mono_font_value' => 'Sistem eş aralıklı yazı tipi yığını (ui-monospace)',
        'icons' => 'Simge seti',
        'icons_value' => 'Lucide, Iconify aracılığıyla (UIcon)',
        'theme' => 'Tema',
        'theme_value' => 'Ayarlar\'dan değiştirilen tek bir koyu/açık tema — bölüm bazında geçersiz kılma yok',
    ],

    'colors' => [
        'primary' => 'Birincil — marka rengi, birincil eylemler, bağlantılar, etkin durumlar',
        'secondary' => 'İkincil — alternatif eylemler ve seçimler',
        'neutral' => 'Nötr — metin, kenarlıklar, yüzeyler (açık modda Slate, koyu modda Zinc)',
        'success' => 'Başarı — onaylar, olumlu durumlar',
        'warning' => 'Uyarı — dikkat, engellemeyen sorunlar',
        'error' => 'Hata — yıkıcı eylemler, başarısızlıklar',
        'info' => 'Bilgi — bilgilendirici mesajlar',
        'resolved_from' => ':token üzerinden canlı olarak çözümlendi',
    ],

    'typography' => [
        'display' => 'Gösterim',
        'heading' => 'Başlık',
        'body' => 'Gövde metni',
        'body_sample' => 'Pijamalı hasta yağız şoföre çabucak güvendi — albüm başlıkları, form etiketleri ve günlük metinler bu yazı tipi yığınıyla görüntülenir.',
        'mono' => 'Eş aralıklı',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'Metin vurgu ölçeği',
        'emphasis_sample' => 'Geçmiş yıllarda bugün çekilen fotoğraflar',
    ],

    'buttons' => [
        'intro' => 'Düğme sisteminin desteklediği tüm renkler, uygulamada gerçekten kullanılan varyantlarıyla.',
    ],

    'forms' => [
        'input_label' => 'Albüm başlığı',
        'input_placeholder' => 'Kapadokya\'da yaz',
        'select_label' => 'Sırala',
        'checkbox_label' => 'Hassas albümleri göster',
        'switch_label' => 'Herkese açık albüm',
        'switch_description' => 'Bağlantıya sahip herkes bu albümü görüntüleyebilir',
        'textarea_label' => 'Açıklama',
        'textarea_placeholder' => 'Bu albüm için bir açıklama ekleyin…',
        'radio_legend' => 'Etiket eşleştirme mantığı',
    ],

    'feedback' => [
        'alert_info' => 'Bu albüme yeni fotoğraflar eklendi.',
        'alert_success' => 'Ayarlar kaydedildi.',
        'alert_warning' => 'Bu albüm, bağlantıya sahip herkes tarafından görülebilir.',
        'alert_error' => 'Sunucuya ulaşılamadı — değişiklikler kaydedilmedi.',
        'empty_title' => 'Henüz paylaşım yok',
        'empty_description' => 'İlk albümünüzü paylaştığınızda paylaşılan albümler ve izinleri burada görünecek.',
    ],
];
