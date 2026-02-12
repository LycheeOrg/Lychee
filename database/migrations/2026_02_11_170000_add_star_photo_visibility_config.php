<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const GALLERY = 'Gallery';
	public const VALUES = 'anonymous|authenticated';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photos_star_visibility',
				'value' => 'authenticated',
				'cat' => self::GALLERY,
				'type_range' => self::VALUES,
				'description' => 'Who can see and star photos.',
				'details' => 'Option to configure who can see star flag on a photo and star/unstar.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 10,
				'not_on_docker' => false,
				'level' => 2,
			],
		];
	}

	/**
	 * Run the migrations.
	 *
	 *  @codeCoverageIgnore Tested but before CI run...
	 */
	final public function up(): void
	{
		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @codeCoverageIgnore Tested but after CI run...
	 */
	final public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
	}
};
