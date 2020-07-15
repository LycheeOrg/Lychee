<?php

use App\Configs;
use App\Photo;
use Illuminate\Database\Migrations\Migration;

class UpdateLicences extends Migration
{
	/**
	 * Update the fields.
	 *
	 * @param array $default_values
	 */
	private function update_fields(array &$default_values)
	{
		foreach ($default_values as $value) {
			Configs::updateOrCreate(['key' => $value['key']],
				[
					'cat' => $value['cat'],
					'type_range' => $value['type_range'],
					'confidentiality' => $value['confidentiality'],
				]);
		}
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
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
		$photos = Photo::where('license', 'like', 'CC-%')->get();
		if (count($photos) == 0) {
			return false;
		}
		foreach ($photos as $photo) {
			$photo->license = $photo->license . '-4.0';
			$photo->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Get all CC licences
		$photos = Photo::where('license', 'like', 'CC-%')->get();
		if (count($photos) == 0) {
			return false;
		}
		foreach ($photos as $photo) {
			// Delete version
			$photo->license = substr($photo->license, 0, -4);
			$photo->save();
		}
	}
}
