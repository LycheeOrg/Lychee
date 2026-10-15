<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'Tài liệu tham khảo trực tiếp về các token và thành phần thực sự tạo nên giao diện v8. Mọi mẫu màu và điều khiển trên trang này đều đọc các thuộc tính CSS tùy chỉnh và thành phần Nuxt UI đang được dùng thực tế, nên nó không bao giờ bị lỗi thời như một bản mockup tĩnh.',

	'sections' => [
		'foundations' => 'Nền tảng',
		'colors' => 'Bảng màu',
		'typography' => 'Kiểu chữ',
		'buttons' => 'Nút',
		'badges' => 'Huy hiệu & chip',
		'forms' => 'Điều khiển biểu mẫu',
		'feedback' => 'Phản hồi & trạng thái',
		'radius' => 'Bo góc & độ nổi',
	],

	'loading' => [
		'full_page' => 'Trình tải toàn trang',
		'full_page_description' => 'Hiện dần chậm, so le — dùng phía sau lớp phủ đen toàn màn hình khi một trang đang tải.',
		'mini_spinner' => 'Spinner nhỏ',
		'mini_spinner_description' => 'Biến thể nhanh, hiển thị ngay — dùng nội tuyến cho nút, danh sách và các trạng thái tải nhỏ.',
	],

	'foundations' => [
		'system' => 'Bộ thành phần',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => 'Phông chữ nội dung',
		'body_font_value' => 'Libre Franklin, tự lưu trữ (Vazirmatn cho tiếng Ả Rập/Ba Tư)',
		'mono_font' => 'Phông chữ đơn cách',
		'mono_font_value' => 'Bộ phông đơn cách của hệ thống (ui-monospace)',
		'icons' => 'Bộ biểu tượng',
		'icons_value' => 'Lucide, qua Iconify (UIcon)',
		'theme' => 'Giao diện',
		'theme_value' => 'Một giao diện tối/sáng duy nhất, chuyển đổi trong Cài đặt — không ghi đè theo từng phần',
	],

	'colors' => [
		'primary' => 'Chính — màu thương hiệu, hành động chính, liên kết, trạng thái hoạt động',
		'secondary' => 'Phụ — hành động và lựa chọn thay thế',
		'neutral' => 'Trung tính — văn bản, viền, bề mặt (Slate ở chế độ sáng, Zinc ở chế độ tối)',
		'success' => 'Thành công — xác nhận, trạng thái tích cực',
		'warning' => 'Cảnh báo — thận trọng, vấn đề không chặn',
		'error' => 'Lỗi — hành động phá hủy, thất bại',
		'info' => 'Thông tin — thông báo mang tính thông tin',
		'resolved_from' => 'Lấy trực tiếp từ :token',
	],

	'typography' => [
		'display' => 'Tiêu đề lớn',
		'heading' => 'Tiêu đề',
		'body' => 'Văn bản nội dung',
		'body_sample' => 'Tiếng Việt có dấu: ăn, ơn, ưng, đường, chữ, quốc, ngữ — tiêu đề album, nhãn biểu mẫu và văn bản thường ngày hiển thị với bộ phông này.',
		'mono' => 'Đơn cách',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'emphasis' => 'Thang nhấn mạnh văn bản',
		'emphasis_sample' => 'Ảnh chụp vào ngày này trong những năm trước',
	],

	'buttons' => [
		'intro' => 'Mọi màu mà hệ thống nút hỗ trợ, với các biến thể thực sự được dùng trong ứng dụng.',
	],

	'forms' => [
		'input_label' => 'Tiêu đề album',
		'input_placeholder' => 'Mùa hè ở Kyoto',
		'select_label' => 'Sắp xếp theo',
		'checkbox_label' => 'Hiển thị album nhạy cảm',
		'switch_label' => 'Album công khai',
		'switch_description' => 'Bất kỳ ai có liên kết đều có thể xem album này',
		'textarea_label' => 'Mô tả',
		'textarea_placeholder' => 'Thêm mô tả cho album này…',
		'radio_legend' => 'Logic khớp thẻ',
	],

	'feedback' => [
		'alert_info' => 'Ảnh mới đã được thêm vào album này.',
		'alert_success' => 'Đã lưu cài đặt.',
		'alert_warning' => 'Album này hiển thị với bất kỳ ai có liên kết.',
		'alert_error' => 'Không thể kết nối máy chủ — các thay đổi chưa được lưu.',
		'empty_title' => 'Chưa có chia sẻ nào',
		'empty_description' => 'Album được chia sẻ và quyền của chúng sẽ hiển thị ở đây khi bạn chia sẻ album đầu tiên.',
	],
];
