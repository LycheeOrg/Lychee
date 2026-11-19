<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => '用户',
    'description' => '在这里您可以管理 Lychee 安装的用户。您可以创建、编辑和删除用户。',
    'create' => '创建新用户',
    'username' => '用户名',
    'password' => '密码',
    'legend' => '图例',
    'upload_rights' => '选中后，用户可以上传内容。',
    'edit_rights' => '选中后，用户可以修改其个人资料（用户名、密码）。',
    'upload_trust_level' => '上传信任级别 — 控制上传内容是否立即公开。',

    'quota' => '设置后，用户将有照片空间配额限制（以 kB 为单位）。',
    'user_deleted' => '用户已删除',
    'user_created' => '用户已创建',
    'user_updated' => '用户已更新',
    'change_saved' => '更改已保存！',
    'create_edit' => [
        'upload_rights' => '用户可以上传内容。',
        'edit_rights' => '用户可以修改其个人资料（用户名、密码）。',
        'admin_rights' => '用户拥有管理员权限。',
        'upload_trust_level' => '上传信任级别',
        'upload_trust_level_check' => '检查 – 上传内容需经管理员批准后才会公开。',
        'upload_trust_level_monitor' => '监控 – 上传内容公开，除非因内容被标记。',
        'upload_trust_level_trust_but_verify' => '信任但验证 – 上传内容公开；“审核”类发现自动批准，“阻止”类发现可配置。',
        'upload_trust_level_trusted' => '受信任 – 上传内容立即公开。',
        'trust_level_options' => [
            'trusted' => '受信任',
            'trust_but_verify' => '信任但验证',
            'monitor' => '监控',
            'check' => '检查',
        ],
        'quota' => '用户有配额限制。',
        'quota_kb' => '配额（kB，0 表示使用默认值）',
        'note' => '管理员备注（不公开显示）',
        'create' => '创建',
        'edit' => '编辑',
    ],
    'invite' => [
        'button' => '邀请用户',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => '此链接的有效期为 %d 天。',
    ],
    'line' => [
        'owner' => '所有者',
        'admin' => '管理员用户',
        'edit' => '编辑',
        'delete' => '删除',
    ],
];
