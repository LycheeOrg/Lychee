<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Kullanıcılar',
    'description' => 'Buradan Lychee kurulumunuzun kullanıcılarını yönetebilirsiniz. Kullanıcı oluşturabilir, düzenleyebilir ve silebilirsiniz.',
    'create' => 'Yeni kullanıcı oluştur',
    'username' => 'Kullanıcı adı',
    'password' => 'Parola',
    'legend' => 'Açıklama',
    'upload_rights' => 'Seçildiğinde kullanıcı içerik yükleyebilir.',
    'edit_rights' => 'Seçildiğinde kullanıcı profilini (kullanıcı adı, parola) değiştirebilir.',
    'upload_trust_level' => 'Yükleme güven düzeyi — yüklemelerin hemen herkese açık olup olmayacağını belirler.',

    'quota' => 'Ayarlandığında kullanıcının resimler için bir alan kotası olur (kB cinsinden).',
    'user_deleted' => 'Kullanıcı silindi',
    'user_created' => 'Kullanıcı oluşturuldu',
    'user_updated' => 'Kullanıcı güncellendi',
    'change_saved' => 'Değişiklik kaydedildi!',
    'create_edit' => [
        'upload_rights' => 'Kullanıcı içerik yükleyebilir.',
        'edit_rights' => 'Kullanıcı profilini (kullanıcı adı, parola) değiştirebilir.',
        'admin_rights' => 'Kullanıcının yönetici hakları var.',
        'upload_trust_level' => 'Yükleme güven düzeyi',
        'upload_trust_level_check' => 'Denetle – yüklemeler herkese açık olmadan önce yönetici onayı gerektirir.',
        'upload_trust_level_monitor' => 'İzle – yüklemeler, içerik nedeniyle işaretlenmedikçe herkese açıktır.',
        'upload_trust_level_trust_but_verify' => 'Güven ama doğrula – yüklemeler herkese açıktır; inceleme bulguları otomatik onaylanır, engelleme bulguları yapılandırılabilir.',
        'upload_trust_level_trusted' => 'Güvenilir – yüklemeler hemen herkese açık olur.',
        'trust_level_options' => [
            'trusted' => 'Güvenilir',
            'trust_but_verify' => 'Güven ama doğrula',
            'monitor' => 'İzle',
            'check' => 'Denetle',
        ],
        'quota' => 'Kullanıcının kota sınırı var.',
        'quota_kb' => 'kB cinsinden kota (varsayılan için 0)',
        'note' => 'Yönetici notu (herkese açık değil)',
        'create' => 'Oluştur',
        'edit' => 'Düzenle',
    ],
    'invite' => [
        'button' => 'Kullanıcı davet et',
        'links_are_not_revokable' => 'Davet bağlantıları iptal edilemez.',
        'link_is_valid_x_days' => 'Bu bağlantı %d gün geçerlidir.',
    ],
    'line' => [
        'owner' => 'Sahip',
        'admin' => 'Yönetici kullanıcı',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
    ],
];
