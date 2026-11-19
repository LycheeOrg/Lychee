<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contact form page
    |--------------------------------------------------------------------------
    */
    'title' => 'お問い合わせ',
    'description' => 'お気軽にご連絡ください！',
    'name_label' => 'お名前',
    'name_placeholder' => 'お名前',
    'email_label' => 'メールアドレスまたは連絡方法',
    'email_placeholder' => 'your@email.com',
    'message_label' => 'メッセージ',
    'message_placeholder' => 'ここにメッセージを入力してください...',
    'security_question_label' => 'セキュリティ質問',
    'security_answer_placeholder' => '回答',
    'sample_qa_label' => '質問と回答の例',
    'consent_label' => '次に同意します:',
    'consent_privacy_link' => 'プライバシーポリシー',
    'name_required_error' => 'お名前は必須です。',
    'email_required_error' => '連絡方法は必須です。',
    'message_min_length_error' => 'メッセージは10文字以上で入力してください。',
    'security_answer_required_error' => 'セキュリティ質問に回答してください。',
    'consent_required_error' => 'プライバシーポリシーに同意する必要があります。',
    'submit_button' => 'メッセージを送信',
    'clear_button' => 'クリア',
    'success_message' => 'メッセージをお送りいただきありがとうございます。追ってご連絡いたします。',
    'submit_error' => 'メッセージの送信中にエラーが発生しました。もう一度お試しください。',
    'rate_limit_error' => '送信回数が多すぎます。しばらくしてからもう一度お試しください。',
    'validation_error' => '以下のエラーを修正してください。',

    /*
    |--------------------------------------------------------------------------
    | Admin contact messages page
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'title' => 'お問い合わせメッセージ',
        'description' => 'お問い合わせフォームから送信されたメッセージです。',
        'no_messages' => 'お問い合わせメッセージはまだありません。',
        'name_column' => '名前',
        'email_column' => 'メールアドレス',
        'message_column' => 'メッセージ',
        'date_column' => '日付',
        'read_column' => '既読',
        'search_placeholder' => 'メッセージを検索...',
        'filter_unread' => '未読',
        'filter_read' => '既読',
        'filter_all' => 'すべて',
        'mark_read' => '既読にする',
        'mark_unread' => '未読にする',
        'delete_confirm_header' => 'メッセージを削除',
        'delete_confirm_message' => '":name" からのメッセージを削除してもよろしいですか？',
        'delete_warning' => 'この操作は元に戻せません。',
        'delete_success' => 'メッセージを削除しました。',
        'delete_error' => 'メッセージの削除に失敗しました。',
        'update_error' => 'メッセージの更新に失敗しました。',
        'cancel' => 'キャンセル',
        'delete' => '削除',
    ],
];
