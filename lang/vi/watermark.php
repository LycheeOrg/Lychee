<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Xem trước hình mờ',
        'se_required' => 'Mô-đun hình mờ yêu cầu bật Lychee Supporter Edition (SE) hoặc SE Preview.',

        'section_settings' => 'Cài đặt hình mờ',
        'section_preview' => 'Xem trước trực tiếp',
        'disclaimer' => 'Bản xem trước này cho bạn hình dung hình mờ sẽ trông như thế nào. Kết quả cuối cùng trên ảnh thực tế có thể hơi khác.',

        'watermark_photo_id' => 'ID ảnh hình mờ',
        'watermark_photo_id_placeholder' => 'ID ảnh gồm 24 ký tự',
        'watermark_photo_id_hint' => 'ID của ảnh được dùng làm hình mờ. Mở một ảnh và sao chép 24 ký tự cuối trong URL.',

        'preview_photo_id' => 'ID ảnh nền',
        'preview_photo_id_placeholder' => 'ID ảnh gồm 24 ký tự',
        'preview_photo_id_hint' => 'Nhập ID ảnh để dùng làm nền cho bản xem trước.',

        'size' => 'Kích thước (:value%)',
        'opacity' => 'Độ mờ đục (:value%)',
        'position' => 'Vị trí',
        'position_options' => [
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

        'section_shift' => 'Dịch chuyển / Độ lệch',
        'shift_type' => 'Đơn vị dịch chuyển',
        'shift_type_options' => [
            'relative' => 'Tương đối (%)',
            'absolute' => 'Tuyệt đối (px)',
        ],
        'shift_type_hint' => 'Dịch chuyển tương đối là phần trăm kích thước ảnh; dịch chuyển tuyệt đối là một số điểm ảnh cố định.',
        'shift_mode_use_slider' => 'Dùng thanh trượt',
        'shift_mode_use_classic' => 'Dùng ô nhập số',
        'shift_x' => 'Dịch ngang (:value)',
        'shift_x_direction_options' => [
            'left' => 'Trái',
            'right' => 'Phải',
        ],
        'shift_y' => 'Dịch dọc (:value)',
        'shift_y_direction_options' => [
            'up' => 'Lên',
            'down' => 'Xuống',
        ],
        'reset_to_zero' => 'Đặt lại về 0',

        'save' => 'Lưu cài đặt',
        'saved' => 'Đã lưu cài đặt hình mờ.',
        'save_error' => 'Không thể lưu cài đặt hình mờ.',
        'save_requires_se' => 'Lưu cài đặt hình mờ yêu cầu giấy phép Supporter Edition (SE) đầy đủ. SE Preview chỉ cho phép xem trước hiệu ứng.',

        'no_watermark_image' => 'Chưa cấu hình ảnh hình mờ. Nhập ID ảnh hình mờ và nhấn "Load" để xem trước.',
        'no_preview_photo' => 'Nhập ID ảnh nền ở trên để xem trước lớp phủ hình mờ.',
        'photo_load_error' => 'Không thể tải ảnh. Hãy đảm bảo ID chính xác và bạn có quyền truy cập ảnh.',
        'watermark_load_error' => 'Không thể tải ảnh hình mờ. Hãy đảm bảo ID ảnh chính xác.',
    ],
];
