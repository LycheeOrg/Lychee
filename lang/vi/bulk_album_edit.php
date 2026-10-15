<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Sửa album hàng loạt',
    'description' => 'Sửa siêu dữ liệu và cài đặt hiển thị cho nhiều album cùng lúc.',
    'warning' => 'Các thay đổi tại đây được áp dụng ngay lập tức và không thể hoàn tác. Album thẻ không được hiển thị.',

    // Table columns
    'col_title' => 'Tiêu đề',
    'col_owner' => 'Chủ sở hữu',
    'col_license' => 'Giấy phép',
    'col_is_nsfw' => 'Nhạy cảm',
    'col_is_public' => 'Công khai',
    'col_is_link_required' => 'Liên kết',
    'col_grants_full_photo_access' => 'Ảnh đầy đủ',
    'col_grants_download' => 'Tải xuống',
    'col_grants_upload' => 'Tải lên',
    'col_photo_sorting' => 'Sắp xếp ảnh',
    'col_album_sorting' => 'Sắp xếp album',
    'col_created_at' => 'Ngày tạo',

    // Filter
    'filter_placeholder' => 'Tìm theo tiêu đề...',

    // Pagination
    'per_page' => 'Mỗi trang',
    'total_selected' => 'Đã chọn :n album|Đã chọn :n album',
    'select_all_page' => 'Chọn tất cả trên trang này',
    'select_all_matching' => 'Chọn tất cả kết quả khớp',
    'cap_warning' => 'Chỉ 1.000 album đầu tiên đã được chọn.',

    // Mode toggle
    'mode_paginated' => 'Phân trang',
    'mode_infinite' => 'Cuộn vô hạn',

    // Action buttons
    'action_delete' => 'Xóa',
    'action_set_owner' => 'Đặt chủ sở hữu',
    'action_edit_fields' => 'Sửa trường',

    // Edit Fields modal
    'edit_fields_title' => 'Sửa trường',
    'edit_fields_description' => 'Chỉ các trường được đánh dấu mới được cập nhật. Giá trị trống sẽ xóa trường.',
    'section_metadata' => 'Siêu dữ liệu',
    'section_visibility' => 'Hiển thị',
    'field_description' => 'Mô tả',
    'field_copyright' => 'Bản quyền',
    'field_license' => 'Giấy phép',
    'field_photo_layout' => 'Bố cục ảnh',
    'field_photo_sorting_col' => 'Cột sắp xếp ảnh',
    'field_photo_sorting_order' => 'Thứ tự sắp xếp ảnh',
    'field_album_sorting_col' => 'Cột sắp xếp album',
    'field_album_sorting_order' => 'Thứ tự sắp xếp album',
    'field_album_thumb_aspect_ratio' => 'Tỷ lệ khung hình ảnh thu nhỏ',
    'field_album_timeline' => 'Dòng thời gian album',
    'field_photo_timeline' => 'Dòng thời gian ảnh',
    'field_published_at' => 'Ngày xuất bản Flow',
    'field_is_nsfw' => 'Nhạy cảm',
    'field_is_public' => 'Công khai',
    'field_is_link_required' => 'Yêu cầu liên kết',
    'field_grants_full_photo_access' => 'Truy cập ảnh đầy đủ',
    'field_grants_download' => 'Tải xuống',
    'field_grants_upload' => 'Tải lên (SE)',
    'apply' => 'Áp dụng',
    'cancel' => 'Hủy',

    // Set Owner modal
    'set_owner_title' => 'Đặt chủ sở hữu',
    'set_owner_description' => 'Tất cả album đã chọn sẽ được chuyển lên cấp gốc và các album con của chúng cũng sẽ được chuyển giao.',
    'set_owner_select_user' => 'Chọn chủ sở hữu mới',
    'transfer' => 'Chuyển giao',

    // Delete confirmation modal
    'delete_title' => 'Xóa album',
    'delete_confirm' => 'Bạn sắp xóa vĩnh viễn :count album cùng tất cả album con và ảnh của chúng. Hành động này không thể hoàn tác.|Bạn sắp xóa vĩnh viễn :count album cùng tất cả album con và ảnh của chúng. Hành động này không thể hoàn tác.',
    'confirm_delete' => 'Xác nhận xóa',

    // Toasts
    'success_patch' => 'Đã cập nhật album thành công.',
    'success_set_owner' => 'Đã chuyển quyền sở hữu thành công.',
    'success_delete' => 'Đã xóa album thành công.',
    'error_load' => 'Không thể tải album.',
    'error_load_ids' => 'Không thể tải ID album.',
    'error_patch' => 'Không thể cập nhật album.',
    'error_missing_timezone' => 'Vui lòng chọn múi giờ cho ngày xuất bản.',
    'error_set_owner' => 'Không thể chuyển quyền sở hữu.',
    'error_delete' => 'Không thể xóa album.',
    'error_load_users' => 'Không thể tải danh sách người dùng.',
];
