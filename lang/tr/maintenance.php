<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maintenance Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Bakım',
    'description' => 'Bu sayfada, Lychee kurulumunuzun sorunsuz ve düzgün çalışması için gereken tüm işlemleri bulabilirsiniz.',
    'cleaning' => [
        'title' => '%s temizleniyor',
        'result' => '%s silindi.',
        'description' => '<span class="font-mono">%s</span> içindeki tüm içeriği kaldır',
        'button' => 'Temizle',
    ],
    'duplicate-finder' => [
        'title' => 'Yinelenenler',
        'description' => 'Bu modül, resimler arasındaki olası yinelenenleri sayar.',
        'duplicates-all' => 'Tüm albümlerdeki yinelenenler',
        'duplicates-title' => 'Albüm başına başlık yinelenenleri',
        'duplicates-per-album' => 'Albüm başına yinelenenler',
        'show' => 'Yinelenenleri göster',
        'load' => 'Sayıları yükle',
    ],
    'fix-jobs' => [
        'title' => 'İş Geçmişini Düzeltme',
        'description' => '<span class="text-ready-400">%s</span> veya <span class="text-primary-500">%s</span> durumundaki işleri <span class="text-danger-700">%s</span> olarak işaretle.',
        'button' => 'İş geçmişini düzelt',
    ],
    'gen-sizevariants' => [
        'title' => 'Eksik %s',
        'description' => 'Oluşturulabilecek %d %s bulundu.',
        'button' => 'Oluştur!',
        'success' => '%d %s başarıyla oluşturuldu.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Eksik dosya boyutları',
        'description' => 'Dosya boyutu olmayan %d küçük varyant bulundu.',
        'button' => 'Verileri getir!',
        'success' => '%d küçük varyantın boyutu başarıyla hesaplandı.',
    ],
    'fix-tree' => [
        'title' => 'Ağaç istatistikleri',
        'Oddness' => 'Tutarsızlık',
        'Duplicates' => 'Yinelenenler',
        'Wrong parents' => 'Yanlış üst öğeler',
        'Missing parents' => 'Eksik üst öğeler',
        'button' => 'Ağacı düzelt',
    ],
    'optimize' => [
        'title' => 'Veritabanını Optimize Et',
        'description' => 'Kurulumunuzda yavaşlama fark ederseniz, bunun nedeni veritabanınızda gerekli tüm dizinlerin bulunmaması olabilir.',
        'button' => 'Veritabanını Optimize Et',
    ],
    'missing-palettes' => [
        'title' => 'Eksik Paletler',
        'description' => '%d eksik palet bulundu.',
        'button' => 'Eksikleri oluştur',
    ],
    'statistics-check' => [
        'title' => 'İstatistik Bütünlüğü Denetimi',
        'missing_photos' => '%d fotoğraf istatistiği eksik.',
        'missing_albums' => '%d albüm istatistiği eksik.',
        'button' => 'Eksikleri oluştur',
    ],
    'flush-cache' => [
        'title' => 'Önbelleği Temizle',
        'description' => 'Geçersiz kılma sorunlarını çözmek için tüm kullanıcıların önbelleğini temizleyin.',
        'button' => 'Temizle',
    ],
    'old-orders' => [
        'title' => 'Eski Siparişler',
        'description' => '%d eski sipariş bulundu.<br/><br/>Eski sipariş; 14 günden eski olan, ilişkili bir kullanıcısı bulunmayan ve hâlâ ödeme bekleyen ya da hiç ürün içermeyen sipariştir.',
        'button' => 'Eski siparişleri sil',
    ],
    'fulfill-orders' => [
        'title' => 'Tamamlanacak siparişler',
        'description' => 'İçeriği henüz kullanıma sunulmamış %d sipariş bulundu.<br/><br/>Mümkün olduğunda içerik atamak için düğmeye tıklayın.',
        'button' => 'Siparişleri tamamla',
    ],
    'fulfill-precompute' => [
        'title' => 'Albümün Önceden Hesaplanan Alanları',
        'description' => 'Önceden hesaplanan alanları eksik %d albüm bulundu.<br/><br/>Şunu çalıştırmaya eşdeğerdir: php artisan lychee:recompute-album-fields',
        'button' => 'Alanları hesapla',
    ],
    'flush-queue' => [
        'title' => 'Kuyruğu Temizle',
        'description' => 'Kuyrukta bekleyen %d iş bulundu.<br/><br/>DİKKAT: Kuyruğu temizlemek bekleyen tüm işleri kalıcı olarak siler. Bu işlem geri alınamaz.',
        'button' => 'Kuyruğu temizle',
    ],
    'backfill-album-sizes' => [
        'title' => 'Albüm Boyutu İstatistikleri',
        'description' => 'Boyut istatistikleri olmayan %d albüm bulundu.<br/><br/>Şunu çalıştırmaya eşdeğerdir: php artisan lychee:recompute-album-sizes',
        'button' => 'Boyutları hesapla',
    ],

    'face_quality' => [
        'title' => 'Yüz Kalitesi İncelemesi',
        'description' => 'Yüz algılamalarını kalite puanına göre inceleyin ve düşük kaliteli veya hatalı yüzleri yok sayın.',
        'sort_by' => 'Sırala:',
        'sort_confidence' => 'Güven',
        'sort_blur' => 'Bulanıklık (Laplace)',
        'no_faces' => 'Uygun yüz yok. Her şey yolunda görünüyor!',
        'col_face' => 'Yüz',
        'col_person' => 'Kişi',
        'col_cluster' => 'Küme',
        'col_confidence' => 'Güven',
        'col_blur' => 'Bulanıklık Puanı',
        'col_actions' => 'İşlemler',
        'unassigned' => 'Atanmamış',
        'dismiss' => 'Yüzü yok say',
        'readd' => 'Yüzü yeniden ekle',
        'load_error' => 'Yüzler yüklenemedi.',
        'dismissed' => 'Yüz yok sayıldı.',
        'readded' => 'Yüz yeniden eklendi.',
        'dismiss_error' => 'Yüz yok sayılamadı.',
        'readd_error' => 'Yüz yeniden eklenemedi.',
        'batch_dismiss' => 'Seçilenleri yok say',
        'batch_dismissed' => ':count yüz yok sayıldı.',
        'batch_dismiss_error' => 'Seçilen yüzler yok sayılamadı.',
        'batch_reactivate' => 'Seçilenleri yeniden etkinleştir',
        'batch_reactivated' => ':count yüz yeniden etkinleştirildi.',
        'batch_reactivate_error' => 'Seçilen yüzler yeniden etkinleştirilemedi.',
        'show_dismissed' => 'Yok sayılanları göster',
        'show_active' => 'Etkin olanları göster',
        'show_unassigned' => 'Yalnızca atanmamışlar',
        'select_all' => 'Tümünü seç',
        'deselect_all' => 'Tüm seçimi kaldır',
        'selected_count' => ':count seçildi',
    ],
    'bulk-scan-faces' => [
        'description' => 'Henüz yüz tanıma için taranmamış %d fotoğraf bulundu.<br/><br/>AI Vision hizmetinin çalışıyor olması gerekir.',
    ],
    'run-clustering' => [
        'description' => 'AI Vision hizmetinde yüz kümelemeyi başlatın. Algılanan yüzleri benzerliğe göre gruplar, böylece onları kişilere atayabilirsiniz.',
        'success' => 'Kümeleme başarıyla başlatıldı.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Yok Sayılan Yüzleri İmha Et',
        'description' => '%d yok sayılmış yüz bulundu. Bunları imha etmek, kırpılmış dosyalarını ve gömmelerini kalıcı olarak siler.',
        'action' => 'Tümünü İmha Et',
        'success' => 'Yok sayılan yüzler başarıyla imha edildi.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Yüz Gömmelerini Eşitle',
        'description' => 'Yüz sayısı uyuşmazlığı algılandı (%d fark). Eşitleme, en güncel yüz verilerini AI Vision hizmetinden Lychee\'ye çeker.',
        'action' => 'Şimdi Eşitle',
        'success' => 'Yüz gömmeleri başarıyla eşitlendi.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Yüz Tarama Durumunu Sıfırla',
        'description' => 'Yüz tarama durumu beklemede takılı kalmış veya başarısız olan %d fotoğraf bulundu. Sıfırlamak, bunların yeniden taranmasına olanak tanır.',
        'action' => 'Tümünü Sıfırla',
        'success' => 'Yüz tarama durumları başarıyla sıfırlandı.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Sahipsiz Yüz Gömmelerini Temizle',
            'description' => 'AI Vision hizmeti, artık Lychee\'de bulunmayan en fazla %d gömme tutuyor olabilir. Temizleme, mevcut yüz listesini AI Vision\'a gönderir ve geriye kalan her şeyi kalıcı olarak siler.',
            'action' => 'Şimdi Temizle',
            'success' => 'Sahipsiz yüz gömmeleri başarıyla temizlendi.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Toplu NSFW Taraması',
        'description' => 'Taranmamış tüm fotoğrafları, yapılandırılmış ön ayarı kullanarak NSFW içerik için tarayın. NSFW sınıflandırma hizmetinin çalışıyor olması gerekir.',
        'button' => 'Taranmamışların Tümünü Tara',
        'success' => 'NSFW taraması başarıyla başlatıldı.',
    ],
];
