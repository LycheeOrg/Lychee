<?php

use Illuminate\Database\Migrations\Migration;
use function Safe\mkdir;
use function Safe\rename;
use function Safe\touch;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		if (env('LYCHEE_UPLOADS', '') !== '' && is_dir(public_path('uploads/'))) {
			// only do this if this is the default
			rename(public_path('uploads/'), storage_path('app/public/uploads'));
		}

		if (file_exists(public_path('sym/'))) {
			rename(public_path('sym/'), storage_path('app/public/sym'));
		}
		if (file_exists(public_path('dist/user.css'))) {
			rename(public_path('dist/user.css'), storage_path('app/public/user.css'));
		}
		if (!is_dir(storage_path('app/public/uploads/import'))) {
			mkdir(storage_path('app/public/uploads/import'));
		}
		if (!is_dir(storage_path('app/public/sym'))) {
			mkdir(storage_path('app/public/sym'));
		}
		if (!is_file(storage_path('app/public/user.css'))) {
			touch(storage_path('app/public/user.css'));
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		if (env('LYCHEE_UPLOADS', '') !== '') {
			// only do this if this is the default
			rename(storage_path('app/public/uploads'), public_path('uploads/'));
		}

		rename(storage_path('app/public/sym'), public_path('sym/'));
		rename(storage_path('app/public/user.css'), public_path('dist/user.css'));
	}
};
