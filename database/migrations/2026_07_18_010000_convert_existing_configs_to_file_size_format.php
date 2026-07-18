<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Enum\ConfigType;
use App\Facades\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Converts pre-existing byte-size settings, previously stored as plain
 * integers, to the new human-readable "512MB" / "10GB" string format
 * used by ConfigType::FILE_SIZE.
 *
 * - `upload_chunk_size` was already stored in bytes.
 * - `default_user_quota` was stored in KB, so it is converted to bytes first.
 *
 * Both used `0` as a sentinel ("auto" / "disabled" respectively); this is
 * preserved as `"0 B"`, which `Helpers::humanSizeToBytes()` parses back to 0.
 */
return new class() extends Migration {
	public const INT = 'int';

	/**
	 * @return array<int,array{key:string,unit_size_in_bytes:int,description:string,details:string}>
	 */
	private function keys(): array
	{
		return [
			[
				'key' => 'upload_chunk_size',
				'unit_size_in_bytes' => 1,
				'description' => 'Size of chunks when uploading.',
				'details' => 'Format: a number followed by B, KB, MB, GB or TB (e.g. "10MB"). Use "0 B" for auto.',
			],
			[
				'key' => 'default_user_quota',
				'unit_size_in_bytes' => 1024,
				'description' => 'Default space quota for new users.',
				'details' => 'Format: a number followed by B, KB, MB, GB or TB (e.g. "10GB"). Use "0 B" to disable the quota.',
			],
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		foreach ($this->keys() as ['key' => $key, 'unit_size_in_bytes' => $unit_size_in_bytes, 'description' => $description, 'details' => $details]) {
			$raw_value = DB::table('configs')->where('key', '=', $key)->value('value');
			if ($raw_value === null) {
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$bytes = intval($raw_value) * $unit_size_in_bytes;

			DB::table('configs')->where('key', '=', $key)->update([
				'value' => Helpers::getSymbolByQuantity((float) $bytes),
				'type_range' => ConfigType::FILE_SIZE->value,
				'description' => $description,
				'details' => $details,
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		foreach ($this->keys() as ['key' => $key, 'unit_size_in_bytes' => $unit_size_in_bytes]) {
			$raw_value = DB::table('configs')->where('key', '=', $key)->value('value');
			if ($raw_value === null) {
				// @codeCoverageIgnoreStart
				continue;
				// @codeCoverageIgnoreEnd
			}

			$bytes = Helpers::humanSizeToBytes($raw_value);

			DB::table('configs')->where('key', '=', $key)->update([
				'value' => (string) intdiv($bytes, $unit_size_in_bytes),
				'type_range' => self::INT,
			]);
		}
	}
};
