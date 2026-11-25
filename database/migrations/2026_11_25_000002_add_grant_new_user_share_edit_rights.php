<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Feature 118: rights given to new accounts (registration, OAuth), next to
 * `grant_new_user_upload_rights` (FR-118-19). Both start with its current
 * value, so new accounts get what they got before the split.
 */
return new class() extends Migration {
	public const CAT = 'Users Management';
	public const KEYS = ['grant_new_user_share_rights', 'grant_new_user_edit_rights'];

	public function up(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'grant_new_user_share_rights',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => true,
				'description' => 'Newly created user are allowed to share their albums.',
				'details' => '',
				'level' => 0,
				'order' => 32767,
				'is_expert' => false,
			],
			[
				'key' => 'grant_new_user_edit_rights',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => true,
				'description' => 'Newly created user are allowed to edit, move and delete their own content.',
				'details' => '',
				'level' => 0,
				'order' => 32767,
				'is_expert' => false,
			],
		]);

		$this->copyUploadSetting();
	}

	public function down(): void
	{
		DB::table('configs')->whereIn('key', self::KEYS)->delete();
	}

	/**
	 * Give both settings the current value of `grant_new_user_upload_rights`.
	 */
	public function copyUploadSetting(): void
	{
		$value = DB::table('configs')->where('key', '=', 'grant_new_user_upload_rights')->value('value') ?? '0';
		DB::table('configs')->whereIn('key', self::KEYS)->update(['value' => $value]);
	}
};
