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
	    // create temporary field.
	    Schema::table('albums', function (Blueprint $table) {
		    $table->timestamp('min_takestamp_temp')->nullable()->after('min_takestamp');
		    $table->timestamp('max_takestamp_temp')->nullable()->after('max_takestamp');
	    });

	    // copy
	    $photos = Album::all();
	    foreach ($photos as $album) {
		    $album->min_takestamp_temp = ($album->min_takestamp == 0 || $album->min_takestamp == null) ? null : date("Y-m-d H:i:s", $album->min_takestamp);
		    $album->max_takestamp_temp = ($album->max_takestamp == 0 || $album->max_takestamp == null) ? null : date("Y-m-d H:i:s", $album->max_takestamp);
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	// rename
	    Schema::table('albums', function (Blueprint $table) {
		    $table->renameColumn('min_takestamp', 'min_takestamp_temp');
		    $table->renameColumn('max_takestamp', 'max_takestamp_temp');
	    });

		// create
	    Schema::table('albums', function (Blueprint $table) {
		    $table->integer('min_takestamp')->nullable()->after('min_takestamp_temp');
		    $table->integer('max_takestamp')->nullable()->after('max_takestamp_temp');
	    });

	    // copy
	    $albums = Album::all();
	    foreach ($albums as $album) {
		    $album->min_takestamp = ($album->min_takestamp_temp == null) ? 0 : strtotime($album->min_takestamp_temp);
		    $album->max_takestamp = ($album->max_takestamp_temp == null) ? 0 : strtotime($album->max_takestamp_temp);
		    $album->save();
	    }

	    // delete
	    Schema::table('albums', function (Blueprint $table) {
		    $table->dropColumn('min_takestamp_temp');
		    $table->dropColumn('max_takestamp_temp');
	    });
    }
}
