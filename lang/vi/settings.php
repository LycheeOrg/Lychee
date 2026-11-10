<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Cài đặt',
    'small_screen' => 'Để có trải nghiệm tốt hơn trên trang này,<br />chúng tôi khuyên bạn nên dùng màn hình lớn hơn.',
    'tabs' => [
        'basic' => 'Cơ bản',
        'all_settings' => 'Tất cả cài đặt',
    ],
    'toasts' => [
        'change_saved' => 'Đã lưu thay đổi!',
        'details' => 'Cài đặt đã được thay đổi theo yêu cầu',
        'error' => 'Lỗi!',
        'error_load_css' => 'Không thể tải dist/user.css',
        'error_load_js' => 'Không thể tải dist/custom.js',
        'error_save_css' => 'Không thể lưu CSS',
        'error_save_js' => 'Không thể lưu JS',
        'thank_you' => 'Cảm ơn bạn đã ủng hộ.',
        'reload' => 'Tải lại trang để sử dụng đầy đủ chức năng.',
    ],
    'system' => [
        'header' => 'Hệ thống',
        'use_dark_mode' => 'Dùng chế độ tối cho Lychee',
        'language' => 'Ngôn ngữ dùng cho Lychee',
        'nsfw_album_visibility' => 'Hiển thị album Nhạy cảm theo mặc định.',
        'nsfw_album_explanation' => 'Nếu album ở chế độ công khai, album vẫn có thể truy cập được, chỉ bị ẩn khỏi chế độ xem và <b>có thể hiện ra bằng cách nhấn <kbd>H</kbd></b>.',
        'cache_enabled' => 'Bật bộ nhớ đệm cho phản hồi.',
        'cache_enabled_details' => 'Tùy chọn này sẽ tăng đáng kể tốc độ phản hồi của Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Nếu bạn đang dùng album được bảo vệ bằng mật khẩu, bạn không nên bật tùy chọn này.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Nhận các tính năng độc quyền và ủng hộ sự phát triển của Lychee. Mở khóa <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">phiên bản SE</a>.',
        'preview' => 'Bật xem trước các tính năng của Lychee SE',
        'hide_call4action' => 'Ẩn biểu mẫu đăng ký Lychee SE này. Tôi hài lòng với Lychee như hiện tại. :)',
        'hide_warning' => 'Nếu bật, cách duy nhất để đăng ký khóa giấy phép là qua thẻ Thêm ở trên. Thay đổi sẽ được áp dụng khi tải lại trang.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Để nhập ảnh từ Dropbox, bạn cần một khóa ứng dụng drop-ins hợp lệ từ trang web của họ.',
        'api_key' => 'Khóa API Dropbox',
        'set_key' => 'Đặt khóa Dropbox',
    ],
    'gallery' => [
        'header' => 'Thư viện',
        'photo_order_column' => 'Cột mặc định dùng để sắp xếp ảnh',
        'photo_order_direction' => 'Thứ tự mặc định dùng để sắp xếp ảnh',
        'album_order_column' => 'Cột mặc định dùng để sắp xếp album',
        'album_order_direction' => 'Thứ tự mặc định dùng để sắp xếp album',
        'aspect_ratio' => 'Tỷ lệ khung hình mặc định cho ảnh thu nhỏ của album',
        'photo_layout' => 'Bố cục cho ảnh',
        'album_decoration' => 'Hiển thị trang trí trên ảnh bìa album (số album con và/hoặc số ảnh)',
        'album_decoration_direction' => 'Căn trang trí album theo chiều ngang hoặc chiều dọc',
        'photo_overlay' => 'Thông tin lớp phủ ảnh mặc định',
        'album_date_scrubber_enabled' => 'Hiển thị thanh cuộn ngày trong chế độ xem album',
        'rounded_corners_enabled' => 'Bo tròn góc ảnh thu nhỏ của ảnh và album',
        'album_border_enabled' => 'Hiển thị viền quanh ảnh thu nhỏ của ảnh và album',
        'photo_ken_burns_on_hover_enabled' => 'Bật hiệu ứng Ken Burns khi di chuột qua ảnh',
        'photo_ken_burns_on_hover_scale' => 'Mức thu phóng Ken Burns khi di chuột (%)',
        'photo_ken_burns_on_hover_duration' => 'Thời lượng thu phóng Ken Burns khi di chuột (giây)',
        'license_default' => 'Giấy phép mặc định dùng cho album',
        'license_help' => 'Cần trợ giúp để chọn?',
    ],
    'geolocation' => [
        'header' => 'Định vị địa lý',
        'map_display' => 'Hiển thị bản đồ theo tọa độ GPS',
        'map_display_public' => 'Cho phép người dùng ẩn danh truy cập bản đồ',
        'map_provider' => 'Xác định nhà cung cấp bản đồ',
        'map_include_subalbums' => 'Bao gồm ảnh của các album con trên bản đồ',
        'location_decoding' => 'Dùng giải mã vị trí GPS',
        'location_show' => 'Hiển thị vị trí trích xuất từ tọa độ GPS',
        'location_show_public' => 'Người dùng ẩn danh có thể truy cập vị trí trích xuất từ tọa độ GPS',
        'gps_coordinate_display' => 'Hiển thị tọa độ GPS',
        'gps_coordinate_display_public' => 'Cho phép người dùng ẩn danh truy cập tọa độ GPS',
    ],
    'cssjs' => [
        'header' => 'CSS & Js tùy chỉnh',
        'change_css' => 'Thay đổi CSS',
        'change_js' => 'Thay đổi JS',
    ],
    'all' => [
        'old_setting_style' => 'Kiểu cài đặt cũ',
        'expert_settings' => 'Chế độ chuyên gia',
        'change_detected' => 'Một số cài đặt đã thay đổi.',
        'save' => 'Lưu',
        'back_to_settings' => 'Quay lại cài đặt theo nhóm',
    ],
    'tool_option' => [
        'disabled' => 'đã tắt',
        'enabled' => 'đã bật',
        'discover' => 'khám phá',
    ],
    'groups' => [
        'general' => 'Chung',
        'system' => 'Hệ thống',
        'modules' => 'Mô-đun',
        'advanced' => 'Nâng cao',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Dùng bảng điều khiển quản trị',
            'help' => 'Thay menu con quản trị lồng nhau bằng một liên kết duy nhất tới trang bảng điều khiển quản trị mới.',
        ],
    ],
    'password_field' => [
        'set' => 'Đã đặt',
        'not_set' => 'Chưa đặt',
        'placeholder' => 'Mật khẩu mới',
        'clear' => 'Xóa',
    ],
];
