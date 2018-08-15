<?php

use App\Album;
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
        $results = DB::table('lychee_albums')->select('*')->get();
        foreach ($results as $result)
        {
            $id = $result->id;
            $id = substr($id,1,10);
            $id[0] = strval(intval($id[0]) % 4);
            $album = new Album();
            $album->id = $id;
            $album->title = $result->title;
            $album->description = $results->description;
            $album->public = $result->public;
            $album->visible_hidden = $result->visible;
            $album->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('albums')->delete();
    }
}
