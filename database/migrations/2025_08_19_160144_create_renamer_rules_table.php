<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {

	public const CAT = 'Mod Pro';
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
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// No mercy
		$this->down();

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

			$table->unique(['owner_id', 'order']);
			$table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
		});

		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();

		Schema::dropIfExists('renamer_rules');
	}
};
