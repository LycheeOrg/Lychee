<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Design System',
    'description' => 'v8 フロントエンドを実際に構成しているトークンとコンポーネントのライブリファレンスです。このページのすべてのスウォッチとコントロールは、現在使用されている実際の CSS カスタムプロパティと Nuxt UI コンポーネントを読み込むため、静的なモックアップのように内容が古くなることはありません。',

    'sections' => [
        'foundations' => '基礎',
        'colors' => 'カラーパレット',
        'typography' => 'タイポグラフィ',
        'buttons' => 'ボタン',
        'badges' => 'バッジとチップ',
        'forms' => 'フォームコントロール',
        'feedback' => 'フィードバックと状態',
        'radius' => '角丸と影',
    ],

    'loading' => [
        'full_page' => '全画面ローダー',
        'full_page_description' => 'ゆっくりと段階的に表示 — ビューの読み込み中、全画面の暗転の背後で使用されます。',
        'mini_spinner' => 'ミニスピナー',
        'mini_spinner_description' => '高速版で、すぐに表示 — ボタン、リスト、小さな読み込み状態にインラインで使用されます。',
    ],

    'foundations' => [
        'system' => 'コンポーネントキット',
        'system_value' => '@nuxt/ui on Tailwind CSS v4',
        'body_font' => '本文フォント',
        'body_font_value' => 'Libre Franklin（セルフホスト、アラビア語/ペルシア語は Vazirmatn）',
        'mono_font' => '等幅フォント',
        'mono_font_value' => 'システムの等幅フォントスタック (ui-monospace)',
        'icons' => 'アイコンセット',
        'icons_value' => 'Lucide（Iconify 経由、UIcon）',
        'theme' => 'テーマ',
        'theme_value' => '単一のダーク/ライトテーマを設定で切り替え — セクションごとの上書きはなし',
    ],

    'colors' => [
        'primary' => 'プライマリ — ブランドカラー、主要なアクション、リンク、アクティブ状態',
        'secondary' => 'セカンダリ — 代替アクションと選択',
        'neutral' => 'ニュートラル — テキスト、境界線、サーフェス（ライトモードは Slate、ダークモードは Zinc）',
        'success' => '成功 — 確認、肯定的な状態',
        'warning' => '警告 — 注意、操作を妨げない問題',
        'error' => 'エラー — 破壊的なアクション、失敗',
        'info' => '情報 — お知らせメッセージ',
        'resolved_from' => ':token からライブで取得',
    ],

    'typography' => [
        'display' => 'ディスプレイ',
        'heading' => '見出し',
        'body' => '本文',
        'body_sample' => 'いろはにほへと ちりぬるを — アルバムのタイトル、フォームのラベル、日常的な文章はこのフォントスタックで表示されます。',
        'mono' => '等幅',
        'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
        'emphasis' => 'テキスト強調スケール',
        'emphasis_sample' => '過去の今日に撮影された写真',
    ],

    'buttons' => [
        'intro' => 'ボタンシステムがサポートするすべての色を、アプリ全体で実際に使用されているバリエーションで表示します。',
    ],

    'forms' => [
        'input_label' => 'アルバムのタイトル',
        'input_placeholder' => '京都の夏',
        'select_label' => '並べ替え',
        'checkbox_label' => 'センシティブなアルバムを表示',
        'switch_label' => '公開アルバム',
        'switch_description' => 'リンクを知っている人は誰でもこのアルバムを閲覧できます',
        'textarea_label' => '説明',
        'textarea_placeholder' => 'このアルバムの説明を追加…',
        'radio_legend' => 'タグの一致条件',
    ],

    'feedback' => [
        'alert_info' => 'このアルバムに新しい写真が追加されました。',
        'alert_success' => '設定を保存しました。',
        'alert_warning' => 'このアルバムはリンクを知っている人なら誰でも閲覧できます。',
        'alert_error' => 'サーバーに接続できませんでした — 変更は保存されていません。',
        'empty_title' => 'まだ共有はありません',
        'empty_description' => '最初のアルバムを共有すると、共有アルバムとその権限がここに表示されます。',
    ],
];
