<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const MOD_MAP = 'Mod Map';
	public const BOOL = '0|1';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'gps_coordinate_display',
				'value' => '1',
				'cat' => self::MOD_MAP,
				'type_range' => self::BOOL,
				'description' => 'Display the GPS coordinates.',
				'details' => 'Disabling this hides the Latitude and Longitude information from all users.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 9,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'gps_coordinate_display_public',
				'value' => '0',
				'cat' => self::MOD_MAP,
				'type_range' => self::BOOL,
				'description' => 'Allow anonymous users to access the GPS coordinates.',
				'details' => 'Disabling this hides the Latitude and Longitude information from anonymous users.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 10,
				'not_on_docker' => false,
				'level' => 0,
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
		DB::table('configs')->where('key', 'location_show')->update(['details' => 'Only the decoded location is impacted by this setting.']);
		DB::table('configs')->where('key', 'location_show_public')->update(['details' => 'Only the decoded location is impacted by this setting.']);
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
		DB::table('configs')->whereIn('key', ['location_show', 'location_show_public'])->update(['details' => '']);
	}
};
