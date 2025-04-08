<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CREATED_AT_COL_NAME = 'created_at';
	private const DATETIME_PRECISION = 0;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('live_metrics', function (Blueprint $table) {
            $table->id();
            $table->dateTime(
				self::CREATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable();
            $table->string('visitor_id')->index();
            $table->string('action');
            $table->string('photo_id')->nullable(true);
            $table->string('album_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_metrics');
    }
};
