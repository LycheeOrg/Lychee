<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Bakım',
    'intro' => 'Bu sayfa albümlerinizi elle yeniden sıralamanızı ve onarmanızı sağlar.<br />Herhangi bir değişiklik yapmadan önce Nested Set ağaç yapıları hakkında bilgi edinmenizi önemle tavsiye ederiz.',
    'warning' => 'Burada Lychee kurulumunuzu gerçekten bozabilirsiniz; değerleri kendi sorumluluğunuzda değiştirin.',
    'help' => [
        'header' => 'Yardım',
        'hover' => 'İlgili albümleri vurgulamak için kimliklerin veya başlıkların üzerine gelin.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Sol</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Sağ</span>',
        'convenience' => 'Kolaylık olması için <i class="pi pi-angle-up" ></i> ve <i class="pi pi-angle-down" ></i> düğmeleri, %s ve %s değerlerini yayılımla birlikte sırasıyla +1 ve -1 değiştirmenizi sağlar.',
        'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left" ></i> ve <i class="text-warning-600 pi pi-chevron-circle-right" ></i> simgeleri, %s (ve sırasıyla %s) değerinin başka bir yerde yinelendiğini gösterir.',
        'parent-marked' => 'İşaretli <span class="font-bold text-danger-600">Üst Kimlik</span>, %s ve %s değerlerinin Nested Set ağaç yapısını karşılamadığını gösterir. <span class="font-bold text-danger-600">Üst Kimlik</span> değerini veya %s/%s değerlerini düzenleyin.',
        'slowness' => 'Albüm sayısı fazla olduğunda bu sayfa yavaş çalışır.',
    ],
    'buttons' => [
        'reset' => 'Sıfırla',
        'check' => 'Denetle',
        'apply' => 'Uygula',
    ],
    'no-changes' => 'Uygulanacak değişiklik yok.',
    'table' => [
        'title' => 'Başlık',
        'left' => 'Sol',
        'right' => 'Sağ',
        'id' => 'Id',
        'parent' => 'Üst Kimlik',
    ],
    'errors' => [
        'invalid' => 'Geçersiz ağaç!',
        'invalid_details' => 'Kesinlikle bozuk bir duruma yol açacağı için bu uygulanmıyor.',
        'invalid_left' => '%s albümünün sol değeri geçersiz.',
        'invalid_right' => '%s albümünün sağ değeri geçersiz.',
        'invalid_left_right' => '%s albümünün sol/sağ değerleri geçersiz. Sol değer sağ değerden kesinlikle küçük olmalıdır: %s < %s.',
        'duplicate_left' => '%s albümünün sol değeri %s yinelenmiş.',
        'duplicate_right' => '%s albümünün sağ değeri %s yinelenmiş.',
        'parent' => '%s albümünün beklenmeyen bir üst kimliği var: %s.',
        'unknown' => '%s albümünde bilinmeyen bir hata var.',
    ],
];
