<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Chia sẻ',
    'info' => 'Trang này cung cấp tổng quan và cho phép chỉnh sửa quyền chia sẻ gắn với các album.',
    'album_title' => 'Tiêu đề album',
    'username' => 'Tên người dùng',
    'no_data' => 'Danh sách chia sẻ trống.',
    'filter_placeholder' => 'Tìm theo tiêu đề album...',
    'hide_empty' => 'Ẩn album chưa được chia sẻ',
    'screen_too_small' => 'Trang này yêu cầu màn hình lớn hơn.',
    'share' => 'Chia sẻ',
    'add_new_access_permission' => 'Thêm quyền truy cập mới',
    'permission_deleted' => 'Đã xóa quyền!',
    'permission_created' => 'Đã tạo quyền!',
    'propagate' => 'Lan truyền',
    'propagate_help' => 'Lan truyền quyền truy cập hiện tại xuống tất cả album con<br>(album con và các album con của chúng, v.v.)',
    'propagate_default' => 'Theo mặc định, các quyền hiện có (album-người dùng)<br>sẽ được cập nhật và các quyền còn thiếu sẽ được thêm.<br>Các quyền khác không có trong danh sách này được giữ nguyên.',
    'propagate_overwrite' => 'Ghi đè các quyền hiện có thay vì cập nhật.<br>Thao tác này cũng sẽ xóa tất cả quyền không có trong danh sách này.',
    'propagate_warning' => 'Thao tác này không thể hoàn tác.',
    'permission_overwritten' => 'Lan truyền thành công! Đã ghi đè quyền!',
    'permission_updated' => 'Lan truyền thành công! Đã cập nhật quyền!',
    'bluk_share' => 'Chia sẻ hàng loạt',
    'bulk_share_instr' => 'Chọn nhiều album và người dùng để chia sẻ.',
    'albums' => 'Album',
    'users' => 'Người dùng',
    'no_users' => 'Không có người dùng nào để chọn.',
    'no_albums' => 'Không có album nào để chọn.',
    'grants' => [
        'read' => 'Cấp quyền xem',
        'original' => 'Cấp quyền truy cập ảnh gốc',
        'download' => 'Cấp quyền tải xuống',
        'upload' => 'Cấp quyền tải lên',
        'edit' => 'Cấp quyền chỉnh sửa',
        'move' => 'Cấp quyền di chuyển, sao chép và hợp nhất',
        'delete' => 'Cấp quyền xóa',
    ],
];
