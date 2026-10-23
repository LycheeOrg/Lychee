<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => '维护',
    'description' => '在此页面中，您可以找到保持 Lychee 安装运行顺畅所需的所有操作。',
    'cleaning' => [
        'title' => '清理 %s',
        'result' => '已删除 %s。',
        'description' => '删除 <span class="font-mono">%s</span> 中的所有内容',
        'button' => '清理',
    ],
    'duplicate-finder' => [
        'title' => '重复项',
        'description' => '此模块统计图片之间的潜在重复项。',
        'duplicates-all' => '所有相册中的重复项',
        'duplicates-title' => '每个相册中的标题重复项',
        'duplicates-per-album' => '每个相册中的重复项',
        'show' => '显示重复项',
        'load' => '加载计数',
    ],
    'fix-jobs' => [
        'title' => '修复任务历史',
        'description' => '将状态为 <span class="text-ready-400">%s</span> 或 <span class="text-primary-500">%s</span> 的任务标记为 <span class="text-danger-700">%s</span>。',
        'button' => '修复任务历史',
    ],
    'gen-sizevariants' => [
        'title' => '缺失的 %s',
        'description' => '发现 %d 个可以生成的 %s。',
        'button' => '生成！',
        'success' => '已成功生成 %d 个 %s。',
    ],
    'fill-filesize-sizevariants' => [
        'title' => '缺失文件大小',
        'description' => '发现 %d 个缺少文件大小的小型变体。',
        'button' => '获取数据！',
        'success' => '已成功计算 %d 个小型变体的大小。',
    ],
    'fix-tree' => [
        'title' => '树结构统计',
        'Oddness' => '异常项',
        'Duplicates' => '重复项',
        'Wrong parents' => '错误的父级',
        'Missing parents' => '缺失的父级',
        'button' => '修复树结构',
    ],
    'optimize' => [
        'title' => '优化数据库',
        'description' => '如果您注意到安装运行变慢，可能是因为您的数据库缺少必要的索引。',
        'button' => '优化数据库',
    ],
    'missing-palettes' => [
        'title' => '缺失的调色板',
        'description' => '发现 %d 个缺失的调色板。',
        'button' => '创建缺失项',
    ],
    'statistics-check' => [
        'title' => '统计数据完整性检查',
        'missing_photos' => '缺失 %d 条照片统计数据。',
        'missing_albums' => '缺失 %d 条相册统计数据。',
        'button' => '创建缺失项',
    ],
    'flush-cache' => [
        'title' => '清除缓存',
        'description' => '清除所有用户的缓存以解决失效问题。',
        'button' => '清除',
    ],
    'old-orders' => [
        'title' => '旧订单',
        'description' => '发现 %d 个旧订单。<br/>旧订单是指超过 14 天、没有关联用户，且仍在等待付款或不包含任何商品的订单。',
        'button' => '删除旧订单',
    ],
    'fulfill-orders' => [
        'title' => '待履行订单',
        'description' => '发现 %d 个订单的内容尚未交付。<br/>点击按钮以在可能时分配内容。',
        'button' => '履行订单',
    ],
    'fulfill-precompute' => [
        'title' => '相册预计算字段',
        'description' => '发现 %d 个相册缺少预计算字段。<br/>等同于运行：php artisan lychee:recompute-album-stats',
        'button' => '计算字段',
    ],
    'flush-queue' => [
        'title' => '清空队列',
        'description' => '队列中发现 %d 个待处理任务。<br/>注意：清空队列将永久删除所有待处理任务。此操作无法撤销。',
        'button' => '清空队列',
    ],
    'backfill-album-sizes' => [
        'title' => '相册大小统计',
        'description' => '发现 %d 个相册没有大小统计数据。<br/>等同于运行：php artisan lychee:recompute-album-sizes',
        'description_queue' => '在后台重新计算所有相册的大小统计。<br/>等同于运行：php artisan lychee:recompute-album-sizes',
        'button' => '计算大小',
    ],
    'recompute-buckets' => [
        'title' => '时间线分组',
        'description' => '重新计算每个相册和每张照片的时间线分组，然后刷新相册和照片列表。<br/>等同于运行：php artisan lychee:recompute-buckets',
        'button' => '重新计算',
    ],

    'face_quality' => [
        'title' => '人脸质量审核',
        'description' => '按质量分数审核检测到的人脸，并忽略低质量或错误的人脸。',
        'sort_by' => '排序方式：',
        'sort_confidence' => '置信度',
        'sort_blur' => '模糊度（拉普拉斯）',
        'no_faces' => '没有符合条件的人脸。一切正常！',
        'col_face' => '人脸',
        'col_person' => '人物',
        'col_cluster' => '聚类',
        'col_confidence' => '置信度',
        'col_blur' => '模糊分数',
        'col_actions' => '操作',
        'unassigned' => '未分配',
        'dismiss' => '忽略人脸',
        'readd' => '重新添加人脸',
        'load_error' => '加载人脸失败。',
        'dismissed' => '人脸已忽略。',
        'readded' => '人脸已重新添加。',
        'dismiss_error' => '忽略人脸失败。',
        'readd_error' => '重新添加人脸失败。',
        'batch_dismiss' => '忽略所选',
        'batch_dismissed' => '已忽略 :count 张人脸。',
        'batch_dismiss_error' => '忽略所选人脸失败。',
        'batch_reactivate' => '重新激活所选',
        'batch_reactivated' => '已重新激活 :count 张人脸。',
        'batch_reactivate_error' => '重新激活所选人脸失败。',
        'show_dismissed' => '显示已忽略',
        'show_active' => '显示活动',
        'show_unassigned' => '仅未分配',
        'select_all' => '全选',
        'deselect_all' => '取消全选',
        'selected_count' => '已选择 :count 项',
    ],
    'bulk-scan-faces' => [
        'description' => '发现 %d 张照片尚未进行人脸识别扫描。<br/>需要 AI Vision 服务正在运行。',
    ],
    'run-clustering' => [
        'description' => '在 AI Vision 服务中触发人脸聚类。按相似度对检测到的人脸进行分组，以便您将其分配给人物。',
        'success' => '聚类已成功启动。',
    ],
    'destroy-dismissed-faces' => [
        'title' => '销毁已忽略的人脸',
        'description' => '发现 %d 张已忽略的人脸。销毁它们将永久删除其裁剪文件和嵌入向量。',
        'action' => '全部销毁',
        'success' => '已忽略的人脸已成功销毁。',
    ],
    'sync-face-embeddings' => [
        'title' => '同步人脸嵌入向量',
        'description' => '检测到人脸数量不一致（相差 %d）。同步将从 AI Vision 服务拉取最新的人脸数据到 Lychee。',
        'action' => '立即同步',
        'success' => '人脸嵌入向量同步成功。',
    ],
    'reset-face-scan-status' => [
        'title' => '重置人脸扫描状态',
        'description' => '发现 %d 张照片的人脸扫描状态卡在待处理或失败。重置后即可重新扫描。',
        'action' => '全部重置',
        'success' => '人脸扫描状态重置成功。',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => '清除孤立的人脸嵌入向量',
            'description' => 'AI Vision 服务中可能保留了多达 %d 个在 Lychee 中已不存在的嵌入向量。清除操作会将当前人脸列表推送到 AI Vision，并永久删除所有残留数据。',
            'action' => '立即清除',
            'success' => '孤立的人脸嵌入向量已成功清除。',
        ],
        'bulk-scan-nsfw' => [
        'title' => '批量 NSFW 扫描',
        'description' => '使用已配置的预设扫描所有未扫描照片中的 NSFW 内容。需要 NSFW 分类服务正在运行。',
        'button' => '扫描所有未扫描项',
        'success' => 'NSFW 扫描已成功派发。',
    ],
];
