<?php

use App\Photo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTakestampFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		// create temporary field.
	    Schema::table('photos', function (Blueprint $table) {
		    $table->timestamp('takestamp_temp')->nullable()->after('takestamp');
	    });

	    // copy
	    $photos = Photo::all();
	    foreach ($photos as $photo) {
		    $photo->takestamp_temp = ($photo->takestamp == 0 || $photo->takestamp == null) ? null : date("Y-m-d H:i:s", $photo->takestamp);
		    $photo->save();
	    }

	    // delete
	    Schema::table('photos', function (Blueprint $table) {
		    $table->dropColumn('takestamp');
	    });

	    // rename
	    Schema::table('photos', function (Blueprint $table) {
		    $table->renameColumn('takestamp_temp', 'takestamp');
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
	    Schema::table('photos', function (Blueprint $table) {
		    $table->renameColumn('takestamp', 'takestamp_temp');
	    });

		// create
	    Schema::table('photos', function (Blueprint $table) {
		    $table->integer('takestamp')->nullable()->after('takestamp_temp');
	    });

	    // copy
	    $photos = Photo::all();
	    foreach ($photos as $photo) {
		    $photo->takestamp = ($photo->takestamp_temp == null) ? 0 : strtotime($photo->takestamp_temp);
		    $photo->save();
	    }

	    // delete
	    Schema::table('photos', function (Blueprint $table) {
		    $table->dropColumn('takestamp_temp');
	    });
    }
}
