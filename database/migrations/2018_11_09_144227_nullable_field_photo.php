<?php

use App\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullableFieldPhoto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->integer('width')->nullable()->change();
            $table->integer('height')->nullable()->change();
            $table->char('type_temp',15);
        });

        $photos = Photo::all();
        foreach ($photos as $photo) {
            $photo->type_temp = $photo->type;
            $photo->save();
        }

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->char('type',15)->after('owner_id');
        });

        $photos = Photo::all();
        foreach ($photos as $photo)
        {
            $photo->type = $photo->type_temp;
            $photo->save();
        }

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('type_temp');
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
            $table->integer('width')->change();
            $table->integer('height')->change();
            $table->char('type_temp',15);
        });

        $photos = Photo::all();
        foreach ($photos as $photo) {
            $photo->type_temp = $photo->type;
            $photo->save();
        }

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->char('type',10)->after('owner_id');
        });

        $photos = Photo::all();
        foreach ($photos as $photo)
        {
            $photo->type = $photo->type_temp;
            $photo->save();
        }

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('type_temp');
        });

    }
}
