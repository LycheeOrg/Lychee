<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Jobs page
	|--------------------------------------------------------------------------
	*/
	'title' => '相册',

	'smart_albums' => '智能相册',
	'albums' => '相册',
	'root' => '相册',
	'favourites' => 'Favourites',

	'original' => '原图',
	'medium' => '中等',
	'medium_hidpi' => '中等高清',
	'small' => '缩略图',
	'small_hidpi' => '高清缩略图',
	'thumb' => '方形缩略图',
	'thumb_hidpi' => '高清方形缩略图',
	'placeholder' => '低质量图片占位符',
	'thumbnail' => '照片缩略图',
	'live_video' => '实况照片视频部分',

	'camera_data' => '相机日期',
	'album_reserved' => '所有权利保留',

	'map' => [
		'error_gpx' => '加载 GPX 文件出错',
		'osm_contributors' => 'OpenStreetMap 贡献者',
	],

	'search' => [
		'title' => '搜索',
		'no_results' => '没有找到匹配的内容。',
		'searchbox' => '搜索…',
		'minimum_chars' => '至少需要 %s 个字符。',
		'photos' => '照片（%s）',
		'albums' => '相册（%s）',
	],

	'smart_album' => [
		'unsorted' => '未分类',
		'starred' => '已标星',
		'recent' => '最近',
		'public' => '公开',
		'on_this_day' => '历史上的今天',
	],

	'layout' => [
		'squares' => '方形缩略图',
		'justified' => '等比例对齐',
		'masonry' => '等比例瀑布流',
		'grid' => '等比例网格',
	],

	'overlay' => [
		'none' => '无',
		'exif' => 'EXIF 数据',
		'description' => '描述',
		'date' => '拍摄日期',
	],

	'timeline' => [
		'title' => 'Timelime',
		'load_previous' => 'Load previous',
		'default' => '默认',
		'disabled' => '禁用',
		'year' => '年',
		'month' => '月',
		'day' => '日',
		'hour' => '时',
	],

	'album' => [
		'header_albums' => '相册',
		'header_photos' => '照片',
		'no_results' => '这里什么都没有',
		'upload' => '上传照片',

		'tabs' => [
			'about' => '关于相册',
			'share' => '分享相册',
			'move' => '移动相册',
			'danger' => '危险操作',
		],

		'hero' => [
			'created' => '创建时间',
			'copyright' => '版权',
			'subalbums' => '子相册',
			'images' => '照片',
			'download' => '下载相册',
			'share' => '分享相册',
			'stats_only_se' => '统计功能仅在支持者版本中可用',
		],

		'stats' => [
			'number_of_visits' => 'Number of visits',
			'number_of_downloads' => 'Number of downloads',
			'number_of_shares' => 'Number of shares',
			'lens' => '镜头',
			'shutter' => '快门速度',
			'iso' => 'ISO',
			'model' => '型号',
			'aperture' => '光圈',
			'no_data' => '无数据',
		],

		'properties' => [
			'title' => '标题',
			'description' => '描述',
			'photo_ordering' => '照片排序方式',
			'children_ordering' => '相册排序方式',
			'asc/desc' => '升序/降序',
			'header' => '设置相册封面',
			'compact_header' => '使用紧凑封面',
			'license' => '设置许可证',
			'copyright' => '设置版权',
			'aspect_ratio' => '设置相册缩略图比例',
			'album_timeline' => '设置相册时间线模式',
			'photo_timeline' => '设置照片时间线模式',
			'layout' => '设置照片布局',
			'show_tags' => '设置要显示的标签',
			'tags_required' => '标签为必填项。',
		],
	],

	'photo' => [
		'actions' => [
			'star' => '标星',
			'unstar' => '取消标星',
			'set_album_header' => '设为相册页眉图片',
			'move' => '移动',
			'delete' => '删除',
			'header_set' => '已设为页眉图片',
		],

		'details' => [
			'exif_data' => 'EXIF data',
			'about' => '关于',
			'basics' => '基本信息',
			'title' => '标题',
			'uploaded' => '上传时间',
			'description' => '描述',
			'license' => '许可证',
			'reuse' => '重用',
			'latitude' => '纬度',
			'longitude' => '经度',
			'altitude' => '海拔',
			'location' => '位置',
			'image' => '图片',
			'video' => '视频',
			'size' => '大小',
			'format' => '格式',
			'resolution' => '分辨率',
			'duration' => '时长',
			'fps' => '帧率',
			'tags' => '标签',
			'camera' => '相机',
			'captured' => '拍摄时间',
			'make' => '制造商',
			'type' => '类型/型号',
			'lens' => '镜头',
			'shutter' => '快门速度',
			'aperture' => '光圈',
			'focal' => '焦距',
			'iso' => 'ISO %s',
			'stats' => [
				'header' => 'Statistics',
				'number_of_visits' => 'Number of visits',
				'number_of_downloads' => 'Number of downloads',
				'number_of_shares' => 'Number of shares',
				'number_of_favourites' => 'Number of favourites',
			],
		],

		'edit' => [
			'set_title' => '设置标题',
			'set_description' => '设置描述',
			'set_license' => '设置许可证',
			'no_tags' => '无标签',
			'set_tags' => '设置标签',
			'set_created_at' => '设置上传日期',
			'set_taken_at' => '设置拍摄日期',
			'set_taken_at_info' => '设置后，将显示星号 %s 表示此日期不是原始 EXIF 日期。<br>取消选中复选框并保存以重置为原始日期。',
		],
	],

	'nsfw' => [
		'header' => '敏感内容',
		'description' => '此相册包含敏感内容，可能会令某些人感到不适。',
		'consent' => '点击确认查看。',
	],

	'menus' => [
		'star' => '标星',
		'unstar' => '取消标星',
		'star_all' => '标星所选',
		'unstar_all' => '取消标星所选',
		'tag' => '标签',
		'tag_all' => '为所选添加标签',
		'set_cover' => '设为相册封面',
		'remove_header' => '移除相册页眉图片',
		'set_header' => '设置相册页眉图片',
		'copy_to' => '复制到…',
		'copy_all_to' => '复制所选到…',
		'rename' => '重命名',
		'move' => '移动',
		'move_all' => '移动所选',
		'delete' => '删除',
		'delete_all' => '删除所选',
		'download' => '下载',
		'download_all' => '下载所选',
		'merge' => '合并',
		'merge_all' => '合并所选',

		'upload_photo' => '上传照片',
		'import_link' => '从链接导入',
		'import_dropbox' => '从 Dropbox 导入',
		'new_album' => '新建相册',
		'new_tag_album' => '新建标签相册',
		'upload_track' => '上传轨迹',
		'delete_track' => '删除轨迹',
	],

	'sort' => [
		'photo_select_1' => '上传时间',
		'photo_select_2' => '拍摄日期',
		'photo_select_3' => '标题',
		'photo_select_4' => '描述',
		'photo_select_6' => '标星',
		'photo_select_7' => '照片格式',
		'ascending' => '升序',
		'descending' => '降序',
		'album_select_1' => '创建时间',
		'album_select_2' => '标题',
		'album_select_3' => '描述',
		'album_select_5' => '最新拍摄日期',
		'album_select_6' => '最早拍摄日期',
	],

	'albums_protection' => [
		'private' => '私密',
		'public' => '公开',
		'inherit_from_parent' => '继承自父级',
	],
];