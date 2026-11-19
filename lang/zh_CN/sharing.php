<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => '共享',
    'info' => '此页面提供相册共享权限的概览和编辑功能。',
    'album_title' => '相册标题',
    'username' => '用户名',
    'no_data' => '共享列表为空。',
    'filter_placeholder' => '按相册标题搜索…',
    'hide_empty' => '隐藏未共享的相册',
    'screen_too_small' => '此页面需要更大的屏幕。',
    'share' => '共享',
    'add_new_access_permission' => '添加新的访问权限',
    'permission_deleted' => '权限已删除！',
    'permission_created' => '权限已创建！',
    'propagate' => '传播',
    'propagate_help' => '将当前访问权限传播到所有子项<br>（子相册及其各自的子相册等）',
    'propagate_default' => '默认情况下，现有权限（相册-用户）<br>将被更新，并添加缺失的权限。<br>此列表中不存在的其他权限将保持不变。',
    'propagate_overwrite' => '覆盖现有权限而不是更新。<br>这也将删除此列表中不存在的所有权限。',
    'propagate_warning' => '此操作无法撤销。',
    'permission_overwritten' => '传播成功！权限已覆盖！',
    'permission_updated' => '传播成功！权限已更新！',
    'bluk_share' => '批量共享',
    'bulk_share_instr' => '选择多个相册和要共享的用户。',
    'albums' => '相册',
    'users' => '用户',
    'no_users' => '没有可选择的用户。',
    'no_albums' => '没有可选择的相册。',
    'grants' => [
        'read' => '授予读取权限',
        'original' => '授予访问原始照片的权限',
        'download' => '授予下载权限',
        'upload' => '授予上传权限',
        'edit' => '授予编辑权限',
        'move' => '授予移动、复制和合并权限',
        'delete' => '授予删除权限',
    ],
];
