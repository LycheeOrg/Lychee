<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const CAT = 'Mod Renamer';
	public const BOOL = '0|1';

	private function getConfigs(): array
	{
		return [
			[
				'key' => 'renamer_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable renamer rules',
				'details' => 'This allows you to rename files based on rules defined in the renamer module.',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 10,
				'is_expert' => false,
			],
			[
				'key' => 'renamer_enforced',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enforce renamer rules',
				'details' => 'Rules defined by the owner of the Lychee instance will be applied regardless of user settings.',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 11,
				'is_expert' => false,
			],
			[
				'key' => 'renamer_enforced_before',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enforce renamer rules before user rules',
				'details' => 'Rules defined by the owner of the Lychee instance will be applied before the rules of the user.',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 12,
				'is_expert' => false,
			],
			[
				'key' => 'renamer_enforced_after',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enforce renamer rules after user rules',
				'details' => 'Rules defined by the owner of the Lychee instance will be applied after the rules of the user.',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 13,
				'is_expert' => false,
			],
			[
				'key' => 'renamer_photo_title_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable renamer rules on photo titles at import/upload',
				'details' => '',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 14,
				'is_expert' => false,
			],
			[
				'key' => 'renamer_album_title_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable renamer rules on album titles at creation',
				'details' => '',
				'level' => 1,
				'not_on_docker' => false,
				'order' => 15,
				'is_expert' => false,
			],
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// No mercy
		$this->down();

		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'Renamer',
				'description' => 'This module allows you to automatically apply user-defined renaming rules during upload or import via sync.<br>
				<span class="pi pi-exclamation-triangle text-orange-500"></span> Renaming is likely going to prevent fast duplicate detection on photos/albums that have been renamed via sync.',
				'order' => 23,
			],
		]);

		Schema::create('renamer_rules', function (Blueprint $table) {
			$table->id();
			$table->unsignedInteger('order')->nullable(false);
			$table->unsignedInteger('owner_id');
			$table->string('rule', 200)->nullable(false);
			$table->text('description')->nullable(false);
			$table->text('needle')->nullable(false);
			$table->text('replacement')->nullable(false);
			$table->string('mode')->nullable(false);
			$table->boolean('is_enabled')->nullable(false)->default(true);

			$table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
		});

		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
		DB::table('config_categories')->where('cat', self::CAT)->delete();

		Schema::dropIfExists('renamer_rules');
	}
};
