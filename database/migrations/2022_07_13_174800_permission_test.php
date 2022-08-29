<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class PermissionTest extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		$is_public = Str::endsWith(getcwd(), 'public');
		if ($is_public) {
			chdir('..');
		}
		Artisan::call('lychee:fix-permissions', ['--dry-run' => 1]);
		if ($is_public) {
			chdir('public');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
	}
}
