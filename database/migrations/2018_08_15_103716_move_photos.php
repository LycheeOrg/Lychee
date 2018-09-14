<?php

use App\Photo;
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
        if(Schema::hasTable('lychee_photos')) {
            $results = DB::table('lychee_photos')->select('*')->get();
            foreach ($results as $result) {
                $id = $result->id;
                $id = substr($id, 1, 10);
                $id[0] = strval(intval($id[0]) % 4);

                $album = $result->album;
                $album = substr($album, 1, 10);
                $album[0] = strval(intval($album[0]) % 4);

                $photo = new Photo();
                $photo->id = $id;
                $photo->title = $result->title;
                $photo->description = $result->description;
                $photo->url = $result->url;
                $photo->tags = $result->tags;
                $photo->public = $result->public;
                $photo->type = $result->type;
                $photo->width = $result->width;
                $photo->height = $result->height;
                $photo->size = $result->size;
                $photo->iso = $result->iso;
                $photo->aperture = $result->aperture;
                $photo->make = $result->make;
                $photo->model = $result->model;
                $photo->shutter = $result->shutter;
                $photo->focal = $result->focal;
                $photo->takestamp = $result->takestamp;
                $photo->star = $result->star;
                $photo->thumbUrl = $result->thumbUrl;
                $photo->album_id = $album;
                $photo->checksum = $result->checksum;
                $photo->medium = $result->medium;
                $photo->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('lychee_photos')) {
            DB::table('photos')->delete();
        }
    }
}
