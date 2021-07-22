<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColorsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('colors', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedTinyInteger('r')->nullable();
			$table->unsignedTinyInteger('g')->nullable();
			$table->unsignedTinyInteger('b')->nullable();
			$table->foreignId('photo_id')->constrained();
			$table->unsignedTinyInteger('is_main')->default(0);
			$table->timestamps();
			$table->index(['r', 'g', 'b']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('colors');
	}
}
