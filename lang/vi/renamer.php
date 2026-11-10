<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Quy tắc đổi tên',

    // Modal titles
    'create_rule' => 'Tạo quy tắc đổi tên',
    'edit_rule' => 'Sửa quy tắc đổi tên',

    // Form fields
    'rule_name' => 'Tên quy tắc',
    'description' => 'Mô tả',
    'pattern' => 'Mẫu',
    'replacement' => 'Thay thế',
    'mode' => 'Chế độ',
    'order' => 'Thứ tự',
    'enabled' => 'Đã bật',
    'photo_rule' => 'Quy tắc áp dụng cho ảnh',
    'album_rule' => 'Quy tắc áp dụng cho album',

    // Form placeholders and help text
    'description_placeholder' => 'Mô tả tùy chọn về chức năng của quy tắc này',
    'pattern_help' => 'Mẫu cần khớp (vd: IMG_, DSC_)',
    'replacement_help' => 'Văn bản thay thế (vd: Photo_, Camera_)',
    'order_help' => 'Số nhỏ hơn được xử lý trước (1 = ưu tiên cao nhất)',
    'enabled_help' => '(Chỉ các quy tắc đã bật mới được áp dụng khi đổi tên)',

    // Mode options
    'mode_first' => 'Lần xuất hiện đầu tiên',
    'mode_all' => 'Mọi lần xuất hiện',
    'mode_regex' => 'Biểu thức chính quy',
    'mode_trim' => 'Cắt khoảng trắng',
    'mode_strtolower' => 'chữ thường',
    'mode_strtoupper' => 'CHỮ HOA',
    'mode_ucwords' => 'Viết Hoa Mỗi Từ',
    'mode_ucfirst' => 'Viết hoa chữ cái đầu',

    'mode_first_description' => 'Chỉ thay thế lần xuất hiện đầu tiên',
    'mode_all_description' => 'Thay thế mọi lần xuất hiện',
    'mode_regex_description' => 'Dùng mẫu biểu thức chính quy (regex)',
    'mode_trim_description' => 'Cắt khoảng trắng',
    'mode_strtolower_description' => 'Chuyển chuỗi thành chữ thường',
    'mode_strtoupper_description' => 'Chuyển chuỗi thành CHỮ HOA',
    'mode_ucwords_description' => 'Viết hoa chữ cái đầu mỗi từ',
    'mode_ucfirst_description' => 'Chỉ viết hoa chữ cái đầu tiên',

    'regex_help' => 'Dùng biểu thức chính quy để khớp mẫu. Ví dụ, để thay <code>IMG_1234.jpeg</code> bằng <code>1234_JPG.jpeg</code>, bạn có thể dùng <code>/IMG_(\\d+)/</code> làm mẫu tìm và <code>$1_JPG</code> làm chuỗi thay thế. Bạn có thể tìm thêm giải thích và ví dụ trong các liên kết sau.',

    // Buttons
    'cancel' => 'Hủy',
    'create' => 'Tạo',
    'update' => 'Cập nhật',
    'create_first_rule' => 'Tạo quy tắc đầu tiên của bạn',

    // Validation messages
    'rule_name_required' => 'Tên quy tắc là bắt buộc',
    'pattern_required' => 'Mẫu là bắt buộc',
    'replacement_required' => 'Chuỗi thay thế là bắt buộc',
    'mode_required' => 'Chế độ là bắt buộc',
    'order_positive' => 'Thứ tự phải là số dương',

    // Success messages
    'rule_created' => 'Đã tạo quy tắc đổi tên thành công',
    'rule_updated' => 'Đã cập nhật quy tắc đổi tên thành công',
    'rule_deleted' => 'Đã xóa quy tắc đổi tên thành công',

    // Error messages
    'failed_to_create' => 'Không thể tạo quy tắc đổi tên',
    'failed_to_update' => 'Không thể cập nhật quy tắc đổi tên',
    'failed_to_delete' => 'Không thể xóa quy tắc đổi tên',
    'failed_to_load' => 'Không thể tải các quy tắc đổi tên',

    // List view
    'rules_count' => ':count quy tắc',
    'no_rules' => 'Không tìm thấy quy tắc đổi tên nào',
    'loading' => 'Đang tải quy tắc đổi tên...',
    'pattern_label' => 'Mẫu',
    'replace_with_label' => 'Thay bằng',
    'photo' => 'Ảnh',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Xác nhận xóa',
    'confirm_delete_message' => 'Bạn có chắc muốn xóa quy tắc ":rule" không?',
    'delete' => 'Xóa',

    // Status messages
    'success' => 'Thành công',
    'error' => 'Lỗi',

    // Placeholders
    'select_mode' => 'Chọn chế độ đổi tên',
    'execution_order' => 'Thứ tự thực thi',

    // Test functionality
    'test_input_placeholder' => 'Nhập tên tệp để thử các quy tắc đổi tên (vd: IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Kết quả',
    'test_failed' => 'Không thể thử các quy tắc đổi tên',
    'apply_photo_rules' => 'Áp dụng quy tắc ảnh',
    'apply_album_rules' => 'Áp dụng quy tắc album',
];
