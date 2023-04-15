<?php

use App\Models\Photo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

return new class() extends Migration {
	/**
	 * Update the fields.
	 *
	 * @param array $default_values
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
		/** @var Collection<Photo> $photos */
		$photos = Photo::where('license', 'like', 'CC-%')->get();
		if ($photos->isEmpty()) {
			return;
		}
		foreach ($photos as $photo) {
			$photo->license = $photo->license . '-4.0';
			$photo->save();
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Get all CC licences
		/** @var Collection<Photo> $photos */
		$photos = Photo::where('license', 'like', 'CC-%')->get();
		if ($photos->isEmpty()) {
			return;
		}
		foreach ($photos as $photo) {
			// Delete version
			$photo->license = substr($photo->license, 0, -4);
			$photo->save();
		}
	}
};
