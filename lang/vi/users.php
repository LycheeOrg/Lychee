<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Người dùng',
    'description' => 'Tại đây bạn có thể quản lý người dùng của bản cài đặt Lychee. Bạn có thể tạo, sửa và xóa người dùng.',
    'create' => 'Tạo người dùng mới',
    'username' => 'Tên người dùng',
    'password' => 'Mật khẩu',
    'legend' => 'Chú thích',
    'upload_rights' => 'Khi được chọn, người dùng có thể tải lên nội dung.',
    'edit_rights' => 'Khi được chọn, người dùng có thể sửa hồ sơ của mình (tên người dùng, mật khẩu).',
    'upload_trust_level' => 'Mức tin cậy tải lên — kiểm soát việc ảnh tải lên có được công khai ngay hay không.',

    'quota' => 'Khi được đặt, người dùng có hạn ngạch dung lượng cho ảnh (tính bằng kB).',
    'user_deleted' => 'Đã xóa người dùng',
    'user_created' => 'Đã tạo người dùng',
    'user_updated' => 'Đã cập nhật người dùng',
    'change_saved' => 'Đã lưu thay đổi!',
    'create_edit' => [
        'upload_rights' => 'Người dùng có thể tải lên nội dung.',
        'edit_rights' => 'Người dùng có thể sửa hồ sơ của mình (tên người dùng, mật khẩu).',
        'admin_rights' => 'Người dùng có quyền quản trị.',
        'upload_trust_level' => 'Mức tin cậy tải lên',
        'upload_trust_level_check' => 'Kiểm tra – ảnh tải lên cần quản trị viên phê duyệt trước khi được công khai.',
        'upload_trust_level_monitor' => 'Giám sát – ảnh tải lên được công khai trừ khi bị gắn cờ về nội dung.',
        'upload_trust_level_trust_but_verify' => 'Tin cậy nhưng xác minh – ảnh tải lên được công khai; phát hiện cần xem xét được tự động phê duyệt, phát hiện cần chặn có thể cấu hình.',
        'upload_trust_level_trusted' => 'Tin cậy – ảnh tải lên được công khai ngay lập tức.',
        'trust_level_options' => [
            'trusted' => 'Tin cậy',
            'trust_but_verify' => 'Tin cậy nhưng xác minh',
            'monitor' => 'Giám sát',
            'check' => 'Kiểm tra',
        ],
        'quota' => 'Người dùng có giới hạn hạn ngạch.',
        'quota_kb' => 'hạn ngạch tính bằng kB (0 để dùng mặc định)',
        'note' => 'Ghi chú quản trị (không hiển thị công khai)',
        'create' => 'Tạo',
        'edit' => 'Sửa',
    ],
    'invite' => [
        'button' => 'Mời người dùng',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Liên kết này có hiệu lực trong %d ngày.',
    ],
    'line' => [
        'owner' => 'Chủ sở hữu',
        'admin' => 'Quản trị viên',
        'edit' => 'Sửa',
        'delete' => 'Xóa',
    ],
];
