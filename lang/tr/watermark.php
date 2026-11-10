<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Filigran önizlemesi',
        'se_required' => 'Filigran modülü için Lychee Supporter Edition (SE) veya SE Preview etkin olmalıdır.',

        'section_settings' => 'Filigran ayarları',
        'section_preview' => 'Canlı önizleme',
        'disclaimer' => 'Bu önizleme, filigranın nasıl görüneceği hakkında fikir verir. Gerçek fotoğraflarınızdaki sonuç biraz farklı olabilir.',

        'watermark_photo_id' => 'Filigran görseli kimliği',
        'watermark_photo_id_placeholder' => '24 karakterlik fotoğraf kimliği',
        'watermark_photo_id_hint' => 'Filigran olarak kullanılan görselin fotoğraf kimliği. Bir fotoğraf açın ve URL\'deki son 24 karakteri kopyalayın.',

        'preview_photo_id' => 'Arka plan fotoğrafı kimliği',
        'preview_photo_id_placeholder' => '24 karakterlik fotoğraf kimliği',
        'preview_photo_id_hint' => 'Önizlemede arka plan olarak kullanılacak bir fotoğraf kimliği girin.',

        'size' => 'Boyut (:value%)',
        'opacity' => 'Opaklık (:value%)',
        'position' => 'Konum',
        'position_options' => [
            'top-left' => 'Sol üst',
            'top' => 'Üst orta',
            'top-right' => 'Sağ üst',
            'left' => 'Sol orta',
            'center' => 'Orta',
            'right' => 'Sağ orta',
            'bottom-left' => 'Sol alt',
            'bottom' => 'Alt orta',
            'bottom-right' => 'Sağ alt',
        ],

        'section_shift' => 'Kaydırma / Ofset',
        'shift_type' => 'Kaydırma birimi',
        'shift_type_options' => [
            'relative' => 'Göreli (%)',
            'absolute' => 'Mutlak (px)',
        ],
        'shift_type_hint' => 'Göreli kaydırmalar görsel boyutunun yüzdesidir; mutlak kaydırmalar sabit bir piksel sayısıdır.',
        'shift_mode_use_slider' => 'Kaydırıcı kullan',
        'shift_mode_use_classic' => 'Sayı girişi kullan',
        'shift_x' => 'Yatay kaydırma (:value)',
        'shift_x_direction_options' => [
            'left' => 'Sol',
            'right' => 'Sağ',
        ],
        'shift_y' => 'Dikey kaydırma (:value)',
        'shift_y_direction_options' => [
            'up' => 'Yukarı',
            'down' => 'Aşağı',
        ],
        'reset_to_zero' => '0\'a sıfırla',

        'save' => 'Ayarları kaydet',
        'saved' => 'Filigran ayarları kaydedildi.',
        'save_error' => 'Filigran ayarları kaydedilemedi.',
        'save_requires_se' => 'Filigran ayarlarını kaydetmek için tam bir Supporter Edition (SE) lisansı gerekir. SE Preview yalnızca efektin önizlenmesine izin verir.',

        'no_watermark_image' => 'Yapılandırılmış filigran görseli yok. Önizlemek için bir filigran fotoğraf kimliği girin ve "Yükle" düğmesine tıklayın.',
        'no_preview_photo' => 'Filigran katmanını önizlemek için yukarıya bir arka plan fotoğrafı kimliği girin.',
        'photo_load_error' => 'Fotoğraf yüklenemedi. Kimliğin doğru olduğundan ve erişim izniniz olduğundan emin olun.',
        'watermark_load_error' => 'Filigran görseli yüklenemedi. Fotoğraf kimliğinin doğru olduğundan emin olun.',
    ],
];
