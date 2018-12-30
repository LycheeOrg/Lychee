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
	    // rename
	    Schema::table('photos', function (Blueprint $table) {
		    $table->renameColumn('takestamp', 'takestamp_temp');
	    });

		// create field.
	    Schema::table('photos', function (Blueprint $table) {
		    $table->timestamp('takestamp')->nullable()->after('takestamp_temp');
	    });

	    // copy
	    $photos = Photo::all();
	    foreach ($photos as $photo) {
		    $photo->takestamp = ($photo->takestamp_temp == 0 || $photo->takestamp_temp == null) ? null : date("Y-m-d H:i:s", $photo->takestamp_temp);
		    $photo->save();
	    }

	    // delete
	    Schema::table('photos', function (Blueprint $table) {
		    $table->dropColumn('takestamp_temp');
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
	    Schema::table('photos', function (Blueprint $table) {
		    $table->integer('takestamp_temp')->default(0)->nullable()->after('takestamp');
	    });

	    // copy
	    $photos = Photo::all();
	    foreach ($photos as $photo) {
		    $photo->takestamp_temp = ($photo->takestamp == null) ? 0 : strtotime($photo->takestamp->timestamp);
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
}
