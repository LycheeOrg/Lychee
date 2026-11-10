<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'Liên kết bổ sung',
    'description' => 'Quản lý danh sách có thứ tự các liên kết bổ sung hiển thị trên thanh điều hướng và/hoặc chân trang của trang chủ.',

    // Empty state
    'no_links' => 'Chưa cấu hình liên kết bổ sung nào.',
    'create_first' => 'Tạo liên kết đầu tiên của bạn',

    // Table columns
    'col_label' => 'Nhãn',
    'col_url' => 'URL',
    'col_placement' => 'Vị trí',
    'col_enabled' => 'Đã bật',
    'col_actions' => 'Thao tác',

    // Placement labels
    'placement_nav' => 'Điều hướng',
    'placement_footer' => 'Chân trang',
    'placement_both' => 'Điều hướng & Chân trang',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Tích hợp sẵn',
    'built_in_cannot_delete' => 'Không thể xóa liên kết tích hợp sẵn này. Thay vào đó hãy tắt nó, hoặc sắp xếp lại như mọi liên kết khác.',
    'built_in_url_hint' => 'Đây là liên kết tích hợp sẵn và không thể thay đổi đích của nó.',

    // Buttons
    'create' => 'Tạo liên kết',
    'edit' => 'Sửa',
    'delete' => 'Xóa',
    'cancel' => 'Hủy',
    'save' => 'Lưu',

    // Form fields
    'field_label' => 'Nhãn',
    'field_label_placeholder' => 'vd: Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Vị trí',
    'field_open_in_new_tab' => 'Mở trong tab mới',
    'field_enabled' => 'Đã bật',

    // Modal titles
    'modal_create_title' => 'Tạo liên kết',
    'modal_edit_title' => 'Sửa liên kết',

    // Delete confirmation
    'confirm_delete_header' => 'Xóa liên kết',
    'confirm_delete_message' => 'Bạn có chắc muốn xóa liên kết ":label"? Thao tác này không thể hoàn tác.',

    // Toasts
    'created' => 'Đã tạo liên kết thành công.',
    'updated' => 'Đã cập nhật liên kết thành công.',
    'deleted' => 'Đã xóa liên kết thành công.',
    'reordered' => 'Đã sắp xếp lại liên kết thành công.',
    'error_load' => 'Không thể tải liên kết.',
    'error_save' => 'Không thể lưu liên kết.',
    'error_delete' => 'Không thể xóa liên kết.',
    'error_reorder' => 'Không thể sắp xếp lại liên kết.',
];
