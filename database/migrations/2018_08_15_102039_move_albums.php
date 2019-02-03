<?php

use App\Album;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveAlbums extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	if(count(Album::all()) == 0) {
		    if (Schema::hasTable(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')) {
			    $results = DB::table(env('DB_OLD_LYCHEE_PREFIX', '') . 'lychee_albums')->select('*')->get();
			    foreach ($results as $result) {
//				    $id = $result->id;
//				    $id = substr($id, 1, 10);
//				    $id[0] = strval(intval($id[0]) % 4);
				    $album = new Album();
				    $album->id = $result->id;
				    $album->title = $result->title;
				    $album->description = $result->description;
				    $album->public = $result->public;
				    $album->visible_hidden = $result->visible;
				    $album->license = $result->license;
				    $album->save();
			    }
		    } else {
			    echo env('DB_OLD_LYCHEE_PREFIX', '') . "lychee_albums does not exists!\n";
		    }
	    }
    	else
	    {
		    echo "albums is not empty.\n";
	    }



	    Album::reset_takestamp();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    if(env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK',false)) {
		    if (Schema::hasTable('lychee_albums')) {
			    DB::table('albums')->delete();
		    }
	    }
    }
}
