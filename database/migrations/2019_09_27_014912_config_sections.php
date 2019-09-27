<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class ConfigSections extends Migration
{
	private $admin = [1, 2, 3, 4, 10, 84, 87, 88, 92];
	private $gallery = [5, 6, 7, 8, 14, 15, 16, 26, 31, 36, 104, 106];
	private $image = [9, 12, 27, 28, 29, 30, 32, 37, 71, 72, 73];
	private $smart = [100, 101, 102];

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Configs::whereIn('id', $this->admin)->update(['cat' => 'Admin']);
			Configs::whereIn('id', $this->gallery)->update(['cat' => 'Gallery']);
			Configs::whereIn('id', $this->image)->update(['cat' => 'Image Processing']);
			Configs::whereIn('id', $this->smart)->update(['cat' => 'Smart Albums']);
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
			Configs::whereIn('id', $this->admin)->update(['cat' => 'config']);
			Configs::whereIn('id', $this->gallery)->update(['cat' => 'config']);
			Configs::whereIn('id', $this->image)->update(['cat' => 'config']);
			Configs::whereIn('id', $this->smart)->update(['cat' => 'config']);
		}
	}
}
