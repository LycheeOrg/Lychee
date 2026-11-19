<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Đồng bộ tệp trên máy chủ',
    'description' => 'Đồng bộ các tệp trên máy chủ của bạn với Lychee. Thao tác này sẽ nhập ảnh từ một thư mục và tất cả các thư mục con. Quá trình này rất chậm, chúng tôi khuyên bạn nên dùng worker và hàng đợi (queue) để tránh bị hết thời gian chờ.',
    'sync' => 'Đồng bộ',
    'loading' => 'Đang tải...',
    'selected_directory' => 'Thư mục đang chọn:',
    'resync_metadata' => 'Đồng bộ lại siêu dữ liệu của các tệp hiện có.',
    'delete_imported' => 'Xóa các tệp gốc.',
    'import_via_symlink' => 'Nhập ảnh qua liên kết tượng trưng (symlink) thay vì sao chép tệp.',
    'skip_duplicates' => 'Bỏ qua ảnh và album nếu chúng đã có trong thư viện ảnh.',
    'delete_missing_photos' => 'Xóa các ảnh trong album không có trong thư mục được đồng bộ.',
    'delete_missing_albums' => 'Xóa các album trong album cha không có trong thư mục được đồng bộ.',
    'importing_please_be_patient' => 'Đang nhập, vui lòng chờ...',
];

