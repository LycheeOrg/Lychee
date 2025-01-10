<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const SQUARE = 'square';
	public const JUSTIFIED = 'justified';
	public const UNJUSTIFIED = 'unjustified';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		/** @var int $layout */
		$layout = DB::table('configs')->select('value')->where('key', '=', 'layout')->first()->value;
		DB::table('configs')->where('key', '=', 'layout')->delete();
		DB::table('configs')->insert([
			[
				'key' => 'layout',
				'value' => $this->toEnum($layout),
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::UNJUSTIFIED,
				'description' => 'Layout for pictures',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		/** @var string $layout */
		$layout = DB::table('configs')->select('value')->where('key', '=', 'layout')->first()->value;
		DB::table('configs')->where('key', '=', 'layout')->delete();
		DB::table('configs')->insert([
			[
				'key' => 'layout',
				'value' => $this->fromEnum($layout),
				'confidentiality' => 0,
				'cat' => 'Gallery',
				'type_range' => '0|1|2',
				'description' => 'Layout for pictures',
			],
		]);
	}

	private function toEnum(int $layout): string
	{
		return match ($layout) {
			0 => self::SQUARE,
			1 => self::JUSTIFIED,
			2 => self::UNJUSTIFIED,
			default => self::JUSTIFIED,
		};
	}

	private function fromEnum(string $layout): int
	{
		return match ($layout) {
			self::SQUARE => 0,
			self::JUSTIFIED => 1,
			self::UNJUSTIFIED => 2,
			default => 1,
		};
	}
};
