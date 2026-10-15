<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Bảo trì',
    'description' => 'Trên trang này, bạn sẽ tìm thấy tất cả các thao tác cần thiết để giữ cho bản cài đặt Lychee của bạn hoạt động trơn tru.',
    'cleaning' => [
        'title' => 'Dọn dẹp %s',
        'result' => 'Đã xóa %s.',
        'description' => 'Xóa toàn bộ nội dung khỏi <span class="font-mono">%s</span>',
        'button' => 'Dọn dẹp',
    ],
    'duplicate-finder' => [
        'title' => 'Trùng lặp',
        'description' => 'Mô-đun này đếm các ảnh có khả năng bị trùng lặp.',
        'duplicates-all' => 'Trùng lặp trên tất cả album',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Trùng lặp theo từng album',
        'show' => 'Hiển thị trùng lặp',
        'load' => 'Tải số lượng',
    ],
    'fix-jobs' => [
        'title' => 'Sửa lịch sử tác vụ',
        'description' => 'Đánh dấu các tác vụ có trạng thái <span class="text-ready-400">%s</span> hoặc <span class="text-primary-500">%s</span> là <span class="text-danger-700">%s</span>.',
        'button' => 'Sửa lịch sử tác vụ',
    ],
    'gen-sizevariants' => [
        'title' => 'Thiếu %s',
        'description' => 'Tìm thấy %d %s có thể được tạo.',
        'button' => 'Tạo!',
        'success' => 'Đã tạo thành công %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Thiếu kích thước tệp',
        'description' => 'Tìm thấy %d biến thể nhỏ không có kích thước tệp.',
        'button' => 'Lấy dữ liệu!',
        'success' => 'Đã tính thành công kích thước của %d biến thể nhỏ.',
    ],
    'fix-tree' => [
        'title' => 'Thống kê cây',
        'Oddness' => 'Bất thường',
        'Duplicates' => 'Trùng lặp',
        'Wrong parents' => 'Sai mục cha',
        'Missing parents' => 'Thiếu mục cha',
        'button' => 'Sửa cây',
    ],
    'optimize' => [
        'title' => 'Tối ưu cơ sở dữ liệu',
        'description' => 'Nếu bạn thấy bản cài đặt chạy chậm, có thể là do cơ sở dữ liệu của bạn chưa có đủ các chỉ mục cần thiết.',
        'button' => 'Tối ưu cơ sở dữ liệu',
    ],
    'missing-palettes' => [
        'title' => 'Thiếu bảng màu',
        'description' => 'Tìm thấy %d bảng màu bị thiếu.',
        'button' => 'Tạo phần thiếu',
    ],
    'statistics-check' => [
        'title' => 'Kiểm tra tính toàn vẹn thống kê',
        'missing_photos' => 'Thiếu %d thống kê ảnh.',
        'missing_albums' => 'Thiếu %d thống kê album.',
        'button' => 'Tạo phần thiếu',
    ],
    'flush-cache' => [
        'title' => 'Xóa bộ nhớ đệm',
        'description' => 'Xóa bộ nhớ đệm của mọi người dùng để khắc phục sự cố vô hiệu hóa bộ nhớ đệm.',
        'button' => 'Xóa',
    ],
    'old-orders' => [
        'title' => 'Đơn hàng cũ',
        'description' => 'Tìm thấy %d đơn hàng cũ.<br/><br/>Đơn hàng cũ là đơn hàng đã quá 14 ngày, không gắn với người dùng nào và vẫn đang chờ thanh toán hoặc không có mặt hàng nào.',
        'button' => 'Xóa đơn hàng cũ',
    ],
    'fulfill-orders' => [
        'title' => 'Đơn hàng cần hoàn tất',
        'description' => 'Tìm thấy %d đơn hàng có nội dung chưa được cung cấp.<br/><br/>Nhấn nút để gán nội dung khi có thể.',
        'button' => 'Hoàn tất đơn hàng',
    ],
    'fulfill-precompute' => [
        'title' => 'Trường tính trước của album',
        'description' => 'Tìm thấy %d album thiếu trường tính trước.<br/><br/>Tương đương với việc chạy: php artisan lychee:recompute-album-fields',
        'button' => 'Tính các trường',
    ],
    'flush-queue' => [
        'title' => 'Xóa hàng đợi',
        'description' => 'Tìm thấy %d tác vụ đang chờ trong hàng đợi.<br/><br/>CẢNH BÁO: Xóa hàng đợi sẽ xóa vĩnh viễn tất cả tác vụ đang chờ. Không thể hoàn tác thao tác này.',
        'button' => 'Xóa hàng đợi',
    ],
    'backfill-album-sizes' => [
        'title' => 'Thống kê dung lượng album',
        'description' => 'Tìm thấy %d album chưa có thống kê dung lượng.<br/><br/>Tương đương với việc chạy: php artisan lychee:recompute-album-sizes',
        'button' => 'Tính dung lượng',
    ],

    'face_quality' => [
        'title' => 'Đánh giá chất lượng khuôn mặt',
        'description' => 'Xem xét các khuôn mặt được phát hiện theo điểm chất lượng và loại bỏ những khuôn mặt chất lượng thấp hoặc bị nhận sai.',
        'sort_by' => 'Sắp xếp theo:',
        'sort_confidence' => 'Độ tin cậy',
        'sort_blur' => 'Độ mờ (Laplacian)',
        'no_faces' => 'Không có khuôn mặt nào phù hợp. Mọi thứ đều ổn!',
        'col_face' => 'Khuôn mặt',
        'col_person' => 'Người',
        'col_cluster' => 'Cụm',
        'col_confidence' => 'Độ tin cậy',
        'col_blur' => 'Điểm mờ',
        'col_actions' => 'Thao tác',
        'unassigned' => 'Chưa gán',
        'dismiss' => 'Loại bỏ khuôn mặt',
        'readd' => 'Thêm lại khuôn mặt',
        'load_error' => 'Không thể tải khuôn mặt.',
        'dismissed' => 'Đã loại bỏ khuôn mặt.',
        'readded' => 'Đã thêm lại khuôn mặt.',
        'dismiss_error' => 'Không thể loại bỏ khuôn mặt.',
        'readd_error' => 'Không thể thêm lại khuôn mặt.',
        'batch_dismiss' => 'Loại bỏ mục đã chọn',
        'batch_dismissed' => 'Đã loại bỏ :count khuôn mặt.',
        'batch_dismiss_error' => 'Không thể loại bỏ các khuôn mặt đã chọn.',
        'batch_reactivate' => 'Kích hoạt lại mục đã chọn',
        'batch_reactivated' => 'Đã kích hoạt lại :count khuôn mặt.',
        'batch_reactivate_error' => 'Không thể kích hoạt lại các khuôn mặt đã chọn.',
        'show_dismissed' => 'Hiện mục đã loại bỏ',
        'show_active' => 'Hiện mục đang hoạt động',
        'show_unassigned' => 'Chỉ mục chưa gán',
        'select_all' => 'Chọn tất cả',
        'deselect_all' => 'Bỏ chọn tất cả',
        'selected_count' => 'Đã chọn :count',
    ],
    'bulk-scan-faces' => [
        'description' => 'Tìm thấy %d ảnh chưa được quét nhận diện khuôn mặt.<br/><br/>Yêu cầu dịch vụ AI Vision đang chạy.',
    ],
    'run-clustering' => [
        'description' => 'Kích hoạt phân cụm khuôn mặt trong dịch vụ AI Vision. Nhóm các khuôn mặt được phát hiện theo độ tương đồng để bạn có thể gán chúng cho từng người.',
        'success' => 'Đã bắt đầu phân cụm thành công.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Hủy khuôn mặt đã loại bỏ',
        'description' => 'Tìm thấy %d khuôn mặt đã loại bỏ. Hủy chúng sẽ xóa vĩnh viễn các tệp ảnh cắt và embedding của chúng.',
        'action' => 'Hủy tất cả',
        'success' => 'Đã hủy thành công các khuôn mặt đã loại bỏ.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Đồng bộ embedding khuôn mặt',
        'description' => 'Phát hiện số lượng khuôn mặt không khớp (chênh lệch %d). Đồng bộ sẽ lấy dữ liệu khuôn mặt mới nhất từ dịch vụ AI Vision về Lychee.',
        'action' => 'Đồng bộ ngay',
        'success' => 'Đã đồng bộ thành công embedding khuôn mặt.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Đặt lại trạng thái quét khuôn mặt',
        'description' => 'Tìm thấy %d ảnh có trạng thái quét khuôn mặt bị kẹt ở chờ xử lý hoặc thất bại. Đặt lại sẽ cho phép quét lại các ảnh này.',
        'action' => 'Đặt lại tất cả',
        'success' => 'Đã đặt lại thành công trạng thái quét khuôn mặt.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Dọn embedding khuôn mặt mồ côi',
            'description' => 'Dịch vụ AI Vision có thể đang lưu tới %d embedding không còn tồn tại trong Lychee. Thao tác dọn dẹp sẽ gửi danh sách khuôn mặt hiện tại tới AI Vision và xóa vĩnh viễn mọi dữ liệu còn sót lại.',
            'action' => 'Dọn ngay',
            'success' => 'Đã dọn thành công các embedding khuôn mặt mồ côi.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Quét NSFW hàng loạt',
        'description' => 'Quét tất cả ảnh chưa được quét để tìm nội dung NSFW bằng cấu hình đặt sẵn. Yêu cầu dịch vụ phân loại NSFW đang chạy.',
        'button' => 'Quét tất cả ảnh chưa quét',
        'success' => 'Đã gửi yêu cầu quét NSFW thành công.',
    ],
];
