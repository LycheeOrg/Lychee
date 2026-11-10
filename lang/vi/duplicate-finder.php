<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Bảo trì',
    'intro' => 'Trên trang này, bạn sẽ thấy các ảnh trùng lặp được tìm thấy trong cơ sở dữ liệu.',
    'found' => ' ảnh trùng lặp được tìm thấy!',
    'invalid-search' => ' Phải chọn ít nhất điều kiện checksum hoặc tiêu đề.',
    'checksum-must-match' => 'Checksum phải khớp.',
    'title-must-match' => 'Tiêu đề phải khớp.',
    'must-be-in-same-album' => 'Phải nằm trong cùng một album.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Ảnh',
        'checksum' => 'Checksum',
    ],
    'warning' => [
        'no-original-left' => 'Không còn ảnh gốc nào.',
        'keep-one' => 'Bạn đã chọn tất cả các ảnh trùng lặp trong nhóm này. Vui lòng chọn ít nhất một ảnh để giữ lại.',
    ],
    'delete-selected' => 'Xóa mục đã chọn',
];
