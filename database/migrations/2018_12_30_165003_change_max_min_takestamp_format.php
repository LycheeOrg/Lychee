<?php

use App\Album;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMaxMinTakestampFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    // rename
	    Schema::table('albums', function (Blueprint $table) {
		    $table->renameColumn('min_takestamp', 'min_takestamp_temp');
		    $table->renameColumn('max_takestamp', 'max_takestamp_temp');
	    });

	    // create field.
	    Schema::table('albums', function (Blueprint $table) {
		    $table->timestamp('min_takestamp')->nullable()->after('min_takestamp_temp');
		    $table->timestamp('max_takestamp')->nullable()->after('max_takestamp_temp');
	    });

	    // copy
	    $photos = Album::all();
	    foreach ($photos as $album) {
		    $album->min_takestamp = ($album->min_takestamp_temp == 0 || $album->min_takestamp_temp == null) ? null : date("Y-m-d H:i:s", $album->min_takestamp_temp);
		    $album->max_takestamp = ($album->max_takestamp_temp == 0 || $album->max_takestamp_temp == null) ? null : date("Y-m-d H:i:s", $album->max_takestamp_temp);
		    $album->save();
	    }

	    // delete
	    Schema::table('albums', function (Blueprint $table) {
		    $table->dropColumn('min_takestamp_temp');
		    $table->dropColumn('max_takestamp_temp');
	    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

		// create
	    Schema::table('albums', function (Blueprint $table) {
		    $table->integer('min_takestamp_temp')->nullable()->after('min_takestamp');
		    $table->integer('max_takestamp_temp')->nullable()->after('max_takestamp');
	    });

	    // copy
	    $albums = Album::all();
	    foreach ($albums as $album) {
		    $album->min_takestamp_temp = ($album->min_takestamp == null) ? 0 : $album->min_takestamp->timestamp;
		    $album->max_takestamp_temp = ($album->max_takestamp == null) ? 0 : $album->max_takestamp->timestamp;
		    $album->save();
	    }

	    // delete
	    Schema::table('albums', function (Blueprint $table) {
		    $table->dropColumn('min_takestamp');
		    $table->dropColumn('max_takestamp');
	    });

	    // rename
	    Schema::table('albums', function (Blueprint $table) {
		    $table->renameColumn('min_takestamp_temp', 'min_takestamp');
		    $table->renameColumn('max_takestamp_temp', 'max_takestamp');
	    });

    }
}
