<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MovePhotos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $results = DB::table('lychee_photos')->select('*')->get();
        foreach ($results as $result)
        {
            $id = $result['id'];
            $id = substr($id,1,10);
            $id[0] = strval(intval($id[0]) % 4);

            $album = $result['id'];
            $album = substr($album,1,10);
            $album[0] = strval(intval($album[0]) % 4);
            DB::table('photos')->insert([
                ['id' => $id,
                'title' => $result['title'],
                'description' => $result['description'],
                'url' => $result['url'],
                'tags' => $result['tags'],
                'public' => $result['public'],
                'type' => $result['type'],
                'width' => $result['width'],
                'height' => $result['height'],
                'size' => $result['size'],
                'iso' => $result['iso'],
                'aperture' => $result['aperture'],
                'make' => $result['make'],
                'model' => $result['model'],
                'shutter' => $result['shutter'],
                'focal' => $result['focal'],
                'takestamp' => $result['takestamp'],
                'star' => $result['star'],
                'thumbUrl' => $result['thumbUrl'],
                'album_id' => $album,
                'checksum' => $result['checksum'],
                'medium' => $result['medium']]
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
        DB::table('photos')->delete();
    }
}
