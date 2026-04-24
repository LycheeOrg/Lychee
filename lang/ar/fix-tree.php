<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'الصيانة',
    'intro' => 'تتيح لك هذه الصفحة إعادة ترتيب وإصلاح ألبوماتك يدويًا.<br />قبل إجراء أي تعديلات، نوصي بشدة بقراءة حول بنية شجرة Nested Set.',
    'warning' => 'يمكنك حقًا كسر تثبيت Lychee الخاص بك هنا، قم بتعديل القيم على مسؤوليتك الخاصة.',
    'help' => [
        'header' => 'مساعدة',
        'hover' => 'مرر فوق المعرفات أو العناوين لتسليط الضوء على الألبومات ذات الصلة.',
        'left' => '<span class="text-muted-color-emphasis font-bold">يسار</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">يمين</span>',
        'convenience' => 'لراحتك، تتيح لك أزرار <i class="pi pi-angle-up" ></i> و <i class="pi pi-angle-down" ></i> تغيير قيم %s و %s على التوالي بمقدار +1 و -1 مع الانتشار.',
        'left-right-warn' => 'تشير <i class="text-warning-600 pi pi-chevron-circle-left" ></i> و <i class="text-warning-600 pi pi-chevron-circle-right" ></i> إلى أن قيمة %s (وعلى التوالي %s) مكررة في مكان ما.',
        'parent-marked' => 'تشير علامة <span class="font-bold text-danger-600">Parent Id</span> إلى أن %s و %s لا يفيان ببنية شجرة Nested Set. قم بتحرير إما <span class="font-bold text-danger-600">Parent Id</span> أو قيم %s/%s.',
        'slowness' => 'ستكون هذه الصفحة بطيئة مع عدد كبير من الألبومات.',
    ],
    'buttons' => [
        'reset' => 'إعادة تعيين',
        'check' => 'تحقق',
        'apply' => 'تطبيق',
    ],
    'no-changes' => 'لا توجد تغييرات للتطبيق.',
    'table' => [
        'title' => 'العنوان',
        'left' => 'يسار',
        'right' => 'يمين',
        'id' => 'المعرف',
        'parent' => 'معرف الأصل',
    ],
    'errors' => [
        'invalid' => 'شجرة غير صالحة!',
        'invalid_details' => 'لن نطبق هذا لأنه مضمون أن يكون في حالة مكسورة.',
        'invalid_left' => 'الألبوم %s يحتوي على قيمة يسار غير صالحة.',
        'invalid_right' => 'الألبوم %s يحتوي على قيمة يمين غير صالحة.',
        'invalid_left_right' => 'الألبوم %s يحتوي على قيم يسار/يمين غير صالحة. يجب أن تكون اليسار أصغر من اليمين: %s < %s.',
        'duplicate_left' => 'الألبوم %s يحتوي على قيمة يسار مكررة %s.',
        'duplicate_right' => 'الألبوم %s يحتوي على قيمة يمين مكررة %s.',
        'parent' => 'الألبوم %s يحتوي على معرف أصل غير متوقع %s.',
        'unknown' => 'الألبوم %s يحتوي على خطأ غير معروف.',
    ],
];
