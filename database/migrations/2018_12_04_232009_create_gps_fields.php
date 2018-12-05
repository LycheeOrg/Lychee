<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGpsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('focal');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->decimal('altitude', 10, 4)->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'altitude']);
        });
    }
}
