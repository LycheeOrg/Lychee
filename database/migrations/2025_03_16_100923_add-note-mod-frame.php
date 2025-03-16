<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'mod_frame_enabled')->update(['details' => 'Note that access to the chosen album is required to view the frame.<br><i class="pi pi-exclamation-triangle text-orange-500"></i> The button will be visible only if the condition is satisfied.']);
		DB::table('configs')->where('key', 'random_album_id')->update(['details' => 'Default album displayed, if left empty then all searchable photos will be used.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'mod_frame_enabled')->update(['details' => '1']);
		DB::table('configs')->where('key', 'random_album_id')->update(['details' => '']);
	}
};
