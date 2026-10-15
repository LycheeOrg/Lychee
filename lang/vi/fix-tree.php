<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Bảo trì',
    'intro' => 'Trang này cho phép bạn sắp xếp lại và sửa album theo cách thủ công.<br />Trước khi thay đổi bất cứ điều gì, chúng tôi đặc biệt khuyên bạn nên tìm hiểu về cấu trúc cây Nested Set.',
    'warning' => 'Bạn thực sự có thể làm hỏng bản cài đặt Lychee tại đây, hãy tự chịu rủi ro khi sửa các giá trị.',
    'help' => [
        'header' => 'Trợ giúp',
        'hover' => 'Di chuột lên id hoặc tiêu đề để làm nổi bật các album liên quan.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Trái</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Phải</span>',
        'convenience' => 'Để tiện cho bạn, các nút <i class="pi pi-angle-up" ></i> và <i class="pi pi-angle-down" ></i> cho phép thay đổi giá trị %s và %s lần lượt +1 và -1 kèm lan truyền.',
        'left-right-warn' => 'Biểu tượng <i class="text-warning-600 pi pi-chevron-circle-left" ></i> và <i class="text-warning-600 pi pi-chevron-circle-right" ></i> cho biết giá trị %s (và tương ứng %s) bị trùng lặp ở đâu đó.',
        'parent-marked' => '<span class="font-bold text-danger-600">Parent Id</span> được đánh dấu cho biết %s và %s không thỏa mãn cấu trúc cây Nested Set. Hãy sửa <span class="font-bold text-danger-600">Parent Id</span> hoặc các giá trị %s/%s.',
        'slowness' => 'Trang này sẽ chậm khi có số lượng album lớn.',
    ],
    'buttons' => [
        'reset' => 'Đặt lại',
        'check' => 'Kiểm tra',
        'apply' => 'Áp dụng',
    ],
    'no-changes' => 'Không có thay đổi nào để áp dụng.',
    'table' => [
        'title' => 'Tiêu đề',
        'left' => 'Trái',
        'right' => 'Phải',
        'id' => 'Id',
        'parent' => 'Parent Id',
    ],
    'errors' => [
        'invalid' => 'Cây không hợp lệ!',
        'invalid_details' => 'Chúng tôi không áp dụng thay đổi này vì chắc chắn nó sẽ dẫn đến trạng thái hỏng.',
        'invalid_left' => 'Album %s có giá trị left không hợp lệ.',
        'invalid_right' => 'Album %s có giá trị right không hợp lệ.',
        'invalid_left_right' => 'Album %s có giá trị left/right không hợp lệ. Left phải nhỏ hơn hẳn right: %s < %s.',
        'duplicate_left' => 'Album %s có giá trị left %s bị trùng lặp.',
        'duplicate_right' => 'Album %s có giá trị right %s bị trùng lặp.',
        'parent' => 'Album %s có parent id %s không mong đợi.',
        'unknown' => 'Album %s gặp lỗi không xác định.',
    ],
];
