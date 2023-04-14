<?php

/** @noinspection PhpUndefinedClassInspection */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('sym_links');
		Schema::create('sym_links', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('photo_id')->nullable();
			$table->string('url')->default('');
			$table->string('medium')->default('');
			$table->string('medium2x')->default('');
			$table->string('small')->default('');
			$table->string('small2x')->default('');
			$table->string('thumbUrl')->default('');
			$table->string('thumb2x')->default('');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('sym_links');
	}
};
