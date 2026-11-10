<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Trang chủ',
    'tab_settings' => 'Cài đặt',
    'tab_links' => 'Liên kết',
    'tab_featured' => 'Nổi bật',

    'section_layout' => 'Bố cục & Cấu trúc',
    'section_hero' => 'Phần Hero',
    'section_background_landscape' => 'Nền (Ngang)',
    'section_background_portrait' => 'Nền (Dọc)',
    'section_cta_position' => 'Vị trí nút kêu gọi hành động',
    'section_login_position' => 'Khung đăng nhập',
    'section_meridian' => 'Đường ray Meridian',
    'section_content' => 'Nội dung',

    'field_layout' => 'Bố cục',
    'field_intro_screen_enabled' => 'Màn hình giới thiệu',
    'field_hero_text_position' => 'Vị trí chữ Hero',
    'field_hero_text_color' => 'Màu chữ Hero',
    'field_hero_text_opacity' => 'Độ mờ đục chữ Hero',
    'field_animation_preset' => 'Kiểu hiệu ứng động',
    'field_backdrop_opacity' => 'Độ mờ đục lớp phủ nền (:value%)',
    'field_cta_text' => 'văn bản',
    'field_cta_text_placeholder' => 'Để trống để dùng mặc định của bố cục',
    'field_about_enabled' => 'Phần giới thiệu',
    'field_about_text' => 'Nội dung giới thiệu',

    'field_background_mode' => 'Nguồn',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID ảnh',
        'random' => 'Ảnh công khai ngẫu nhiên',
        'latest_album_cover' => 'Ảnh bìa album mới nhất',
        'random_from_album' => 'Ảnh ngẫu nhiên từ album',
    ],
    'field_background_url' => 'URL hình ảnh',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID ảnh',
    'field_background_photo_id_placeholder' => 'ID ảnh gồm 24 ký tự',
    'field_background_photo_id_hint' => 'ID của ảnh cần dùng. Mở một ảnh và sao chép 24 ký tự cuối cùng trong URL.',
    'field_background_album_id' => 'ID album',
    'field_background_album_id_placeholder' => 'ID album gồm 24 ký tự',
    'background_load_error' => 'Không thể tải ảnh. Hãy đảm bảo ID chính xác và bạn có quyền truy cập ảnh đó.',
    'background_mode_hint' => [
        'random' => 'Một ảnh công khai ngẫu nhiên được chọn mỗi lần tải trang.',
        'latest_album_cover' => 'Sử dụng ảnh bìa của album công khai được xuất bản gần đây nhất.',
        'random_from_album' => 'Một ảnh ngẫu nhiên từ album này được chọn mỗi lần tải trang. Bản xem trước chỉ cập nhật sau khi lưu.',
    ],

    'preview_orientation_landscape' => 'Xem trước nền ngang',
    'preview_orientation_portrait' => 'Xem trước nền dọc',

    'field_cta_position' => 'Vị trí',
    'cta_position_options' => [
        'top-left' => 'Trên cùng bên trái',
        'top' => 'Trên cùng ở giữa',
        'top-right' => 'Trên cùng bên phải',
        'left' => 'Giữa bên trái',
        'center' => 'Chính giữa',
        'right' => 'Giữa bên phải',
        'bottom-left' => 'Dưới cùng bên trái',
        'bottom' => 'Dưới cùng ở giữa',
        'bottom-right' => 'Dưới cùng bên phải',
    ],
    'field_cta_shift_type' => 'Đơn vị dịch chuyển',
    'cta_shift_type_options' => [
        'relative' => 'Tương đối (%)',
        'absolute' => 'Tuyệt đối (px)',
    ],
    'cta_shift_type_hint' => 'Dịch chuyển tương đối tính theo phần trăm kích thước khung nhìn; dịch chuyển tuyệt đối là một số pixel cố định.',
    'field_cta_shift_x' => 'Dịch chuyển ngang (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Trái',
        'right' => 'Phải',
    ],
    'field_cta_shift_y' => 'Dịch chuyển dọc (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Lên',
        'down' => 'Xuống',
    ],
    'reset_to_zero' => 'Đặt lại về 0',

    'field_login_position' => 'Vị trí',
    'login_position_options' => [
        'side' => 'Bên cạnh',
        'center' => 'Chính giữa',
    ],
    'field_login_position_hint' => 'Trên màn hình hẹp, khung đăng nhập luôn được căn giữa bên dưới phần Hero, bất kể cài đặt này. Khung sẽ trượt vào từ cạnh theo hướng đọc khi kiểu hiệu ứng động là "Slide reveal" hoặc "Parallax scroll".',

    'field_meridian_explore_offset' => 'Độ cao nhãn Khám phá (:value%)',
    'field_meridian_contact_offset' => 'Độ cao nhãn Liên hệ (:value%)',
    'field_meridian_offset_hint' => 'Vị trí nhãn trên mỗi đường ray toàn chiều cao: 0% là trên cùng, 100% là dưới cùng.',
    'field_meridian_explore_line_position' => 'Vị trí đường ray Khám phá (:value%)',
    'field_meridian_contact_line_position' => 'Vị trí đường ray Liên hệ (:value%)',
    'field_meridian_line_position_hint' => 'Khoảng cách của mỗi đường ray tính từ cạnh trái: 0% là cạnh trái, 100% là cạnh phải. Đường ray Khám phá luôn tự căn giữa khi đường ray Liên hệ bị tắt.',

    'preview_title' => 'Xem trước trực tiếp',
    'preview_hint' => 'Cập nhật ngay khi bạn chỉnh sửa — không có gì được lưu cho đến khi bạn nhấn Lưu.',
    'flat_list_hint' => 'Các cài đặt này cũng có thể chỉnh sửa trong danh sách Cài đặt chung dạng phẳng.',

    'save' => 'Lưu',
    'saved' => 'Đã lưu cài đặt trang chủ.',
    'save_error' => 'Không thể lưu cài đặt trang chủ.',

    'se_required' => 'Bố cục/kiểu này yêu cầu Lychee SE.',
];
