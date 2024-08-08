<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| as the size rules. Feel free to tweak each of these messages here.
	|
	*/

	'accepted' => ':attribute は承認される必要があります。',
	'active_url' => ':attribute は適切な URL ではありません。',
	'after' => ':attribute は :date より後の日付である必要があります。',
	'after_or_equal' => ':attribute は :date またはその後の日付である必要があります。',
	'alpha' => ':attribute には文字のみを含めることができます。',
	'alpha_dash' => ':attribute には、文字、数字、ダッシュのみを含めることができます。',
	'alpha_num' => ':attribute には文字と数字のみを含めることができます。',
	'array' => ':attribute は配列である必要があります。',
	'before' => ':attribute は :date より前の日付である必要があります。',
	'before_or_equal' => ':attribute は :date またはその前の日付である必要があります。',
	'between' => [
		'numeric' => ':attribute は :min と :max の間になければなりません。',
		'file' => ':attribute は :min kB から :max kB までの範囲でなければなりません。',
		'string' => ':attribute は :min 文字から :max 文字までの範囲でなければなりません。',
		'array' => ':attribute には :min から :max までの項目が必要です。',
	],
	'boolean' => ':attribute フィールドは true または false である必要があります。',
	'confirmed' => ':attribute の確認が一致しません。',
	'date' => ':attribute は有効な日付ではありません。',
	'date_format' => ':attribute が形式 :format と一致しません。',
	'different' => ':attribute と :other は異なる必要があります。',
	'digits' => ':attribute は :digits 桁でなければなりません。',
	'digits_between' => ':attribute は :min 桁から :max 桁までの範囲でなければなりません。',
	'dimensions' => ':attribute の画像サイズが無効です。',
	'distinct' => ':attribute フィールドに重複した値があります。',
	'email' => ':attribute は有効なメールアドレスである必要があります。',
	'exists' => '選択された:attributeは無効です。',
	'file' => ':attribute はファイルである必要があります。',
	'filled' => ':attribute フィールドには値が必要です。',
	'gt' => [
		'numeric' => ':attribute は :value より大きくなければなりません。',
		'file' => ':attribute は :value kB より大きくなければなりません。',
		'string' => ':attribute は :value 文字より大きくなければなりません。',
		'array' => ':attribute には :value より多い項目が必要です。',
	],
	'gte' => [
		'numeric' => ':attribute は :value 以上である必要があります。',
		'file' => ':attribute は :value kB 以上である必要があります。',
		'string' => ':attribute は :value 文字以上である必要があります。',
		'array' => ':attribute には :value 項目以上が必要です。',
	],
	'image' => ':attribute は画像である必要があります。',
	'in' => '選択された:attributeは無効です。',
	'in_array' => ':attribute フィールドは :other に存在しません。',
	'integer' => ':attribute は整数である必要があります。',
	'ip' => ':attribute は有効な IP アドレスである必要があります。',
	'ipv4' => ':attribute は有効な IPv4 アドレスである必要があります。',
	'ipv6' => ':attribute は有効な IPv6 アドレスである必要があります。',
	'json' => ':attribute は有効な JSON 文字列である必要があります。',
	'lt' => [
		'numeric' => ':attribute は :value より小さくなければなりません。',
		'file' => ':attribute は :value kB 未満である必要があります。',
		'string' => ':attribute は :value 文字未満である必要があります。',
		'array' => ':attribute には :value 項目未満が必要です。',
	],
	'lte' => [
		'numeric' => ':attribute は :value 以下でなければなりません。',
		'file' => ':attribute は :value kB 以下である必要があります。',
		'string' => ':attribute は :value 文字以下でなければなりません。',
		'array' => ':attribute には :value 個を超える項目を含めることはできません。',
	],
	'max' => [
		'numeric' => ':attribute は :max より大きくすることはできません。',
		'file' => ':attribute は :max kB を超えることはできません。',
		'string' => ':attribute は :max 文字数を超えることはできません。',
		'array' => ':attribute には :max 個を超える項目を含めることはできません。',
	],
	'mimes' => ':attribute は、 :values タイプのファイルである必要があります。',
	'mimetypes' => ':attribute は、 :values タイプファイルである必要があります。',
	'min' => [
		'numeric' => ':attribute は少なくとも :min である必要があります。',
		'file' => ':attribute は少なくとも :min kB である必要があります。',
		'string' => ':attribute は少なくとも :min 文字である必要があります。',
		'array' => ':attribute には少なくとも :min 個の項目が必要です。',
	],
	'not_in' => '選択された:attributeは無効です。',
	'not_regex' => ':attribute 形式が無効です。',
	'numeric' => ':attribute は数値である必要があります。',
	'present' => ':attribute フィールドが存在する必要があります。',
	'regex' => ':attribute 形式が無効です。',
	'required' => ':attribute フィールドは必須です。',
	'required_if' => ':other が :value の場合、:attribute フィールドは必須です。',
	'required_unless' => ':other が :values に含まれていない限り、:attribute フィールドは必須です。',
	'required_with' => ':values が存在する場合、:attribute フィールドは必須です。',
	'required_with_all' => ':values が存在する場合、:attribute フィールドは必須です。',
	'required_without' => ':values が存在しない場合は、:attribute フィールドが必須です。',
	'required_without_all' => ':values がいずれも存在しない場合は、:attribute フィールドが必須です。',
	'same' => ':attribute と :other は一致する必要があります。',
	'size' => [
		'numeric' => ':attribute は :size である必要があります。',
		'file' => ':attribute は :size kB である必要があります。',
		'string' => ':attribute は :size 文字である必要があります。',
		'array' => ':attribute には :size 項目が含まれている必要があります。',
	],
	'string' => ':attribute は文字列である必要があります。',
	'timezone' => ':attribute は有効なゾーンである必要があります。',
	'unique' => ':attribute はすでに使用されています。',
	'uploaded' => ':attribute のアップロードに失敗しました。',
	'url' => ':attribute 形式が無効です。',

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap attribute place-holders
	| with something more reader friendly such as E-Mail Address instead
	| of "email". This simply helps us make messages a little cleaner.
	|
	*/

	'attributes' => [],
];
