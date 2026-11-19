<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => '設定',
    'small_screen' => 'このページをより快適にご利用いただくため、<br />大きな画面での使用をお勧めします。',
    'tabs' => [
        'basic' => '基本',
        'all_settings' => 'すべての設定',
    ],
    'toasts' => [
        'change_saved' => '変更を保存しました！',
        'details' => 'リクエストに従って設定が変更されました',
        'error' => 'エラー！',
        'error_load_css' => 'dist/user.css を読み込めませんでした',
        'error_load_js' => 'dist/custom.js を読み込めませんでした',
        'error_save_css' => 'CSS を保存できませんでした',
        'error_save_js' => 'JS を保存できませんでした',
        'thank_you' => 'ご支援ありがとうございます。',
        'reload' => 'すべての機能を利用するにはページを再読み込みしてください。',
    ],
    'system' => [
        'header' => 'システム',
        'use_dark_mode' => 'Lychee でダークモードを使用',
        'language' => 'Lychee で使用する言語',
        'nsfw_album_visibility' => 'センシティブなアルバムをデフォルトで表示する。',
        'nsfw_album_explanation' => 'アルバムが公開されている場合でもアクセスは可能で、表示から隠されるだけです。<b><kbd>H</kbd> を押すと表示できます</b>。',
        'cache_enabled' => 'レスポンスのキャッシュを有効にする。',
        'cache_enabled_details' => 'Lychee の応答時間が大幅に短縮されます。<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>パスワード保護されたアルバムを使用している場合は、有効にしないでください。',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => '限定機能を入手し、Lychee の開発を支援しましょう。<a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE エディション</a>をアンロックしてください。',
        'preview' => 'Lychee SE 機能のプレビューを有効にする',
        'hide_call4action' => 'この Lychee SE 登録フォームを非表示にする。現在の Lychee で満足しています。 :)',
        'hide_warning' => '有効にすると、ライセンスキーを登録する唯一の方法は上の「その他」タブからとなります。変更はページの再読み込み時に適用されます。',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Dropbox から写真をインポートするには、Dropbox のウェブサイトで取得した有効な drop-ins アプリキーが必要です。',
        'api_key' => 'Dropbox API キー',
        'set_key' => 'Dropbox キーを設定',
    ],
    'gallery' => [
        'header' => 'ギャラリー',
        'photo_order_column' => '写真の並べ替えに使用するデフォルトの列',
        'photo_order_direction' => '写真の並べ替えに使用するデフォルトの順序',
        'album_order_column' => 'アルバムの並べ替えに使用するデフォルトの列',
        'album_order_direction' => 'アルバムの並べ替えに使用するデフォルトの順序',
        'aspect_ratio' => 'アルバムサムネイルのデフォルトのアスペクト比',
        'photo_layout' => '写真のレイアウト',
        'album_decoration' => 'アルバムカバーに装飾を表示（サブアルバム数および/または写真数）',
        'album_decoration_direction' => 'アルバムの装飾を水平または垂直に配置',
        'photo_overlay' => 'デフォルトの画像オーバーレイ情報',
        'album_date_scrubber_enabled' => 'アルバム表示で日付スクラバーを表示',
        'rounded_corners_enabled' => '写真とアルバムのサムネイルの角を丸くする',
        'album_border_enabled' => '写真とアルバムのサムネイルに枠線を表示',
        'photo_ken_burns_on_hover_enabled' => '写真のホバー時にケン・バーンズ効果を有効にする',
        'photo_ken_burns_on_hover_scale' => 'ケン・バーンズのホバー時のズーム量（%）',
        'photo_ken_burns_on_hover_duration' => 'ケン・バーンズのホバー時のズーム時間（秒）',
        'license_default' => 'アルバムに使用するデフォルトのライセンス',
        'license_help' => '選択に迷っていますか？',
    ],
    'geolocation' => [
        'header' => '位置情報',
        'map_display' => 'GPS 座標に基づいて地図を表示',
        'map_display_public' => '匿名ユーザーに地図へのアクセスを許可',
        'map_provider' => '地図プロバイダーを指定',
        'map_include_subalbums' => 'サブアルバムの写真を地図に含める',
        'location_decoding' => 'GPS 位置情報のデコードを使用',
        'location_show' => 'GPS 座標から抽出した場所を表示',
        'location_show_public' => '匿名ユーザーが GPS 座標から抽出した場所にアクセスできる',
        'gps_coordinate_display' => 'GPS 座標を表示',
        'gps_coordinate_display_public' => '匿名ユーザーに GPS 座標へのアクセスを許可',
    ],
    'cssjs' => [
        'header' => 'カスタム CSS & JS',
        'change_css' => 'CSS を変更',
        'change_js' => 'JS を変更',
    ],
    'all' => [
        'old_setting_style' => '旧設定スタイル',
        'expert_settings' => 'エキスパートモード',
        'change_detected' => '一部の設定が変更されました。',
        'save' => '保存',
        'back_to_settings' => 'グループ化された設定に戻る',
    ],
    'tool_option' => [
        'disabled' => '無効',
        'enabled' => '有効',
        'discover' => '自動検出',
    ],
    'groups' => [
        'general' => '一般',
        'system' => 'システム',
        'modules' => 'モジュール',
        'advanced' => '詳細',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => '管理ダッシュボードを使用',
            'help' => '入れ子になった管理サブメニューを、新しい管理ダッシュボードページへの単一のリンクに置き換えます。',
        ],
    ],
    'password_field' => [
        'set' => '設定済み',
        'not_set' => '未設定',
        'placeholder' => '新しいパスワード',
        'clear' => 'クリア',
    ],
];
