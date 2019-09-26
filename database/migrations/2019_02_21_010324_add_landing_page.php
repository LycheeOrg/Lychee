<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddLandingPage extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				['key' => 'landing_page_enable', 'value' => '0'],
				['key' => 'landing_owner', 'value' => 'John Smith'],
				['key' => 'landing_title', 'value' => 'John Smith'],
				['key' => 'landing_subtitle', 'value' => 'Cat, Dogs & Humans Photography'],
				['key' => 'landing_facebook', 'value' => 'https://www.facebook.com/JohnSmith'],
				['key' => 'landing_flickr', 'value' => 'https://www.flickr.com/JohnSmith'],
				['key' => 'landing_twitter', 'value' => 'https://www.twitter.com/JohnSmith'],
				['key' => 'landing_instagram', 'value' => 'https://instagram.com/JohnSmith'],
				['key' => 'landing_youtube', 'value' => 'https://www.youtube.com/JohnSmith'],
				['key' => 'landing_background', 'value' => 'dist/cat.jpg'],
			]);
		} else {
			echo "Table configs does not exist\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', '=', 'landing_page_enable')->delete();
			Configs::where('key', '=', 'landing_owner')->delete();
			Configs::where('key', '=', 'landing_title')->delete();
			Configs::where('key', '=', 'landing_subtitle')->delete();
			Configs::where('key', '=', 'landing_facebook')->delete();
			Configs::where('key', '=', 'landing_flickr')->delete();
			Configs::where('key', '=', 'landing_twitter')->delete();
			Configs::where('key', '=', 'landing_instagram')->delete();
			Configs::where('key', '=', 'landing_youtube')->delete();
			Configs::where('key', '=', 'landing_background')->delete();
		}
	}
}
