<?php

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
            $id = $result['id'];
            $id = substr($id,1,10);
            $id[0] = strval(intval($id[0]) % 4);
            DB::table('albums')->insert([
                ['id' => $id, 'title' => $result['title'], 'description' => $result['description'], 'public' => $result['public'], 'visible_hidden' => $result['visible']]
            ]);
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
