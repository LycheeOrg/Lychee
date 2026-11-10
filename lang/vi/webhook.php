<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Cấu hình các webhook gửi đi được kích hoạt khi ảnh được thêm, di chuyển hoặc xóa.',

    // Empty state
    'no_webhooks' => 'Chưa cấu hình webhook nào.',
    'create_first' => 'Tạo webhook đầu tiên của bạn',

    // Table columns
    'col_name' => 'Tên',
    'col_event' => 'Sự kiện',
    'col_method' => 'Phương thức',
    'col_url' => 'URL',
    'col_format' => 'Định dạng',
    'col_enabled' => 'Đã bật',
    'col_actions' => 'Thao tác',

    // Event labels
    'event_photo_add' => 'Đã thêm ảnh',
    'event_photo_move' => 'Đã di chuyển ảnh',
    'event_photo_delete' => 'Đã xóa ảnh',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Chuỗi truy vấn',

    // Buttons
    'create' => 'Tạo webhook',
    'edit' => 'Sửa',
    'delete' => 'Xóa',
    'cancel' => 'Hủy',
    'save' => 'Lưu',

    // Form fields
    'field_name' => 'Tên',
    'field_name_placeholder' => 'vd: Webhook của tôi',
    'field_event' => 'Sự kiện',
    'field_method' => 'Phương thức HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Định dạng payload',
    'field_enabled' => 'Đã bật',
    'field_secret' => 'Secret',
    'field_secret_placeholder' => 'Để trống để giữ secret hiện tại',
    'field_secret_header' => 'Header secret',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Gửi ID ảnh',
    'field_send_album_id' => 'Gửi ID album',
    'field_send_title' => 'Gửi tiêu đề',
    'field_send_size_variants' => 'Gửi các biến thể kích thước',

    // Modal titles
    'modal_create_title' => 'Tạo webhook',
    'modal_edit_title' => 'Sửa webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Xóa webhook',
    'confirm_delete_message' => 'Bạn có chắc muốn xóa webhook ":name"? Thao tác này không thể hoàn tác.',
    'delete_warning' => 'Thao tác này không thể hoàn tác.',

    // Toasts
    'created' => 'Đã tạo webhook thành công.',
    'updated' => 'Đã cập nhật webhook thành công.',
    'deleted' => 'Đã xóa webhook thành công.',
    'error_load' => 'Không thể tải webhook.',
    'error_save' => 'Không thể lưu webhook.',
    'error_delete' => 'Không thể xóa webhook.',

    // Secret badge
    'has_secret' => 'Đã đặt secret',
    'no_secret' => 'Không có secret',
];
