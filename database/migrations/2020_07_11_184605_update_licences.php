<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Update the fields.
	 *
	 * @param array{key:string,value:string,cat:string,type_range:string,confidentiality:string}[] $default_values
	 */
	private function update_fields(array &$default_values): void
	{
		foreach ($default_values as $value) {
			DB::table('configs')->updateOrInsert(
				['key' => $value['key']],
				[
					'cat' => $value['cat'],
					'type_range' => $value['type_range'],
					'confidentiality' => $value['confidentiality'],
				]
			);
		}
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		defined('LICENSE') or define('LICENSE', 'license');

		$default_values = [
			[
				'key' => 'default_license',
				'value' => 'none',
				'cat' => 'Gallery',
				'type_range' => LICENSE,
				'confidentiality' => '2',
			],
		];

		$this->update_fields($default_values);

		// Get all CC licences
		/** @var Collection<int,object{id:string,license:string}> $photos */
		$photos = DB::table('photos')->where('license', 'like', 'CC-%')->get();
		if ($photos->isEmpty()) {
			return;
		}
		foreach ($photos as $photo) {
			DB::table('photos')->where('id', '=', $photo->id)->update(['license' => $photo->license . '-4.0']);
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Get all CC licences
		/** @var Collection<int,object{id:string,license:string}> $photos */
		$photos = DB::table('photos')->where('license', 'like', 'CC-%')->get();
		if ($photos->isEmpty()) {
			return;
		}
		foreach ($photos as $photo) {
			// Delete version
			DB::table('photos')->where('id', '=', $photo->id)->update(['license' => substr($photo->license, 0, -4)]);
		}
	}
};
