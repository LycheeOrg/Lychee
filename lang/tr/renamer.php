<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Yeniden Adlandırma Kuralları',

    // Modal titles
    'create_rule' => 'Yeniden Adlandırma Kuralı Oluştur',
    'edit_rule' => 'Yeniden Adlandırma Kuralını Düzenle',

    // Form fields
    'rule_name' => 'Kural Adı',
    'description' => 'Açıklama',
    'pattern' => 'Desen',
    'replacement' => 'Yerine Konan',
    'mode' => 'Mod',
    'order' => 'Sıra',
    'enabled' => 'Etkin',
    'photo_rule' => 'Fotoğraflara uygulanan kural',
    'album_rule' => 'Albümlere uygulanan kural',

    // Form placeholders and help text
    'description_placeholder' => 'Bu kuralın ne yaptığına dair isteğe bağlı açıklama',
    'pattern_help' => 'Eşleşecek desen (ör. IMG_, DSC_)',
    'replacement_help' => 'Yerine konacak metin (ör. Photo_, Camera_)',
    'order_help' => 'Düşük numaralar önce işlenir (1 = en yüksek öncelik)',
    'enabled_help' => '(Yeniden adlandırma sırasında yalnızca etkin kurallar uygulanır)',

    // Mode options
    'mode_first' => 'İlk eşleşme',
    'mode_all' => 'Tüm eşleşmeler',
    'mode_regex' => 'Düzenli ifade',
    'mode_trim' => 'Boşlukları kırp',
    'mode_strtolower' => 'küçük harf',
    'mode_strtoupper' => 'BÜYÜK HARF',
    'mode_ucwords' => 'Her Kelimenin İlk Harfi Büyük',
    'mode_ucfirst' => 'İlk harf büyük',

    'mode_first_description' => 'Yalnızca ilk eşleşmeyi değiştir',
    'mode_all_description' => 'Tüm eşleşmeleri değiştir',
    'mode_regex_description' => 'Düzenli ifade ile desen eşleştirme kullan',
    'mode_trim_description' => 'Baştaki ve sondaki boşlukları kırp',
    'mode_strtolower_description' => 'Metni küçük harfe dönüştür',
    'mode_strtoupper_description' => 'Metni BÜYÜK HARFE dönüştür',
    'mode_ucwords_description' => 'Her kelimenin ilk harfini büyük yap',
    'mode_ucfirst_description' => 'Yalnızca ilk harfi büyük yap',

    'regex_help' => 'Desenleri eşleştirmek için düzenli ifadeler kullanın. Örneğin <code>IMG_1234.jpeg</code> adını <code>1234_JPG.jpeg</code> olarak değiştirmek için aranan ifade olarak <code>/IMG_(\\d+)/</code>, yerine konan olarak <code>$1_JPG</code> kullanabilirsiniz. Daha fazla açıklama ve örneği aşağıdaki bağlantılarda bulabilirsiniz.',

    // Buttons
    'cancel' => 'İptal',
    'create' => 'Oluştur',
    'update' => 'Güncelle',
    'create_first_rule' => 'İlk kuralınızı oluşturun',

    // Validation messages
    'rule_name_required' => 'Kural adı zorunludur',
    'pattern_required' => 'Desen zorunludur',
    'replacement_required' => 'Yerine konan metin zorunludur',
    'mode_required' => 'Mod zorunludur',
    'order_positive' => 'Sıra pozitif bir sayı olmalıdır',

    // Success messages
    'rule_created' => 'Yeniden adlandırma kuralı başarıyla oluşturuldu',
    'rule_updated' => 'Yeniden adlandırma kuralı başarıyla güncellendi',
    'rule_deleted' => 'Yeniden adlandırma kuralı başarıyla silindi',

    // Error messages
    'failed_to_create' => 'Yeniden adlandırma kuralı oluşturulamadı',
    'failed_to_update' => 'Yeniden adlandırma kuralı güncellenemedi',
    'failed_to_delete' => 'Yeniden adlandırma kuralı silinemedi',
    'failed_to_load' => 'Yeniden adlandırma kuralları yüklenemedi',

    // List view
    'rules_count' => ':count kural',
    'no_rules' => 'Yeniden adlandırma kuralı bulunamadı',
    'loading' => 'Yeniden adlandırma kuralları yükleniyor...',
    'pattern_label' => 'Desen',
    'replace_with_label' => 'Şununla değiştir',
    'photo' => 'Fotoğraf',
    'album' => 'Albüm',

    // Delete confirmation
    'confirm_delete_header' => 'Silmeyi Onayla',
    'confirm_delete_message' => '":rule" kuralını silmek istediğinizden emin misiniz?',
    'delete' => 'Sil',

    // Status messages
    'success' => 'Başarılı',
    'error' => 'Hata',

    // Placeholders
    'select_mode' => 'Yeniden adlandırma modunu seçin',
    'execution_order' => 'Yürütme sırası',

    // Test functionality
    'test_input_placeholder' => 'Yeniden adlandırma kurallarınızı test etmek için bir dosya adı girin (ör. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Sonuç',
    'test_failed' => 'Yeniden adlandırma kuralları test edilemedi',
    'apply_photo_rules' => 'Fotoğraf kurallarını uygula',
    'apply_album_rules' => 'Albüm kurallarını uygula',
];
